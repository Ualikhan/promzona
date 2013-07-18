$(function () {

    if ($('#cabinet-filter-form').length > 0) {
        setCabinetFilterListener()
    }

    if ($('#cabinet-all-ads-edit').length > 0) {
        setCabinetAdsEvents();
    }

    if ($('#ad-placement').length > 0) {
        adPlacement();
    }

    if ($('#singleAdEditControls').length > 0) {
        singleAdEditControls();
    }

    if($('.matchPasswords').length > 0 ) {
        passMatcher();
    }

    if ($('#companyKeyWords').length > 0) {
        formsObj.textMaxWordsCutter($('#companyKeyWords'), 10);
    }

    if ($('#companyDescription').length > 0 ) {
        var companyDesc = $('#companyDescription');
        var companyDescCount = $('#companyDescCount');

        companyDesc.keyup(function() {
            if(formsObj.letterLeftCounter(companyDesc) <= 400) {
                companyDescCount.show();
                formsObj.letterLeftCounter(companyDesc, companyDescCount.find('span'));
            } else {
                companyDescCount.hide();
            }
            if(formsObj.letterLeftCounter(companyDesc) <= 50) {
                companyDescCount.addClass('orange').removeClass('black')
            } else {
                companyDescCount.addClass('black').removeClass('orange')
            }
        });
    }

    if ($('.packageOrderPrices').length > 0) {
        $('.packageOrderPrices tr').click(function() {
            window.location.href = $(this).data('location');
        });
    }

    if ($('.cabinet-bills-filter').length > 0) {
        $('.cabinet-bills-filter').on('change', function() {
            $(this).submit();
        });
    }

    if ($('.billFormingForm').length > 0 ) {
        $('.billFormingForm').change(function() {
            var checked = $(this).find(':checked'),
                wrapper = $(this);
                if (checked.hasClass('entity')) {
                    wrapper.find('.forEntity').show()
                } else {
                    wrapper.find('.forEntity').hide()
                }
        });
    }

    if ($('#contacts-edit').length > 0 && window.location.hash == "#contacts-edit") {
        $('#promzonaLink').focus();
    }

    setTimeout(function() {
        eqHeight($('#cabinet-left-b'), $('#cabinet-right-b'));
    }, 0);

    if( $('.add-news-form').length > 0 ){
        loadAddNewsFormEevnts();    
        if( $('#uploadPhoto').is(':visible') > 0 ){
            initNewsUploadPhoto();
        }
    }

    if( $('.edit-news-form').length > 0 ){
        loadEditNewsFormEvents();
    }

    if( $('.edit-adv-form').length > 0 ){
        loadEditAdvsFormEvents();
    }

    $('.btnNewsPreview').on('click',function(){
        newsPreviewWin( $(this) );
    });

});

// filter to choose ads type
function setCabinetFilterListener() {
    var form = $('#cabinet-filter-form');

    form.find('.disabled input').attr('disabled', 'disabled');
    form.find(':input').change(function () {
        form.submit();
    });
}

