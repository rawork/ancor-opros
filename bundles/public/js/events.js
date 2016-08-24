(function($) {
    $(function() {

        var currentMonth = $('.months').attr('data-current');
        var calendarYear = $('.calendar-year');
        var minYear = parseInt(calendarYear.attr('data-min'));
        var maxYear = parseInt(calendarYear.attr('data-max'));
        var currentYear = parseInt(calendarYear.text());


        var galleryContainer = $('.pictures');


        var checkMonth = function(year) {
            if (currentYear != year) {
                $('.month').removeClass('active');
            } else {
                $('.month'+currentMonth).addClass('active').siblings('.month').removeClass('active');
            }
        }

        $('.month'+currentMonth).addClass('active').siblings('.month').removeClass('active');

        $('.calendar-year-prev').on('click', function(e){
            e.preventDefault();
            var selectedYear = parseInt(calendarYear.text());
            if (selectedYear <= minYear) {
                return;
            } else {
                selectedYear = selectedYear - 1;
                calendarYear.text(selectedYear);
                checkMonth(selectedYear);
            }
        });

        $('.calendar-year-next').on('click', function(e){
            e.preventDefault();
            var selectedYear = parseInt(calendarYear.text());
            if (selectedYear >= maxYear) {
                return;
            } else {
                selectedYear = selectedYear + 1;
                calendarYear.text(selectedYear);
                checkMonth(selectedYear);
            }
        });

        $('.month').on('click', function(e){
            e.preventDefault();

            var selectedYear = parseInt(calendarYear.text());
            var selectedMonth = parseInt($(this).attr('data-value'));

            var path = '/events/'+selectedYear+'/'+selectedMonth;

            window.location.href = path;
        })


        $('.gallery').slick({
            infinite: false,
            dots : false,
            lazyLoad: 'progressive',
            slidesToShow: 1,
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
                        dots: true
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

            var newWidth = $fluidEl.width();

            // Resize all videos according to their own aspect ratio
            $allVideos.each(function() {

                var $el = $(this);
                $el
                    .width(newWidth)
                    .height(newWidth * $el.data('aspectRatio'))
                    .css('margin-bottom', '20px');

            });

            // Kick off one resize to fix all videos on page load
        }).resize();



        //gallery
        $(document).on('click', '.tabs li', function(e) {
            e.preventDefault();

            var that = $(this);
            var id = that.attr('data-id');
            that.addClass('active').siblings().removeClass('active');
            $(id).addClass('active').siblings().removeClass('active');
            if ($(window).width() < 900) {
                galleryContainer.slick('setPosition');
            }
        });



        galleryContainer.slick({
            infinite: false,
            dots : false,
            lazyLoad: 'ondemand', //progressive
            slidesToShow: 1,
            adaptiveHeight: true,
            prevArrow: '',
            nextArrow: ''
        })
        //    .on('afterChange', function(event, slick, currentSlide){
        //    handleGalleryAfterChange(currentSlide, desktopTotalPages);
        //    $('#slide-current').html(currentSlide+1);
        //});

    });

})(jQuery);

