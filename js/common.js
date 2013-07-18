// creating main objects
var formsObj = new FormHandler();
var authObj = new Auth();

$(function () {

    if( $('.jsRegionList').length > 0 ){
        $('.jsRegionList').replaceWith('<input class="regionsList" autocomplete="off" type="text" name="region" placeholder="Все регионы" />');
        $('.regionsList')
            .filter(':not(.ready)').typeahead({
                source: currentInfo.typeahead.regions
            }).addClass('ready');
    }

    if( $('.jsCountryList').length > 0 ){
        $('.jsCountryList').replaceWith('<input class="countriesList" autocomplete="off" type="text" name="country" placeholder="Любая" />');
        $('.countriesList')
            .filter(':not(.ready)').typeahead({
                source: currentInfo.typeahead.countries
            }).addClass('ready');
    }

    if( $('.datepicker').length > 0  ){
        initDatePicker();
    }

    $('.index-list-tab').find('li:last').find('a').on('click',function(){
        var div = $('.index-list-wrapper').find('.tab-content').find('.tab-pane:last');
        if( !div.hasClass('loaded') ){
            var mod = $('#nav').find('li').filter('.active').find('a').attr('href');
            mod = (mod.substr(1)).substr(0,(mod.substr(1)).length-1);
            $.get('/?equipment=1&mod='+ ( mod == '' ? 'buy' : mod ),function(response){
                div.addClass('loaded').html( response );
                setIndexTabListEvents( div );
            });
        }
    });

    $('.form-with-reset').each(function(){
        var form = $(this);
        var btn_reset = form.find('.btn-reset-filter');
        getDefaultValues( form );
        form
            .find(':checkbox,:radio,select')
                .on('change',function(){
                    btn_reset.removeAttr('disabled');
                }).end()
            .find('input[type="text"]')
                .on('change keyup',function(){
                    btn_reset.removeAttr('disabled');
                });
        btn_reset
            .on('click',function(){
                var form = $(this).parents('form');
                returnDefaults( form );
            });
    });

    $('.pseudo-radio-link').on('click',function(){
        var selected_year = parseInt($(this).val());
        $('input#filter-date-from').val( ( year != (selected_year+8) ? selected_year : '' ) );
        $('input#filter-date-to').val( year );
        $('.year').on('keyup',function(){
            $('.pseudo-radio-link').removeAttr('checked');
        });
    });

    $('a.empty').on('click',function(){
        return false;
    });

    $('.search-response-filters-form').find('input[type="radio"]').on('change',function(){
        $(this).parents('form').submit();
    });

    $('#companyTabs').find('li a').on('shown',function(e){
        eqHeight( $('#cabinet-left-b'), $('#cabinet-right-b') );
    });

    if ($('[rel="popover"]').length > 0) {
        setPopovers();
    }

    if ($('#nav').length > 0) {
        mainNav()
    }

    if ($('#modal-open-login').length && $('#modal-open-register').length > 0) {
        authObj.setModalEvents();
    }

    if ($('#page-auth').length > 0) {
        authObj.setPageEvents();
    }

    if ($('#index-carousel').length > 0) {
        setIndexCarousel();
    }

    if ($('.index-list-wrapper').length > 0) {
        setIndexTabListEvents();
    }

    if ($('.phones-input-group').length > 0) {
        setPhoneInputGroup();
    }
    if ($('.contacts-input-group').length > 0) {
        setContactInputGroup();
    }
    if ($('.branches-input-group').length > 0) {
        setBranchInputGroup();
    }

    if($('#catSelector').length > 0) {
        setCatSelector();
    }

    if($('.onlyNum').length > 0) {
        onlyNum($('.onlyNum'));
    }

    if($('.sepThousands').length > 0) {
        sepThousands($('.sepThousands'));
    }

    if($('.photoUploader').length > 0) {
        setPhotoUploader();
    }

    if($('.logoUploader').length > 0) {
        setLogoUploader();
    }

    if($('.priceUploader').length > 0) {
        setPriceUploader();
    }

    if($('.listenThisForm').length > 0) {
        formsObj.checkSubmitBtnOnChange($('.listenThisForm'));
    }

    if ($('.catalog-filter').length > 0) {
        setCatalogFilter();
    }

    if ($('[data-toggle="phones"]').length > 0) {
        setPhonesToggler();
    }

    $('.catalog-sorter').change(function() {
        $(this).parents('form').submit();
    });

    if($('.typeaheaded').length > 0) {
        $('.typeaheaded').each(function() {
            switch ($(this).data('typeaheaded')) {
                case 'region':
                    $(this).typeahead({source: currentInfo.typeahead.regions});
                    break;
                case 'country':
                    $(this).typeahead({source: currentInfo.typeahead.countries});
                    break;
            }
        });
    }

    $('[data-close="alert"]')
        .on('click', function() {
            $(this).parent('.alert').hide();
        });

    $('.link-toggle').on('click',function(){
        var container = $(this).parents('.toggle-container'),
            toggle    = container.find('.toggle');
        if( container.hasClass('toggled') ){
            toggle.slideUp('fast',function(){
                container.removeClass('toggled');
            });
        }else{
            toggle.slideDown('fast',function(){
                container.addClass('toggled');
            });
        }
    });

    if( $('#modal-photos').length > 0 ){
        initModalPhotoSlider();
    }

    if( $('.interrelated-container').length > 0 ){
        initInterrelatedEvents();
    }

    if( $('.required-container').length > 0 ){
        initCheckRequired();
    }

    $('select.autoSubmitForm').on('change',function(){
        $(this).closest('form').submit();
    });
    
    $('select[name="on_page"]').on('change',function(){
        $(this).closest('form').submit();
    });

    $('.toggle-block').find('.link-toggle').on('click',function(){
        var div      = $(this).parents('.toggle-block');
        var toggle  = div.find('.toggle');
        if( div.hasClass('toggled') ){
            toggle.slideUp('fast',function(){
               div.removeClass('toggled');
            });
        }else{
            toggle.slideDown('fast',function(){
                div.addClass('toggled');
            });
        }
    });

});