// cabinet ads modals, radios etc.
function setCabinetAdsEvents() {
    var form = $('#cabinet-all-ads-edit'),
        checkAllBtn = $('#cabinet-check-all'),
        adsControlBtns = $('.ads-controls').find('button[type="button"]'),
        adsTable = $('#my-ads-table');

        formsObj.checkAllBoxFunc(checkAllBtn, form);

    // click on modal trigger
    adsControlBtns.click(function(){
        var template = false,
            checkedBoxCount = adsTable.find(':checkbox:checked').length,
            sumRise = checkedBoxCount * currentInfo.prices.riseAds,
            sumHighlight = checkedBoxCount * currentInfo.prices.highlightAds,
            sumHot = checkedBoxCount * currentInfo.prices.hotAds,
            one = checkedBoxCount == 1
        ;
        
        if (checkedBoxCount > 0) {
            var type = $(this).data('type');
        }

        switch (type) {

            default:
                template =
                    '<div id="appendedModal" class="modal hide fade w-690" aria-hidden="true">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Вы не выбрали ни одного объявления.</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p>Сообщение о том, что пользователь не выбрал ни одного объявления</p>'+
                        '</div>'+
                        '<div class="modal-footer ta-l">'+
                            '<button class="btn btn-white btn-grand" data-dismiss="modal" aria-hidden="true">Закрыть</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalArchiveAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690" aria-hidden="true">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red bold" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Отправить в архив</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p>Вы действительно хотите отправить в архив выбранные объявления?</p>'+
                        '</div>'+
                        '<div class="modal-footer ta-l">'+
                            '<button class="btn btn-grey btn-grand" name="archivate" value="1">В архив</button>'+
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Закрыть</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalRecoverAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690" aria-hidden="true">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Восстановить объявления</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p>Восстановить выбранные объявления?</p>'+
                        '</div>'+
                        '<div class="modal-footer ta-l">'+
                            '<button class="btn btn-grey btn-grand">Удалить</button>'+
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalDeleteAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690" aria-hidden="true">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Удалить объявления</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p>Вы действительно хотите удалить выбранные объявления?</p>'+
                        '</div>'+
                        '<div class="modal-footer ta-l">'+
                            '<button class="btn btn-grey btn-grand" name="delete" value="1">Удалить</button>'+
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalRiseAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Поднять ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявления']) + '</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p>Вы выбрали <b>' + checkedBoxCount + ' ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявлений']) + '</b>, стоимость поднятия на первые позиции в поиске и категориях одного объявления = <b>' + currentInfo.prices.riseAds + ' кредитов.</b></p>'+
                            '<p class="mb-10"><b>С вашего баланса будет списано:</b></p>';
                if (one) {
                    template +=
                            '<p><b class="fz-18 orange credits">' + currentInfo.prices.riseAds +' кредитов</b></p>';
                } else {
                    template +=
                            '<p class="fz-18 credits">' + checkedBoxCount + ' ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявлений']) + ' × ' + currentInfo.prices.riseAds + ' кр. = <b class="orange">' + sumRise + ' кредитов</b></p>';
                }
                if ((currentInfo.user.balance - sumRise) >= 0) {
                    template +=
//                           '<p><b class="fz-18 orange credits">' + sumRise +' кредитов</b></p>'+
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td>Текущий баланс:</td>'+
                                    '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Баланс после поднятия:</td>'+
                                    '<td>' + (currentInfo.user.balance - sumRise) + ' кр.</td>'+
                                '</tr>'+
                            '</table>'
                } else {
                    template +=
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td><b class="red">Текущий баланс:</b></td>'+
                                    '<td><b class="red">' + currentInfo.user.balance + ' кр.</b></td>'+
                                '</tr>'+
                            '</table>'+
                            '<span class="red fz-12">На вашем балансе не хватает кредитов для оплаты данной услуги. <a class="bd-beige" href="/cabinet/refill/">Пополнить баланс →</a></span>';
                }
                template +=
                        '</div>'+
                        '<div class="modal-footer ta-l">';
                if ((currentInfo.user.balance - sumRise) >= 0) {
                    if (one) {
                        template +=
                            '<button class="btn btn-grey btn-grand" name="rise" value="1">Поднять объявление</button>';
                    } else {
                        template +=
                            '<button class="btn btn-grey btn-grand" name="rise" value="1">Поднять ' + checkedBoxCount + ' ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявлений']) + '</button>';
                    }
                } else {
                    template +=
                            '<a href="/cabinet/refill/" class="btn btn-orange btn-grand">Пополнить баланс</a>';
                }
                template +=
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalHighlightAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690" aria-hidden="true">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Выделить ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявления']) + '</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p class="mb-0 required">'+
                                'Укажите цвет которым будет выделено выбранное объявление:<br>'+
                                '<span class="inline-frame group frame-green"><input type="radio" name="HighlightColor" id="HighlightGreen" value="1"><label for="HighlightGreen"><b>Зелёный</b></label></span>'+
                                '<span class="inline-frame group frame-yellow"><input type="radio" name="HighlightColor" id="HighlightYellow" value="2"><label for="HighlightYellow"><b>Жёлтый</b></label></span>'+
                                '<span class="inline-frame group frame-blue"><input type="radio" name="HighlightColor" id="HighlightBlue" value="3"><label for="HighlightBlue"><b>Голубой</b></label></span>'+
                            '</p>'+
                            '<p>Стоимость выделения и поднятия на первые позиции в поиске и категориях одного объявления = <b>' + currentInfo.prices.highlightAds + ' кредитов.</b></p>'+
                            '<p class="mb-10"><b>С вашего баланса будет списано:</b></p>';
                if (one) {
                    template +=
                        '<p><b class="fz-18 orange credits">' + currentInfo.prices.highlightAds +' кредитов</b></p>';
                } else {
                    template +=
                            '<p class="fz-18 credits">' + checkedBoxCount + ' ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявлений']) + ' × ' + currentInfo.prices.highlightAds + ' кр. = <b class="orange">' + sumHighlight + ' кредитов</b></p>';
                }
                if (currentInfo.user.balance >= sumHighlight) {
                    template +=
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td>Текущий баланс:</td>'+
                                    '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Баланс после поднятия:</td>'+
                                    '<td>' + (currentInfo.user.balance - sumHighlight) + ' кр.</td>'+
                                '</tr>'+
                            '</table>';

                } else {
                    template +=
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td>Текущий баланс:</td>'+
                                    '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                '</tr>'+
                            '</table>'+
                            '<span class="red fz-12">На вашем балансе не хватает кредитов для оплаты данной услуги. <a class="bd-beige" href="/cabinet/refill/">Пополнить баланс →</a></span>';
                }
                template +=
                        '</div>'+
                        '<div class="modal-footer ta-l">';
                if ((currentInfo.user.balance - sumHighlight) >= 0) {
                    if (one) {
                        template +=
                            '<button class="btn btn-grey btn-grand" name="highlight" value="1">Выделить объявление</button>';
                    } else {
                        template +=
                            '<button class="btn btn-grey btn-grand" name="highlight" value="1">Выделить ' + checkedBoxCount + ' ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявлений']) + '</button>';
                    }
                } else {
                    template +=
                        '<a href="/cabinet/refill/" class="btn btn-orange btn-grand">Пополнить баланс</a>';
                }
                template +=
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalHotAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Разместить ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявления']) + ' в горячих предложениях</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p>Вы выбрали <b>' + checkedBoxCount + ' ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявлений']) + '</b>, стоимость размещения объявления в горячих предложениях и добавление в ротацию на главной странице = <b>' + currentInfo.prices.hotAds + ' кредитов.</b></p>'+
                            '<p>' + getNumEnding(checkedBoxCount, ['Объявление', 'Объявления', 'Объявления']) + ' также ' + getNumEnding(checkedBoxCount, ['будет', 'будут', 'будут']) + ' ' + getNumEnding(checkedBoxCount, ['выделено', 'выделены', 'выделены']) + ' <span class="inline-frame frame-orange">оранжевым</span> цветом и поднято на первые позиции в поиске и категориях.</p>'+
                            '<p class="mb-10"><b>С вашего баланса будет списано:</b></p>';
                if ((currentInfo.user.balance - sumHot) >= 0) {
                    template +=
                           '<p><b class="fz-18 orange credits">' + sumHot +' кредитов</b></p>'+
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td>Текущий баланс:</td>'+
                                    '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Баланс после поднятия:</td>'+
                                    '<td>' + (currentInfo.user.balance - sumHot) + ' кр.</td>'+
                                '</tr>'+
                            '</table>'
                } else {
                    template +=
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td><b class="red">Текущий баланс:</b></td>'+
                                    '<td><b class="red">' + currentInfo.user.balance + ' кр.</b></td>'+
                                '</tr>'+
                            '</table>'+
                            '<span class="red fz-12">На вашем балансе не хватает кредитов для оплаты данной услуги. <a class="bd-beige" href="/cabinet/refill/">Пополнить баланс →</a></span>';
                }
                template +=
                        '</div>'+
                        '<div class="modal-footer ta-l">';
                if ((currentInfo.user.balance - sumHot) >= 0) {
                    if (one) {
                        template +=
                            '<button class="btn btn-grey btn-grand" name="hot" value="1">Поднять объявление</button>';
                    } else {
                        template +=
                            '<button class="btn btn-grey btn-grand" name="hot" value="1">Поднять ' + checkedBoxCount + ' ' + getNumEnding(checkedBoxCount, ['объявление', 'объявления', 'объявлений']) + '</button>';
                    }
                } else {
                    template +=
                            '<a href="/cabinet/refill/" class="btn btn-orange btn-grand">Пополнить баланс</a>';
                }
                template +=
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;
        }

        form
            .find('#appendedModal')
            .remove().end()
                .append(template)
                    .find('#appendedModal')
                    .modal('show');
                    formsObj.checkSubmitBtnOnChange($('#appendedModal'));
     });
}

