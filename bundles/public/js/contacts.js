(function($) {
    $(function() {

        var canvasOffsetX = -20;
        var canvasOffsetY = 224;
        var canvasOffsetX1200 = -20; //150

        var cities = {};
        var defaultCities = [];
        var dealers = {};


        $.get($('#map-city').attr('data-url'),
            function(data){
                if (data.cities) {
                    cities = data.cities;

                    for (var i in cities) {
                        if (cities[i].is_default == '1') {
                            defaultCities.push(cities[i]);
                        }
                    }

                    if ($(window).width() >= 900) {
                        $('.map-dot').remove();
                        for (var i in defaultCities) {
                            var cityData = defaultCities[i];
                            $('<div></div>')
                                .addClass('map-dot map-dot'+cityData.id)
                                .attr('data-id', cityData.id)
                                .html('<div class="dot"></div><div class="title"><span>'+cityData['name']+'</span></div>')
                                .css({
                                    'left': canvasOffsetX + (1200 <= $(window).width() ? canvasOffsetX1200 : 0) + parseInt(cityData.offsetx),
                                    'top': canvasOffsetY + parseInt(cityData.offsety)
                                }).show().appendTo('.map');
                        }
                    }
                }
            });


        var buildList = function (container, cityId) {
            var currentDealers = dealers[cityId];
            var $container = $(container);
            $container.empty();
            $.each(currentDealers, function(key, value) {
                $container
                    .append($('<li></li>')
                        .html('<div class="title">'+value.name+'</div><div class="info">'+(value.openhour ? 'ВРЕМЯ РАБОТЫ<br>'+value.openhour+'<br>' : '')+(value.phone ? '<strong>Тел.</strong>: '+value.phone+'<br>' : '')+'<strong>Адрес</strong>: '+value.address+'</div>'+(value.site ? '<a target="_blank" class="site-link" href="'+value.site+'">Перейти на сайт дилерского центра</a>' : '') ));
                    var text = '<br><strong>E-mail</strong>: <a href="'+value.email+'">'+value.email+'</a><a class="roadmap-link" href="'+value.site+'">Схема проезда</a><div class="service">Предоставляемые услуги:<br>- продажа новых а/м Toyota<br>- тюнинг<br>- слесарный ремонт<br>- trade-in<br>- аренда авто<br>- страхование<br>- кредит<br></div>';
            });
        };

        $(document).on('click', 'a.modal-close', function(e){
            e.preventDefault();
            $(this).parents('.modal').hide();
            $("body").removeClass("modal-open")
        });

        $(document).on('click', '.map-dot', function(e) {
            e.preventDefault();

            var that = $(this);
            var cityId = parseInt(that.attr('data-id'));

            var dataUrl = $('#dealer').attr('data-url');

            if (dealers.cityId != undefined) {
                buildList('#dealer', cityId);
                $('#myModal').show();
                $('.nano').nanoScroller();
                $("body").addClass("modal-open");
                return;
            }
            $.post(dataUrl, {id: cityId},
                function(data){
                    if (data.dealers) {
                        dealers[cityId] = [];
                        for(var i in data.dealers) {
                            dealers[cityId].push(data.dealers[i]);
                        }

                        buildList('#modal-dealers ul', cityId);
                        $('#myModal').show();
                        $('.nano').nanoScroller();
                        $("body").addClass("modal-open");
                    }
                });
        });

        $(".nano").nanoScroller();

        if(jQuery().selectize) {
            $('.map select').selectize({
                allowEmptyOption: true,
                placeholder: 'Начните вводить название города',
                onChange: function(value) {
                    if (!value.length) {
                        $('.map-dot').hide();
                        return;
                    }
                    var cityData = cities[value];

                    ga('send', 'event', 'dealer', 'search');

                    if ($('.map-canvas').is(':visible')) {
                        $('.map-dot').remove();
                        $('<div></div>')
                            .addClass('map-dot map-dot'+value)
                            .attr('data-id', value)
                            .html('<div class="dot"></div><div class="title"><span>'+cityData['name']+'</span></div>')
                            .css({
                            'left': canvasOffsetX + (1200 <= $(window).width() ? canvasOffsetX1200 : 0) + parseInt(cityData.offsetx),
                            'top': canvasOffsetY + parseInt(cityData.offsety)
                        }).show().appendTo('.map');
                        //$('#map-dot')
                        //    .removeClass()
                        //    .addClass('map-dot')
                        //    .addClass('map-dot'+value)
                        //    .attr('data-id', value)
                        //    .css({
                        //        'left': canvasOffsetX + (1200 <= $(window).width() ? canvasOffsetX1200 : 0) + parseInt(cityData.offsetx),
                        //        'top': canvasOffsetY + parseInt(cityData.offsety)
                        //})
                        //    .show()
                        //    .find('span').html(cityData['name']);
                            $.scrollTo('.map-canvas', 1000);

                    } else {

                        var dataUrl = $('#dealer').empty().attr('data-url');
                        if (dealers[value] != undefined) {
                            buildList('#dealer', value);
                            $('#dealer').show();
                            $.scrollTo('#map-city', 1000);
                            return;
                        }

                        $.post(dataUrl, {id: value},
                            function(data){
                                if (data.dealers) {
                                    dealers[value] = [];
                                    for(var i in data.dealers) {
                                        dealers[value].push(data.dealers[i]);
                                    }

                                    buildList('#dealer', value);
                                    $('ul#dealer').show();
                                    $.scrollTo('#map-city', 1000);
                                }
                            });


                    }

                }
            });
        }

    });

})(jQuery);
