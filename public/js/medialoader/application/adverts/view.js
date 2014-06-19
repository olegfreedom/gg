/**
 * Use to URL:
 *  /adverts/view
 *  /adverts/view/*
 */

var func_adverts_view = {
    submit: function(){
        adverts_view_submitCommentForm();
        adverts_view_submitSMSForm();
    },
    get: function(){
        adverts_view_getPhoneNumber();
    },
    show: function() {
        adverts_view_initPopupForm();
    },
    init: function(){
        this.submit();
        this.get();
        this.show();
    }
};

$(document).ready(function(){
    // init func
    func_adverts_view.init();
});

/**
 * Show phone
 */
function adverts_view_getPhoneNumber(){
    bodyOffOn('click', '.get-phone-number', function(e){
        e.preventDefault();
        var el = $(this);
        var url = $(el).find('a').attr('href');
        $.post(url, null, function(data){
            if($(data.phone).length > 0){
                var html = '';
                $(data.phone).each(function(index, elem){
                    html +=  elem.phone + '<br>';
                });
                $(el).html(html);
            }else{
                $(el).html('нет номера');
            }
            $(el).removeClass('get-phone-number');
        });
    });
}

function adverts_view_submitCommentForm(){
    bodyOffOn('submit', '.add-testimonial-comment', function(e){
        e.preventDefault();
        var form = $(this);
        var url = $(form).find('input[name="validator"]').val();
        var vals = {
            name: $(form).find('input[name="name"]').val(),
            email: $(form).find('input[name="email"]').val(),
            message: $(form).find('textarea[name="message"]').val(),
            advert_id: $(form).find('input[name="advert_id"]').val(),
            rate: $(form).find('input[name="rate"]').val(),
            type: 'advert'
        }

        application_removeErrors(form);

        $.post(url, vals, function(data){
            if(data.status == true){
                adverts_view_addComment(form, vals);
            }else{
                application_createErrors(form, data);
            }
        });
    });
}

function adverts_view_addComment(form, vals){
    var url = $(form).attr('action');

    $.post(url, vals, function(data){
        if(data.status == true){
            if($(form).find('input[name="name"]').attr('readonly') != 'readonly'){
                $(form).find('input[name="name"]').val('');
            }
            if($(form).find('input[name="email"]').attr('readonly') != 'readonly'){
                $(form).find('input[name="email"]').val('');
            }
            $(form).find('textarea[name="message"]').val('');

            rating_reload(0);

            alert('Спасибо, Ваш отзыв добавлен и отправлен на проверку.');
        }else{
            alert('Произошла серверная ошибка, попробуйте позже.');
        }
    });
}

function adverts_view_submitSMSForm(){
    bodyOffOn('click', '.add-sms-form .sendIT', function(e){
        e.preventDefault();

        var form = $(this).parents('.add-sms-form');
        var url = $(form).find('input[name="validator"]').val();
        var vals = {
            text: $(form).find('textarea[name="text"]').val(),
            to_user_id: $(form).find('input[name="to_user"]').val(),
            advert_id: $(form).find('input[name="advert_id"]').val()
        }

        //application_removeErrors(form);

        $.post(url, vals, function(data){
            if(data.status === true){
                //adverts_view_addSMS(form, vals);
                alert('ok');
            }else{
                //application_createErrors(form, data);
            }
        }, 'json');
    });
}

function adverts_view_initPopupForm(){
    var form = $('.sms-form');
    var noneClass = 'displayNone';

    bodyOffOn('click', '.send-form-holder .hide-form', function(e){
        e.preventDefault();
        if(form.hasClass(noneClass)){
            form.removeClass(noneClass);
        } else {
            form.addClass(noneClass);
            //adverts_view_addSMS_showForm(form);
        }
    });
}

//
//function adverts_view_addSMS_showForm(form) {
//    $(form).find('.toError').addClass('hidden');
//    $(form).find('.toShow').addClass('hidden');
//    $(form).find('.toHide').removeClass('hidden');
//}
//function adverts_view_addSMS_showError(form) {
//    $(form).find('.toHide').addClass('hidden');
//    $(form).find('.toShow').addClass('hidden');
//    $(form).find('.toError').removeClass('hidden');
//}
//function adverts_view_addSMS_showSuccess(form) {
//    $(form).find('.toError').addClass('hidden');
//    $(form).find('.toHide').addClass('hidden');
//    $(form).find('.toShow').removeClass('hidden');
//}

//function adverts_view_addSMS(form, vals){
//    var url = $(form).attr('action');
//    var smsForm = $(form).parents('.send-form-holder.sms-form');
//
//    $.post(url, vals, function(data){
//        if(data.status === true){
//            adverts_view_addSMS_showSuccess(smsForm);
//            text: $(form).find('textarea[name="text"]').val('');
//        }else{
//            adverts_view_addSMS_showError(smsForm);
//        }
//
//        setTimeout(function(){
//            if($(smsForm).find('.toHide').hasClass('hidden')){
//                $('.send-sms').trigger('click');
//            }
//        }, 2000);
//    }, 'json');
//}