// place ads, dependence with setCatSelector
function adPlacement() {
    var form = $('#ad-placement'),
        steps = form.find('.step-b');

    // check step for disabling.
    function stepChecker() {
        $(steps).each(function(){
            if (!formsObj.checkFormFields($(this)) && $(this).prevAll('.disabled-el').length == 0) {
                $(this).next('.step-b').removeClass('disabled-el')
                    .find('.input-group :disabled:not(.disabled-el :disabled),.input-group button').removeProp('disabled');
            } else {
                $(this).nextAll().addClass('disabled-el')
                    .find('.input-group :input, .input-group button').prop('disabled', true);
                return false;
            }
        });
    }

    // check current type
    (function (wrapper) {
        var current = form.find(':checked');

        checker(current);
        $(steps).find(':input').each(function() {
            $(this).on('change keyup', function() {
                stepChecker();
            });
        });
        form.on('change', ':radio', function() {
            checker($(this));
        });

        form.find('input.contractual').on('change',function(){
            stepChecker();
        });

        function checker(active) {
            if (active.hasClass('rent')) {
                wrapper.find('.forRent').show();
            } else if (active.hasClass('notRent')) {
                wrapper.find('.forRent').hide();
            }
            if (active.hasClass('search')) {
                wrapper.find('.forSearch').show();
                wrapper.find('.notForSearch').hide();
            } else if (active.hasClass('notSearch')) {
                wrapper.find('.forSearch').hide();
                wrapper.find('.notForSearch').show();
            }
            if (active.hasClass('contractual')) {
                wrapper.find('.notForContractual').attr('disabled', 'disabled').removeClass('required').addClass('noCheck');
                wrapper.find('.notForContractual').parents('.step-b').next('.step-b').removeClass('disabled-el')
                    .find('.input-group :disabled:not(.disabled-el :disabled),.input-group button').removeProp('disabled');
            } else if (active.hasClass('notContractual')) {
                wrapper.find('.notForContractual').removeAttr('disabled').addClass('required').removeClass('noCheck');
            }
        }
    })(form);

    form
        .find('.previewAdBtn').on('click',function(){
            advPreviewWin( $(this) );
        }).end()
        .find('.previewAd').on('click',function() {
            var data = form.serialize();
                $.get('url', data, function (responseURL) {
                    window.open(responseURL, '_blank');
                    window.focus();
                });
            return false;
        });

    formsObj.checkSubmitBtnOnChange(form);
}

