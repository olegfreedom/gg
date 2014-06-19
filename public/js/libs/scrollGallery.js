
/*
 * jQuery Cycle Carousel plugin
 */
;(function($){
    function ScrollAbsoluteGallery(options) {
        this.options = $.extend({
            activeClass: 'active',
            mask: 'div.slides-mask',
            slider: '>ul',
            slides: '>li',
            btnPrev: '.btn-prev',
            btnNext: '.btn-next',
            pagerLinks: 'ul.pager > li',
            generatePagination: false,
            pagerList: '<ul>',
            pagerListItem: '<li><a href="#"></a></li>',
            pagerListItemText: 'a',
            galleryReadyClass: 'gallery-js-ready',
            currentNumber: 'span.current-num',
            totalNumber: 'span.total-num',
            maskAutoSize: false,
            autoRotation: false,
            pauseOnHover: false,
            stretchSlideToMask: false,
            switchTime: 3000,
            animSpeed: 500,
            handleTouch: true,
            swipeThreshold: 50,
            verticalThumbnails: false
        }, options);
        this.init();
    }
    ScrollAbsoluteGallery.prototype = {
        init: function() {
            if(this.options.holder) {
                this.findElements();
                this.attachEvents();
            }
        },
        findElements: function() {
            // find structure elements
            this.holder = $(this.options.holder).addClass(this.options.galleryReadyClass);
            this.mask = this.holder.find(this.options.mask);
            this.slider = this.mask.find(this.options.slider);
            this.slides = this.slider.find(this.options.slides);
            this.btnPrev = this.holder.find(this.options.btnPrev);
            this.btnNext = this.holder.find(this.options.btnNext);

            // slide count display
            this.currentNumber = this.holder.find(this.options.currentNumber);
            this.totalNumber = this.holder.find(this.options.totalNumber);

            // create gallery pagination
            if(typeof this.options.generatePagination === 'string') {
                this.pagerLinks = this.buildPagination();
            } else {
                this.pagerLinks = this.holder.find(this.options.pagerLinks);
            }

            // define index variables
            this.slideWidth = this.slides.width();
            this.currentIndex = 0;
            this.prevIndex = 0;

            // reposition elements
            this.slider.css({
                position: 'relative',
                height: this.slider.height()
            });
            this.slides.css({
                position: 'absolute',
                left: -9999,
                top: 0
            }).eq(this.currentIndex).css({
                    left: 0
                });
            this.refreshState();
        },
        buildPagination: function() {
            var pagerLinks = $();
            if(!this.pagerHolder) {
                this.pagerHolder = this.holder.find(this.options.generatePagination);
            }
            if(this.pagerHolder.length) {
                this.pagerHolder.empty();
                this.pagerList = $(this.options.pagerList).appendTo(this.pagerHolder);
                for(var i = 0; i < this.slides.length; i++) {
                    $(this.options.pagerListItem).appendTo(this.pagerList).find(this.options.pagerListItemText).text(i+1);
                }
                pagerLinks = this.pagerList.children();
            }
            return pagerLinks;
        },
        attachEvents: function() {
            // attach handlers
            var self = this;
            if(this.btnPrev.length) {
                this.btnPrevHandler = function(e) {
                    e.preventDefault();
                    if ( self.options.verticalThumbnails ) {
                        var offset = $(this).closest('.left-bar').find('li').last().position().top + 10;
                        if( $(this).closest('.left-bar').find('li').first().hasClass('active') ){
                            $(this).closest('.left-bar').find('.pager').animate({
                                marginTop: -offset
                            }, 500, function() {
                                $(this).clearQueue();
                            });
                        }
                        else{
                            $(this).closest('.left-bar').find('.pager').animate({
                                marginTop: '+=66'
                            }, 500, function() {
                                $(this).clearQueue();
                            });
                        }
                    }
                    self.prevSlide();

                };
                this.btnPrev.click(this.btnPrevHandler);
            }
            if(this.btnNext.length) {
                this.btnNextHandler = function(e) {
                    e.preventDefault();
                    if ( self.options.verticalThumbnails ) {
                        var offset = $(this).closest('.left-bar').find('li').first().position().top;
                        if( $(this).closest('.left-bar').find('li').last().hasClass('active') ){
                            $(this).closest('.left-bar').find('.pager').animate({
                                marginTop: offset
                            }, 500, function() {
                                $(this).clearQueue();
                            });
                        }
                        else{
                            $(this).closest('.left-bar').find('.pager').animate({
                                marginTop: '-=66'
                            }, 500, function() {
                                $(this).clearQueue();
                            });
                        }
                    }
                    self.nextSlide();
                };
                this.btnNext.click(this.btnNextHandler);
            }
            if(this.pagerLinks.length) {
                this.pagerLinksHandler = function(e) {
                    e.preventDefault();
                    self.numSlide(self.pagerLinks.index(e.currentTarget));
                };
                this.pagerLinks.click(this.pagerLinksHandler);
            }

            // handle autorotation pause on hover
            if(this.options.pauseOnHover) {
                this.hoverHandler = function() {
                    clearTimeout(self.timer);
                };
                this.leaveHandler = function() {
                    self.autoRotate();
                };
                this.holder.bind({mouseenter: this.hoverHandler, mouseleave: this.leaveHandler});
            }

            // handle holder and slides dimensions
            this.resizeHandler = function() {
                if(!self.animating) {
                    if(self.options.stretchSlideToMask) {
                        self.resizeSlides();
                    }
                    self.resizeHolder();
                    self.setSlidesPosition(self.currentIndex);
                }
            };
            $(window).bind('load resize orientationchange', this.resizeHandler);
            if(self.options.stretchSlideToMask) {
                self.resizeSlides();
            }

            // handle swipe on mobile devices
            if(this.options.handleTouch && $.fn.swipe && this.slides.length > 1) {
                this.mask.swipe({
                    excludedElements: '',
                    fallbackToMouseEvents: false,
                    threshold: this.options.swipeThreshold,
                    allowPageScroll: 'vertical',
                    swipeStatus: function(e, phase, direction, offset) {
                        // avoid swipe while gallery animating
                        if(self.animating) {
                            return false;
                        }

                        // move gallery
                        if(direction === 'left' || direction === 'right') {
                            self.swipeOffset = -self.slideWidth + (direction === 'left' ? -1 : 1) * offset;
                            self.slider.css({marginLeft: self.swipeOffset});
                        }
                        clearTimeout(self.timer);
                        switch(phase) {
                            case 'cancel':
                                self.slider.animate({marginLeft: -self.slideWidth}, {duration: self.options.animSpeed});
                                break;
                            case 'end':
                                if(direction === 'left') {
                                    self.nextSlide();
                                } else {
                                    self.prevSlide();
                                }
                                self.swipeOffset = 0;
                                break;
                        }
                    }
                });
            }

            // start autorotation
            this.autoRotate();
            this.resizeHolder();
            this.setSlidesPosition(this.currentIndex);
        },
        resizeSlides: function() {
            this.slideWidth = this.mask.width();
            this.slides.css({
                width: this.slideWidth
            });
        },
        resizeHolder: function() {
            if(this.options.maskAutoSize) {
                this.slider.css({
                    height: this.slides.eq(this.currentIndex).outerHeight(true)
                });
            }
        },
        prevSlide: function() {
            if(!this.animating && this.slides.length > 1) {
                this.direction = -1;
                this.prevIndex = this.currentIndex;
                if(this.currentIndex > 0) this.currentIndex--;
                else this.currentIndex = this.slides.length - 1;
                this.switchSlide();
            }
        },
        nextSlide: function(fromAutoRotation) {
            if(!this.animating && this.slides.length > 1) {
                this.direction = 1;
                this.prevIndex = this.currentIndex;
                if(this.currentIndex < this.slides.length - 1) this.currentIndex++;
                else this.currentIndex = 0;
                this.switchSlide();
            }
        },
        numSlide: function(c) {
            if(!this.animating && this.currentIndex !== c && this.slides.length > 1) {
                this.direction = c > this.currentIndex ? 1 : -1;
                this.prevIndex = this.currentIndex;
                this.currentIndex = c;
                this.switchSlide();
            }
        },
        preparePosition: function() {
            // prepare slides position before animation
            this.setSlidesPosition(this.prevIndex, this.direction < 0 ? this.currentIndex : null, this.direction > 0 ? this.currentIndex : null, this.direction);
        },
        setSlidesPosition: function(index, slideLeft, slideRight, direction) {
            // reposition holder and nearest slides
            if(this.slides.length > 1) {
                var prevIndex = (typeof slideLeft === 'number' ? slideLeft : index > 0 ? index - 1 : this.slides.length - 1);
                var nextIndex = (typeof slideRight === 'number' ? slideRight : index < this.slides.length - 1 ? index + 1 : 0);

                this.slider.css({marginLeft: this.swipeOffset ? this.swipeOffset : -this.slideWidth});
                this.slides.css({left:-9999}).eq(index).css({left: this.slideWidth});

                if(prevIndex === nextIndex && typeof direction === 'number') {
                    this.slides.eq(nextIndex).css({left: direction > 0 ? this.slideWidth*2 : 0 });
                } else {
                    this.slides.eq(prevIndex).css({left: 0});
                    this.slides.eq(nextIndex).css({left: this.slideWidth * 2});
                }
            }
        },
        switchSlide: function() {
            // prepare positions and calculate offset
            var self = this;
            var oldSlide = this.slides.eq(this.prevIndex);
            var newSlide = this.slides.eq(this.currentIndex);

            // start animation
            var animProps = {marginLeft: this.direction > 0 ? -this.slideWidth*2 : 0 };
            if(this.options.maskAutoSize) {
                // resize holder if needed
                animProps.height = newSlide.outerHeight(true);
            }
            this.animating = true;
            this.preparePosition();
            this.slider.animate(animProps,{duration:this.options.animSpeed, complete:function() {
                self.setSlidesPosition(self.currentIndex);

                // start autorotation
                self.animating = false;
                self.autoRotate();
            }});

            // refresh classes
            this.refreshState();
        },
        refreshState: function(initial) {
            // slide change function
            this.slides.removeClass(this.options.activeClass).eq(this.currentIndex).addClass(this.options.activeClass);
            this.pagerLinks.removeClass(this.options.activeClass).eq(this.currentIndex).addClass(this.options.activeClass);

            // display current slide number
            this.currentNumber.html(this.currentIndex + 1);
            this.totalNumber.html(this.slides.length);
        },
        autoRotate: function() {
            var self = this;
            clearTimeout(this.timer);
            if(this.options.autoRotation) {
                this.timer = setTimeout(function() {
                    self.nextSlide();
                }, this.options.switchTime);
            }
        },
        destroy: function() {
            // destroy handler
            this.btnPrev.unbind('click', this.btnPrevHandler);
            this.btnNext.unbind('click', this.btnNextHandler);
            this.pagerLinks.unbind('click', this.pagerLinksHandler);
            this.holder.unbind({mouseenter: this.hoverHandler, mouseleave: this.leaveHandler});
            $(window).unbind('load resize orientationchange', this.resizeHandler);
            clearTimeout(this.timer);

            // destroy swipe handler
            if(this.options.handleTouch && $.fn.swipe) {
                this.mask.swipe('destroy');
            }

            // remove inline styles, classes and pagination
            this.holder.removeClass(this.options.galleryReadyClass);
            this.slider.add(this.slides).removeAttr('style');
            if(typeof this.options.generatePagination === 'string') {
                this.pagerHolder.empty();
            }
        }
    };

    // jquery plugin
    $.fn.scrollAbsoluteGallery = function(opt){
        return this.each(function(){
            $(this).data('ScrollAbsoluteGallery', new ScrollAbsoluteGallery($.extend(opt,{holder:this})));
        });
    };
}(jQuery));