// Instruments
//-----------------
function FormHandler() {

    var that = this;

    // check required form fields and return flag
    this.checkFormFields = function (wrapper) {
        var flag = false;
        wrapper.find('.required').each(function () {
            if ($(this).is(':checkbox,:radio') && !$(this).is(':checked') ||
                $(this).is(':input:not(select, :checkbox, :radio)') && $(this).val() == '' ||
                $(this).is('select') && $(this).find('option:selected:not(.default)').length == 0) {
                flag = true;
            } else if (!$(this).is(':input') && $(this).find(':checked').length == 0) {
                flag = true;
            } else if ($(this).hasClass('noSubmitAllowed') ) {
                flag = true;
            }
            if( $(this).hasClass('noCheck') ) flag = false;
        });
        return flag;
    };

    // Disable/enable submit button
    this.checkSubmitBtn = function (wrapper) {
        var submit = wrapper.find(':submit');
        var flag = this.checkFormFields(wrapper);

        if( wrapper.hasClass('required-container') ){
            if( wrapper.find('.phones-input-group').length > 0 ){
                var phones = $('.phones-input-group');
                phones
                    .find('input[type="text"]')
                        .removeClass('required required-inp')
                    .filter(':first')
                    .addClass('required-inp');
                initCheckRequired();
                wrapper.find('.required-btn').removeAttr('disabled');
                phones.find('.required-inp').each(function(){
                   if( !$(this).val() ) wrapper.find('.required-btn').attr('disabled','disabled');
                });
            }
        }else{
            if (flag == true) {
                submit.attr('disabled', 'disabled');
//                wrapper
//                    .off('submit')
//                    .on('submit', function() {
//                        return false;
//                    });
            } else {
                submit.removeAttr('disabled');
//                wrapper.off('submit');
            }
        }

    };

    //Disable/enable submit button on change or keyup
    this.checkSubmitBtnOnChange = function (wrapper) {
        wrapper.on('keyup change', 'required, :input, select', function() {
            that.checkSubmitBtn(wrapper);
        });
    };

    // serialize and send form using AJAX
    this.setCommonFormAJAX = function (wrapper, error, success) {

        if (wrapper.is('form')) {
            this.form = wrapper;
        } else {
            this.form = wrapper.children('form');
        }

        this.form.submit(function () {
            var values = $(this).serializeArray();
            var data = {};
            var url = $(this).attr('action');

            $.each(values, function (i, el) {
                data[el.name] = el.value;
            });
            $.post(url, data, function (response) {
				if (response.success) {
                    if( typeof wrapper.attr('id') != 'undefined' && wrapper.attr('id') == 'modal-reset-password' ){
                        wrapper
                            .find('form')
                            .find('input[type="email"]')
                                .attr('disabled','disabled').end()
                            .find('.submit-group')
                                .find('.submit-btn').remove().end()
                                .prepend('<div class="alert alert-success" style="margin-right:15px;">письмо для восстановления пароля отправлено Вам на почту</div>');
                        return false;
                    }else typeof success !== 'undefined' ? success() : document.location.replace('/cabinet/');
                }else typeof error !== 'undefined' ? error() : wrapper.addClass('error').find('.alert-error').show();
            }, 'json');

            return false;
        });
    };

    // cut input max words length
    this.textMaxWordsCutter = function(el, maxLength) {
        var val, valArr, length, cutted;
        el
            .keydown(function() {
                val = el.val();
                valArr = val.match(/[A-Za-zА-Яа-я]+\W?/g);
                length = valArr ? valArr.length: 0;

                if (length >= maxLength ) {
                    cutted = valArr.slice(0, maxLength).join('');
                    el.val($.trim(cutted));
                }
            });
    };

    // count input/textarea letters left
    this.letterLeftCounter = function (el, to) {
        var left = el.attr('maxlength') - el.val().length;
        if( left <= 0 ){
            el.val( el.val().substr(0,el.attr('maxlength')) );
            if (to !== undefined) to.text(left);
            return false;
        }
        if (to !== undefined) {
            to.text(left);
        } else {
            return left;
        }
    };

    // set check all boxes checkbox
    this.checkAllBoxFunc = function (el, wrapper) {
        el.click(function () {
            if (el.is(':checked')) {
                wrapper.find('[type="checkbox"]').attr('checked', true);
            } else {
                wrapper.find('[type="checkbox"]').attr('checked', false);
            }
        });
    };
}

// match passwords and show errors
function passMatcher() {
    var one = $('#passInput'),
        two = $('#passConfirmInput'),
        form = one.closest('form'),
        oneParent = one.parent(),
        twoParent = two.parent(),
        errorMatch = $('#passMatchError'),
        errorRules = $('#passRulesError');

    form.on('keyup', '#passInput, #passConfirmInput', function () {
        var oneVal = one.val(),
            twoVal = two.val();

        if ((twoVal.length >= 5) && (twoVal.length >= oneVal.length) && (oneVal !== twoVal) || hasForbiddenSymbols(oneVal) || hasForbiddenSymbols(twoVal)) {
            oneParent.addClass('error');
            twoParent.addClass('error');
        } else {
            oneParent.removeClass('error');
            twoParent.removeClass('error');
        }

        if ((twoVal.length < 5) || (oneVal !== twoVal) || hasForbiddenSymbols(oneVal) || hasForbiddenSymbols(twoVal) ) {
            one.addClass('noSubmitAllowed');
        } else {
            one.removeClass('noSubmitAllowed');
        }

        if ((twoVal.length >= 5) && (twoVal.length >= oneVal.length) && (oneVal !== twoVal)) {
            errorMatch.show();
        } else {
            errorMatch.hide();
        }

        if (hasForbiddenSymbols(oneVal) || hasForbiddenSymbols(twoVal)) {
            errorRules.show();
        } else {
            errorRules.hide();
        }
    });

    function hasForbiddenSymbols(oneVal) {
        return oneVal.search(/(?![$A-Za-z0-9_.-])./) !== -1;
    }
}


// equal heights of two elements (.css() method must be first)
function eqHeight(first, second) {
    first.css('height', 'auto');
    second.css('height', 'auto');
    var firstH = first.height();
    var secondH = second.height();
    secondH > firstH ? first.height(secondH) : second.height(firstH);
}

// function changing case due to count
function getNumEnding(iNumber, aEndings) {
    var sEnding, i;
    iNumber = iNumber % 100;
    if (iNumber >= 11 && iNumber <= 19) {
        sEnding = aEndings[2];
    } else {
        i = iNumber % 10;
        switch (i) {
            case (1):
                sEnding = aEndings[0];
                break;
            case (2):
            case (3):
            case (4):
                sEnding = aEndings[1];
                break;
            default:
                sEnding = aEndings[2];
        }
    }
    return sEnding;
}

// let enter only numbers spaces etc.
function onlyNum(el) {
    el.keydown(function(event) {
        // Allow: F5, backspace, delete, tab, escape, and enter
        if (event.keyCode == 116 || event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
            // Allow: Ctrl+A
            (event.keyCode == 65 && event.ctrlKey === true) ||
            // Allow: home, end, left, right
            (event.keyCode >= 35 && event.keyCode <= 39)) {
            // let it happen, don't do anything
            return;
        }
        else {
            // Ensure that it is a number and stop the keypress
            if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
                event.preventDefault();
            }
        }
    });
}

// serparate thousads by any separator on change
function sepThousands(el, sep) {
    sep = sep || ' ';
    el.change(function(){
        var parts = el.val().replace(/\s/g, '');
        parts = parts.replace(/\B(?=(\d{3})+(?!\d))/g, sep);
        el.val($.trim(parts));
    });
}

// Common functions
//-----------------