// simmilar to setCabinetAdsEvents just single
function singleAdEditControls() {
    var form = $('#singleAdEditControls');

    form.find('button').click(function() {
        var template = false;
        var type = $(this).data('type');

        switch (type) {

            case 'modalArchiveAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690" aria-hidden="true">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red bold" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Отправить в архив</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p>Вы действительно хотите отправить объявление в архив?</p>'+
                        '</div>'+
                        '<div class="modal-footer ta-l">'+
                            '<button  name="archivate" value="1" class="btn btn-grey btn-grand">В архив</button>'+
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Закрыть</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalDeleteAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690" aria-hidden="true">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Удалить объявление</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<p>Вы действительно хотите удалить объявление?</p>'+
                        '</div>'+
                        '<div class="modal-footer ta-l">'+
                            '<button name="delete" value="1" class="btn btn-grey btn-grand">Удалить</button>'+
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalRiseAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Поднять объявление</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                        '<input type="hidden" name="rise" />'+
                        '<p>Стоимость поднятия на первые позиции в поиске и категориях одного объявления = <b>' + currentInfo.prices.riseAds + ' кредитов.</b></p>'+
                            '<p class="mb-10"><b>С вашего баланса будет списано:</b></p>'+
                            '<p><b class="fz-18 orange credits">' + currentInfo.prices.riseAds +' кредитов</b></p>';
                if (currentInfo.user.balance >= currentInfo.prices.riseAds) {
                            template +=
                                    '<table class="fz-13 grey unstyled">'+
                                        '<colgroup>'+
                                            '<col width="160">'+
                                        '</colgroup>'+
                                        '<tr>'+
                                            '<td>Текущий баланс:</td>'+
                                            '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                        '</tr>'+
                                        '<tr>'+
                                            '<td>Баланс после поднятия:</td>'+
                                            '<td>' + (currentInfo.user.balance - currentInfo.prices.riseAds) + ' кр.</td>'+
                                        '</tr>'+
                                    '</table>';
                        } else {
                            template +=
                                    '<table class="fz-13 grey unstyled">'+
                                        '<colgroup>'+
                                            '<col width="160">'+
                                        '</colgroup>'+
                                        '<tr>'+
                                            '<td>Текущий баланс:</td>'+
                                            '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                        '</tr>'+
                                    '</table>'+
                                    '<span class="red fz-12">На вашем балансе не хватает кредитов для оплаты данной услуги. <a class="bd-beige" href="/cabinet/refill/">Пополнить баланс →</a></span>';
                        }
                template +=
                        '</div>'+
                        '<div class="modal-footer ta-l">';
                if ((currentInfo.user.balance - currentInfo.prices.riseAds) >= 0) {
                    template +=
                            '<button class="btn btn-grey btn-grand">Поднять объявление</button>';
                } else {
                    template +=
                            '<a href="/cabinet/refill/" class="btn btn-orange btn-grand">Пополнить баланс</a>';
                }
                template +=
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalHighlightAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690" aria-hidden="true">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Выделить объявление</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<input type="hidden" name="highlight" />' +
                            '<p class="mb-0 required">'+
                                'Укажите цвет которым будет выделено выбранное объявление:<br>'+
                                '<span class="inline-frame group frame-green"><input type="radio" name="HighlightColor" value="green" id="HighlightGreen"><label for="HighlightGreen"><b>Зелёный</b></label></span>'+
                                '<span class="inline-frame group frame-yellow"><input type="radio" name="HighlightColor" value="yellow" id="HighlightYellow"><label for="HighlightYellow"><b>Жёлтый</b></label></span>'+
                                '<span class="inline-frame group frame-blue"><input type="radio" name="HighlightColor" value="blue" id="HighlightBlue"><label for="HighlightBlue"><b>Голубой</b></label></span>'+
                            '</p>'+
                            '<p>Стоимость выделения и поднятия на первые позиции в поиске и категориях одного объявления = <b>' + currentInfo.prices.highlightAds + ' кредитов.</b></p>'+
                            '<p class="mb-10"><b>С вашего баланса будет списано:</b></p>'+
                            '<p><b class="fz-18 orange credits">' + currentInfo.prices.highlightAds +' кредитов</b></p>';
                if (currentInfo.user.balance >= currentInfo.prices.highlightAds) {
                    template +=
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td>Текущий баланс:</td>'+
                                    '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Баланс после поднятия:</td>'+
                                    '<td>' + (currentInfo.user.balance - currentInfo.prices.highlightAds) + ' кр.</td>'+
                                '</tr>'+
                            '</table>';
                } else {
                    template +=
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td>Текущий баланс:</td>'+
                                    '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                '</tr>'+
                            '</table>'+
                            '<span class="red fz-12">На вашем балансе не хватает кредитов для оплаты данной услуги. <a class="bd-beige" href="/cabinet/refill/">Пополнить баланс →</a></span>';
                }
                template +=
                        '</div>'+
                        '<div class="modal-footer ta-l">';
                if ((currentInfo.user.balance - currentInfo.prices.highlightAds) >= 0) {
                    template +=
                        '<button class="btn btn-grey btn-grand" disabled>Выделить объявление</button>';
                } else {
                    template +=
                        '<a href="/cabinet/refill/" class="btn btn-orange btn-grand">Пополнить баланс</a>';
                }
                template +=
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;

            case 'modalHotAds':
                template =
                    '<div id="appendedModal" class="modal hide fade w-690">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">×</button>'+
                            '<h3>Разместить объявление в горячих предложениях</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                            '<input type="hidden" name="hot" />' +
                            'Стоимость размещения объявления в горячих предложениях и добавление в ротацию на главной странице = <b>' + currentInfo.prices.hotAds + ' кредитов.</b></p>'+
                            '<p>Объявление также будет выделено <span class="inline-frame frame-orange">оранжевым</span> цветом и поднято на первые позиции в поиске и категориях.</p>'+
                            '<p class="mb-10"><b>С вашего баланса будет списано:</b></p>'+
                            '<p><b class="fz-18 orange credits">' + currentInfo.prices.hotAds +' кредитов</b></p>';
                if ((currentInfo.user.balance - currentInfo.prices.hotAds) >= 0) {
                    template +=
                           '<p><b class="fz-18 orange credits">' + currentInfo.prices.hotAds +' кредитов</b></p>'+
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td>Текущий баланс:</td>'+
                                    '<td>' + currentInfo.user.balance + ' кр.</td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Баланс после поднятия:</td>'+
                                    '<td>' + (currentInfo.user.balance - currentInfo.prices.hotAds) + ' кр.</td>'+
                                '</tr>'+
                            '</table>'
                } else {
                    template +=
                            '<table class="fz-13 grey unstyled">'+
                                '<colgroup>'+
                                    '<col width="160">'+
                                '</colgroup>'+
                                '<tr>'+
                                    '<td><b class="red">Текущий баланс:</b></td>'+
                                    '<td><b class="red">' + currentInfo.user.balance + ' кр.</b></td>'+
                                '</tr>'+
                            '</table>'+
                            '<span class="red fz-12">На вашем балансе не хватает кредитов для оплаты данной услуги. <a class="bd-beige" href="/cabinet/refill/">Пополнить баланс →</a></span>';
                }
                template +=
                        '</div>'+
                        '<div class="modal-footer ta-l">';
                if ((currentInfo.user.balance - currentInfo.prices.hotAds) >= 0) {
                    template +=
                            '<button class="btn btn-grey btn-grand" name="hot" value="1">Поднять объявление</button>';
                } else {
                    template +=
                            '<a href="/cabinet/refill/" class="btn btn-orange btn-grand">Пополнить баланс</a>';
                }
                template +=
                            '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>'+
                        '</div>'+
                    '</div>';
                break;
        }

        form
            .find('#appendedModal')
            .remove().end()
                .append(template)
                    .find('#appendedModal')
                    .modal('show');
                    formsObj.checkSubmitBtnOnChange($('#appendedModal'));
    });
}

