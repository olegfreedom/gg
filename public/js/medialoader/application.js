/**
 * Use to URL:
 *  /*
 */

/**
 * Login processing function
 * */

var func_main = {
    action: function(){
        application_catalog_createCitiesSelect();
    },
    link: function(){
        adverts_searchButton();
        adverts_favoritesButton();
    },
    validator: function(){
        profile_login_formValidator();
//        profile_registration_formValidator();
//        profile_forgot_formValidator();
    },
    load: function(){
        resetErrorStyle();
        catalog_refreshJcf();
        application_search_getPhoneNumber();
        application_catalog_createHtmlSelectCity();
        numbersOnly();
    },
    init: function(){
        this.validator();
        this.link();
        this.action();
        this.load();

    }
}

$(document).ready(function(){
    //init func
    func_main.init();
});

/**
 * @param object form
 */
function application_removeErrors(form){
    $(form).find('.error').removeClass('error');
}

/**
 * @param object form
 * @param array data
 */
function application_createErrors(form, data) {
    if ($(data.error).length > 0) {
        for (var i in data.error) {
            if (data.error[i] == false) {
                var elem = $(form).find('[name="'+i+'"]');
                if($(elem).attr('type') == 'checkbox'){
                    $(elem).parent().addClass('error');
                }else{
                    $(elem).addClass('error');
                }

            }else if(data.error[i] == true && i == 'subscribe_email'){
                $(form).find('[name="'+i+'"]').addClass('error');
                alert('Этот email уже подписан на рассылку.');
            }
        }
        var errorItem = $(form).find('.error:first').parent();
        if ($(errorItem).length > 0) {
            scrollingPage(errorItem, 10);
        }
    }
}

 function profile_login_formValidator(){
    bodyOffOn('submit', '.login-holder .popup_form.login-form', function(e){
        var form = $(this);
        var status = $(form).find('input[name="login-form"]');

        console.log(status);
        if($(status).val() == 0){
            e.preventDefault();
            var url = $(form).find('input[name="validator"]').val();

            var vals = {
                username: $(form).find('input[name="username"]').val(),
                password: $(form).find('input[name="password"]').val(),
                remember: $(form).find('input[name="remember"]:checked').val()
            }

            $(form).find('.row .error').removeClass('error');
            $.post(url, vals, function(data){
                if(data.status == true){
                    $(status).val(1);
                    $(form).submit();
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

function profile_registration_formValidator(){
    bodyOffOn('submit', '.header .login-holder .popup.register', function(e){
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

            $(form).find('.row .error').removeClass('error');

            $.post(url, vals, function(data){
                if(data.status == true){
                    $(status).val(1);
                    $(form).submit();
                }else{
                    if($(data.error).length > 0){
                        for(var i in data.error){
                            if(data.error[i] == false){
                                $(form).find('[name="'+i+'"]').addClass('error');
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
    bodyOffOn('submit', '.header .login-holder .popup.forgot', function(e){
        var form = $(this);
        var status = $(form).find('input[name="forgot-form"]');

        if($(status).val() == 0){
            e.preventDefault();
            var url = $(form).find('input[name="validator"]').val();
            var vals = {
                username: $(form).find('input[name="username"]').val()
            }

            $(form).find('.row .error').removeClass('error');

            $.post(url, vals, function(data){
                if(data.status == true){
                    $(status).val(1);
                    $(form).submit();
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

function adverts_searchButton() {
    bodyOffOn('click', 'form.srch .btn-search', function (e) {
        e.preventDefault();
        var form = $(this).parents('form.srch');
        $(form).submit();
    });
}

/**
 * Show phone
 */
function application_search_getPhoneNumber(){
    bodyOffOn('click', '.article_bottom.group .links', function(e){
        e.preventDefault();
        var el = $(this);
        var url = $(el).attr('href');
        var article = $(el).parent('.article_bottom.group');
        
        $('.tel').not($(article).find('.tel')).removeClass('active');

        if (!$(article.find('.tel')).hasClass('is-loaded')) {
            $.post(url, null, function(data) {
                if($(data.phone).length > 0){
                    var html = '';
                    $(data.phone).each(function(index, elem){
                        html += '<span>' + elem.phone + ';</span>';
                    });
                    $(article).find('.phones').html(html);
                }else{
                    $(article).find('.phones').html('нет номера');
                }
                $(article).find('.tel').addClass('is-loaded');
            });
        }
    });
}

/**
 * Refresh jcf
 */
function catalog_refreshJcf() {
    bodyOffOn('click', '.opener', function (e) {
        e.preventDefault();
        console.log('test');
    });
}

/**
 * Add to favorites
 */
function adverts_favoritesButton(){
    bodyOffOn('click', '.favorites-button', function(e){
        e.preventDefault();
        var el = $(this);
        var url = $(el).attr('data-url');

        $.post(url, null, function(data){
            if(data.status == true){
                $(el).attr('data-url', data.url).attr('title', data.text);

                if(data.addClass == true){
                    $(el).addClass('full');
                }else{
                    $(el).removeClass('full');
                }
            }
        });
    });
}

function application_catalog_createCitiesSelect(){
    bodyOffOn('change', 'select[name="region"]', function(e){
        e.preventDefault();
        application_catalog_createHtmlSelectCity();
    });
}

function application_catalog_createHtmlSelectCity(){
    var select = $('select[name="region"]');
    var region = $(select).val();
    var city = $('form.srch').find('input[name="location_city"]').val();
    if(region > 0){
        var url = $('form.srch').find('input[name="location_url"]').val();
        var vals = {
            region: region
        };
        
        $.post(url, vals, function(data){
            if (data.citiesList.length > 0){
                $('#twn').html('');
                var html = '';
                html += '<option value="0">По всему региону</option>';
                for (var i in data.citiesList){
                    html += '<option '+((data.citiesList[i].id == city)?'selected="selected"': '')+' value="'+data.citiesList[i].id+'">'+data.citiesList[i].name+'</option>';
                }
                $('#twn').html(html);
//                $('#twn')[0].jcf.buildDropdown();
//                $('#twn')[0].jcf.refreshState();
                jcf.customForms.destroyAll();
                jcf.customForms.replaceAll();
            }
        });
    } else {
        $('#twn').html('');
        var html = '<option value="0">Выберите регион</option>';
        $('#twn').html(html);
//        $('#twn')[0].jcf.buildDropdown();
//        $('#twn')[0].jcf.refreshState();
        jcf.customForms.destroyAll();
        jcf.customForms.replaceAll();
    }
}
