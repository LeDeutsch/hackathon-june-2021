(function ($) {

    wp.customize('header_nav_enabled', function (value) {
        value.bind(function (newval) {
            if (newval) {
                jQuery('.mesmerize-front-page #mainmenu_container').hide();
            } else {
                jQuery('.mesmerize-front-page #mainmenu_container').show();
            }
        });
    });
    wp.customize('inner_header_nav_enabled', function (value) {
        value.bind(function (newval) {
            if (newval) {
                jQuery('.mesmerize-inner-page #mainmenu_container').hide();
            } else {
                jQuery('.mesmerize-inner-page #mainmenu_container').show();
            }
        });
    });

    wp.customize('show_hero_bullet_on_navigation', function (value) {
        value.bind(function (newval) {
            var heroBullet = jQuery('#side-navigation .hero-bullet');

            if (newval) {
                heroBullet.removeClass('hidden-bullet');
            } else {
                heroBullet.addClass('hidden-bullet');
            }

        });
    });

    wp.customize('show_side_navigation_after_scroll', function (setting) {


        setting.bind(function (value) {

            var sideMenu = jQuery('#side-navigation');
            var headerHeight = jQuery('.header-wrapper').outerHeight();
            var fromTop = jQuery(this).scrollTop();

            if (value === true) {
                sideMenu.find('ul').attr('data-after-scroll', 'true');
                if (fromTop < headerHeight) {
                    sideMenu.css('right', '-220px');
                }
            } else {
                if (value === false) {
                    sideMenu.find('ul').attr('data-after-scroll', 'false');
                    sideMenu.css('right', '0');
                }
            }

        })
    });

    wp.customize('hero_bullet_label', function (value) {
        value.bind(function (newval) {
            jQuery('#side-navigation .hero-bullet > a').html(newval);
        })
    });

    wp.customize('side_navigation_visible_labels', function (value) {
        value.bind(function (newval) {
            jQuery('#side-navigation ul').attr('data-type', newval);
        })
    });

})(jQuery);