// init add news upload photo
function initNewsUploadPhoto(){
    var wrapperBody = $('.newsPhotoUploader'),
        wrapperResponse = $('.photo-upload-response'),
        input = wrapperResponse.find('input[type="hidden"]'),
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
                    '<div class="qq-upload-button btn btn-white soft" style="width: auto;"><span><i class="icon-upload"></i> ' + (input.val().length > 0 ? 'Заменить фотографию'  : 'Выбрать фотографию') + '</span></div>' +
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
                    .find('.photo')
                        .find('img').replaceWith('<img src="/cms/uploads_temp/' + response.filename + '" alt="" class="mr-10 va-t">').end()
                        .find('a').show();
                wrapperBody
                    .find('.btn > span').html('<i class="icon-upload"></i> Заменить фотографию');
                input
                    .val(response.filename);

                formsObj.checkSubmitBtn(parentForm);

                var cabinetLeftBlock = $('#cabinet-left-b');
                if (cabinetLeftBlock.length > 0) {
                    setTimeout( function() {
                        eqHeight(cabinetLeftBlock, $('#cabinet-right-b'))
                    }, 100);
                }
            }
        });

    wrapperResponse.on('click', '.link-remove', function() {
        wrapperResponse
            .find('img').attr('src','/img/nophoto-medium.png').end()
            .find('a').hide().end()
            .find('input').val('');
        wrapperBody
            .find('.btn > span').html('<i class="icon-upload"></i> Загрузить логотип');

        formsObj.checkSubmitBtn(parentForm);

        var cabinetLeftBlock = $('#cabinet-left-b');
        if (cabinetLeftBlock.length > 0) eqHeight(cabinetLeftBlock, $('#cabinet-right-b'));
    });
}

