function iDiv(x, y){
    return (x-x%y)/y
}

// Timer
function getTimer(deadline) {

    deadline = deadline * 1000;

    var daySeconds = 86400;
    var hourSeconds = 3600;

    var dt = new Date();
    var currentTime = dt.getTime();
    var deltaSeconds = (deadline - currentTime) / 1000;
    var days = iDiv(deltaSeconds, daySeconds);
    var hours = iDiv(deltaSeconds - days*daySeconds, hourSeconds)

    return {day: days, hour: hours};
}



(function($) {
    $(function() {

        var deadline = 1454878799;

        $(document).on('click', 'a.close', function(e){
            e.preventDefault();
            $(this).parent().hide();
        });

        $(document).on('click', '.menu-button a', function(e) {
            e.preventDefault();
            $('.mainmenu').slideToggle('slow');
        });

        $(document).on('click', "a[href$='Rules_10.pdf']", function(e, options) {
            options = options || {};

            if ( !options.ga_complete ) {
                e.preventDefault();
                ga('send', 'event', 'download', 'pdf', 'rules');
                //console.log('ga pdf');
                window.location = $(this).attr('href');
            }

        });

        $(document).on('click', "a[href$='anketa2015.pdf']", function(e, options) {
            options = options || {};

            if ( !options.ga_complete ) {
                e.preventDefault();
                ga('send', 'event', 'download', 'pdf', 'anketa');
                //console.log('ga pdf');
                window.location = $(this).attr('href');
            }

        });

        $(window).on('resize', function(e){
            if (900 <= $(this).width()) {
                $('.mainmenu').show();
                $('ul#dealer').hide();
                $('.map-dot').show();
            } else {
                $('ul#dealer').show();
                $('.modal').hide();
                $('.map-dot').hide();
            }
        });

        $( window ).scroll(function() {
            if ($(this).scrollTop() >= 125) {
                $('.logo-small').fadeIn(800);
            } else {
                $('.logo-small').fadeOut(500);
            }
        });

        if (typeof $.fn.jcarousel !== 'undefined') {

            var carousel = $('.jcarousel')
                .on('jcarousel:create jcarousel:reload', function() {
                    var element = $(this),
                        width = element.innerWidth();

                    // This shows 1 item at a time.
                    // Divide `width` to the number of items you want to display,
                    // eg. `width = width / 3` to display 3 items at a time.
                    element.jcarousel('items').css('width', width + 'px');
                });

            carousel.jcarousel({
                wrap: 'both'
            });

            if (carousel.find('li').length > 1 && $(window).width() >= 900) {
                $('.slider-prev, .slider-next').show();
            }

            $('.slider-wrapper>.jcarousel').jcarouselAutoscroll({
                interval: 5000
            });

            $('.jcarousel-control-prev')
                .on('jcarouselcontrol:active', function() {
                    $(this).removeClass('inactive');
                })
                .on('jcarouselcontrol:inactive', function() {
                    $(this).addClass('inactive');
                })
                .jcarouselControl({
                    target: '-=1'
                });

            $('.jcarousel-control-next')
                .on('jcarouselcontrol:active', function() {
                    $(this).removeClass('inactive');
                })
                .on('jcarouselcontrol:inactive', function() {
                    $(this).addClass('inactive');
                })
                .jcarouselControl({
                    target: '+=1'
                });

            //$('.jcarousel-pagination')
            //    .on('jcarouselpagination:active', 'li', function() {
            //        $(this).addClass('active');
            //    })
            //    .on('jcarouselpagination:inactive', 'li', function() {
            //        $(this).removeClass('active');
            //    })
            //    .jcarouselPagination({
            //        'perPage':1,
            //        'item': function(page, carouselItems) {
            //            return '<li class="' + (page == 1 ? "active" : "") + '">&nbsp;</li>';
            //        }
            //    });

            //var setTimer = function(){
            //    timerData = getTimer(deadline);
            //
            //    $('#day').html((timerData.day < 10 ? "0" : "") + timerData.day);
            //    $('#hour').html((timerData.hour < 10 ? "0" : "") + timerData.hour);
            //};
            //
            //setTimer();
            //setInterval(setTimer(), 600000);

        }

    });

})(jQuery);
