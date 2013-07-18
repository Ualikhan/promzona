$(function () {

    $('#registerActivity').val($('#activitytypeID').text());

    if ($('#page-register').length > 0) {
        setRegisterEvents();
    }

});

function newCaptcha(img) {
    img.attr('src', img.attr('src') + '/');
    return false;
}

setRegisterEvents = function () {
    var that = this;
    this.form = $('#page-register');
    this.companyDesc = $('#companyDesc');
    this.companyDescCount = $('#companyDescCount');
    this.passMatchError = $('#passMatchError');

    formsObj.checkSubmitBtn(this.form);
    formsObj.checkSubmitBtnOnChange(this.form);
    passMatcher();

    this.companyDesc.keyup(function () {
        if (formsObj.letterLeftCounter(that.companyDesc) <= 400) {
            that.companyDescCount.show();
            formsObj.letterLeftCounter(that.companyDesc, that.companyDescCount.find('span'));
        } else {
            that.companyDescCount.hide();
        }
        if (formsObj.letterLeftCounter(that.companyDesc) <= 50) {
            that.companyDescCount.addClass('orange').removeClass('black')
        } else {
            that.companyDescCount.addClass('black').removeClass('orange')
        }
    });
};
