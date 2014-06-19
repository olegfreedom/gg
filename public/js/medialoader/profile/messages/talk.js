/**
 * Use to URL:
 *  /profile/messages/talk
 *  /profile/messages/talk/*
 */
$(document).ready(function(){
    // init func
    func_profile_messages.init();
});

var func_profile_messages = {
    actions: function(){
        profile_add_messages_submitButton();
        profile_talks_ScrollToLastAnswer();
    },
    validator: function(){
        profile_add_messages_formValidator();
    },
    init: function(){
        this.actions();
        this.validator();
    }
};

function profile_add_messages_submitButton(){
    bodyOffOn('click', '.form-validator .submit_talk', function(e){
        e.preventDefault();
        $(this).submit();
    });
}

function profile_add_messages_formValidator(){
    bodyOffOn('submit', '.form-validator', function(e){
        var form = $(this);
        var status = $(form).find('input[name="'+($(form).hasClass('edit') ? 'edit' : 'add')+'-form"]');

        if($(status).val() == 0){
            e.preventDefault();
            profile_removeErrors(form);
            var url = $(form).find('input[name="validator"]').val();

            var vals = {
                username: $(form).find('input[name="username"]').val(),
                title: $(form).find('input[name="title"]').val(),
                text: $(form).find('textarea[name="text"]').val()
            };
            $.post(url, vals, function(data){
                if(data.status == true){
                    $(status).val(1);
                    $(form).submit();
                }else{
                    profile_createErrors(form, data);
                }
            });
        }
    });
}

function profile_talks_ScrollToLastAnswer() {
    var top = $('.author2').children().last('div.text').offset().top - 200;
    $('html, body').animate({scrollTop : top},'slow');
}
