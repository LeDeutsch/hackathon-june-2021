(function (root, CP_Customizer, $) {

    CP_Customizer.addModule(function (CP_Customizer) {

        var sectionPanel = CP_Customizer.panels.sectionPanel;
        var priority = 4;

        sectionPanel.registerArea('fullscreen_options', {
            priority: priority,
            areaTitle: root.CP_Customizer.translateCompanionString('Fullscreen Options'),
            init: function ($container) {

                var rowFullscreen = CP_Customizer.createControl.checkbox(
                    this.getPrefixed('fullscreen'),
                    $container,
                    root.CP_Customizer.translateCompanionString('Make section fullscreen')
                );

                var rowVisibleInBulletNav = CP_Customizer.createControl.checkbox(
                    this.getPrefixed('visibility-in-bullets'),
                    $container,
                    root.CP_Customizer.translateCompanionString('Visible in side navigation')
                );

                this.addToControlsList(rowFullscreen);
                this.addToControlsList(rowVisibleInBulletNav);
            },

            update: function (data) {

                var selector = '[data-id="' + data.section.attr('data-id') + '"]';

                var fullScreenValue = CP_Customizer.preview.find(selector).hasClass('full-screen-section');
                var visibleInSideNav = !!CP_Customizer.preview.find(selector).attr('data-bullets-visibility');

                this.getControl('fullscreen').attachWithSetter(
                    fullScreenValue,
                    function (value) {
                        if (value) {
                            CP_Customizer.preview.find(selector).addClass('full-screen-section');
                        } else {
                            CP_Customizer.preview.find(selector).removeClass('full-screen-section');
                        }
                    }
                );

                this.getControl('visibility-in-bullets').attachWithSetter(
                    visibleInSideNav,
                    function (value) {

                        if (value) {
                            CP_Customizer.preview.find(selector).attr('data-bullets-visibility', value);
                        } else {
                            CP_Customizer.preview.find(selector).removeAttr('data-bullets-visibility');
                        }

                        // recreate the sidenav content and trigger a scroll event
                        CP_Customizer.preview.frame().highlight_makeSidebarNavigation();
                        CP_Customizer.preview.jQuery(CP_Customizer.preview.frame()).trigger('scroll');

                    }
                );

            }
        });

        CP_Customizer.hooks.addAction('before_section_remove', function ($section) {
            var sectionID = jQuery($section).attr('id');
            var node = CP_Customizer.preview.find('#side-navigation li[data-ssid="' + sectionID + '"]');
            node.remove();
        });

    });


})(window, CP_Customizer, jQuery);
