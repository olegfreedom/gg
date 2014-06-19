/**
 * Use to URL:
 *  /admin/login/index
 *  /admin/login/index/*
 */
var func_main = {
    validator: function(){
        profile_login_formValidator();
    },

    init: function(){
        this.validator();
    }
}

$(document).ready(function(){
    //init func
    func_main.init();
});

function profile_login_formValidator(){
    bodyOffOn('submit', '.form-horizontal', function(e){
        var form = $(this);
        var status = $(form).find('input[name="login-form"]');
        var error_box = $(form).find('#error-box');

        if($(status).val() == 0){
            e.preventDefault();
            var url = $(form).find('input[name="validator"]').val();
            var vals = {
                username: $(form).find('input[name="username"]').val(),
                password: $(form).find('input[name="password"]').val()
            }

            $(form).find('.error').removeClass('error');
            $(error_box).html('');

            $.post(url, vals, function(data){
                if(data.status == true){
                    if (data.user == true) {
                        $(status).val(1);
                        $(form).submit();
                    } else {
                        $(error_box).html(admin_errorAlert('Некорректый вход в систему'));
                    }
                }else{
                    if($(data.error).length > 0){
                        for(var i in data.error){
                            if(data.error[i] == false){
                                $(form).find('[name="'+i+'"]').addClass('error');
                            }
                        }
                    }
                }
            });
        }

    });
}