// promzona bootstrap popovers with all needed functionality and view
function setPopovers() {
    var that = this;
    var popoverTrigger = $('[rel="popover"]');
    this.timeoutHide = false;
    this.timeoutShow = false;

    popoverTrigger.popover({
        trigger:'manual',
        placement:'bottom',
        html: true
    })
        .mouseenter(function () {
            var pop = $(this);
            clearTimeout(that.timeoutShow);
            clearTimeout(that.timeoutHide);

            if (!$(this).hasClass('trigger-popovered')) {
                popoverTrigger.removeClass('trigger-popovered');
            }

            if (!pop.hasClass('trigger-popovered')) {
                that.timeoutShow = setTimeout(function () {
                    pop
                        .addClass('trigger-popovered')
                        .popover('show');
                }, 500);
            }
            popoverTrigger.not($(this)).popover('hide');
        })
        .mouseleave(function () {
            clearTimeout(that.timeoutShow);
            clearTimeout(that.timeoutHide);
            that.timeoutHide = setTimeout(function () {
                popoverTrigger
                    .removeClass('trigger-popovered')
                    .popover('hide');
            }, 500);
        });

    $('body')
        .on('mouseenter', '.popover', function () {
            clearTimeout(that.timeoutHide);
        })
        .on('mouseleave', '.popover', function () {
            that.timeoutHide = setTimeout(function () {
                popoverTrigger
                    .removeClass('trigger-popovered')
                    .popover('hide');
            }, 500);
        });
}

// just public navigation
function mainNav() {
    var navLi = $('#nav > ul > li');
    navLi.hover(function () {
        $(this).addClass('open').children('ul').show();
    }, function () {
        $(this).removeClass('open').children('ul').hide();
    });
}

// index carousel using bxSlider
function setIndexCarousel() {
    var carousel = $('#index-carousel');
    if( carousel.find('li').length > 5 ){
        var indexCarousel = carousel.bxSlider({
            auto: true,
            pager: false,
            prevText: '',
            nextText: '',
            minSlides: 5,
            maxSlides: 5,
            moveSlides: 1,
            slideWidth: 140,
            infiniteLoop: true,
            hideControlOnEnd: false,
            autoHover: true
        });
    }
    carousel.find('li a').hover(function () {
        var slide = $(this).parent().parent();
        var hidden = slide.find('.hidden');
        var top = parseInt(slide.offset().top) - 85;
        var left = parseInt(slide.offset().left) - 45;
        var clone = hidden.clone(false);
        $('.hidden').filter('.carousel-added').remove();
        clone.addClass('carousel-added');
        $('body').append(clone);
        clone = $('.hidden').filter('.carousel-added');
        clone
            .css({
                'top':top,
                'left':left
            })
            .show()
            .mouseleave(function () {
                $(this).remove();
            })
    });
}

// index tabs, outmoded way
function setIndexTabListEvents(el) {
    var item = ( typeof el != 'undefined' ? $(el).find('.item') : $('.tab-content .item') );
    var triggerShow = item.find('.triggerShow');
    var triggerHide = item.find('.triggerHide');

    $('body').click(function () {
        item.find('.wrapper').removeClass('open');
    });

    triggerShow.click(function () {
        var elHeight = $(this).parents('li.item').height();

        item.find('.wrapper').removeClass('open');

        $(this)
            .parents('li.item').height(elHeight)
            .find('.wrapper').addClass('open');

        return false;
    });

    triggerHide.click(function () {
        $(this).closest('.wrapper').removeClass('open');
        return false;
    });

}

// authorization modals and page object
function Auth() {
    var pLogin = $('#page-login');
    var pPass = $('#page-reset-password');

    this.setModalEvents = function () {
        var modalAuth = $('#modal-auth');
        var modalLogin = $('#modal-login');
        var modalReset = $('#modal-reset-password');

        formsObj.checkSubmitBtnOnChange(modalLogin);
        formsObj.checkSubmitBtnOnChange(modalReset);
        formsObj.setCommonFormAJAX(modalLogin);
        formsObj.setCommonFormAJAX(modalReset);

        $('#modal-open-login').click(function () {
            modalAuth.modal('show')
                .find('.header li:first-child a').tab('show');
            formsObj.checkSubmitBtn(modalLogin);
        });

        modalAuth.find('.header li:first-child a').click(function () {
            formsObj.checkSubmitBtn($('#modal-login'));
        });

        $('#modal-open-register').click(function () {
            modalAuth.modal('show')
                .find('.header li:last-child a').tab('show');
        });

        $('a[href="#modal-reset-password"]').click(function () {
            formsObj.checkSubmitBtn(modalReset);
        });

    };

    this.setPageEvents = function () {
        formsObj.checkSubmitBtnOnChange(pLogin);
        formsObj.checkSubmitBtnOnChange(pPass);
        formsObj.setCommonFormAJAX(pLogin);
        formsObj.setCommonFormAJAX(pPass);
    }
}

//phones in form, guess need more versatile function
function setPhoneInputGroup() {
    var wrapper = $('.phones-input-group').not('.handled');
    var removeFirstT = '<button class="btn btn-white btn-smaller ml-10 removeFirstPhoneInput" type="button"><i class="icon-remove"></i></button>';
    var addT = '<button class="btn btn-white btn-smaller ml-10 addPhoneInput" type="button"><i class="icon-plus-sign"></i> Добавить телефон</button> ';
    var tipT =
        '<div class="middle-tip-b ml-0"> '+
            'Рекомендуем писать телефонные номера в формате: +7 777 123-45-67'+
        '</div>';
    var template =
        '<div class="input-group ml-220 clearfix">'+
            '<input class="w-160 required phoneInput" type="text" name=""> '+
            '<button class="btn btn-white btn-smaller ml-10 removePhoneInput" type="button"><i class="icon-remove"></i></button> '+
            addT +
            tipT +
        '</div>';

    wrapper.each(function() {
        var currentWrapper = $(this),
            form = currentWrapper.closest('form'),
            currentWrapperIn = false;

        //checking if group is in contacts or branch
        if (currentWrapper.hasClass('contactPhones')) {
            currentWrapperIn = 'contactPhones';
        } else if (currentWrapper.hasClass('branchPhones')) {
            currentWrapperIn = 'branchPhones'
        }

        // checking if input is last
        function checkFirstBtn() {
            if (currentWrapper.find('.input-group').length < 2) {
                currentWrapper
                    .find('.addPhoneInput')
                        .remove()
                        .end()
                    .find('.input-group')
                        .append(addT)
                        .append(tipT)
                        .find('.middle-tip-b')
                            .removeClass('ml-0');
                currentWrapper
                    .find('.removeFirstPhoneInput')
                        .remove();
            } else {
                currentWrapper.find('.input-group:first .removeFirstPhoneInput').length == 0 ? currentWrapper.find('.input-group:first').append(removeFirstT) : '';
            }
        }

        (function setPhoneEvents(currentWrapperIn) {
            currentWrapper
                .addClass('handled')
                .on('click', 'button', function(){
                    var contactNumber, branchNumber;

                    if ($(this).hasClass('addPhoneInput')) {
                        currentWrapper
                            .find('.middle-tip-b')
                                .remove()
                                .end()
                            .find('.addPhoneInput')
                                .remove()
                                .end()
                            .append(template);
                    } else if ($(this).hasClass('removePhoneInput')) {
                        $(this)
                            .parent('.input-group')
                                .remove();
                        currentWrapper.find('.input-group:last .addPhoneInput').length == 0 ? currentWrapper.find('.input-group:last').append(addT) : '';
                    } else if ($(this).hasClass('removeFirstPhoneInput')) {
                        var replacePhone =
                            $(this)
                                .parent('.input-group')
                                    .next()
                                        .find('input[type="text"]')
                                            .val();
                        $(this)
                                .prev('input')
                                    .attr('value', replacePhone)
                                    .end()
                                .parent('.input-group')
                                    .next()
                                        .remove();
                    }

                    checkFirstBtn();

                    switch (currentWrapperIn) {
                        default :
                            currentWrapper.find('.phoneInput').each(function(i, el) {
                                $(this).attr('name', 'f[phone]['+i+']' );
                            });
                            break;

                        case 'contactPhones' :
                            contactNumber = form.find('.oneContact').index() - 1;
                            currentWrapper.find('.phoneInput').each(function(i, el) {
                                $(this).attr('name', 'contacts['+contactNumber+'][phone]['+i+']' );
                            });
                            break;

                        case 'branchPhones' :
                            branchNumber = form.find('.oneBranch').index() - 1;
                            currentWrapper.find('.phoneInput').each(function(i, el) {
                                $(this).attr('name', 'branches['+branchNumber+'][phone]['+i+']' );
                            });
                            break;
                    }

                    formsObj.checkSubmitBtn(form);

                    var cabinetLeftBlock = $('#cabinet-left-b');
                    if (cabinetLeftBlock.length > 0) eqHeight(cabinetLeftBlock, $('#cabinet-right-b'));
                });
        })(currentWrapperIn);
    });
}


