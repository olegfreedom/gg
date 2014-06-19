/**
 * Use to URL:
 *  /profile/*
 */
var func_main = {
    link: function(){
        submitButton();
    },
    validator: function(){
        profile_registration_formValidator();
        profile_forgot_formValidator();
    },
    init: function(){
        this.validator();
        this.link();
    }
}

$(document).ready(function(){
    //init func
    func_main.init();
});

function profile_registration_formValidator(){
    bodyOffOn('submit', '.regForm-holder .registration', function(e){
        var form = $(this);
        var status = $(form).find('input[name="registration-form"]');
        if($(status).val() == 0){
            e.preventDefault();
            var url = $(form).find('input[name="validator"]').val();
            var vals = {
                username: $(form).find('input[name="username"]').val(),
                password: $(form).find('input[name="password"]').val(),
                retry_password: $(form).find('input[name="retry_password"]').val()
            }

            $(form).find('.error').removeClass('error');

            $.post(url, vals, function(data){
                if(data.status == true){
                    $(status).val(1);
                    $(form).submit();
                }else{
                    if($(data.error).length > 0){
                        for(var i in data.error){
                            if(data.error[i] == false){
                                $(form).find('[name="'+i+'"]').parent().addClass('error');
                            }else if(data.error[i] == true && i == 'username'){
                                $(form).find('[name="'+i+'"]').addClass('error');
                                alert('Такой логин уже существует, выберите другой.');
                            }
                        }
                    }
                }
            });
        }

    });
}

function profile_forgot_formValidator(){
    bodyOffOn('submit', '.regForm-holder .forgot', function(e){
        var form = $(this);
        var status = $(form).find('input[name="forgot-form"]');

        if($(status).val() == 0){
            e.preventDefault();
            var url = $(form).find('input[name="validator"]').val();
            var vals = {
                username: $(form).find('input[name="username"]').val()
            }

            $(form).find('.error').removeClass('error');

            $.post(url, vals, function(data){
                if(data.status == true){
                    $(status).val(1);
                    $(form).submit();
                }else{
                    if($(data.error).length > 0){
                        for(var i in data.error){
                            if(data.error[i] == false){
                                $(form).find('[name="'+i+'"]').parent().addClass('error');
                            }
                        }
                    }
                }
            });
        }

    });
}
/**
 * Add class error
 * @param form
 * @param data
 */
function profile_createErrors(form, data){
    if($(data.error).length > 0){
        for(var i in data.error){
            if(i == 'phoneArray' && $(data.error[i]).length > 0){
                $(form).find('.input-holder.phone').addClass('error');
                for(var k in data.error[i]){
                    if(data.error[i][k] == false){
                        $(form).find('.phone-box .phone-items').eq(k).addClass('error');
                    }
                };
            }else{
                if(data.error[i] == false){
                    if (i == 'description'){
                        $(form).find('[name="'+i+'"]').parent().addClass('error');
                    }else{
                        $(form).find('[name="'+i+'"]').parent().addClass('error');
                    }
                }
            }
        }

        var errorItem = $(form).find('.error:first').parent();
        if($(errorItem).length > 0){
            var top = $(errorItem).offset().top;
            $('html, body').animate({scrollTop : top},'slow');
        }

    }
}
/**
 * Remove class error
 * @param form
 */
function profile_removeErrors(form){
    $(form).find('.error').removeClass('error');
}

/**
 * @description Delete adverts item
 */
function profile_advertsRemove(){
    bodyOffOn('click', '.advert-delete-link', function(e){
        e.preventDefault();
        if(confirm('Подтвердите удаление')){
            var el = $(this);
            var url = $(el).attr('href');

            window.location.href = url;
        }else{
            return false;
        }
    });
}