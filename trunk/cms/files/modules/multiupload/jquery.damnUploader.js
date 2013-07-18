/**
 * jQuery-плагин, облегчающий загрузку файлов на сервер.
 *
 * Принцип работы: инициируется для input type="file", либо для контейнера, куда можно будет
 * перетаскивать файлы, однако и в первом случае можно указать контейнер, который также
 * будет принимать перетаскиваемые файлы помимо стандартного поля выбора.
 * Если загрузка файлов через file API невозможна, то при вызове метода начала загрузки, просто
 * будет инициирована отправка формы, содержащей поле выбора.
 *
 * Данное расширение также добавляет свойства в стандартный jQuery-объект $.support,
 * позволяющие проверить степень поддержки браузером File API:
 * $.support.fileSelecting - возможность выбора и загрузки файлов через File API
 * $.support.fileReading   - возможность прочитать содержимое файла на стороне клиента
 * $.support.fileSending   - возможность отправки файла при помощи FormData (как рекомендует W3C),
 *                           однако, если содержит false, загрузка будет выполнена при помощи
 *                           ручной формировки тела запроса
 * $.support.uploadControl - возможность следить за процессом загрузки (индикация выполнения)
 *
 *
 * **********************
 * ПРИМЕР ИСПОЛЬЗОВАНИЯ:
 * $("input[type='file']").damnUploader({
 *     url: './serverLogic.php',
 *     dropBox: $("#drop-files-here"),
 *     onAllComplete :function() {
 *         alert('ready!');
 *     }
 * });
 *
 * **********************
 * ПРИНИМАЕМЫЕ ПАРАМЕТРЫ (в скобках - значения по умолч.):
 *
 * url       - адрес, куда будут отправляться файлы ('upload.php')
 * multiple  - возможность выбора нескольких файлов (true)
 * fieldName - имитация имени поля с файлом, кторое будет ключом в $_FILES, если используется PHP ('file')
 * dropping  - вкл./выключить drag'n'drop файлов. Имеет смысл, если передается параметр dropBox (false)
 * dropBox   - jQuery-набор или селектор, содержащий контейнер, на который можно перетаскивать файлы (null)
 * limit     - максимальное допустимое кол-во файлов в очереди, если параметр multiple включен (false - неограниченно)
 *
 * **********************
 * ОБРАБОТЧИКИ СОБЫТИЙ (в скобках - параметры, передаваемые в функцию обратного вызова):
 *
 * onSelect(file - встроенный объект File)
 * вызывается при выборе файла, если выбирается сразу несколько,
 * то для каждого вызывается отдельно. Если функция возвращает false, то файл не добавляется в очередь
 * автоматически, благодаря чему можно получить контроль над добавлением файлов, назначая каждому
 * свои обработчики событий onComplete и onProgress (см. метод addItem)
 *
 * onLimitExceeded ()
 * вызывается, если превышен лимит, установленный параметром limit
 *
 * onAllComplete ()
 * вызывается, когда вся очередь загружена
 *
 * **********************
 * МЕТОДЫ.
 *
 * // Пример вызова:
 * var myUploader = $("input[type='file']").damnUploader({
 *     url: './serverLogic.php'
 * });
 * myUploader.damnUploader('addItem', uploadItem);
 * // здесь вызывается метод addItem, который добавляет в очередь специально подготовленный объект для загрузки
 *
 * ОПИСАНИЕ МЕТОДОВ:
 *
 * damnUploader('addItem', uploadItem)
 * добавляет в очередь специально подготовленный объект для загрузки,
 * содержащий встроенный объект File и функции обратного вызова (необязательно).
 * Метод возвращает уникальный id, присвоенный данному объекту (по которому можно,
 * например, отменить загрузку конкретного файла).
 * В следующем примере перехватывается стандартное добавление файла в очередь и создается собственный объект загрузки:
 * $("input[type='file']").damnUploader({
 *     onSelect: function(file) {
 *         var uploadId = this.damnUploader('addItem', {
 *             file: file,
 *             onProgress: function(percents) { .. Some code, updating progress info .. },
 *             onComplete: function(successfully, data, errorCode) {
 *                 if (successfully) {
 *                     alert('Файл '+file.name+' загружен, полученные данные: '+data);
 *                 } else {
 *                     alert('Ошибка при загрузке. Код ошибки: '+errorCode); // errorCode содержит код HTTP-ответа, либо 0 при проблеме с соединением
 *                 }
 *             }
 *         });
 *         return false; // отменить стандартную обработку выбора файла
 *     }
 * });
 *
 * damnUploader('startUpload')
 * начать загрузку файлов
 *
 * damnUploader('itemsCount')
 * возвращает кол-во файлов в очереди
 *
 * damnUploader('cancelAll')
 * остановить все текущие загрузки и удалить все файлы из очереди
 *
 * damnUploader('cancel', queueId)
 * отменяет загрузку для файла queueId (queueId возвращается методом addItem)
 *
 * damnUploader('setParam', paramsArray)
 * изменить один, или несколько параметров. Например:
 * myUploader.setParam({
 *     url: 'anotherWay.php'
 * });
 */

 
