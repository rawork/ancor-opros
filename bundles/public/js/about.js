(function($) {
    $(function() {

        var popupSlick = $('.modal-members');
        var juriArray = [];

        var galleryContainer = $('.prizes-desktop');
        var galleryMobileContainer = $('.prizes-mobile');

        var setArrowVibibility = function(currentSlide, totalSlides) {
            if (currentSlide+1 >= totalSlides) {
                $('.gallery-next').hide();
            } else {
                $('.gallery-next').show();
            }

            if (0 >= currentSlide) {
                $('.gallery-prev').hide();
            } else {
                $('.gallery-prev').show();
            }

            if (1 == totalSlides) {
                $('.gallery-prev').hide();
                $('.gallery-next').hide();
            }
        };

        var modalElementHtml = function (item) {
            return '<div class="juri modal-juri clearfix"><div class="foto modal-foto"><img src="'+item.img+'"></div><div class="modal-info"><div class="name">'+item.name+'</div><div class="activity">'+item.activity+'</div><div class="bio"><div>'+item.bio+'</div></div><div class="quotation"><div class="quote-pop"><div class="quote-top"></div><div class="quote-content">'+item.quotation+'</div><div class="quote-bottom"></div></div></div></div></div>';
        };

        var setModalArrowVibibility = function(currentSlide, totalSlides) {
            if (currentSlide+1 >= totalSlides) {
                $('.popup-next').hide();
            } else {
                $('.popup-next').show();
            }

            if (0 >= currentSlide) {
                $('.popup-prev').hide();
            } else {
                $('.popup-prev').show();
            }

            if (1 == totalSlides) {
                $('.popup-prev').hide();
                $('.popup-next').hide();
            }
        };

        $(document).on('click', '.tabs li', function(e) {
            e.preventDefault();

            var that = $(this);
            var id = that.attr('data-id');
            that.addClass('active').siblings().removeClass('active');
            $(id).addClass('active').siblings().removeClass('active');
            if ($(window).width() >= 900) {
                galleryContainer.slick('setPosition');
                if (id.indexOf('prize') > -1) {
                    var num = that.attr('data-num');
                    galleryContainer.get(num).slick.slickGoTo(0, true);
                    setArrowVibibility(0,galleryContainer.slideCount);
                }
            } else {
                galleryMobileContainer.slick('setPosition');
                if (id.indexOf('prize') > -1) {
                    var num = that.attr('data-num');
                    galleryMobileContainer.get(num).slick.slickGoTo(0, true);
                }
            }

        });


        $(document).on('click', 'ul.juri .foto a', function(e){
            if ($(window).width() >= 600) {
                e.preventDefault();

                if (juriArray.length == 0) {
                    $('ul.juri li').each(function(){
                        var member = {};
                        var that  = $(this);
                        member.img = that.find('.foto img').attr('src');
                        member.name = that.find('.info .name').html();
                        member.activity = that.find('.info .activity').html();
                        member.bio = that.find('.info .bio div').html();
                        member.quotation = that.find('.info .quotation-desktop').html();
                        juriArray.push(member);
                    })

                }

                var pos = parseInt($(this).attr('data-position'));

                popupSlick.empty();

                for (var i in juriArray) {
                    popupSlick.append(modalElementHtml(juriArray[i]))
                }

                $('body').addClass('modal-open');
                popupSlick.slick({
                    infinite: false,
                    dots : false,
                    lazyLoad: 'progressive',
                    slidesToShow: 1,
                    initialSlide: pos,
                    adaptiveHeight: true,
                    prevArrow: '<button type="button" class="slick-prev popup-prev"><img src="/bundles/public/img/popup_prev.png"></button>',
                    nextArrow: '<button type="button" class="slick-next popup-next"><img src="/bundles/public/img/popup_next.png"></button>'
                }).on('afterChange', function(event, slick, currentSlide){
                    setModalArrowVibibility(currentSlide, juriArray.length);
                });
                setModalArrowVibibility(pos, juriArray.length);
                $('.modal').show();

                popupSlick.slick('setPosition');
            }

        });

        $(document).on('click', 'a.modal-close', function(e){
            e.preventDefault();
            $(this).parents('.modal').hide();
            popupSlick.slick('unslick');
            $("body").removeClass("modal-open");
        });


        $(window).resize( function(){
            if ($(window).width() >= 900) {
                galleryContainer.slick({
                    infinite: false,
                    dots : false,
                    lazyLoad: 'progressive',
                    slidesToShow: 1,
                    adaptiveHeight: true,
                    prevArrow: '<button type="button" class="slick-prev gallery-prev"><img src="/bundles/public/img/prev.png"></button>',
                    nextArrow: '<button type="button" class="slick-next gallery-next"><img src="/bundles/public/img/next.png"></button>'
                }).on('afterChange', function(event, slick, currentSlide){
                    setArrowVibibility(currentSlide, slick.slideCount);
                });

                setArrowVibibility(0, galleryContainer.slideCount);

                galleryContainer.slick('setPosition');
            } else {
                galleryMobileContainer.slick({
                    infinite: false,
                    dots : false,
                    lazyLoad: 'progressive',
                    slidesToShow: 1,
                    adaptiveHeight: true,
                    prevArrow: '',
                    nextArrow: ''
                });

                galleryContainer.slick('setPosition');
            }

        }).resize();

        $(window).trigger('resize');

    });

})(jQuery);
