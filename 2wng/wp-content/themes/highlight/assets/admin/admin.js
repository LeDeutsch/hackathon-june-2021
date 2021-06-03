jQuery(function ($) {
    $(document).on('click', '.highlight-welcome-notice .notice-dismiss', function () {
        jQuery.post(ajaxurl, {
            'action': 'highlight_dismiss_welcome_popup'
        });
    });
});

function  highlight_close_popup() {
    jQuery('.highlight-welcome-notice').fadeOut();
}