(function($) {
	
    // defining compatibility of upload control object
    var xhrUploadFlag = false;
    if (window.XMLHttpRequest) {
        var testXHR = new XMLHttpRequest();
        xhrUploadFlag = (testXHR.upload != null);
    }

    // utility object for checking browser compatibility
    $.extend($.support, {
        fileSelecting: (window.File != null) && (window.FileList != null),
        fileReading: (window.FileReader != null),
        fileSending: (window.FormData != null),
        uploadControl: xhrUploadFlag
    });


    // generating uniq id
    function uniq(length, prefix) {
        length = parseInt(length);
        prefix = prefix || '';
        if ((length == 0) || isNaN(length)) {
            return prefix;
        }
        var ch = String.fromCharCode(Math.floor(Math.random() * 26) + 97);
        return prefix + ch + uniq(--length);
    }

    function checkIsFile(item) {
        return (item instanceof File) || (item instanceof Blob);
    }

    ////////////////////////////////////////////////////////////////////////////
    // plugin code
    $.fn.damnUploader = function(params, data) {
		
        if (this.length == 0) {
            return this;
        }

        // context
        var self = this;
		
		
		// locals
        var queue = self._damnUploaderQueue;
        var set = self._damnUploaderSettings || {};

        ////////////////////////////////////////////////////////////////////////
        // initialization (on first call)
        if (!params || $.isPlainObject(params)) {

            /* default settings */
            self._damnUploaderSettings = $.extend({
                url: '/upload.php',
                multiple: true,
                fieldName: 'file',
                dropping: true,
                dropBox: false,
                limit: false,
                onSelect: false,
                onLimitExceeded: false,
                onAllComplete: false
            }, params || {});

            /* private properties */
            self._damnUploaderQueue = {};
            self._damnUploaderItemsCount = 0;
            queue = self._damnUploaderQueue;
            set = self._damnUploaderSettings;

            /* private items-adding method */
            self._damnUploaderFilesAddMap = function(files, callback) {
                var callbackDefined = $.isFunction(callback);
                if (!$.support.fileSelecting) {
                    if (self._damnUploaderItemsCount === set.limit) {
                        return $.isFunction(set.onLimitExceeded) ? set.onLimitExceeded.call(self) : false;
                    }
                    var file = {
                        fake: true,
                        name: files.value,
                        inputElement: files
                    };
					
                    if (callbackDefined) {
                        if (!callback.call(self, file)) {
                            return true;
                        }
                    }
                    self.damnUploader('addItem', file);
                    return true;
                }
                if (files instanceof FileList) {
                    $.each(files, function(i, file) {
                        if (self._damnUploaderItemsCount === set.limit) {
                            if (self._damnUploaderItemsCount === set.limit) {
                                return $.isFunction(set.onLimitExceeded) ? set.onLimitExceeded.call(self) : false;
                            }
                        }
                        if (callbackDefined) {
                            if (!callback.call(self, file)) {
                                return true;
                            }
                        }
						
                        self.damnUploader('addItem', {
                            file: file
                        });
                    });
                }
                return true;
            };


            /* private file-uploading method */
            self._damnUploaderUploadItem = function(url, item) {
				
                if (!checkIsFile(item.file)) {
                    return false;
                }
                var xhr = new XMLHttpRequest();
                var progress = 0;
                var uploaded = false;

                if (xhr.upload) {
                    xhr.upload.addEventListener("progress", function(e) {
                        if (e.lengthComputable) {
                            progress = (e.loaded * 100) / e.total;
                            if ($.isFunction(item.onProgress)) {
                                item.onProgress.call(item, Math.round(progress));
                            }
                        }
                    }, false);

                    xhr.upload.addEventListener("load", function(e){
                        progress = 100;
                        uploaded = true;
                    }, false);

                } else {
                    uploaded = true;
                }

                xhr.onreadystatechange = function () {
                    var callbackDefined = $.isFunction(item.onComplete);
                    if (this.readyState == 4) {
                        item.cancelled = item.cancelled || false;
                        if (this.status < 400) {
                            if (!uploaded) {
                                if (callbackDefined) {
                                    item.onComplete.call(item, false, null, 0);
                                }
                            } else {
                                if ($.isFunction(item.onProgress)) {
                                    item.onProgress.call(item, 100);
                                }
                                if (callbackDefined) {
                                    item.onComplete.call(item, true, this.responseText);
                                }
                            }
                        } else {
                            if (callbackDefined) {
                                item.onComplete.call(item, false, null, this.status);
                            }
                        }
                    }
                };

                var filename = item.replaceName || item.file.name;
				xhr.open("POST", url);
				//alert(url);
                if ($.support.fileSending) {
                    // W3C (Chrome, Safari, Firefox 4+)
                    var formData = new FormData();
                    formData.append((item.fieldName || 'file'), item.file);
                    xhr.send(formData);
                } else if ($.support.fileReading && xhr.sendAsBinary) {
                    // firefox < 4
                    var boundary = "xxxxxxxxx";
                    xhr.setRequestHeader("Content-Type", "multipart/form-data, boundary="+boundary);
                    xhr.setRequestHeader("Cache-Control", "no-cache");
                    var body = "--" + boundary + "\r\n";
                    filename = unescape(encodeURIComponent(filename));
                    body += "Content-Disposition: form-data; name='"+(item.fieldName || 'file')+"'; filename='" + filename + "'\r\n";
                    body += "Content-Type: application/octet-stream\r\n\r\n";
                    body += (item.file.getAsBinary ? item.file.getAsBinary() : item.file.readAsBinary()) + "\r\n";
                    body += "--" + boundary + "--";
                    xhr.sendAsBinary(body);
                } else {
                    // Other
                    xhr.setRequestHeader('Upload-Filename', item.file.name);
                    xhr.setRequestHeader('Upload-Size', item.file.size);
                    xhr.setRequestHeader('Upload-Type', item.file.type);
                    xhr.send(item.file);
                }
                item.xhr = xhr;
            }



            /* binding callbacks */
            var isFileField = ((self.get(0).tagName == 'INPUT') && (this.attr('type') == 'file'));

            if (isFileField) {
                var myName = self.eq(0).attr('name');
				var HeadId = self.eq(0).attr('rel');
                if (!$.support.fileSelecting) {
                    if (myName.charAt(myName.length-1) != ']') {
                        myName += '[]';
                    }
                    self.attr('name', myName);
                    self.attr('multiple', false);
                    var action = self.parents('form').attr('action');
                    self._damnUploaderFakeForm = $('<form/>').attr({
                        method: 'post',
                        enctype: 'multipart/form-data',
                        action: action
                    }).hide().appendTo('body');
                } else {
                    self.attr('multiple', true);
                }

                self._damnUploaderChangeCallback = function() {
                    self._damnUploaderFilesAddMap($.support.fileSelecting ? this.files : this, set.onSelect);
                };

                self.on({
                   change: self._damnUploaderChangeCallback
                });
            }

            if (set.dropping) {
                self.on({
                    drop: function(e) {
                        self._damnUploaderFilesAddMap(e.originalEvent.dataTransfer.files, set.onSelect);
                        return false;
                    }
                });
                if (set.dropBox) {
                    $(set.dropBox).on({
                        drop: function(e) {
                            self._damnUploaderFilesAddMap(e.originalEvent.dataTransfer.files, set.onSelect);
                            return false;
                        }
                    });
                }
            }
            return self;
        }


        ////////////////////////////////////////////////////////////////////
        // controls
        switch(params) {

            case 'addItem':
                if (!data) {
                    return false;
                }
                var queueId = uniq(5);

                if (data.file.fake) {
                    var input = $(data.file.inputElement);
                    var cloned = $(input).clone();
                    $(input).before(cloned);
                    $(input).attr('id', queueId);
                    $(input).appendTo(self._damnUploaderFakeForm);
                    cloned.on({
                        change: self._damnUploaderChangeCallback
                    });
                    self._damnUploaderItemsCount++;
                    return queueId;
                }
                if (!checkIsFile(data.file)) {
                    return false;
                }
                queue[queueId] = data;
                self._damnUploaderItemsCount++;
                return queueId;
                break;


            case 'startUpload':
                if (!set.url) {
                    return self;
                }
				//alert(set.url+'-------------');
                if (!$.support.fileSelecting) {
                    self._damnUploaderFakeForm.submit();
                    return self;
                }
                $.each(queue, function(queueId, item) {
					var compl = item.onComplete;
                    item.fieldName = item.fieldName || set.fieldName;
					
					item.onComplete = function(successful, data, error) {
                        if (!this.cancelled) {
                            delete queue[queueId];
                            self._damnUploaderItemsCount--;
                        }
                        if ($.isFunction(compl)) {
                            compl.call(this, successful, data, error);
                        }
                        if ((self._damnUploaderItemsCount == 0) && ($.isFunction(set.onAllComplete))) {
                            set.onAllComplete.call(self);
                        }
                    };
					
                    self._damnUploaderUploadItem(set.url, item);
                });
                break;


            case 'itemsCount':
                return self._damnUploaderItemsCount;
                break;


            case 'cancelAll':
                if (!$.support.fileSelecting) {
                    self._damnUploaderItemsCount = 0;
                    self._damnUploaderFakeForm.empty();
                    return self;
                }
                $.each(queue, function(key, item) {
                    self.damnUploader('cancel', key);
                });
                break;


            case 'cancel':
                var queueId = data.toString();
                if (self._damnUploaderItemsCount > 0) {

                    if (!$.support.fileSelecting) {
                        var removingItem = $('#'+queueId);
                        if (removingItem.length > 0) {
                            removingItem.remove();
                            self._damnUploaderItemsCount--;
                        }
                        return self;
                    }

                    if (queue[queueId] !== undefined) {
                        if (queue[queueId].xhr) {
                            queue[queueId].cancelled = true;
                            queue[queueId].xhr.abort();
                        }
                        delete queue[queueId];
                        self._damnUploaderItemsCount--;
                    }
                }
                break;


            case 'setParam':
                var acceptParams = ['url', 'multiple', 'fieldName', 'limit'];				
                $.each(data, function(key, val) {
					//alert(key);
					self._damnUploaderSettings[key] = val;					
					//alert(set.url);
					if ($.inArray(key, acceptParams)) {						
                        self._damnUploaderSettings[key] = val;						
                    }
                });
                break;
        }

        return self;
    };

})(window.jQuery);



