// load add news form events
function loadAddNewsFormEevnts(){
    var form = $('.add-news-form');
    form
        .find('.photo-upload-response')
            .find('a.link-remove').on('click',function(){
                var upload   = form.find('#photoUpload');
                var response = form.find('.photo-upload-response');
                upload
                    .find('.text').text('Выбрать фотографию');
                response
                    .find('img')
                        .attr('src','../img/nophoto-medium.png').end()
                    .find('a').hide()
            });
}

// load edit news form events 
function loadEditNewsFormEvents(){
    var form = $('.edit-news-form');    
    form
        .find('.btn-remove')
            .on('click',function(){
                var news =  form.find('tbody').find(':checkbox').filter(':checked');
                var win  =  '<div id="winRemoveNews" class="modal hide fade w-690" aria-hidden="true">'+
                                '<div class="modal-header">'+
                                    '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">&times;</button>'+
                                    '<h3>'+ ( news.length > 0 ? 'Отправить в архив' : 'Вы не выбрали ни одной новости.' ) +'</h3>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    ( news.length > 0 ? 
                                        '<p>Вы действительно хотите отправить в архив выбранные новости?</p>'
                                     : 
                                        '<p>Сообщение о том, что пользователь не выбрал ни одной новости</p>'
                                    ) +
                                '</div>'+
                                '<div class="modal-footer ta-l">'+
                                    ( news.length > 0 ?
                                        ('<button class="btn btn-grey btn-remove btn-grand">Удалить</button>'+
                                         '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>')
                                     : 
                                         '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Закрыть</button>'
                                     ) +
                                '</div>'+
                            '</div>';
                $('body')
                    .find('#winRemoveNews')
                        .remove().end()
                    .append( win )
                    .find('#winRemoveNews')
                        .modal('show')
                        .find('.btn-remove')
                            .on('click',function(){
                                $('form#editNewsForm').submit();
                            });
            });
}

