(function ($) {
    window.CP_Customizer.addModule(function (CP_Customizer) {
        var control = wp.customize.panel('footer_panel');

        if (!control) {
            return;
        }

        var $footerLi = control.container.eq(0);
        // remove default events
        $footerLi.children('h3').addBack().off();

        $footerLi.find('[data-name="change"]').addBack().click(function (event) {
            event.preventDefault();
            event.stopPropagation();

            var sectionID = 'reiki_webpage_footers';
            if (CP_Customizer.isRightSidebarVisible(sectionID)) {
                CP_Customizer.hideRightSidebar();
            } else {
                CP_Customizer.openRightSidebar(sectionID);
            }
        });
    });
})(jQuery);
