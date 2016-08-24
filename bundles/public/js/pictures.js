(function($) {
    $(function() {

        if (navigator.appVersion.indexOf("Win")!=-1)
        {
            $('#modal-gallery .modal-dialog').css('margin-left','-438px');
        }

        var galleryContainer = $('#pictures');
        var gallerySlick = false;
        var currentCategory = 0;
        var currentPerson = '';
        var currentCity = '';
        var filterChanged = true;
        var isArchive = parseInt(galleryContainer.attr('data-archive'));
        var currentAction = galleryContainer.attr('data-action');

        var popupSlick = $('.modal-pictures');

        var desktopTotalPictures = 0;
        var desktopTotalPages = 0;
        var desktopLoadedPages = 0;
        var desktopCurrentPage = 0;
        var desktopLoadedDataPages = 0;
        var desktopPictures = [];
        var desktopPageLimit = 120;

        var desktopLoading = false;


        var mobileTotalPictures = 0;
        var mobileCurrentPicture = 0;
        var mobileLoadedPages = 0;
        var mobilePictures = [];
        var mobilePageLimit = 50;

        var urls = {
            'index'   : '/ajax/picture',
            'archive' : '/ajax/picture/archive',
            'works'   : '/ajax/picture/works'
        };


        var elementHtml = function(item, pos) {
            //<a href="#" data-id="'+item.id+'" '+(item.vote ? 'class="inactive"' : '')+'></a>
            return '<div class="picture"><div class="img"><a href="" data-position="'+pos+'" data-id="'+item.id+'"><img class="display-md" data-lazy="'+ item.picture_main +'"></a></div>'+(item.nomination ? '<div class="nomination">'+item.nomination+'</div>' : '')+'<div class="person">'+item.person+ ', ' + item.age +'</div><div class="city">' + item.city + '</div><div class="name">'+item.name+'</div>' + (currentAction == 'works' ? '<div class="picture-vote"><div class="likes">' + item.likes + '</div></div>': '') + (parseInt(item.position) > 0 ? '<div class="place">'+item.position+' место</div>' : '' )+'<div class="idea"><span class="red">Идея</span>' + item.idea + '</div></div>';
        };

        var elementMobileHtml = function(item, pos) {
            //<a  href="#" data-id="'+item.id+'" '+(item.vote ? 'class="inactive"' : '')+'></a>
            return '<div class="img"><a href="" data-position="'+pos+'" data-id="'+item.id+'"><img class="display-xs" src="'+ item.picture_big +'"></a></div>'+(item.nomination ? '<div class="nomination">'+item.nomination+'</div>' : '')+'<div class="person">'+item.person+ ', ' + item.age +'</div><div class="city">' + item.city + '</div><div class="name">'+item.name+'</div>' + (currentAction == 'works' ? '<div class="picture-vote"><div class="likes">' + item.likes + '</div></div>': '') + (parseInt(item.position) > 0 ? '<div class="place">'+item.position+' место</div>' : '' )+'<div class="idea"><span class="red">Идея</span>' + item.idea + '</div>';
        };

        var modalElementHtml = function (item) {
            //<a href="#" data-id="'+item.id+'" '+(item.vote ? 'class="inactive"' : '')+'></a>
            return '<div class="modal-picture"><img data-lazy="'+item.picture_big+'">'+(parseInt(item.position) > 0 || item.nomination ? '<div class="place-container"><div class="place"><span>'+(parseInt(item.position) > 0 ? item.position+' место' : (item.nomination ? item.nomination : ''))+'</span></div></div>' : '') + (currentAction == 'works' ? '<div class="picture-vote"><div class="likes">' + item.likes + '</div></div>': '') +'<div class="title">'+item.name+'</div><div class="person">'+item.person+' ('+item.city+'), '+item.age+'</div><div class="idea"><span class="red">Идея</span>'+item.idea+'</div></div>';
        };

        var shuffle = function(o) {
            var j, x, i;
            for (i = o.length; i; i -= 1) {
                j = Math.floor(Math.random() * i);
                x = o[i-1];
                o[i-1] = o[j];
                o[j] = x;
            }

            return o;
        };


        var buildGallery = function() {

            if ($('.noload').length > 0) {
                return;
            }

            var windowWidth = $(this).width();

            $('#picture-counter').hide();

            if (900 > windowWidth) {
                buildMobileGallery();
                return;
            }

            var category = parseInt(galleryContainer.attr('data-category'));
            var currentPicture = parseInt(galleryContainer.attr('data-picture'));
            var person = $('#person').val();
            var city = $('#city').val();

            if (currentCategory != category || currentPerson != person || currentCity != city) {
                filterChanged = true;
                currentCategory = category;
                currentPerson = person;
                currentCity = city;
            }

            if (!filterChanged)  {
                return;
            }

            $('.preloader').show();
            $('#pictures').hide();

            //console.log('ajax request ', Date.now());

            desktopLoadedDataPages = 0;

            //console.log(currentAction);

            $.post(urls[currentAction], {category: category, person: person, city: city, limit: desktopPageLimit, page:desktopLoadedDataPages},
                function(data){

                    //console.log('ajax responce',  Date.now());

                    if (data.gallery_disabled) {
                        $('body').addClass('modal-open');
                        $('#modal-closed').show();
                    }

                    if (data.pictures != undefined) {

                        desktopPictures = [];

                        for (var i in data.pictures) {
                            desktopPictures.push(data.pictures[i]);
                        }

                        if (currentAction == 'works'){
                            shuffle(desktopPictures);
                        }

                        desktopLoadedDataPages = desktopLoadedDataPages + 1;

                        //console.log('desktopLoadedDataPages', desktopLoadedDataPages);

                        if (gallerySlick) {
                            galleryContainer.slick('unslick');
                        }
                        galleryContainer.empty();

                        var currentElement = 1;
                        var maxElement = 6;

                        desktopTotalPictures = data.total;
                        desktopTotalPages =  Math.ceil(desktopTotalPictures / maxElement);
                        desktopLoadedPages = Math.ceil(desktopPictures.length / maxElement);

                        //console.log('first loaded pages', desktopLoadedPages);

                        desktopCurrentPage = 1;

                        for (var i in desktopPictures) {
                            if (currentElement == 1) {
                                var newBlock = $('<div></div>');
                            }

                            newBlock.append(elementHtml(desktopPictures[i], i));

                            if (currentElement >= maxElement) {
                                galleryContainer.append(newBlock.get(0).outerHTML);
                                currentElement = 1;
                                continue;
                            }
                            currentElement = currentElement + 1;
                        }
                        if (currentElement > 1) {
                            galleryContainer.append(newBlock.get(0).outerHTML);
                        }

                        //console.log('end build html', Date.now());

                        // hide vote block
                        if (data.vote_disabled) {
                            $('.picture-vote a').css({visibility: 'hidden'});
                            //$('.share').hide();
                        }

                        $('#slide-current').html(desktopCurrentPage);
                        $('#slide-total').html(desktopTotalPages);

                        if (desktopTotalPages > 0) {
                            $('#picture-counter').show();
                        } else {
                            $('#picture-counter').hide();
                        }

                        filterChanged = false;

                        //console.log('start init slick',  Date.now());
                        initSliderPlugin(windowWidth);
                        //console.log('stop init slick',  Date.now());

                        if (currentPicture) {
                            $('.picture .img a[data-id='+currentPicture+']').trigger('click');
                        }

                        $('.preloader').hide();
                    }
                }, "json");
        };

        var buildMobileGallery = function() {

            $('.preloader').show();
            $('#pictures').hide();

            var category = parseInt(galleryContainer.attr('data-category'));
            var person = $('#person').val();
            var city = $('#city').val();

            if (currentCategory != category || currentPerson != person || currentCity != city) {
                filterChanged = true;
                currentCategory = category;
                currentPerson = person;
                currentCity = city;
            }

            if (!filterChanged)  {
                return;
            }

            mobileLoadedPages = 0;

            //console.log('mobile ajax request',  Date.now());
            //console.log(currentAction);

            $.post(urls[currentAction], {category: category, person: person, city: city, limit: mobilePageLimit, page: mobileLoadedPages},
                function(data){

                    //console.log(data);

                    //console.log('mobile ajax response',  Date.now());

                    if (data.pictures != undefined) {

                        mobilePictures = [];

                        for (var i in data.pictures) {
                            mobilePictures.push(data.pictures[i]);
                        }

                        if (currentAction == 'works'){
                            shuffle(mobilePictures);
                        }

                        mobileLoadedPages = 1;

                        //console.log('mobile', mobilePageLimit, mobilePictures);

                        if (mobilePictures.length == 0) {
                            galleryContainer.html('<div class="picture-search-result">Поиск по заданным параметрам не дал результатов.</div>');
                            $('.preloader').hide();
                            $('#pictures').show();
                            $('#picture-counter').hide();
                            return;
                        }

                        mobileTotalPictures =  data.total;
                        mobileCurrentPicture = 0;
                        galleryContainer.empty();
                        galleryContainer.html('<div class="picture">' + elementMobileHtml(mobilePictures[0], 0) +'</div>');

                        var pic = mobilePictures[0];
                        setGraph(pic['id'], pic['name'], pic['person'], pic['age'], pic['city'], pic['picture_main']);

                        $('#slide-current').html(mobileCurrentPicture+1);
                        $('#slide-total').html(mobileTotalPictures);

                        // hide vote block
                        if (data.vote_disabled) {
                            $('.picture-vote a').css({visibility: 'hidden'});
                            //$('.share').hide();
                        }

                        if (data.gallery_disabled) {
                            $('body').addClass('modal-open');
                            $('#modal-closed').show();
                        }

                        if (mobileTotalPictures > 0) {
                            $('#picture-counter').show();
                        } else {
                            $('#picture-counter').hide();
                        }

                        filterChanged = false;
                        $('.preloader').hide();
                        $('#pictures').show();

                    }
                }, "json");
        };

        var clickMobilePicture = function(el, e){
            var that = $(el);

            $('.preloader').show();
            $('#pictures').hide();

            var category = parseInt(galleryContainer.attr('data-category'));
            var person = $('#person').val();
            var city = $('#city').val();

            if (e.clientX > $(window).width()/2 && mobileTotalPictures-1 > mobileCurrentPicture) {
                mobileCurrentPicture = mobileCurrentPicture + 1;
            } else if (e.clientX < $(window).width()/2 && mobileCurrentPicture > 0) {
                mobileCurrentPicture = mobileCurrentPicture - 1;
            } else {
                $('.preloader').hide();
                $('#pictures').show();
                return;
            }
            console.log(currentAction);

            if (!(mobileCurrentPicture in mobilePictures && mobileLoadedPages < mobileTotalPictures)) {
                $.post(urls[currentAction], {category: category, person: person, city: city, limit: mobilePageLimit, page: mobileLoadedPages},
                    function(data){

                        //console.log(data);

                        var tempArray = [];

                        for (var i in data.pictures) {
                            tempArray.push(data.pictures[i]);
                        }

                        if (currentAction == 'works'){
                            shuffle(tempArray);
                        }

                        mobilePictures = mobilePictures.concat(tempArray);

                        mobileLoadedPages = mobileLoadedPages + 1;

                        //console.log(mobilePictures);

                        mobileTotalPictures =  data.total;

                        galleryContainer.find('.picture').html(elementMobileHtml(mobilePictures[mobileCurrentPicture], mobileCurrentPicture));

                        var pic = mobilePictures[mobileCurrentPicture];
                        setGraph(pic['id'], pic['name'], pic['person'], pic['age'], pic['city'], pic['picture_main']);

                        $('#slide-current').html(mobileCurrentPicture+1);
                        $('#slide-total').html(mobileTotalPictures);

                        if (data.vote_disabled) {
                            $('.picture-vote a').css({visibility: 'hidden'});
                            //$('.share').hide();
                        }

                        $('.preloader').hide();
                        $('#pictures').show();

                    }, "json")
            } else {
                galleryContainer.find('.picture').html(elementMobileHtml(mobilePictures[mobileCurrentPicture]));

                var pic = mobilePictures[mobileCurrentPicture];
                setGraph(pic['id'], pic['name'], pic['person'], pic['age'], pic['city'], pic['picture_main']);

                $('#slide-current').html(mobileCurrentPicture+1);
                $('.preloader').hide();
                $('#pictures').show();
            }

        };

        var setGraph = function(picture_id, picture_name, person, age, city, picture, history) {

            if (typeof(history)==='undefined') history = false;

            console.log(picture_id);

            var currentUrl = window.location.protocol + '//' + window.location.host + '/pictures/' +picture_id;


            var url = $('meta[property="og:url"]').attr('content', currentUrl);
            var title = $('meta[property="og:title"]').attr('content', picture_name);
            var description = $('meta[property="og:description"]').attr('content', person+', '+age+', '+city);
            var image = $('meta[property="og:image"]').attr('content', window.location.protocol + '//' + window.location.host + picture);

            if (history) {
                window.history.pushState({currentSlide: picture_id},'', '/pictures/' + picture_id);
            }

        };

        var handleGalleryAfterChange = function(currentSlide, totalSlides) {

            desktopCurrentPage = currentSlide+1;

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

            //console.log(desktopCurrentPage, desktopLoadedPages - 2,desktopTotalPages);

            if (desktopCurrentPage >= desktopLoadedPages - 2 && desktopLoadedPages < desktopTotalPages) {

                if (desktopLoading) {
                    return;
                }

                var category = parseInt(galleryContainer.attr('data-category'));
                var person = $('#person').val();
                var city = $('#city').val();

                desktopLoading = true;

                //console.log('desktopLoadedDataPages', desktopLoadedDataPages);
                //console.log('ajax request next',  Date.now());
                //console.log(currentAction);

                $.post(urls[currentAction], {category: category, person: person, city: city, limit: desktopPageLimit, page:desktopLoadedDataPages},
                    function(data){

                        //console.log('ajax response next',  Date.now());

                        if (data.gallery_disabled) {
                            $('body').addClass('modal-open');
                            $('#modal-closed').show();
                        }

                        if (data.pictures != undefined) {

                            var tempPictures = [];

                            for (var i in data.pictures) {
                                tempPictures.push(data.pictures[i]);
                            }

                            if (currentAction == 'works'){
                                shuffle(tempPictures);
                            }

                            desktopPictures = desktopPictures.concat(tempPictures);

                            desktopLoadedDataPages = desktopLoadedDataPages + 1;

                            //console.log('desktopLoadedDataPages', desktopLoadedDataPages);


                            var currentElement = 1;
                            var maxElement = 6;

                            desktopTotalPictures = data.total;
                            desktopTotalPages =  Math.ceil(desktopTotalPictures / maxElement);
                            desktopLoadedPages = Math.ceil(desktopPictures.length / maxElement);

                            //console.log('loaded pages', desktopLoadedPages);

                            $('#slide-current').html(desktopCurrentPage);
                            $('#slide-total').html(desktopTotalPages);

                            for (var i in tempPictures) {
                                if (currentElement == 1) {
                                    var newBlock = $('<div></div>');
                                }

                                newBlock.append(elementHtml(tempPictures[i], desktopPictures.length - tempPictures.length + i));

                                if (currentElement >= maxElement) {
                                    galleryContainer.slick('slickAdd',newBlock.get(0).outerHTML);
                                    currentElement = 1;
                                    continue;
                                }
                                currentElement = currentElement + 1;
                            }
                            if (currentElement > 1) {
                                galleryContainer.slick('slickAdd',newBlock.get(0).outerHTML);
                            }

                            // hide vote block
                            if (data.vote_disabled) {
                                $('.picture-vote a').css({visibility: 'hidden'});
                                //$('.share').hide();
                            }

                            desktopLoading = false;

                        }
                    }, "json");
            }
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

        var initSliderPlugin = function(windowWidth) {
            if (windowWidth >= 900) {
                var prevArrow = '<button type="button" class="slick-prev gallery-prev"><img src="/bundles/public/img/prev.png"></button>';
                var nextArrow = '<button type="button" class="slick-next gallery-next"><img src="/bundles/public/img/next.png"></button>';
            } else {
                var prevArrow = '';
                var nextArrow = '';
            }

            galleryContainer.slick({
                infinite: false,
                dots : false,
                lazyLoad: 'ondemand', //progressive
                slidesToShow: 1,
                adaptiveHeight: true,
                prevArrow: prevArrow,
                nextArrow: nextArrow
            }).on('afterChange', function(event, slick, currentSlide){
                handleGalleryAfterChange(currentSlide, desktopTotalPages);
                $('#slide-current').html(currentSlide+1);
            });

            //$('#slide-current').html(desktopCurrentPage);
            //$('#slide-total').html(desktopTotalPages);

            handleGalleryAfterChange(0, desktopTotalPages);

            $('#pictures').show();

            gallerySlick = true;
            galleryContainer.slick('setPosition');
        };

        $(document).on('click', '.tabs li', function(e) {
            var that = $(this);
            var id = that.attr('data-id');
            that.addClass('active').siblings().removeClass('active');
            galleryContainer.attr('data-category', id);
            buildGallery();
            $.scrollTo('.picture-help', 1000);
        });

        $(document).on('click', '.btn-picture-search', function(e) {
            e.preventDefault();
            buildGallery();
            $.scrollTo('.picture-help', 1000);
        });

        $(document).on('click', '.picture .img a', function(e) {
            e.preventDefault();

            if ($(window).width() < 900) {
                clickMobilePicture(this, e);
                return;
            }

            ga('send', 'event', 'gallery', 'popup');

            var pos = parseInt($(this).attr('data-position'));

            popupSlick.empty();

            for (var i in desktopPictures) {
                popupSlick.append(modalElementHtml(desktopPictures[i]))
            }

            $('.picture-vote a').css({visibility: 'hidden'});

            $('body').addClass('modal-open');
            popupSlick.slick({
                infinite: false,
                dots : false,
                lazyLoad: 'ondemand', //progressive
                slidesToShow: 1,
                initialSlide: pos,
                adaptiveHeight: true,
                prevArrow: '<button type="button" class="slick-prev popup-prev"><img src="/bundles/public/img/popup_prev.png"></button>',
                nextArrow: '<button type="button" class="slick-next popup-next"><img src="/bundles/public/img/popup_next.png"></button>'
            }).on('afterChange', function(event, slick, currentSlide){
                setModalArrowVibibility(currentSlide, desktopPictures.length);

                var pic = desktopPictures[currentSlide];
                setGraph(pic['id'], pic['name'], pic['person'], pic['age'], pic['city'], pic['picture_main']);

            });
            $('#modal-gallery').show();

            popupSlick.slick('setPosition');

            var pic = desktopPictures[pos];
            setGraph(pic['id'], pic['name'], pic['person'], pic['age'], pic['city'], pic['picture_main']);

        });

        $(document).on('click', 'a.modal-close', function(e){
            e.preventDefault();
            $(this).parents('.modal').hide();
            $("body").removeClass("modal-open");
            if ($(this).parents('.modal').get(0).id == 'modal-gallery') {
                popupSlick.slick('unslick');
            }
        });

        buildGallery();


        // auth functions

        $(document).on('click', '.picture-vote a:not(.busy)', function(e) {
            e.preventDefault();
            var that = $(this);

            that.addClass('busy');

            var picture = that.attr('data-id');

            //console.log('vote click', 'picture - ' + picture);

            $.post('/pictures/vote', {picture: picture}, function(data) {
                if (data.voted ) {
                    if (data.like == true) {
                        that.addClass('inactive');
                    } else {
                        that.removeClass('inactive');
                    }
                    that.siblings('.likes').html(data.likes);
                    that.removeClass('busy');
                } else if (data.redirect) {
                    //console.log(data);
                    var url = data.redirect;
                    $.get(url, { "_": $.now() },function(data){
                        $('#modal-auth .modal-content').html(data);
                        $('body').addClass('modal-open');
                        $('#modal-auth').show();
                    })

                } else {
                    //console.log(data.message);
                }
                that.removeClass('busy');
            });
        });

        var popupwindow = function(url, title, w, h) {
            var left = (screen.width/2)-(w/2);
            var top = (screen.height/2)-(h/2);
            return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
        };

        $(document).on('click', '.modal-dialog-auth form a:not(.dynamic), .nologin-controls a', function(e){
            e.preventDefault();

            var that = $(this);

            var url = that.attr('href');

            $.get(url, { "_": $.now() }, function(data){
                $('#modal-auth .modal-content').html(data);
            })
        });

        $(document).on('click', '.modal-dialog-auth form a.dynamic', function(e){
            e.preventDefault();

            var that = $(this);

            var block = that.attr('href');

            $(block).show();

        });

        $(document).on('submit', '.modal-dialog-auth form', function(e){
            e.preventDefault();

            var that = $(this);
            var url = that.attr('action');
            var params = {};
            var formdata = that.serializeArray();
            that.find('.message.message-error').hide();

            $.each(formdata, function(i, field) {
                params[field.name] = field.value;
            });

            $.post(url, params, function(data){
                //console.log(data);
                if (data.status) {

                    if (data.task == 'redirect') {
                        $.get(data.redirect, { "_": $.now() }, function(data){
                            $('#modal-auth .modal-content').html(data);
                        });
                        return;
                    }

                    if (data.task == 'reload') {
                        window.location.reload();
                        return;
                    }

                    if (data.task == 'message') {
                        $('#modal-auth .modal-content').html('<div class="text-center">' + data.message + '</div>');
                    }

                    if (data.task == 'close') {
                        checkUserVotes();
                        $('#modal-auth a.modal-close').trigger('click');
                    }

                    if (data.picture) {
                        $('.picture-vote a[data-id='+data.picture+']').trigger('click');
                    }

                    $('.account-control').trigger('check');

                } else {
                    that.find('.message.message-error').html(data.message);
                    that.find('.message.message-error').show();
                    //console.log(data.message);
                }
            });
        });

        $(document).on('click', '.modal-dialog-auth .social-selector a', function(e){
            e.preventDefault();

            var that = $(this);
            var url = that.attr('href');

            popupwindow(url, 'Social OAuth', 640, 420);
        });

        $(document).on('click', '.btn-user-login', function(e){
            e.preventDefault();

            var url = '/pictures/login';

            $.get(url, { "_": $.now() }, function(data){
                $('#modal-auth .modal-content').html(data);
                $('body').addClass('modal-open');
                $('#modal-auth').show();
            });
        });

        $(document).on('click', 'input[type=checkbox]+label', function(e){
            var that = $(this);
            var checkbox = that.prev();

            checkbox.prop('checked', !checkbox.prop('checked'));
            if (checkbox.attr('name') == 'is_driver') {
                if (checkbox.prop('checked')) {
                    $('input[name=auto_brand]').show();
                    $('input[name=auto_model]').show();
                } else {
                    $('input[name=auto_brand]').hide();
                    $('input[name=auto_model]').hide();
                }
            }
        });

        $(document).on('check', '.account-control', function(e){

            var that = $(this);

            $.get('/pictures/user', { "_": $.now() }, function(data){
                that.after(data.content).remove();
            })
        })


        $(document).on('click', '.btn-share-vk', function(e) {
            e.preventDefault();

            var url = $('meta[property="og:url"]').attr('content');
            var title = $('meta[property="og:title"]').attr('content');
            var description = $('meta[property="og:description"]').attr('content');
            var image = $('meta[property="og:image"]').attr('content');

            popupwindow(Share.vkontakte(url,title,image,description), 'VK Share Page', 626, 426);
        });

        $(document).on('click', '.btn-share-fb', function(e) {
            e.preventDefault();

            var url = $('meta[property="og:url"]').attr('content');

            popupwindow(Share.facebook(url), 'Facebook Share Page', 626, 426);
        })

    });

})(jQuery);

Share = {
    vkontakte: function(purl, ptitle, pimg, text) {
        url  = 'http://vkontakte.ru/share.php?';
        url += 'url='          + encodeURIComponent(purl);
        url += '&title='       + encodeURIComponent(ptitle);
        url += '&description=' + encodeURIComponent(text);
        url += '&image='       + encodeURIComponent(pimg);
        url += '&noparse=true';
        return url;
    },
    facebook: function(purl) {
        url  = 'http://www.facebook.com/sharer.php?src=100';
        url += '&u='       + encodeURIComponent(purl);
        return url;
    }

};

function openProfileForm(picture) {
    $('.account-control').trigger('check');
    try {
        if (picture) {
            $('.picture-vote a[data-id=' + picture + ']').trigger('click');
        }
    } catch (err) {
        console.log(err);
        var url = '/pictures/profile';

        $.get(url, { "_": $.now() }, function(data){
            $('#modal-auth .modal-content').html(data);
            $('body').addClass('modal-open');
            $('#modal-auth').show();
        })

    }

}

function closeAuthForm(picture) {
    $('.account-control').trigger('check');
    $('#modal-auth a.modal-close').trigger('click');
    try {
        if (picture) {
            $('.picture-vote a[data-id=' + picture + ']').trigger('click');
        }
    } catch (err) {
        console.log(err);
    }
    checkUserVotes();
}

function checkUserVotes() {
    $.post('/pictures/votes', {}, function(data){
        if (data.votes) {
            for (var i in data.votes) {
                $('.picture-vote a[data-id='+data.votes[i]['picture_id']+']').addClass('inactive');
            }
        } else {
            console.log(data.message);
        }
    },"json");
}