function setContactInputGroup() {
    var wrapper = $('.contacts-input-group'),
        form = wrapper.closest('form'),
        addBtn = $('#contactAddContact'),
        addBtnParent = addBtn.parent();

    addBtn.click(function() {
        var number = wrapper.find('.oneContact').length;
    var template =
        '<div class="oneContact">'+
            '<div class="input-group">'+
                '<label class="left" for="contactName' + number + '">'+
                    'Имя контактного лица: <sup class="red">*</sup> '+
                    '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                '</label>'+
                '<input class="required w-320" type="text" id="contactName' + number + '" name="contacts[' + number + '][name]">'+
                '<button type="button" class="btn ml-5 deleteContact"><i class="icon-remove"></i> Удалить</button>'+
            '</div>'+
            '<div class="input-group">'+
                '<label class="left" for="contactPosition' + number + '">'+
                    'Должность: <sup class="red">*</sup> '+
                    '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                '</label>'+
                '<input class="required w-320" type="text" id="contactPosition' + number + '" name="contacts[' + number + '][position]">'+
            '</div>'+
            '<div class="phones-input-group contactPhones">'+
                '<div class="input-group clearfix">'+
                    '<label class="left" for="contactPhone' + number + '">'+
                        'Телефоны: <sup class="red">*</sup> '+
                        '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                    '</label>'+
                    '<input class="w-160 required phoneInput" type="text" id="contactPhone' + number + '" name="contacts[' + number + '][phone][0]"> '+
                    '<button class="btn btn-white btn-smaller ml-10 addPhoneInput" type="button"><i class="icon-plus-sign"></i> Добавить телефон</button>'+
                    '<div class="middle-tip-b"> Рекомендуем писать телефонные номера в формате: +7 777 123-45-67</div>'+
                '</div>'+
            '</div>'+
            '<div class="input-group">'+
                '<label class="left" for="contactEmail' + number + '">'+
                    'Электронная почта: '+
                    '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                '</label>'+
                '<input class="w-320" type="email" id="contactEmail' + number + '" name="contacts[' + number + '][email]">'+
            '</div>'+
            '<hr class="mr-30">'+
        '</div>';

        addBtnParent.before(template);
        setPhoneInputGroup();

        formsObj.checkSubmitBtn(form);
        var cabinetLeftBlock = $('#cabinet-left-b');
        if (cabinetLeftBlock.length > 0) eqHeight(cabinetLeftBlock, $('#cabinet-right-b'));
    });

    wrapper.on('click', '.deleteContact', function() {
        $(this).closest('.oneContact').remove();
        formsObj.checkSubmitBtn(form);
    });
}

function setBranchInputGroup() {
    var wrapper = $('.branches-input-group'),
        form = wrapper.closest('form'),
        addBtn = $('#contactAddBranch'),
        addBtnParent = addBtn.parent();

    addBtn.click(function() {
        var number = wrapper.find('.oneBranch').length;
    var template =
        '<div class="oneBranch">'+
            '<div class="input-group">'+
                '<label class="left" for="branchRegion' + number + '">'+
                    'Регион филиала: <sup class="red">*</sup> '+
                    '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                '</label>'+
                '<input class="required w-320 typeaheaded" autocomplete="off" data-typeaheaded="region" type="text" id="branchRegion' + number + '" name="branches[' + number + '][name]">'+
                '<button type="button" class="btn ml-5 deleteBranch"><i class="icon-remove"></i> Удалить</button>'+
                '<div class="middle-tip-b">Начните вводить название города, затем выберите его из списка.</div>'+
            '</div>'+
            '<div class="input-group">'+
                '<label class="left" for="branchAddress' + number + '">'+
                    'Адрес филиала: <sup class="red">*</sup> '+
                    '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                '</label>'+
                '<input class="required w-320" type="text" id="branchAddress' + number + '" name="branches[' + number + '][address]">'+
            '</div>'+
            '<div class="phones-input-group branchPhones">'+
                '<div class="input-group">'+
                    '<label class="left" for="branchPhone' + number + '">'+
                        'Телефоны филиала: <sup class="red">*</sup> '+
                        '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                    '</label>'+
                    '<input class="w-160 required phoneInput" type="text" id="branchPhone' + number + '" name="branches[' + number + '][phone][0]">'+
                    '<button class="btn btn-white btn-smaller ml-10 addPhoneInput" type="button"><i class="icon-plus-sign"></i> Добавить телефон</button>'+
                    '<div class="middle-tip-b"> Рекомендуем писать телефонные номера в формате: +7 777 123-45-67</div>'+
                '</div>'+
            '</div>'+
            '<div class="input-group">'+
                '<label class="left" for="branchContactName' + number + '"> '+
                    'Имя контактного лица: '+
                    '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                '</label>'+
                '<input class="w-320" type="text" id="branchContactName' + number + '" name="branches[' + number + '][contactName]">'+
            '</div>'+
            '<div class="input-group">'+
                '<label class="left" for="branchContactPosition' + number + '">'+
                    'Должность: '+
                    '<i class="icon-question-sign icon-yellow helper-popup"></i>'+
                '</label>'+
                '<input class="w-320" type="text" id="branchContactPosition' + number + '" name="branches[' + number + '][contactPosition]">'+
            '</div>'+
            '<hr class="mr-30">'+
        '</div>';

        addBtnParent.before(template);
        wrapper.find('.typeaheaded').not('.handled').typeahead({source: currentInfo.typeahead.regions}).addClass('handled');
        setPhoneInputGroup();
        formsObj.checkSubmitBtn(form);
        var cabinetLeftBlock = $('#cabinet-left-b');
        if (cabinetLeftBlock.length > 0) eqHeight(cabinetLeftBlock, $('#cabinet-right-b'));
    });

    wrapper.on('click', '.deleteBranch', function() {
        $(this).closest('.oneBranch').remove();
        formsObj.checkSubmitBtn(form);
    });
}

