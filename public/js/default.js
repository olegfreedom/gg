/**
 * @description Alternative replacement code:
 * @description $('body').off(events, selector).on(events, selector, callback);
 *
 * @param string events
 * @param string selector
 * @param mixed callback
 */
function bodyOffOn(events, selector, callback){
    $('body').off(events, selector).on(events, selector, callback);
}

/**
 * @description Close popup and reload page
 *
 * @param string selector
 */
function pageReload(selector){
    $(selector).remove();
    window.location.reload();
}

/**
 * @description Close popup
 *
 * @param string selector
 */
function closePopupBox(selector){
    $(selector).parents('.pop-up-tmp').find('select').selectBox('destroy');
    $(selector).parents('.pop-up-tmp').remove();
    return false;
}

/**
 * @description Scrolling Page
 *
 * @param string selector
 * @param int marginTop
 */
function scrollingPage(selector, marginTop){
    var scrolling = false;

    $(document).scroll(function(){
        if(scrolling == false){
            scrolling = true;
        }
    });
    
    setTimeout(function(){        
        if(scrolling == false){
            var margin = ($(marginTop).length > 0) ? parseInt(marginTop) : 0;
            var top = $(selector).offset().top - margin;
            $('html, body').animate({scrollTop : top},'slow');
        }
    }, 500);
}

/**
 * @description Set cursor wait
 *
 * @param string|object selector
 */
function setCursorWait(selector){
    $('body').css('cursor', 'wait');
    $(selector).css('cursor', 'wait');
}

/**
 * @description Set cursor default
 *
 * @param string|object selector
 */
function setCursorDefault(selector){
    $('body').css('cursor', 'default');
    $(selector).css('cursor', 'default');
}

/**
 * @description Set cursor Auto
 *
 * @param string|object selector
 */
function setCursorAuto(selector){
    $('body').css('cursor', 'auto');
    $(selector).css('cursor', 'auto');
}

/**
 * Checkbox switcher
 * @param object elem
 * @param bool status
 */
function checkboxChecked(elem, status){
    if(status === true){
        $(elem).attr('checked', 'checked');
        elem.checked = true;
    }else{
        $(elem).removeAttr('checked');
        elem.checked = false;
    }
}

// card aside popup alignment
function initAdvertPopupAlign() {
    var selector = $(".cards .menu").find('.submnu');
    selector.each(function () {
        var offset = $(this).height()/2;
        $(this).css({
            'top': -offset + 15 + "px"
        });
    });
}

/**
 * Get outerHTML
 */
jQuery.fn.outerHTML = function(s) {
    return s
        ? this.before(s).remove()
        : jQuery("<p>").append(this.eq(0).clone()).html();
}; 

/**
 * Remove error class
 */
function resetErrorStyle() {
    bodyOffOn('keypress change', 'form [name].error, form div.error [name]', function(){
        if($(this).hasClass('error')){
            $(this).removeClass('error');
        }else{
            $(this).closest('div.error').removeClass('error');
        }
    });
}

/**
 * only number
 */
function numbersOnly() {
    bodyOffOn('keypress', 'form [name].num-only', function(evt){
        var charCode = (evt.which) ? evt.which : evt.keyCode
        
        if (charCode == 46 || charCode == 44 || charCode == 8 || (charCode > 47 && charCode < 58)){
            return true;
        }else{
            return false;
        }
    });
}

function submitButton(){
    bodyOffOn('click', '.submitIt', function(e){
        e.preventDefault();
        var form = $(this).closest('form');
        $(form).submit();
    });
}
