(function ( $ ) {

//определяем максимальную глубину скроллинга и функции обработки времени
    $.scrollEvent = function(current_max) {
        var documentObj = $(document);
        var windowObj = $(window);

        var documentHeight = documentObj.height();
        var windowHeight = windowObj.height();
        var currentHeight = windowHeight + documentObj.scrollTop();

        current_value = getPercent(documentHeight, currentHeight);
        return(current_max > current_value ? current_max : current_value);
    }

    $.fixTime = function() {
        var dateObj = new Date();
        return Math.floor(dateObj.getTime() / 1000);
    }

    function num(val){
        val = Math.floor(val);
        return val < 10 ? '0' + val : val;
    }

    $.timeFormat = function(ms){
        var sec = ms, hours = sec / 3600 % 24, minutes = sec / 60 % 60, seconds = sec % 60;
        return num(hours) + ":" + num(minutes) + ":" + num(seconds);
    };

    function getPercent(doc, cur) {
        return !cur ? 0 : Math.floor(cur * 100 / doc);
    }

})( jQuery );

//Определяем временные интервалы
function getTimeInterval(time) {
    if (time >= 0 && time < 30)
        return 'from 0 sec to 30 sec';
    if (time >= 30 && time < 60)
        return 'from 30 sec to 1 min';
    if (time >= 60 && time < 120)
        return 'from 1 min to 2 min';
    if (time >= 120 && time < 180)
        return 'from 2 min to 3 min';
    if (time >= 180 && time < 300)
        return 'from 3 min to 5 min';
    if (time >= 300 && time < 480)
        return 'from 5 min to 8 min';
    if (time >= 480)
        return 'more 8 min';
}

//Определяем интервалы глубины скроллинга
function getScrollingInterval(deep) {
    if (deep >= 0 && deep < 20)
        return 'from 0% to 20%';
    if (deep >= 20 && deep < 40)
        return 'from 20% to 40%';
    if (deep >= 40 && deep < 60)
        return 'from 40% to 60%';
    if (deep >= 60 && deep < 80)
        return 'from 60% to 80%';
    if (deep >= 80 && deep <= 100)
        return 'from 80% to 100%';
}

//Осуществляем вызов вышеописанных функций и передаем данные в GTM
jQuery(document).ready(function() {
    var startLiveDoc = jQuery.fixTime();
    var current_max = 0;

    jQuery(window).scroll(function() {
        current_max = jQuery.scrollEvent(current_max);
    });

//назначаем объекту window событие, действие которого выполнится в момент завершения работы пользователя со страницей (закрытие, обновление, переход на другую страницу)
    jQuery(window).bind('beforeunload', function(){
        current_max_string = current_max.toString() + '%';
        var endLiveDoc = jQuery.fixTime();
        var timeLiveDoc = jQuery.timeFormat(endLiveDoc - startLiveDoc);
        var percent_of_scrolling_int = getScrollingInterval(current_max);
        var time_on_page_int = getTimeInterval(endLiveDoc - startLiveDoc);
        dataLayer.push({'event': 'Scroll to', 'percent_of_scrolling': current_max_string, 'time_on_page': timeLiveDoc, 'percent_of_scrolling_interval' : percent_of_scrolling_int, 'time_on_page_interval' : time_on_page_int});
    });
});