// catalog selector, bulky
function setCatSelector() {
    var that = this,
        wrapper = $('#catSelector'),
        searchBtn = wrapper.find('.catSearchBtn'),
        searchInput = wrapper.find('.catSearchInput'),
        renderBody = wrapper.find('.catRender'),
        input = wrapper.find('.catHiddenInput'),
        goal = '&goal=list',
        sectionSelectGroup = $('.productSectionGroup'),
        crumbs = wrapper.next('.cat-selector-crumbs').find('.append'),
        addCrumbs = $('.catSelectorPseudoCrumbs'),
        modal = $('#catSelectorModal'),
        sendLink = '/ajaxfile.php?mod=cabinet&action=add';
    this.rootID = '&rootID=' + input.val();

    function setSectionSelect() {
        var id = sectionSelectGroup.find(':checked').data('root-id');
        that.rootID = '&rootID=' + id;
        goal = '&goal=list';

        $.getJSON(sendLink + that.rootID + goal, function(response) {
            renderCats(response);
            if (addCrumbs.length > 0) {
                addCrumbs.find('.append').remove();
                crumbs.clone().appendTo(addCrumbs);
                changeInput(renderBody);
            }
        });
    }

    if (sectionSelectGroup.length > 0) {
        sectionSelectGroup.on('change', ':radio', function() {
            setSectionSelect();
        })
    }

    //click li render
        renderBody.on('click', 'li:not(.active):not(.false)', function () {
        var id = $(this).attr('id');
        var url = sendLink + that.rootID + '&id=' + id + goal;

        if (!$(this).hasClass('obj') && !$(this).hasClass('active')) {
            var active = $(this);
            $.getJSON(url, function(response) {
                renderCats(response, active)
            });
        } else {
            var activeList = $(this).parent('ul');
            activeList.nextAll().remove();
            activeList.find('li').removeClass('active');
            $(this).addClass('active');
            renderCrumbs(renderBody);
        }
    });

    // key search events
    searchInput.keyup(function() {
        if (searchInput.val().length == 0) {
            goal = '&goal=list';
            $.getJSON(sendLink + that.rootID + goal, function(response) {
                renderCats(response)
            });
        }
    })
        .keydown(function(event) {
            if (event.which == 13) {
                searchEvent();
                return false
            }
        });

    // click search events
    searchBtn.click(function() {
        searchEvent();
    });

    if (input.hasClass('modal')) {
        var btn = modal.find('.catAccept');
        btn.click(function() {
            changeInput(renderBody);
            modal.modal('hide');
            if (addCrumbs.length > 0) {
                addCrumbs.find('.append').remove();
                crumbs.clone().appendTo(addCrumbs);
                formsObj.checkSubmitBtn( renderBody.parents('form') );
            }
        });
    }

    function searchEvent() {
        var word = searchInput.val();

        if (word.length > 0) {
            goal = '&goal=search';
            $.getJSON(sendLink + that.rootID + '&search=' + word + goal, function(response) {
                renderCats(response);
            });
        } else {
            goal = '&goal=list';
            $.getJSON(sendLink + that.rootID + goal, function(response) {
                renderCats(response)
            });
        }

        return goal;
    }

    function renderCats(response, active) {
        var template;
        if(!$.isEmptyObject(response)) {
            template ='<ul>';
            $.each(response, function(id, el) {
                var nameArr = el.name.split('&;');
                template += '<li id="' + id + '"' + (el.object ? 'class="obj"' : '') + '>';
                $.each(nameArr, function(i, el) {
                    template += '<span>' + el + '</span>';
                });
                template += '</li>';
            });
            template += '</ul>';
        } else {
            template = '<ul><li class="false">По вашему запросу ничего не найдено</li></ul>'
        }

        if (active) {
            var activeList = active.parent('ul');
            if ($(this).hasClass('obj')) {
                activeList.nextAll().remove();
                activeList.find('li').removeClass('active');
                active.addClass('active');
            } else {
                activeList.nextAll().remove();
                activeList.find('li').removeClass('active');
                active.addClass('active');
            }
        } else {
            renderBody.empty();
        }

        renderBody
            .append(template)
            .stop(true)
            .animate({
                scrollLeft: (wrapper.find('ul:last-child').position().left) + (renderBody.scrollLeft())
            }, 1000);

        renderCrumbs(renderBody);
    }

    function renderCrumbs(body) {
        crumbs.empty();

        body.find('.active').each(function() {
            if (!$(this).hasClass('obj')) {
                crumbs.append($(this).html());
            } else {
                crumbs.append($(this).html());
                crumbs.find('span:last').addClass('obj');
            }
        });
        if (!input.hasClass('modal')) {
            changeInput(body);
        } else {
            if (body.find('.active.obj').length > 0) {
                btn.removeAttr('disabled');
            } else {
                btn.prop('disabled', true);
            }
        }

        var cabinetLeftBlock = $('#cabinet-left-b');
        if (cabinetLeftBlock.length > 0) eqHeight(cabinetLeftBlock, $('#cabinet-right-b'));
    }

    function changeInput(body) {
        if (body.find('.active.obj').length > 0) {
            input.val(body.find('.active.obj').attr('id'))
                .triggerHandler('change');
        } else {
            wrapper.closest('.step-b').next().addClass('disabled-el');
            input.val('')
                .triggerHandler('change');
        }
    }
}