$(document).ready(function() {
	
    //var $console = $("#console");
	var $console = $("#console1");

    // Инфа о выбранных файлах
    var countInfo = $("#info-count");
    var sizeInfo = $("#info-size");

    // ul-список, содержащий миниатюрки выбранных файлов
    var imgList = $('#img-list');

    // Контейнер, куда можно помещать файлы методом drag and drop
    var dropBox = $('#img-container');

    // Счетчик всех выбранных файлов и их размера
    var imgCount = 0;
    var imgSize = 0;
	
	// Стандарный input для файлов
	fileInput = $('#file-field');
	
	
	var namef = '';
	var head = 	fileInput.attr('rel');

    ////////////////////////////////////////////////////////////////////////////
    // Подключаем и настраиваем плагин загрузки
	
	var namef = '';
    fileInput.damnUploader({
		
        // куда отправлять
        url: '/cms/files/modules/multiupload/multiupload.php?head='+head,
        // имитация имени поля с файлом (будет ключом в $_FILES, если используется PHP)
        fieldName:  'mypic',
        // дополнительно: элемент, на который можно перетащить файлы (либо объект jQuery, либо селектор)
        dropBox: dropBox,
        // максимальное кол-во выбранных файлов (если не указано - без ограничений)
        limit: 50,
        // когда максимальное кол-во достигнуто (вызывается при каждой попытке добавить еще файлы)
        onLimitExceeded: function() {
            log('Допустимое кол-во файлов уже выбрано');
        },
        // ручная обработка события выбора файла (в случае, если выбрано несколько, будет вызвано для каждого)
        // если обработчик возвращает true, файлы добавляются в очередь автоматически
        onSelect: function(file) {
			
			//alert(namef+'---'+file.name);
			if(namef == file.name){
				return false;
			}else{
				namef = file.name;
			}
			addFileToQueue(file);
            return false;
        },
        // когда все загружены
        onAllComplete: function() {
			//change
			$('#upload-all').removeClass('disabled');
			$('#usenamecheck').removeAttr('disabled');
				
			$('ul#img-list>li').fadeOut();
			setTimeout(function(){
				$('.closebtn').trigger('click');
				$('ul#img-list>li').remove();
			},700);
			setTimeout(function(){
				location.reload();
			},1000);
			
            log('<span style="color: blue;">*** Все загрузки завершены! ***</span>');
            imgCount = 0;
            imgSize = 0;
            updateInfo();
        }
    });
	
	

	
	
 ////////////////////////////////////////////////////////////////////////////
    // Вспомогательные функции

    // Вывод в консоль
    function log(str) {
        $('<p/>').html(str).prependTo($console);
    }

    // Вывод инфы о выбранных
    function updateInfo() {
        countInfo.text( (imgCount == 0) ? 'Изображений не выбрано' : ('Изображений выбрано: '+imgCount));
        sizeInfo.text( (imgSize == 0) ? '-' : Math.round(imgSize / 1024));
    }

    // Обновление progress bar'а
    function updateProgress(bar, value) {
        var width = bar.width();
        var bgrValue = -width + (value * (width / 100));
        bar.attr('rel', value).css('background-position', bgrValue+'px center').text(value+'%');
    }

    // преобразование формата dataURI в Blob-данные
    function dataURItoBlob(dataURI) {
        var BlobBuilder = (window.MSBlobBuilder || window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder);
        if (!BlobBuilder) {
            return false;
        }
        // convert base64 to raw binary data held in a string
        // doesn't handle URLEncoded DataURIs
        var pieces = dataURI.split(',');
        var byteString = (pieces[0].indexOf('base64') >= 0) ? atob(pieces[1]) : unescape(pieces[1]);
        // separate out the mime component
        var mimeString = pieces[0].split(':')[1].split(';')[0];
        // write the bytes of the string to an ArrayBuffer
        var ab = new ArrayBuffer(byteString.length);
        var ia = new Uint8Array(ab);
        for (var i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
        // write the ArrayBuffer to a blob, and you're done
        var bb = new BlobBuilder();
        bb.append(ab);
        return bb.getBlob(mimeString);
    }



    // Отображение выбраных файлов, создание миниатюр и ручное добавление в очередь загрузки.
    function addFileToQueue(file) {
				
        // Если браузер поддерживает выбор файлов (иначе передается специальный параметр fake,
        // обозночающий, что переданный параметр на самом деле лишь имитация настоящего File)
        if(!file.fake) {
			
			
			
            // Отсеиваем не картинки
            var imageType = /image.*/;
            if (!file.type.match(imageType)) {
				alert('Файл не является картинкой и был беспощадно отсеян: `'+file.name+'` (тип '+file.type+')');
                log('Файл отсеян: `'+file.name+'` (тип '+file.type+')');
                return true;
            }
			
			// Создаем элемент li и помещаем в него название, миниатюру и progress bar
			var li = $('<li/>').appendTo(imgList);
			var title = $('<div/>').text(file.name+' ').appendTo(li);
			var cancelButton = $('<a/>').attr({
				href: '#cancel',
				title: 'отменить'
			}).text('X').appendTo(title);
			
            // Добавляем картинку и прогрессбар в текущий элемент списка
				var imgwrap = $('<span/>').appendTo(li);
				var img = $('<img/>').appendTo(imgwrap);
				var pBar = $('<div/>').addClass('progress').attr('rel', '0').text('0%').appendTo(li);
				
            // Создаем объект FileReader и по завершении чтения файла, отображаем миниатюру и обновляем
            // инфу обо всех файлах (только в браузерах, подерживающих FileReader)
            if($.support.fileReading) {
                var reader = new FileReader();
                reader.onload = (function(aImg) {
                    return function(e) {
                        aImg.attr('src', e.target.result);
                        aImg.attr('width', 80);
                    };
                })(img);
                reader.readAsDataURL(file);
            }

            log('Картинка добавлена: `'+file.name + '` (' +Math.round(file.size / 1024) + ' Кб)');
            imgSize += file.size;
        } else {
			// Создаем элемент li и помещаем в него название, миниатюру и progress bar
			var li = $('<li/>').appendTo(imgList);
			var title = $('<div/>').text(file.name+' ').appendTo(li);
			var cancelButton = $('<a/>').attr({
				href: '#cancel',
				title: 'отменить'
			}).text('X').appendTo(title);	
			
            log('Файл добавлен: '+file.name);
        }

        imgCount++;
        updateInfo();

        // Создаем объект загрузки
		//alert(file.);
        var uploadItem = {
            file: file,
            onProgress: function(percents) {
                updateProgress(pBar, percents);
            },
            onComplete: function(successfully, data, errorCode) {
				//alert(data);
				
                if(successfully) {
					
                    log('Файл `'+this.file.name+'` загружен'); //, полученные данные:<br/>*****<br/>'+data+'<br/>*****');
                } else {
                    if(!this.cancelled) {
                        log('<span style="color: red;">Файл `'+this.file.name+'`: ошибка при загрузке. Код: '+errorCode+'</span>');
                    }
					//$('#upload-all').removeClass('disabled');
					//$('#usenamecheck').removeAttr('disabled');
                }
            }
        };

        // ... и помещаем его в очередь
        var queueId = fileInput.damnUploader('addItem', uploadItem);

        // обработчик нажатия ссылки "отмена"
        cancelButton.click(function() {
            fileInput.damnUploader('cancel', queueId);
            li.remove();
            imgCount--;
            imgSize -= file.fake ? 0 : file.size;
            updateInfo();
            log(file.name+' удален из очереди');
			$('#upload-all').removeClass('disabled');
			$('#usenamecheck').removeAttr('disabled');
            return false;
        });

        return uploadItem;
    }
	
	




    ////////////////////////////////////////////////////////////////////////////
    // Обработчики событий


    // Обработка событий drag and drop при перетаскивании файлов на элемент dropBox
	
    dropBox.bind({
        dragenter: function() {
            $(this).addClass('highlighted');
            return false;
        },
        dragover: function() {
            return false;
        },
        dragleave: function() {
            $(this).removeClass('highlighted');
            return false;
        }
    });
	


    // Обаботка события нажатия на кнопку "Загрузить все".
    // стартуем все загрузки
    $("#upload-all").live('click',function() {
		if($('#img-list>li').size() < 1){
			alert('Нащальника хитрый, однако!.. Хотя бы один фотка добавьте, чтобы загрузчика работала...');
			return false;
		}
		if($(this).hasClass('disabled')){
			//alert('Нащальника, загрузщика уже работает!.. Не нажимай на кнопка пажалуста!..');
			return false;
		}else{
			$('#upload-all').addClass('disabled');
			$('#usenamecheck').attr('disabled','disabled');
			fileInput.damnUploader('startUpload');
		}
	});


    // Обработка события нажатия на кнопку "Отменить все"
    $("#cancel-all").click(function() {
        fileInput.damnUploader('cancelAll');
        imgCount = 0;
        imgSize = 0;
        updateInfo();
        log('*** Все загрузки отменены ***');
        imgList.empty();
    });


    // Обработка нажатия на тестовую канву
	/*
    $(canvas).click(function() {
        var blobData;
        if (canvas.toBlob) {
            // ожидается, что вскоре браузерами будет поддерживаться метод toBlob() для объектов Canvas
            blobData = canvas.toBlob();
        } else {
            // ... а пока - конвертируем вручную из dataURI
            blobData = dataURItoBlob(canvas.toDataURL('image/png'));
        }
        if (blobData === false) {
            log("Ваш браузер не поддерживает BlobBuilder");
            return ;
        }
        addFileToQueue(blobData)
    });
	*/




    ////////////////////////////////////////////////////////////////////////////
    // Проверка поддержки File API, FormData и FileReader

    if(!$.support.fileSelecting) {
        log('Ваш браузер не поддерживает выбор файлов (загрузка будет осуществлена обычной отправкой формы)');
        $("#dropBox-label").html('<br><br>Если бы Вы использовали хороший браузер, то файлы можно было бы перетаскивать прямо в область ниже! Установите Google Crome для полного счастья)');
    } else {
        if(!$.support.fileReading) {
            log('* Ваш браузер не умеет читать содержимое файлов (миниатюрки не будут показаны)');
        }
        if(!$.support.uploadControl) {
            log('* Ваш браузер не умеет следить за процессом загрузки (progressbar не работает)');
        }
        if(!$.support.fileSending) {
            log('* Ваш браузер не поддерживает объект FormData (отправка с ручной формировкой запроса)');
        }
        log('Выбор файлов поддерживается');
    }
    log('*** Проверка поддержки ***');

	
});









