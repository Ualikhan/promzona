$().ready(function(){

    $('.moderation-form')
        .find('table')
            .filter('.adv-list')
                .find('.btn-reject,.btn-remove').on('click',function(){            
                    var action = $(this).attr('data-action');
                    moderationAdv( action ); 
                }).end().end()
            .filter('.company-list')
                .find('.btn-reject,.btn-remove').on('click',function(){            
                    var action = $(this).attr('data-action');
                    moderationCompany( action ); 
                }).end().end()
            .filter('.company-news-list')
                .find('.btn-reject,.btn-remove,.btn-approve').on('click',function(){
                    var action = $(this).attr('data-action');
                    moderationNews( action ); 
                }).end().end()
            .filter('.company-bills-list')
                .find('.btn-reject,.btn-remove').on('click',function(){
                    var action = $(this).attr('data-action');
                    moderationBills( action );
                }).end()
                .find('.link-modal-bill').on('click',function(){
                    var link = $(this);
                    var id   = parseInt( link.attr('rel') );
                    $.getJSON('/moderator/bills?bill='+ id ,function(response){
                        openBillsModal( response );
                    });
                });

    if( $('.add-company-form').length > 0 ){
        loadAddCompanyEvents();
    }

    if( $('#filesUploader').is(':visible') ){
        initFileUploader();
    }

});

// open bills modal
function openBillsModal( data ){
    var template =  '<div class="bill-modal modal hide fade in">' +
                        '<div class="modal-header">' +
                            '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                            '<h2 class="ml-20"><i class="icon-time icon-yellow mt-5"></i>&nbsp;Счёт № PZ '+ data.number +' от '+ data.date +'. '+ data.name +'</h2>' +
                        '</div>' +
                        '<div class="modal-body">' +
                            '<table class="bill-table ml-30 mr-30">' +
                                '<colgroup>' +
                                    '<col width="30" /><col width="400" /><col width="100" /><col width="150" /><col width="40" />' +
                                '</colgroup>' +
                                '<thead>' +
                                    '<tr>' +
                                        '<th></th>' +
                                        '<th>Наименование</th>' +
                                        '<th></th>' +
                                        '<th>Сумма</th>' +
                                        '<th></th>' +
                                    '</tr>' +
                                '</thead>' +
                                '<tbody>' +
                                    '<tr>' +
                                        '<td>1</td>' +
                                        '<td>'+ data.name +'</td>' +
                                        '<td></td>' +
                                        '<td><b>'+ data.sum +'</b></td>' +
                                        '<td><span class="fz-11">KZT</span></td>' +
                                    '</tr>' +
                                '</tbody>' +
                                '<tfoot>' +
                                    '<tr>' +
                                        '<td></td>' +
                                        '<td></td>' +
                                        '<td><b>Итого:</b></td>' +
                                        '<td><b class="fz-24">'+ data.sum +'</b></td>' +
                                        '<td><span class="fz-11">KZT</span></td>' +
                                    '</tr>' +
                                '</tfoot>' +
                            '</table>' +
                            '<div class="dashed ml-30 mr-30 mb-20"></div>' +
                            '<div class="ml-50 mr-30">' +
                                '<div class="pull-right bold orange"><i class="icon-time icon-yellow"></i>&nbsp;Ожидает оплаты</div>' +
                                '<b class="ml-20">Счёт для физического лица.</b>' +
                                '<table class="ml-50 mb-50 bill-info-table">' +
                                    '<colgroup>' +
                                        '<col width="180" /><col width="" />' +
                                    '</colgroup>' +
                                    '<tbody>' +
                                        '<tr>' +
                                            '<td>Плательщик: <span class="fz-20 red">*</span></td>' +
                                            '<td><b>'+ data.payer +'</b></td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td>РНН:</td>' +
                                            '<td><b>'+ data.bin +'</b></td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td>Счёт сформирован:</td>' +
                                            '<td><b>'+ data.time +'</b></td>' +
                                        '</tr>' +
//                                        '<tr>' +
//                                            '<td>Счёт оплачен:</td>' +
//                                            '<td><b></b></td>' +
//                                        '</tr>' +
                                    '</tbody>' +
                                '</table>' +
                            '</div>' +
                            '<div class="modal-footer">' +
                                '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</button>' +
                            '</div>' +
                        '</div>';
    $('body')
        .find('.bill-modal').remove().end()
        .append( template );
    var modal = $('.bill-modal');
    modal.modal('show');
}

