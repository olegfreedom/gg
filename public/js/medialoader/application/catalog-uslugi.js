/**
 * Use to URL:
 *  /catalog-uslugi
 *  /catalog-uslugi/*
 */

var func_application_catalog = {
    validator: function(){
        application_search_formValidator();
    },
    actions: function(){
        application_catalog_createCitiesSelect()
    },
    get: function(){

    },
    load: function(){
    },
    init: function(){
        this.validator();
        this.get();
        this.actions();
//        this.load();
    }
};

$(document).ready(function(){
    //init func
    func_application_catalog.init();
});

function application_search_formValidator(){
    bodyOffOn('click', '.btn_search', function(e){
        var form = $('.srch');
        var status = $(form).find('input[name="search-form"]');
        if($(status).val() == 0){
            e.preventDefault();
//            application_removeErrors(form);
            var url = $(form).find('input[name="validator"]').val(); 
            var vals = {
                
            };
            $.post(url, vals, function(data){
                if(data.status == true){
                    $(status).val(1);
                    $(form).submit();
                }else{
//                    application_createErrors(form, data);
                }
            });
        }
    });
}