// photo uploader via fineUploader
function setPhotoUploader() {
    var wrapperBody = $('.photoUploader'),
        wrapperResponse = $('.photoUploaderResponse'),
        parentForm = wrapperResponse.closest('form'),
        noPhotoDummy = $('.noPhotoDummy'),
        mainPhotoInput = $('.mainPhotoInput');

    var checkFileLimit = function(){
        var count = wrapperResponse.find('.item').length;
        var limit = 7;
        var btn   = wrapperBody.find('.qq-upload-button');
        if( count >= limit ){
            btn.addClass('disabled').append('<span class="btn-overlay" />');
            for( var index = limit; index < count; index++){
                wrapperResponse.find('.item').filter(':eq('+ index +')').remove();
            }
        }else btn.removeClass('disabled').find('.btn-overlay').remove();
    };

    wrapperBody
        .fineUploader({
            request: {
                endpoint: '/uploader.php'
            },
            allowedExtensions: ['jpg','jpeg','gif','png'],
            template:
                '<pre class="qq-upload-drop-area" style="display:none !important;"><span>{dragZoneText}</span></pre>' +
                '<div class="qq-upload-button btn btn-white soft" style="width: auto;"><span><i class="icon-picture"></i> Выбрать фотографии</span></div>' +
                '<ul class="qq-upload-list" style="display:none !important;"></ul>',
            classes: {
                success: 'alert alert-success',
                fail: 'alert alert-error'
            }
        })
        .on('submit',function(id, fileName) {
            checkFileLimit();
        })
        .on('complete', function(event, id, fileName, response) {
            if (response.success) {
                var alone = wrapperResponse.find('.item').length == 0;
                var template =
                    '<div class="item clearfix ' + (alone ? 'main': '') + '">'+
                        '<img src="/cms/uploads_temp/' + response.filename + '" alt="" class="mr-10 va-t pull-left">'+
                        '<a class="pseudo dashed red fz-12 delPhoto mb-5 d-ib" href="javascript:void(0)"><i class="icon-trash icon-red"></i> <span>Удалить фото</span></a><br>'+
                        (alone ? '<b class="ml-5">Основное фото</b>': '<a class="dashed ml-5 makeMain" href="javascript:void(0)">Сделать основным</a>')+
                        '<input type="hidden" name="photo[' + id + ']" value="' + response.filename + '">'+
                    '</div>';

                if(alone) mainPhotoInput.val(response.filename);

                noPhotoDummy.hide();
                wrapperResponse.show().append(template);

                formsObj.checkSubmitBtn(parentForm);

                var cabinetLeftBlock = $('#cabinet-left-b');
                if (cabinetLeftBlock.length > 0) {
                    setTimeout( function() {
                        eqHeight(cabinetLeftBlock, $('#cabinet-right-b'))
                    }, 30);
                }

                checkFileLimit();
            }
        });

    checkFileLimit();

    wrapperResponse
        .on('click', '.delPhoto', function() {
            var item = $(this).parent();

            if (item.hasClass('main')) {
                var next = item.next();
                var value = next.find('input').val();

                next
                    .addClass('main')
                    .find('.makeMain')
                        .remove()
                        .end()
                    .append('<b class="ml-5">Основное фото</b>');

                mainPhotoInput.val(value);
            }
            item.remove();

            if(wrapperResponse.find('.item').length == 0) {
                noPhotoDummy.show();
                wrapperResponse.hide();
            }

            formsObj.checkSubmitBtn(parentForm);

            var cabinetLeftBlock = $('#cabinet-left-b');
            if (cabinetLeftBlock.length > 0) eqHeight(cabinetLeftBlock, $('#cabinet-right-b'));

            checkFileLimit();

        })
        .on('click', '.makeMain', function() {
            var item = $(this).parent();
            var value = item.find('input').val();

            wrapperResponse
                .find('.item.main')
                    .removeClass('main')
                    .find('b')
                    .remove()
                    .end()
                .append('<a class="dashed ml-5 makeMain" href="javascript:void(0)">Сделать основным</a>');

            item
                .addClass('main')
                .find('.makeMain')
                    .remove()
                    .end()
                .append('<b class="ml-5">Основное фото</b>');

            mainPhotoInput.val(value);
            formsObj.checkSubmitBtn(parentForm);
        });
}

// logo uploader via fineUploader
function setLogoUploader() {
    var wrapperBody = $('.logoUploader'),
        wrapperResponse = $('.logoUploaderResponse'),
        input = wrapperResponse.find('input'),
        parentForm = input.closest('form');

    wrapperBody
        .fineUploader({
            multiple: false,
            request: {
                endpoint: '/uploader.php'
            },
            allowedExtensions: ['jpg','jpeg','gif','png'],
            template:
                '<pre class="qq-upload-drop-area" style="display:none !important;"><span>{dragZoneText}</span></pre>' +
                '<div class="qq-upload-button btn btn-white soft" style="width: auto;"><span><i class="icon-upload"></i> ' + (input.val().length > 0 ? 'Заменить логотип'  : 'Загрузить логотип') + '</span></div>' +
                '<ul class="qq-upload-list" style="display:none !important;"></ul>',
            classes: {
                success: 'alert alert-success',
                fail: 'alert alert-error'
            },
            debug: true
        })
        .on('complete', function(event, id, fileName, response) {
            if (response.success) {
                wrapperResponse
                    .find('img').remove().end()
                    .prepend('<img src="/cms/uploads_temp/' + response.filename + '" alt="" class="mr-10 va-t">')
                    .show();
                wrapperBody
                    .find('.btn > span').html('<i class="icon-upload"></i> Заменить логотип');
                input
                    .val(response.filename);

                if( !parentForm.hasClass('required-container') ) formsObj.checkSubmitBtn(parentForm);

                var cabinetLeftBlock = $('#cabinet-left-b');
                if (cabinetLeftBlock.length > 0) {
                    setTimeout( function() {
                        eqHeight(cabinetLeftBlock, $('#cabinet-right-b'))
                    }, 100);
                }
            }
        });

    wrapperResponse.on('click', '.delLogo', function() {
        wrapperResponse
            .find('img').remove().end()
            .hide();
        wrapperBody
            .find('.btn > span').html('<i class="icon-upload"></i> Загрузить логотип');

        formsObj.checkSubmitBtn(parentForm);

        var cabinetLeftBlock = $('#cabinet-left-b');
        if (cabinetLeftBlock.length > 0) eqHeight(cabinetLeftBlock, $('#cabinet-right-b'));
    });
}