// moderation adv
function moderationAdv( action ){
    var form        = $('.moderation-form').find('table').filter('.adv-list');
    var selected    = form.find('tbody').find('input[type="checkbox"]').filter(':checked');   
    var checkboxes  = '';
    if( selected.length ==  1 ){
        var item = selected.parents('.item').clone(false);
        item
            .find('.checkbox')
                .remove().end()
            .find('.category,.condition,.date')
                .remove().end()
            .find('.contacts')
                .remove();
    }
    if( selected.length > 0 ){
        $.each( selected, function( i, checkbox ){
            checkboxes += '<input type="hidden" name="'+ $(checkbox).attr('name') +'" /> ';
        });
    }
    var template    =   '<div id="winModeration" class="moderation-modal modal hide fade in" aria-hidden="false">'+
                            '<form action="" method="post">'+
                                checkboxes +
                                '<div class="modal-header">'+
                                    '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">&times;</button>'+
                                    '<h3>'+ ( selected.length > 0 ? ( selected.length > 1 ? ( ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' объявления') : ( ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' объявление') ) : 'Вы не выбрали ни одного объявления.' ) +'</h3>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row-fluid main-catalog">'+
                                        ( selected.length > 0 ?
                                            (
                                                ( selected.length > 1 ?
                                                    '<p>Вы выбрали <b>'+ selected.length + getNumEnding( selected.length,[' объявление',' объявления',' объявлений'] ) +'</b>.</p>'
                                                :
                                                    ('<div class="item clearfix">'+ item.html() +'</div>'+
                                                    '<div class="control-group">'+
                                                        '<div class="control-label"><b>Укажите причину '+ ( action == 'reject' ? 'отклонения' : 'удаления' ) +' объявления:</b></div>'+
                                                        '<div class="controls">'+
                                                            '<textarea rows="4" class="span12" name="desc"></textarea>'+
                                                        '</div>'+
                                                    '</div>')
                                                )
                                            )
                                        : '<p>Сообщение о том, что пользователь не выбрал ни одного объявления</p>') +
                                    '</div>'+
                                '</div>'+
                                '<div class="modal-footer">'+
                                    ( selected.length > 0 ?
                                        ('<button type="submit" class="btn btn-grey btn-remove btn-grand" name="'+( action == 'reject' ? 'reject' : 'remove' )+'" value="1">'+ ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' '+ ( selected.length > 1 ? 'объявления' : 'объявление' ) +'</button>'+
                                         '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>')
                                    :
                                        '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Закрыть</button>'
                                    ) +
                                '</div>'+
                            '</form>'+
                        '</div>';
    $('body').find('.moderation-modal').remove().end().append( template );
    var win = $('#winModeration');
    win.modal('show');
}

// moderation company
function moderationCompany( action ){
    var form        = $('.moderation-form').find('table').filter('.company-list');
    var selected    = form.find('tbody').find('input[type="checkbox"]').filter(':checked');   
    var checkboxes  = '';
    if( selected.length ==  1 ){
        var item  = selected.parents('.item').clone(false);
        var photo = item.find('td').filter(':eq(1)').find('img').attr('src');
            item  = item.find('.company-item');
        item
            .prepend( ( typeof photo != 'undefined' ? ( '<img class="logo" src="'+ photo +'" />' ) : '' ) ).end()
            .find('.company-category,.company-date')
                .remove();
    }
    if( selected.length > 0 ){
        $.each( selected, function( i, checkbox ){
            checkboxes += '<input type="hidden" name="'+ $(checkbox).attr('name') +'" /> ';
        });
    }
    var template    =   '<div id="winModeration" class="moderation-modal modal hide fade in" aria-hidden="false">'+
                            '<form action="" method="post">'+
                                checkboxes +
                                '<div class="modal-header">'+
                                    '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">&times;</button>'+
                                    '<h3>'+ ( selected.length > 0 ? ( selected.length > 1 ? ( ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' компании') : ( ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' компанию') ) : 'Вы не выбрали ни одной компании.' ) +'</h3>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row-fluid main-catalog">'+
                                        ( selected.length > 0 ?
                                            (
                                                ( selected.length > 1 ?
                                                    '<p>Вы выбрали <b>'+ selected.length + getNumEnding( selected.length,[' компанию',' компании',' компаний'] ) +'</b>.</p>'
                                                :
                                                    ('<div class="item clearfix company '+ ( typeof photo != 'undefined' ? 'with-logo' : '' ) +'">'+ item.html() +'</div>'+
                                                    '<div class="control-group">'+
                                                        '<div class="control-label"><b>Укажите причину '+ ( action == 'reject' ? 'отклонения' : 'удаления' ) +' компании:</b></div>'+
                                                        '<div class="controls">'+
                                                            '<textarea rows="4" class="span12" name="desc"></textarea>'+
                                                        '</div>'+
                                                    '</div>')
                                                )
                                            )
                                        : '<p>Сообщение о том, что пользователь не выбрал ни одной компании</p>') +
                                    '</div>'+
                                '</div>'+
                                '<div class="modal-footer">'+
                                    ( selected.length > 0 ?
                                        ('<button type="submit" class="btn btn-grey btn-remove btn-grand" name="'+( action == 'reject' ? 'reject' : 'remove' )+'" value="1">'+ ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' '+ ( selected.length > 1 ? 'компании' : 'компанию' ) +'</button>'+
                                         '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>')
                                    :
                                        '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Закрыть</button>'
                                    ) +
                                '</div>'+
                            '</form>'+
                        '</div>';
    $('body').find('.moderation-modal').remove().end().append( template );
    var win = $('#winModeration');
    win.modal('show');
}

