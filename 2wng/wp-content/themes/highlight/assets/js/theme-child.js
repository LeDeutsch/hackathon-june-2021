(function () {

    function animateSideNavigation(sideMenu) {

        var menuItems = sideMenu.find("a");

        // Anchors corresponding to menu items
        var scrollItems = menuItems.map(function () {
            var item = jQuery(jQuery(this).attr("href"));
            if (item.length) {
                return item;
            }
        });


        var fromTop = jQuery(this).scrollTop() + window.innerHeight / 2;
        var cur = scrollItems.map(function () {
            if (jQuery(this).offset().top < fromTop) {
                return this;
            }
        });

        cur = cur[cur.length - 1];
        var id = cur && cur.length ? cur[0].id : "";

        if (!sideMenu.is('.no-activation')) {
            menuItems.parent().removeClass("active").end().filter("[href='#" + id + "']").parent().addClass("active");
        }

        var showAfterScroll = sideMenu.find('ul').attr('data-after-scroll');
        var headerHeight = jQuery('.header-wrapper').outerHeight();

        var showSideNavFrom = jQuery(this).scrollTop() + window.innerHeight * 0.2;

        if (showAfterScroll === 'true' && showSideNavFrom < headerHeight) {
            sideMenu.css('right', '-220px');
        } else {
            sideMenu.css('right', '0');

            if (!jQuery('.page-content div[data-export-id][data-bullets-visibility]').length) {
                sideMenu.hide();
            } else {
                sideMenu.show();
            }

        }

    }

    window.highlight_makeSidebarNavigation = function () {
        var $ = jQuery,
            sections = $('.page-content div[data-export-id][data-bullets-visibility]'),
            sideMenu = jQuery("#side-navigation");


        sections = sections.map(function (index, item) {
            return {
                "data-label": $(item).data('label'),
                "data-id": $(item).attr('id'),
                "data-visible": !!$(item).attr('data-bullets-visibility')
            }
        });

        if (sideMenu.length) {
            sideMenu.find('li:not(.hero-bullet)').remove();
            sections.each(function (index, section) {
                var listElement = $('<li data-ssid=' + section['data-id'] + '><a href="#' + section['data-id'] + '">' + section['data-label'] + '</a></li>');
                if (!section['data-visible']) {
                    listElement.addClass('hidden-bullet');
                }
                sideMenu.find('ul').append(listElement);
            });
        }

        return sideMenu;
    };


    window.mesmerizeDomReady(function () {

        var $ = jQuery;


        var sideMenu = window.highlight_makeSidebarNavigation();

        animateSideNavigation(sideMenu);

        // Bind to scroll
        jQuery(window).scroll(function () {
            animateSideNavigation(sideMenu);
        });
        // Bind to resize
        jQuery(window).resize(function () {
            animateSideNavigation(sideMenu);
        });


        sideMenu.on('click tap', 'li, a', function (e) {
            e.preventDefault();
            e.stopPropagation();

            sideMenu.addClass('no-activation');
            var target = "#",
                link = $(this);
            if (jQuery(this).is('li')) {
                link = jQuery(this).find('a');
                target = link.attr('href');

            } else {
                target = link.attr('href');
            }

            link.closest('li').addClass('active').siblings().removeClass('active');

            jQuery([document.documentElement, document.body]).animate(
                {
                    scrollTop: jQuery(target).offset().top
                },
                500,
                'swing',
                function () {
                    sideMenu.removeClass('no-activation');
                    link.closest('li').addClass('active');
                }
            );

            return false;
        });

    });


})();