// load edit advs form events 
function loadEditAdvsFormEvents(){
    var form = $('.edit-adv-form');
    form
        .find('.btn-remove')
            .on('click',function(){
                var checked =  form.find('tbody').find(':checkbox').filter(':checked');
                var hiddens = '';
                $.each( checked, function( i, checkbox ){
                    hiddens += '<input type="checkbox" class="hidden" name="'+ $(checkbox).attr('name') +'" checked />';
                });
                var win  =  '<div id="winRemoveNews" class="modal hide fade w-690" aria-hidden="true">'+
                                '<form action="" method="post">'+
                                    hiddens +
                                    '<div class="modal-header">'+
                                        '<button type="button" class="close red" data-dismiss="modal" aria-hidden="true">&times;</button>'+
                                        '<h3>'+ ( checked.length > 0 ? 'Отправить в архив' : 'Вы не выбрали ни одного объявления.' ) +'</h3>'+
                                    '</div>'+
                                    '<div class="modal-body">'+
                                        ( checked.length > 0 ?
                                            '<p>Вы действительно хотите отправить в архив выбранные объявления?</p>'
                                         :
                                            '<p>Сообщение о том, что пользователь не выбрал ни одного объявления</p>'
                                        ) +
                                    '</div>'+
                                    '<div class="modal-footer ta-l">'+
                                        ( checked.length > 0 ?
                                            ('<button type="submit" class="btn btn-grey btn-remove btn-grand">Удалить</button>'+
                                             '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Отменить</button>')
                                         :
                                             '<button class="btn btn-white btn-grand" type="button" data-dismiss="modal" aria-hidden="true">Закрыть</button>'
                                         ) +
                                    '</div>'+
                                '</form>'+
                            '</div>';
                $('body')
                    .find('#winRemoveNews')
                        .remove().end()
                    .append( win )
                    .find('#winRemoveNews')
                        .modal('show');
            });
}

// adv preview modal
function advPreviewWin(el){
    var form        = $(el).parents('form');
    var photos      = [];
    var photo_main  = false;
    form.find('.photoUploaderResponse').find('.item').each(function(){
        var photo = $(this).find('img').attr('src');
        photos.push( photo );
        if( $(this).hasClass('main') ) photo_main = photo;
    });
    var currency = form.find('#productCurrency').find('option').filter(':selected').text().split(' ');
    currency = currency[ currency.length - 1 ];
    if( currency.indexOf('(') >= 0 )        currency = currency.substr(1);
    if( currency.lastIndexOf(')') >= 0 )    currency = currency.substr(0,currency.lastIndexOf(')') );
    var data    = {
        name:           form.find('#productName').val(),
        type_text:      form.find('.adTypeGroup').find('input[type="radio"]').filter(':checked').parents('label').text(),
        category_name:  ( form.find('.catSelectorPseudoCrumbs').length > 0 ? form.find('.catSelectorPseudoCrumbs').find('.obj').text() : form.find('.cat-selector-crumbs').find('.obj').text() ),
        country:        form.find('#productCountry').val(),
        year:           form.find('#productYear').val(),
        year_to:        ( form.find('#productYearTo').is(':visible') ? form.find('#productYearTo').find('option').filter(':selected').text() : false ),
        condition:      form.find('#productCondiiton').find('option').filter(':selected').text(),
        available:      form.find('#productAvailability').find('option').filter(':selected').text(),
        price:          ( form.find('.contractual').is(':checked') ? false : form.find('#productPrice').val() ),
        currency:       currency,
        for_rent:       ( form.find('#rentDuration').is(':visible') ? ( form.find('#rentDuration').find('option').filter(':selected').text() != 'Нету' ? form.find('#rentDuration').find('option').filter(':selected').text() : false ) : false ),
        photos:         photos,
        supplier:       {
            name:           $('#company-name').val(),
            email:          $('#company-email').val(),
            type:           $('#company-type').val(),
            description:    $('#company-description').val(),
            logo:           $('#company-logo').val(),
            persona:        $('#company-contactName').val(),
            region:         $('#company-region').val(),
            address:        $('#company-address').val(),
            phones:         $('#company-phone').val().split('\n'),
            site:           $('#company-site').val(),
            region_icon:    $('#company-region-icon').val()
        }
    };
    var phones = '<div class="all">'+
                    '<span>'+ data.supplier.phones +'</span>'+
                 '</div>';
    if( data.supplier.phones.length > 1 ){
        phones = '<a class="as-text bd-grey dashed toggle link-phone-toggle" href="#">'+ data.supplier.phones[0] +'</a>'+
                    '<div class="all hide">';
                        $.each( data.supplier.phones, function( i, phone ){
                            phones += '<span>'+ phone +'</span>';
                        });
        phones += '</div>';
    }
    var win =   '<div class="modal hide fade in adv-preview-win">'+
                    '<div class="modal-header">'+
                        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'+
                        '<h3>Предварительный просмотр</h3>'+
                    '</div>'+
                    '<div class="modal-body">'+
                        '<div class="main-catalog">'+
                            '<div class="item clearfix">'+
                                '<div class="photo">'+
                                    ( data.photos.length > 0 ? ( '<a href="#"><img src="'+ photo_main +'"></a>'+ data.photos.length + ' фото' ) : '<a href="#"><img src="/img/nophoto-medium.png"></a>' ) +
                                '</div>'+
                                '<div class="desc">'+
                                    '<h3 class="header"><a class="as-text bd-grey" href="#">'+ data.name +'</a></h3>'+
                                    '<div class="category"><a class="bd-beige" href="#">'+ data.category_name +'</a></div>'+
                                    '<div class="condition">'+
                                        ( typeof data.condition != 'undefined' && data.condition != '' ?
                                            (data.country +', '+
                                            ( data.year_to ? ( data.year + ' &mdash; '+ data.year_to +' гг., ') : ( data.year +' г., ' ) ) +
                                            data.condition)
                                        : '' ) +
                                    '</div>'+
                                    '<div class="supplier"><b>Поставщик:</b> <a class="bd-beige link-go" target="_blank" href="'+ data.supplier.site +'">'+ data.supplier.name +'</a></div>'+
                                    '<div class="place"><img class="ico-flag" src="'+ data.supplier.region_icon +'" />'+ data.supplier.region +'</div>'+
                                '</div>'+
                                '<div class="contacts">'+
                                    ( data.price ?
                                        ('<div class="price">'+ data.price +' <span>'+ data.currency +'</span>'+ ( data.for_rent ? ('<span class="durationRent">/ '+ data.for_rent +'</span>') : '' ) +'</div>')
                                    : '<span class="noprice">Цена договорная</span>' ) +
                                    '<div class="presence">'+ data.available +'</div>'+
                                    '<div class="phones" data-toggle="phones">'+
                                        '<img class="ico phone-ico" src="/img/phone.png" />'+
                                        phones +
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="modal-footer">'+
                        '<button type="button" data-dismiss="modal" aria-hidden="true" class="btn">Закрыть</button>'+
                    '</div>'+
                '</div>';
    $('body').find('.adv-preview-win').remove().end().append( win );
    win = $('.adv-preview-win');
    win
        .modal('show')
        .find('.item')
            .find('a').filter(':not(.link-phone-toggle,.link-go)')
                .on('click',function(){ return false; });
    setPhonesToggler();
}