// moderation news
function moderationNews( action ){
    var form        = $('.moderation-form').find('table').filter('.company-news-list');
    var selected    = form.find('tbody').find('input[type="checkbox"]').filter(':checked');   
    var checkboxes  = '';
    if( selected.length ==  1 ){
        var item    = selected.parents('tr').clone(false);
        var photo   = item.find('td').filter(':eq(1)').find('img').attr('src');
            item    = item.find('.company-news-item');
        item
            .prepend( ( typeof photo != 'undefined' ? ( '<img class="logo" src="'+ photo +'" />' ) : '' ) ).end()
            .find('.btn-group')
                .remove().end()
            .find('.company-news-item-in')
                .remove();
    }
    if( selected.length > 0 ){
        $.each( selected, function( i, checkbox ){
            checkboxes += '<input type="hidden" name="'+ $(checkbox).attr('name') +'" /> ';
        });
    }
    var template    =   '<div id="winModeration" class="moderation-modal modal hide fade in" aria-hidden="false">'+
                            '<form action="" method="post">'+
                                checkboxes +
                                '<div class="modal-header">'+
                                    '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">&times;</button>'+
                                    '<h3>'+ ( selected.length > 0 ? ( selected.length > 1 ? ( ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' новости') : ( ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' новости') ) : 'Вы не выбрали ни одной новости.' ) +'</h3>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row-fluid main-catalog">'+
                                        ( selected.length > 0 ?
                                            (
                                                ( selected.length > 1 ?
                                                    '<p>Вы выбрали <b>'+ selected.length + getNumEnding( selected.length,[' новость',' новости',' новости'] ) +'</b>.</p>'
                                                :
                                                    ('<div class="item clearfix news '+ ( typeof photo != 'undefined' ? 'with-logo' : '' ) +'">'+ item.html() +'</div>'+
                                                    '<div class="control-group">'+
                                                        '<div class="control-label"><b>Укажите причину '+ ( action == 'reject' ? 'отклонения' : 'удаления' ) +' новости:</b></div>'+
                                                        '<div class="controls">'+
                                                            '<textarea rows="4" class="span12" name="desc"></textarea>'+
                                                        '</div>'+
                                                    '</div>')
                                                )
                                            )
                                        : '<p>Сообщение о том, что пользователь не выбрал ни одной новости</p>') +
                                    '</div>'+
                                '</div>'+
                                '<div class="modal-footer">'+
                                    ( selected.length > 0 ?
                                        ('<button type="submit" class="btn btn-grey btn-remove btn-grand" name="'+( action == 'reject' ? 'reject' : 'remove' )+'" value="1">'+ ( action == 'reject' ? 'Отклонить' : 'Удалить' ) +' '+ ( selected.length > 1 ? 'новости' : 'новость' ) +'</button>'+
                                         '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>')
                                    :
                                        '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Закрыть</button>'
                                    ) +
                                '</div>'+
                            '</form>'+
                        '</div>';
    if( action != 'approve' || ( action == 'approve' && selected.length == 0 ) ){
        $('body').find('.moderation-modal').remove().end().append( template );
        var win = $('#winModeration');
        win.modal('show');
    }
    if( action == 'approve' && selected.length > 0 ) $('.moderation-form').find('form').submit();
}

