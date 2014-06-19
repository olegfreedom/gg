
(function($) {
	$(function(){
        ui_slider();
        initCycleCarousel();
        login();
        telShow();
        itemHeight();
        navHeight();
        mapInit();
        scrollTo();

        jcf.customForms.replaceAll();

        jQuery('div.toggle-block').openClose({
            addClassBeforeAnimation: true,
            activeClass:'active',
            opener:'.opener',
            slider:'.slide',
            animSpeed: 400,
            effect:'fade',
            event:'click'
        });
	});

    function initCycleCarousel() {
        var sliderItem = $('div.item-gallery');
        if(sliderItem.length>0){
            sliderItem.scrollAbsoluteGallery({
                mask: 'div.holder',
                autoRotation: false,
                switchTime: 3000,
                maskAutoSize: false,
                animSpeed: 500,
                verticalThumbnails: true
            });
        }
    }

    function scrollTo(){
        var items = $('.goTo'),
            page = $('body, html'),
            animSpeed = 800;

        items.each(function(i){
            var link = $(this),
                target = $('#' + link.attr('href'));
            link.on('click', function(e){
                e.preventDefault();
                page.animate({scrollTop: target.offset().top}, animSpeed);
            });
        });
    }

    function mapInit(){
        $(function() {

            if($('#map-canvas').length>0){
                function initialize() {
                    var mapOptions = {
                        center: new google.maps.LatLng(55.75, 37.625),
                        zoom: 12,
                        /*scrollwheel: false,*/
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    };
                    var map = new google.maps.Map(document.getElementById("map-canvas"),
                        mapOptions);

                    var activeClass = "full";
                    $('#map-canvas').on('dblclick', function(){

                        if( $(this).hasClass(activeClass)){
                            $(this).removeClass(activeClass);
                        } else{
                            $(this).addClass(activeClass);
                            var currCenter = map.getCenter();
                            google.maps.event.trigger(map, 'resize');
                            map.setCenter(currCenter);

                        }
                    });
                }
            google.maps.event.addDomListener(window, 'load', initialize);
            }
        });
    }

    function itemHeight(){
        var post = $('.clickable');
        if(post.length>0){
            //post.attr('data-href', 'index.html');
            post.off('click').on('click', function(){
                window.location = this.getAttribute('data-href');
            });

            function change(){
                var posts = $('.clickable').map(function () {
                        return $(this).height();
                    }).get(),
                    maxHeight = Math.max.apply(null, posts);
                post.height(maxHeight);
            }

            change();

            $(window).off('resize').on('resize', function(){
               change();
            })
        }
    }

    function navHeight(){
        var post = $('.add_advert .steps li');
        if(post.length>0){

            function change(){

                var posts = $('.steps li').map(function () {
                        return $(this).height();
                    }).get(),
                    maxHeight = Math.max.apply(null, posts);
                post.height(maxHeight);
            }

            change();

            $(window).on('resize', function(){
                change();
            })
        }
    }

    function login(){
        var popup = $('.popup_form');
        var btn = $('.btn-login');
        var activeClass = 'active';
        var doc = $(document);

        btn.on('click', function(e){
            e.stopPropagation();
            if(popup.hasClass(activeClass)){
                popup.removeClass(activeClass);
            }else{
                popup.addClass(activeClass);
                doc.on('click', clickOutside);
            }
        });

        function clickOutside(e){
            var target = $(e.target);
            if(!target.is(popup) && !target.parents('.' + activeClass).is(popup)){
                popup.removeClass(activeClass);
                doc.off('click', clickOutside);
            }
        }



        /*var item = $('.login-holder'),
            form = $('.popup_form'),
            activeclass = "active";

        item.find('.btn-login').on('click', function(){
            if(form.hasClass(activeclass)){
                form.removeClass(activeclass);
            }else{
                form.addClass(activeclass);
                setTimeout(function(){
                    clickOnDocument
                }, 13);
            }
        });
        function clickOnDocument(e){
            var target = $(e.target);
            if(!target.is(form) && !target.parents(item).length){
                form.addClass(activeclass);
                $(document).off('click', clickOnDocument);
            }
        }*/
    }

    function telShow(){
        var item = $('.article_bottom'),
            opener = item.find('.links'),
            activeclass = "active";
        if( typeof(item) !== 'undefined'){
            opener.on('click', function(e){
                e.preventDefault();
                var tel = $(this).parent().find('.tel');
                tel.hasClass(activeclass) ? tel.removeClass(activeclass) : tel.addClass(activeclass);
            })
        }
    }

    function ui_slider(){
        ;(function(){
            var maxPrice = ($('input.maxPrice').length > 0) ? $('input.maxPrice').val() : 10000;
            var pmin = ($('#input-number1').length > 0) ? $('#input-number1').val() : 0;
            var pmax = ($('#input-number2').length > 0) && $('#input-number2').val() > 0 ? $('#input-number2').val() : maxPrice;
            ;$('.sample-rate').noUiSlider({
                range: [0,maxPrice],
                start: [pmin,pmax],
                handles: 2,
                connect: true,
                step: 1,
                serialization: {
                    to: [ [$('#input-number1'), handler1], [$('#input-number2'), handler2] ]
                    ,resolution: 1
                },
            });
//            $('.sample-rate .noUi-handle').each(function(ind){
//                var item = this.appendChild(document.createElement('span'));
//                item.id = 'helper'+ ind;
//            });
            function handler1(val){
                if(this.data('data-helper1')){
                    this.data('data-helper1').html(val);
                }else{
                    var item = $('<span id="helper1"></span>').appendTo(this.find('.noUi-handle-lower'));
                    item.html(val);
                    this.data('data-helper1', item);
                }
                $('#from').val(val);
            }
            function handler2(val){
                if(this.data('data-helper2')){
                    this.data('data-helper2').html(val);
                }else{
                    var item = $('<span id="helper2"></span>').appendTo(this.find('.noUi-handle-upper'));
                    item.html(val);
                    this.data('data-helper2', item);
                }
                $('#to').val(val);
            }
        })();



    }

	/*This area from declaration plugins*/
})(jQuery);