// news preview win
function newsPreviewWin(el){
    var form = $(el).parents('form');
    var data = {
        new_name:       form.find('#newsName').val(),
        description:    ( form.find('#newsAnnouncement').val() != '' ? form.find('#newsAnnouncement').val() : form.find('#newsDescription').val() ),
        photo:          form.find('.photo-upload-response').find('img').attr('src'),
        supplier: {
            name:           $('#company-name').val(),
            region:         $('#company-region').val(),
            region_icon:    $('#company-region-icon').val()
        }
    };
    var win  =  '<div class="modal hide fade in news-preview-win">' +
                    '<div class="modal-header">' +
                        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                        '<h3>Предварительный просмотр</h3>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<div class="company-news-list">' +
                            '<table class="table">' +
                                '<colgroup>' +
                                    '<col width="120" />' +
                                    '<col width="*" />' +
                                '</colgroup>' +
                                '<tbody>' +
                                    '<tr>' +
                                        '<td><img src="'+ data.photo +'" /></td>' +
                                        '<td>' +
                                            '<div class="company-news-item">' +
                                                '<div class="company-name">' +
                                                    '<b>Компания:</b> <a href="#" class="bd-beige pseudo"><span>'+ data.supplier.name +'</span></a>' +
                                                '</div>' +
                                                '<div class="company-location">' +
                                                    '<img src="'+ data.supplier.region_icon +'" /> ' + data.supplier.region +
                                                '</div>' +
                                                '<div class="company-news-item-name">' +
                                                    '<a href="#" class="as-text bd-grey"><b>'+ data.new_name +'</b></a>' +
                                                '</div>' +
                                                '<div class="company-news-item-in">' +
                                                    '<p>'+ data.description +'</p>' +
                                                '</div>' +
                                            '</div>' +
                                        '</td>' +
                                    '</tr>' +
                                '</tbody>' +
                            '</table>' +
                        '</div>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" data-dismiss="modal" aria-hidden="true" class="btn">Закрыть</button>' +
                    '</div>' +
                '</div>';
    $('body').find('.news-preview-win').remove().end().append( win );
    win = $('.news-preview-win');
    win
        .modal('show')
        .find('.company-news-list')
            .find('a').on('click',function(){ return false; });
}


