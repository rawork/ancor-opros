
(function($) {
    $(function() {

        var videos = $('.video-detail > div');
        var popupSlick = $('.modal-pictures');
        var picturesArray = [];

        if (videos.length > 1 && $(window).width() >= 900) {
            if (videos.length % 2 == 0) {
                videos.css({'width': '49.6%'});
            }
        }

        var modalElementHtml = function (item) {
            return '<div class="modal-picture"><img src="'+item.path+'"><div class="idea">'+item.name+'</div></div>';
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

        $('.gallery').slick({
            infinite: false,
            dots : true,
            lazyLoad: 'progressive',
            slidesToShow: 3,
            slidesToScroll: 3,
            prevArrow: '<button type="button" class="slick-prev gallery-prev"><img src="/bundles/public/img/prev.png"></button>',
            nextArrow: '<button type="button" class="slick-next gallery-next"><img src="/bundles/public/img/next.png"></button>',
            responsive: [
                {
                    breakpoint: 900,
                    settings: {

                    }
                },
                {
                    breakpoint: 899,
                    settings: {
                        arrows: false,
                        dots: true,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        adaptiveHeight: true
                    }
                }
                // You can unslick at a given breakpoint now by adding:
                // settings: "unslick"
                // instead of a settings object
            ]
        });

        // Find all YouTube videos
        var $allVideos = $("iframe[src^='https://www.youtube.com']"),

        // The element that is fluid width
            $fluidEl = $(".content");

        // Figure out and save aspect ratio for each video
        $allVideos.each(function() {

            $(this)
                .data('aspectRatio', this.height / this.width)

                // and remove the hard coded width/height
                .removeAttr('height')
                .removeAttr('width');

        });

        // When the window is resized
        // (You'll probably want to debounce this)
        $(window).resize(function() {



            // Resize all videos according to their own aspect ratio
            $allVideos.each(function() {

                var $el = $(this);
                var newWidth = $el.parent().width();


                $el
                    .width(newWidth)
                    .height(newWidth * $el.data('aspectRatio'));
                    //.css('margin-bottom', '20px');

            });

            // Kick off one resize to fix all videos on page load
        }).resize();

        $(document).on('click', 'ul.selector li', function(e){
            e.preventDefault();

            var that = $(this);
            var id = that.attr('data-id');

            that.addClass('active').siblings().removeClass('active');
            $('#year'+id).addClass('active').siblings().removeClass('active');

        });

        $(document).on('click', '.photo a', function(e) {
            e.preventDefault();

            if ($(window).width() < 900) {
                return;
            }

            if (picturesArray.length == 0) {
                var images = $('.photo');

                images.each(function(index) {
                    picturesArray.push({'name': $(this).find('.description').html(),'path': $(this).find('a').attr('href')})
                });

            }

            var pos = parseInt($(this).attr('data-position'));

            popupSlick.empty();

            for (i in picturesArray) {
                popupSlick.append(modalElementHtml(picturesArray[i]))
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
                setModalArrowVibibility(currentSlide, picturesArray.length);
            });
            $('.modal').show();

            popupSlick.slick('setPosition');

        });

        $(document).on('click', 'a.modal-close', function(e){
            e.preventDefault();
            $(this).parents('.modal').hide();
            popupSlick.slick('unslick');
            $("body").removeClass("modal-open");
        });

    });

})(jQuery);