// file uploader via fineUploader
function setPriceUploader() {
    var wrapperBody = $('.priceUploader'),
        wrapperResponse = $('.priceUploaderResponse'),
        parentForm = wrapperResponse.closest('form'),
        mainPriceInput = $('.mainPriceInput');

    wrapperBody
        .fineUploader({
            request: {
                endpoint: '/uploader.php'
            },
            allowedExtensions: ['jpg','jpeg','gif','png'],
            template:
                '<pre class="qq-upload-drop-area" style="display:none !important;"><span>{dragZoneText}</span></pre>' +
                    '<div class="qq-upload-button btn btn-white soft"><i class="icon-upload"></i> Загрузить прайс-лист</div>' +
                    '<ul class="qq-upload-list" style="display:none !important;"></ul>',
            classes: {
                success: 'alert alert-success',
                fail: 'alert alert-error'
            }
        })
        .on('complete', function(event, id, fileName, response) {
            if (response.success) {
                var template =
                    '<div class="item clearfix mr-30 mb-10 hide">'+
                        '<a class="pseudo dashed red fz-12 delPrice mb-5 d-ib pull-right mr-30" href="javascript:void(0)"><i class="icon-trash icon-red"></i> <span>Удалить прайс-лист</span></a>'+
                        '<span>' + fileName + '</span>'+
                        '<input type="hidden" name="companyPrice[' + id + ']" value="' + response.filename + '">'+
                        '</div>';
                wrapperResponse
                    .show()
                    .append(template)
                    .find('.item')
                        .fadeIn('slow');

                formsObj.checkSubmitBtn(parentForm);

                var cabinetLeftBlock = $('#cabinet-left-b');
                if (cabinetLeftBlock.length > 0) {
                    setTimeout( function() {
                        eqHeight(cabinetLeftBlock, $('#cabinet-right-b'))
                    }, 30);
                }
            }
        });

    wrapperResponse
        .on('click', '.delPrice', function() {
            var item = $(this).parent();

            item.remove();

            formsObj.checkSubmitBtn(parentForm);

            var cabinetLeftBlock = $('#cabinet-left-b');
            if (cabinetLeftBlock.length > 0) eqHeight(cabinetLeftBlock, $('#cabinet-right-b'));
        });
}

function setCatalogFilter() {
    var form = $('.catalog-filter'),
        toggle = $('.catalogFilterToggle'),
        shown = form.find('.shown'),
        extended = form.find('.extended'),
        templateOff = '<span class="off">Ещё</span> <i class="icon-chevron-down icon-yellow"></i>',
        templateOn = '<span>Скрыть</span> <i class="icon-chevron-up icon-yellow"></i>'
    ;

    var formChangeSubmit = form.on('change', function() {
        if (toggle.find('span').hasClass('off')) $(this).submit();
    });

        toggle.click(function() {
            if (toggle.find('span').hasClass('off')) {
                extended.stop(true, true).slideDown();
                toggle.html(templateOn);
                return false;
            } else {
                extended.stop(true, true).slideUp();
                toggle.html(templateOff);
                return false;
            }
        });
}

function setPhonesToggler() {
    var wrapper = $('[data-toggle="phones"]'),
        that = this;
    wrapper
        .off('click')
        .on('click',function(e) {
            el = e.target;
            while (el !== this) {
                if ($(el).hasClass('toggle')) {
                    $(el).remove();
                        $(this).find('.all').show();
                    return false;
                }
                el = el.parentNode;
            }
        });
}

// show extend search
function showEntendSearch(){
    var extendSearch    = $('.extend-search');
    var searchQuery     = $('#search-b').children('form').filter(':first').find('.input-append').children('input[type="text"]');
    var searchQueryWin  = extendSearch.find('input.inp-search-query');
    var btnCancel       = extendSearch.find('.btn-reset-filter');
    $('body').prepend('<div id="overlay" />');
    $('#overlay').on('click',function(){
        searchQuery.val( ( searchQueryWin.val() == searchQueryWin.attr('placeholder') ? searchQuery.attr('placeholder') : searchQueryWin.val() ) );
        $(this).remove();
        extendSearch.fadeOut('fast');
    });
    var dropDownCategory = extendSearch.find('.dropdown-category');
    var category = dropDownCategory.find('.active').filter(':last').children('a');
    dropDownCategory
        .find('input[name="category"]')
//            .data('default', ( typeof dropDownCategory.find('input[name="category"]').data('default') == 'undefined' ? category.attr('alt') : dropDownCategory.find('input[name="category"]').data('default') ) )
            .val( category.attr('alt') ).end()
        .find('.dropdown-toggle')
            .find('.text')
                .text( category.text() ).end().end()
        .delegate('.dropdown-menu a','click',function(){
            var categoryId   = $(this).attr('alt');
            var categoryName = $(this).text();
            dropDownCategory
                .find('input[name="category"]')
                    .val( categoryId ).end()
                .find('.dropdown-toggle')
                    .find('.text')
                        .text( categoryName ).end().end()
                .find('li')
                    .removeClass('active')
                    .find('a')
                        .filter('[alt="'+ categoryId +'"]')
                    .parent('li')
                        .addClass('active')
                    .parents('.dropdown-submenu')
                        .addClass('active');
            btnCancel.removeAttr('disabled');
        });
    category
        .parents('.dropdown-submenu')
            .addClass('active');
    extendSearch
        .fadeIn('fast')
        .find('.close')
            .off('click')
            .on('click',function(){
                searchQuery.val( ( searchQueryWin.val() == searchQueryWin.attr('placeholder') ? searchQuery.attr('placeholder') : searchQueryWin.val() ) );
                $('#overlay').remove();
                extendSearch.fadeOut('fast');
            }).end()
        .find('.section-radio')
            .find('input[type="radio"]')
                .on('change',function(){
                    var sectionId  = $(this).val();
                    var categories = extendSearch.find('.dropdown-category');
                    var template   = '<input type="hidden" name="category" value="" />'+
                                     '<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="text">Все категории</span> <span class="caret"></span></button>'+
                                     '<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">';
                    var loader     = '<img src="/img/loading.gif" class="loader" style="margin-left:5px;" />';
                    var inner_count, template_inner;
                    categories
                        .find('.dropdown-toggle')
                            .attr('disabled','disabled')
                            .after( loader );
                    $.getJSON('/ajaxfile.php',{ 'section':sectionId },function( response ){
                        $.each( response, function( id, items ){
                            template_inner = '';
                            $.each( items.inner, function( categoryId, categoryName ){
                                template_inner += '<li><a href="#" alt="'+ categoryId +'">'+ categoryName +'</a></li>';
                            });
                            if( template_inner != '' ){
                                template += '<li class="dropdown-submenu">'+
                                                '<a tabindex="-1" href="#" alt="'+ id +'">'+ items.name +'</a>'+
                                                '<ul class="dropdown-menu">'+ template_inner +'</ul>'+
                                            '</li>';
                            }else template += '<li><a href="#" alt="'+ id +'">'+ items.name +'</a></li>';
                        });
                        template += '</ul>';
                        categories
                            .html( template );
                    });
                }).end().end()
        .find('.btn-reset-filter')
            .off('click')
            .on('click',function(){
                var form = $(this).parents('form');
//                var categoryId = form.find('input[name="category"]').data('default');
//                form
//                    .find('.dropdown-category')
//                        .find('input[name="category"]')
//                            .val( categoryId ).end()
//                        .find('.dropdown-toggle')
//                            .find('.text')
//                                .text( form.find('a').filter('[alt="'+ categoryId +'"]').text() ).end().end()
//                        .find('li')
//                            .removeClass('active')
//                            .find('a')
//                                .filter('[alt="'+ categoryId +'"]')
//                                    .parent('li')
//                                        .addClass('active')
//                                    .parents('.dropdown-submenu')
//                                        .addClass('active');
                returnDefaults( form );
            });
    searchQueryWin
        .val( ( searchQuery.val() != '' ? searchQuery.val() : '' ) )
        .focus();
}

