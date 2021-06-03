<?php

add_action('admin_init', 'highlight_admin_init_welcome_notice', 0);
add_action('admin_init', 'highlight_admin_init_enqueue_assets');

add_action('wp_ajax_highlight_dismiss_welcome_popup', 'highlight_admin_ajax_welcome_notice_dismiss');

function highlight_admin_init_enqueue_assets()
{
    
    wp_enqueue_style(
        'highlight_admin_init_welcome_notice',
        highlight_get_stylesheet_directory_uri() . "/assets/admin/admin.css"
    );
    wp_enqueue_script(
        'highlight_admin_init_welcome_notice',
        highlight_get_stylesheet_directory_uri() . "/assets/admin/admin.js",
        array('jquery')
    );
}

function highlight_admin_init_welcome_notice()
{
    global $pagenow;
  
    if($pagenow === "update.php"){
        return;
    }
    
    if ( ! get_option('highlight_welcome_notice_dismissed', false)) {
        
        
        if (class_exists("\\Mesmerize\\Companion")) {
            return;
        }
        
        add_action('admin_notices', 'highlight_welcome_notice_render_content', 0);
    }
}


add_filter('mesmerize_start_with_front_page_name', function () { return __('Highlight'); });

function highlight_welcome_notice_cntent() {
	wp_enqueue_style( 'mesmerize_customizer_css',
								get_template_directory_uri() . '/customizer/css/companion-install.css' );	
    ob_start();
	mesmerize_require( "/customizer/start-with-frontpage.php" );
    $popup = ob_get_clean();
    
	$popup = preg_replace( '#<div class="image-wrapper".*?</div>#ms', "<div class='image-scroll'></div>", $popup );
    
    return $popup;
}

function highlight_welcome_notice_render_content()
{
    ?>
    <div class="notice is-dismissible highlight-welcome-notice">
        <div class="notice-content-wrapper">
            <?php echo highlight_welcome_notice_cntent(); ?>
        </div>
    </div>
    <?php
}

function highlight_admin_ajax_welcome_notice_dismiss()
{
    update_option('highlight_welcome_notice_dismissed', true);
}
