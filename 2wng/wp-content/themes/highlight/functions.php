<?php

// Exit if accessed directly
if ( ! defined('ABSPATH')) {
    exit;
}

add_filter('mesmerize_notifications_template_slug', function () {
    return "highlight";
});

add_filter('mesmerize_notifications_stylesheet_slug', function () {
    return "highlight";
});

function highlight_is_embedded()
{
    return apply_filters('mesmerize_is_child_embedded', false);
}

function highlight_text_domain()
{
    $theme      = wp_get_theme();
    $textDomain = $theme->get('TextDomain');
    
    return $textDomain;
}

function highlight_get_stylesheet_directory()
{
    if (highlight_is_embedded()) {
        return get_template_directory() . "/child";
    } else {
        return get_stylesheet_directory();
    }
    
}


function highlight_get_stylesheet_directory_uri()
{
    if (highlight_is_embedded()) {
        return get_template_directory_uri() . "/child";
    } else {
        return get_stylesheet_directory_uri();
    }
    
}

function highlight_require($path)
{
    $path = trim($path, "\\/");
    
    if (file_exists(highlight_get_stylesheet_directory() . "/{$path}")) {
        require_once highlight_get_stylesheet_directory() . "/{$path}";
    }
}

highlight_require("inc/defaults.php");

highlight_require("customizer/customizer.php");

 


function highlight_enqueue_styles()
{
    
    if (highlight_is_embedded()) {
        $text_domain        = highlight_text_domain();
        $parent_text_domain = mesmerize_get_text_domain();
        wp_enqueue_style("{$text_domain}-child", highlight_get_stylesheet_directory_uri() . '/style.min.css', array("{$parent_text_domain}-style"));
    } else {
        $parent_style = 'mesmerize-parent';
        wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.min.css');
    }
    
    
}

function highlight_kirki_add_inline_style_handle($handle)
{
    
    if (highlight_is_embedded()) {
        $text_domain = highlight_text_domain();
        $handle      = "{$text_domain}-child";
    }
    
    return $handle;
}


function highlight_print_sticky_class($class = array())
{
    
    $class = (array)$class;
    if (is_sticky()) {
        $class[] = 'sticky';
    }
    echo esc_attr(implode(" ", $class));
}


add_filter('mesmerize_archive_entry_class', function ($class) {
    if (is_sticky()) {
        $class[] = 'sticky';
    }
    
    return $class;
});

function highlight_default_values_filter($args)
{
    
    $default_values = highlight_theme_defaults();
    
    if (array_key_exists($args['settings'], $default_values) && array_key_exists('default', $args)) {
        if ($args['default'] != $default_values[$args['settings']]) {
            $args['default'] = $default_values[$args['settings']];
        }
    }
    
    return $args;
}

function highlight_default_headings_typography_filter($args)
{
    
    $args = array_merge((array)$args, array(
        'font-family' => 'Open Sans',
        'font-weight' => "400",
    ));
    
    return $args;
}

function highlight_default_headings_font_family_filter($args)
{
    
    $args = highlight_default_headings_typography_filter(array(
        'font-family' => $args,
    ));
    
    return $args['font-family'];
}

function highlight_replace_theme_tag($content)
{
    
    return str_replace('[tag_child_theme_uri]', highlight_get_stylesheet_directory_uri(), $content);
    
}

function highlight_theme_page_name()
{
    return __('Highlight Info', 'highlight');
}


function highlight_demos_page_name()
{
    return __('Highlight Demos', 'highlight');
}


function highlight_demos_available_in_pro()
{
    return __('Highlight PRO', 'highlight');
}

function highlight_thankyou_message()
{
    return __('Thank you for choosing Highlight!', 'highlight');
}

function highlight_companion_description()
{
    return esc_html__('Mesmerize Companion plugin adds drag and drop functionality and many other features to the Highlight theme.', 'highlight');
}


function highlight_info_page_tabs($tabs)
{
    //Notice: This filter will be removed when the child imports will be created
    if (array_key_exists('demo-imports', $tabs)) {
        unset($tabs['demo-imports']);
    }
    
    return $tabs;
}