// moderation news
function moderationBills( action ){
    var form        = $('.moderation-form').find('table').filter('.company-bills-list');
    var selected    = form.find('tbody').find('input[type="checkbox"]').filter(':checked');
    var checkboxes  = '';
    if( selected.length ==  1 ){
        var item    = selected.parents('tr').clone(false);
        item
            .find('.checkbox')
                .remove().end()
            .find('.bd-beige')
                .after('<br />').end()
            .find('.fz-11')
                .after('<br />');
    }
    if( selected.length > 0 ){
        $.each( selected, function( i, checkbox ){
            checkboxes += '<input type="hidden" name="'+ $(checkbox).attr('name') +'" /> ';
        });
    }
    var template    =   '<div id="winModeration" class="moderation-modal modal hide fade in" aria-hidden="false">'+
                            '<form action="" method="post">'+
                                checkboxes +
                                '<div class="modal-header">'+
                                    '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">&times;</button>'+
                                    '<h3>'+ ( selected.length > 0 ? ( selected.length > 1 ? ( ( action == 'reject' ? 'Аннулировать' : 'Удалить' ) +' счета') : ( ( action == 'reject' ? 'Аннулировать' : 'Удалить' ) +' счет') ) : 'Вы не выбрали ни одного счёта.' ) +'</h3>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<div class="row-fluid main-catalog">'+
                                        ( selected.length > 0 ?
                                            (
                                                ( selected.length > 1 ?
                                                    '<p>Вы выбрали <b>'+ selected.length + getNumEnding( selected.length,[' счёт',' счёта',' счёта'] ) +'</b>.</p>'
                                                :
                                                    ('<div class="item clearfix orders">'+ item.html() +'</div>'+
                                                        '<div class="control-group">'+
                                                            '<div class="control-label"><b>Укажите причину '+ ( action == 'reject' ? 'аннулирования' : 'удаления' ) +' счёта:</b></div>'+
                                                            '<div class="controls">'+
                                                                '<textarea rows="4" class="span12"></textarea>'+
                                                            '</div>'+
                                                        '</div>')
                                                    )
                                                )
                                            : '<p>Сообщение о том, что пользователь не выбрал ни одного счёта</p>') +
                                    '</div>'+
                                '</div>'+
                                '<div class="modal-footer">'+
                                    ( selected.length > 0 ?
                                        ('<button type="submit" class="btn btn-grey btn-remove btn-grand" name="'+( action == 'reject' ? 'reject' : 'remove' )+'" value="1">'+ ( action == 'reject' ? 'Аннулировать' : 'Удалить' ) +' '+ ( selected.length > 1 ? 'счета' : 'счёт' ) +'</button>'+
                                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>')
                                        :
                                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Закрыть</button>'
                                        ) +
                                '</div>'+
                            '</form>'+
                        '</div>';
    $('body').find('.moderation-modal').remove().end().append( template );
    var win = $('#winModeration');
    win.modal('show');
}

// load add company events 
function loadAddCompanyEvents(){
    var form = $('.add-company-form');

    var textLimit = function(){
        var textarea = $('textarea#companyDesc');
        var limit    = parseInt( textarea.attr('maxlength') );
        var text     = textarea.val().length;
        var counter  = $('#companyDescCount');
        counter
            .css('display', ( text > 0 ? 'inline' : 'none' ) )
            .find('span').text( limit - text );
    };

    form 
        .delegate('.filesResponse .link-remove','click',function(){
            $(this)
                .parents('li')
                    .fadeOut(function(){
                        $(this).remove();
                        checkFileUploader();
                    });
        })

        .find('textarea#companyDesc').on('keyup',function(){
            textLimit();
        });

    textLimit();
    checkFileUploader();
}

// check file Uploader 
function checkFileUploader(){
    var response = $('.filesResponse');
    if( response.find('li').length > 0 ){
        response
            .show()
            .find('li').each(function(){
                $(this).find('input[type="hidden"]').attr('name','files['+ $(this).index() +']');
            });
    }else response.hide();
}

// init file uploader 
function initFileUploader(){
    var form = $('.add-company-form');
    
    new qq.FineUploader({
        element: $('#filesUploader')[0],            
        multiple: true,
        request: {
            endpoint: '/uploader.php'
        },
        // allowedExtensions: ['jpg','jpeg','gif','png'],
        template:
            '<pre class="qq-upload-drop-area" style="display:none !important;"><span>{dragZoneText}</span></pre>' +
            '<div class="qq-upload-button btn btn-white soft" style="width: auto;"><span><i class="icon-upload"></i> Загрузить файлы</span></div>' +
            '<ul class="qq-upload-list" style="display:none !important;"></ul>',
        classes: {
            success: 'alert alert-success',
            fail: 'alert alert-error'
        },
        callbacks: {
            onComplete: function(id, fileName, response) {

                var bytes = response.filesize;
                var size  = ( bytes > 999999 ? bytes/1024/1024 : bytes/1024 );
                size = size.toFixed(2);
                size = ( bytes > 999999 ? (size +' Мб') : (size +' Кб') );
                if( response.success ){
                    var responseDiv = $('.filesResponse');
                    var files = responseDiv.find('li').length;
                    responseDiv
                        .append('<li>'+
                                    '<a target="_blank" href="/cms/uploads_temp/'+ response.filename +'" class="bd-beige pseudo"><i class="icon-file icon-yellow"></i> <span>'+ fileName +' </span></a> <sup>('+ size +')</sup> '+
                                    '<a href="javascript:void(0);" class="pseudo link-remove"><i class="icon-trash icon-red"></i> <span><i>Удалить файл</i></span></a>'+
                                    '<input type="hidden" name="files['+ files +']" value="'+ response.filename +'" />'+
                                '</li>');
                    checkFileUploader();
                }
            }
        }
    });
}