// return defaults
function returnDefaults(el){
    var form = $(el);
    form
        .find('input,textarea,select,label,button')
            .each(function(){
                $(this).attr('disabled', $(this).data('disabled') );
            }).end()
        .find('input[type="text"]')
            .each(function(){
                $(this).val( $(this).data('default') );
            }).end()
        .find('input[type="checkbox"],input[type="radio"]')
            .each(function(){
                $(this).attr('checked', $(this).data('default') );
            }).end()
        .find('label')
            .each(function(){
                $(this)
                    .removeClass('active')
                    .addClass( ( $(this).data('default') ? 'active' : '' ) )
            }).end()
        .find('select')
            .each(function(){
                $(this).val( $(this).data('default') );
            });
}

// get defaults values extend search
function getDefaultValues( el ){
    var form = $(el);
    if( typeof form.data('defaultsReady') != 'undefined' ) return false;
    form
        .data('defaultsReady', true )
        .find('select,textarea,input,label,button')
            .each(function(){
                $(this).data('disabled', $(this).is(':disabled') );
            }).end()
        .find('input[type="text"]')
            .each(function(){
                $(this).data('default', $(this).val() );
            }).end()
        .find('input[type="checkbox"],input[type="radio"]')
            .each(function(){
                $(this).data('default', $(this).is(':checked') );
            }).end()
        .find('label')
            .each(function(){
                $(this).data('default', $(this).hasClass('active') );
            }).end()
        .find('select')
            .each(function(){
                $(this).data('default', $(this).val() );
            });
}

// init modal photo slider
function initModalPhotoSlider(){
    var modal   = $('#modal-photos'),
        slider  = modal.find('.carousel'),
        thumbs  = modal.find('.thumbs');
    thumbs.find('li').on('click',function(){
        slider.carousel( $(this).index() );
    });
    modal
        .find('.carousel-control').on('click',function(){
            slider.carousel( $(this).attr('data-slide') );
        }).end()
        .on('show',function(){
            slider
                .carousel()
                .bind('slid',function(){
                    thumbs.find('li').removeClass('active').filter(':eq('+ slider.find('.item').filter('.active').index() +')').addClass('active');
                });
        });
    $('.photo-in')
        .find('.photo')
            .find('img')
            .on('click',function(){
                $('.photo-in').find('.thumbs').find('li').filter(':first').click();
            }).end().end()
        .find('.thumbs').find('li').on('click',function(){
            var index = $(this).index();
            modal
                .find('.item')
                    .removeClass('active')
                        .filter(':eq('+ index +')')
                            .addClass('active').end().end()
                .find('.thumbs li')
                    .removeClass('active')
                        .filter(':eq('+ index +')')
                            .addClass('active').end().end()
                .modal('show');
    });
}

// check required
function initCheckRequired(){
    var form, inp, btn;
    $('.required-inp').on('keyup',function(){
        form = $(this).parents('.required-container');
        btn  = form.find('.required-btn');
        btn.removeAttr('disabled');
        form.find('.required-inp').each(function(){
            if( !$(this).val() ) btn.attr('disabled','disabled');
        });
        form.find('.required-checkbox').each(function(){
            if( !$(this).is(':checked') ) btn.attr('disabled','disabled');
        });
    });
    $('.required-checkbox').on('change',function(){        
        form = $(this).parents('.required-container');
        btn  = form.find('.required-btn');
        btn.removeAttr('disabled');
        if( form.find('.required-checkbox').filter(':checked').length == 0 ) btn.attr('disabled','disabled');
    });
}

// init interrelated events 
function initInterrelatedEvents(){
    var container, checkboxes, amount_checkbox, radios, amount_radio, hide, show, hide_outer, show_outer;
    $('.interrelated-container').each(function(){
        container       = $(this);
        amount_checkbox = container.find(':checkbox.interrelated-amount-checkbox');
        checkboxes      = container.find(':checkbox.interrelated-checkbox');
        hide            = container.find('.interrelated-hide');
        show            = container.find('.interrelated-show');
        hide_outer      = $('.interrelated-hide-outer');
        show_outer      = $('.interrelated-show-outer');
        checkboxes
            .off('change')
            .on('change',function(){
                if( checkboxes.length == checkboxes.filter(':checked').length ) amount_checkbox.attr('checked','checked').parent('label').addClass('active'); else amount_checkbox.removeAttr('checked').parent('label').removeClass('active');
                if( checkboxes.filter(':checked').length > 0 ){
                    hide.show();
                    show.hide();
                    hide_outer.show();
                    show_outer.hide();
                }else{
                    hide.hide();
                    show.show();
                    hide_outer.hide();
                    show_outer.show();
                }
            });
        amount_checkbox
            .off('change')
            .on('change',function(){
                if( $(this).is(':checked') ){
                    checkboxes.attr('checked','checked');
                    $(this).parent('label').addClass('active');
                    hide.show();
                    show.hide();
                    hide_outer.show();
                    show_outer.hide();
                }else{
                    checkboxes.removeAttr('checked');
                    $(this).parent('label').removeClass('active');
                    hide.hide();
                    show.show();
                    hide_outer.hide();
                    show_outer.show();
                }
            });
    });
}

function openWindow( url, width, height ){
    window.open( url, 'print', 'resizable=1,' + 'width=' + width + ',height=' + height + ',left=' + ((window.innerWidth - width)/2) + ',top=' + ((window.innerHeight - height)/2) )
}

function initDatePicker(){
    ;(function($){
        $.fn.datepicker.dates['ru'] = {
            days: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота", "Воскресенье"],
            daysShort: ["Вск", "Пнд", "Втр", "Срд", "Чтв", "Птн", "Суб", "Вск"],
            daysMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"],
            months: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
            monthsShort: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"],
            today: "Сегодня"
        };
    }(jQuery));
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        language: 'ru'
    })
        .on('changeDate',function(ev){
            $(this).datepicker('hide');
        });
}


//Change marks
$(function(){
	$('.productSectionGroup').on('change', ':radio', function() {
		var link = '/ajaxfile.php?mod=cabinet&action=add';
		var goal = '&goal=marks';
		var id =$('.productSectionGroup').find(':checked').data('root-id');
		marks_head = '&marks_head=255';
		if (id === 261) marks_head = '&marks_head=3936';
		console.log(id);
		$.post(link + goal + marks_head, function( response ){
			$('#productBrand').html('<option class="default">Выбрать марку</option>').append( response );
		});
		// $('#productBrand').
	});
});

//Если блоки Гугл Баннера пусты то эти блоки удаляются
setTimeout(function() {
    $.each($('div[id ^= "div-gpt-ad-"]'), function(idx, value) {
        var fold = true;
        $.each($(value).find('iframe'), function(i, v) {
            if ($(v).contents().find('body').html().length !== 0) {
                fold = false;
            }
        });
        if (fold) {
            $(value).remove();
        }
    });
}, 200);