function highlight_get_footer_copyright($copyright, $preview_atts)
{
    
    $copyrightText = __('Built using WordPress and the %s', 'highlight');
    $copyrightText = sprintf($copyrightText, '<a target="_blank" href="%1$s" rel="nofollow" class="mesmerize-theme-link">Highlight Theme</a>');
    
    $copyrightText = sprintf($copyrightText, 'https://extendthemes.com/go/built-with-highlight/');
    
    $copyright = '<p ' . $preview_atts . ' class="copyright">&copy;&nbsp;' . "&nbsp;" . date_i18n(__('Y', 'highlight')) . '&nbsp;' . esc_html(get_bloginfo('name')) . '.&nbsp;' . wp_kses_post($copyrightText) . '</p>';
    
    return $copyright;
}

function highlight_remove_mesmerize_demos_menu_item()
{
    //Notice: This filter will be removed when the child imports will be created
    remove_submenu_page('themes.php', 'mesmerize-demos');
}

function highlight_remove_demo_import_popup($popups)
{
    //Notice: This filter will be removed when the child imports will be created
    foreach ($popups as $index => $popup) {
        if (array_key_exists('id', $popup) && $popup['id'] === "demo_import") {
            unset($popups[$index]);
        }
    }
    
    return $popups;
}


add_filter('mesmerize_kirki_field_filter', 'highlight_default_values_filter', 10, 1);
add_filter('mesmerize_default_headings_typography', 'highlight_default_headings_typography_filter', 10, 1);
add_filter('mesmerize_default_headings_font_family', 'highlight_default_headings_font_family_filter', 10, 1);
add_filter('mesmerize_kirki_add_inline_style_handle', 'highlight_kirki_add_inline_style_handle');

add_filter('mesmerize_already_colored_sections', function ($args) {
    return array_merge($args, array('portfolio-5-mc'));
}, 10, 1);


add_action('after_setup_theme', function () {
    
    add_action('wp_enqueue_scripts', 'highlight_enqueue_styles');
    
    add_filter('mesmerize_stylesheet_has_min', "__return_true");
    
    add_filter('mesmerize_stylesheet_deps', function ($args) {
        
        if ( ! highlight_is_embedded()) {
            $args[] = 'mesmerize-parent';
        }
        
        return $args;
    });
    
    add_filter('cloudpress\theme_support', function ($args) {
        
        $args['custom-background']['default-color'] = '#ffffff';
        
        return $args;
        
    }, 10, 1);
    
    
    highlight_require("inc/template-functions.php");
    highlight_require("inc/admin.php");
    
    add_action('wp_enqueue_scripts', function () {
        
        $js     = 'theme-child';
        $url_js = highlight_get_stylesheet_directory_uri() . "/assets/js/{$js}.js";
        
        wp_enqueue_script($js, $url_js, array(), null, true);
        
    });
    
    add_action('wp_footer', 'highlight_create_side_navigation');
    
    add_action('cloudpress\template\load_assets', function ($companion) {
        
        /**@var \Mesmerize\Companion $companion */
        if ($companion->isMaintainable()) {
            $ver = $companion->version;
            wp_enqueue_style('companion-page-css', highlight_get_stylesheet_directory_uri() . "/customizer/sections/content.css", array(), $ver);
        }
        
    });
    
    add_filter('mesmerize_theme_page_name', 'highlight_theme_page_name');
    add_filter('mesmerize_demos_page_name', 'highlight_demos_page_name');
    add_filter('mesmerize_thankyou_message', 'highlight_thankyou_message');
    add_filter('mesmerize_companion_description', 'highlight_companion_description');
    add_filter('mesmerize_demos_available_in_pro', 'highlight_demos_available_in_pro');
    add_filter('mesmerize_theme_logo_url', '__return_false');
    
    
    add_filter('mesmerize_get_footer_copyright', 'highlight_get_footer_copyright', 0, 2);
    
    //Notice: This filter will be removed when the child imports will be created
//    add_filter('mesmerize_info_page_tabs', 'highlight_info_page_tabs');
//    add_action('admin_menu', 'highlight_remove_mesmerize_demos_menu_item', 20);
//    add_filter('cloudpress\customizer\feature_popups', 'highlight_remove_demo_import_popup');
    
});



