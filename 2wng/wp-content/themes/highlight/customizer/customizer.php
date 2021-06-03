<?php

add_filter('pre_option_mesmerize_companion_disable_popup', '__return_true');
//add_filter('mesmerize_show_info_pro_messages', '__return_false', 30);
//add_filter('mesmerize_show_main_info_pro_messages', '__return_false', 30);

add_filter('mesmerize_upgrade_url', function ($url, $base_url, $args, $hash) {
    
    $base_url = "https://extendthemes.com/go/highlight-upgrade/";
    $url      = add_query_arg($args, $base_url);
    
    if ($hash = trim($hash)) {
        $hash = "#" . $hash;
    }
    
    $url = $url . esc_url($hash);
    
    return $url;
    
}, 10, 4);

function highlight_default_content_spacing_filter($args, $content_type)
{
    
    if ($content_type == 1) {
        $args = array(
            "top"    => "30%",
            "bottom" => "0%",
        );
    }
    if ($content_type == 2) {
        $args = array(
            "top"    => "15%",
            "bottom" => "0%",
        );
    }
    
    return $args;
}

function highlight_default_sections($sections = array())
{
    $sections = array(
        "features-ov-4-mc",
        "about-images-right-section-mc",
        "features-16-card-bordered-mc",
        "content-7-mc",
        "content-8-mc",
        "portfolio-5-mc",
        "testimonials-5-mc",
        "cta-blue-section-mc",
        "team-5-mc",
        "blog-section-mc",
        "contact-1-mc",
    );
    
    return $sections;
    
}

function highlight_sidenav_default_sections()
{
    return array(
        "about-images-right-section-mc",
        "features-16-card-bordered-mc",
        "portfolio-5-mc",
        "team-5-mc",
        "contact-1-mc",
    );
    
}

function highlight_load_customizer_controls()
{
    
    wp_enqueue_script('customizer-child-section-panel', highlight_get_stylesheet_directory_uri() . "/customizer/js/customizer-section-panel.js", array('customizer-base'), false, true);
    
}

add_filter('mesmerize_header_content_spacing_partial_update', 'highlight_default_content_spacing_filter', 10, 2);

add_filter('mesmerize_default_sections', 'highlight_default_sections', 10, 1);

add_action('after_setup_theme', function () {
    
    highlight_require("inc/side-navigation.php");
    
    add_action('cloudpress\customizer\preview_scripts', function ($customizer) {
        
        $url_js = highlight_get_stylesheet_directory_uri() . "/customizer/js/preview.js";
        wp_enqueue_script('highlight-customizer-js', $url_js, array('customize-base'), false, true);
        
    }, 10, 1);
    
    add_action('cloudpress\companion\ready', function ($companion) {
        /** @var \Mesmerize\Customizer\Customizer $customizer */
        /** @var \Mesmerize\Companion $companion */
        $customizer = $companion->customizer();
        if ($customizer) {
            $customizer->registerScripts('highlight_load_customizer_controls', 80);
        }
        
    });
    
});

function highlight_get_demo_import_slugs($query = array())
{
    $query = array_merge($query,
        array(
            'theme'    => implode(",", array(get_template(), get_stylesheet())),
            'pro-slug' => implode(',', array('mesmerize-pro', 'mesmerize-full-screen-pro')),
        )
    );
    
    return apply_filters('highlight_demos_import_query', $query);
}

add_filter('extendthemes_demos_import_url', function ($url, $base, $query) {
    
    $query = highlight_get_demo_import_slugs($query);
    
    $query_string = build_query($query);
    
    if ($query_string) {
        $query_string = "?" . $query_string;
    }
    
    $url = $base . $query_string;
    
    return $url;
}, 10, 3);

add_filter('extendthemes_ocdi_customizer_data', function ($data) {
    
    $templates = array('mesmerize', 'mesmerize-pro');
    
    if (in_array($data['template'], $templates)) {
        
        $defaults       = mesmerize_theme_defaults();
        $current_preset = mesmerize_current_preset();
        $defaults       = $defaults[$current_preset];
        // If demo is from parent merge with parent defaults.
        $data['mods'] = array_merge($defaults, $data['mods']);
        
    }
    
    return $data;
});


add_action('customize_register', function ($wp_customize) {
    
    /** @var WP_Customize_Manager $wp_customize */
    $wp_customize->remove_setting('blog_posts_per_row');
    $wp_customize->remove_control('blog_posts_per_row');
    $wp_customize->remove_control('blog_post_highlight_enabled');
    $wp_customize->remove_control('blog_post_highlight_enabled');
    
//    $wp_customize->remove_section('extendthemes_start_from_demo_site');
    $wp_customize->remove_section('mesmerize_woocommerce_panel');
    
}, 100);

add_filter('theme_mod_blog_posts_per_row', function () {
    
    return mesmerize_mod_default('blog_posts_per_row');
});

add_filter('theme_mod_blog_post_highlight_enabled', function () {
    
    return mesmerize_mod_default('false');
});

add_filter('cloudpress\companion\theme_name', function ($name) {
    
    $theme = wp_get_theme();
    $name  = $theme->get('Name');
    
    return $name;
});


add_filter('cloudpress\companion\ajax_cp_data', function ($data, $companion, $filter) {
    
    if ($filter !== "sections") {
        return $data;
    }
    
    /** @var \Mesmerize\Companion $companion */
    $child_sections = $companion->loadPHPConfig(highlight_get_stylesheet_directory() . "/customizer/sections/sections.php");
    
    foreach ($child_sections as $id => $child_section) {
        $child_sections[$id]['content'] = highlight_replace_theme_tag($child_section['content']);
        $child_sections[$id]['thumb']   = highlight_replace_theme_tag($child_section['thumb']);
        $child_sections[$id]['preview'] = highlight_replace_theme_tag($child_section['preview']);
    }
    
    if (apply_filters('mesmerize_show_info_pro_messages', true)) {
        $proSectionsChild = $companion->loadPHPConfig(highlight_get_stylesheet_directory() . "/customizer/sections/pro-only-sections-child.php");
        $child_sections   = array_merge($child_sections, $proSectionsChild);
        
    }
    
    
    foreach ($child_sections as $child_id => $child_section) {
        
        if (array_key_exists('replace', $child_section) && $child_section['replace']) {
            
            foreach ($data['sections'] as $parent_id => $parent_section) {
                
                if ($parent_section['id'] === $child_section['replace']) {
                    
                    $data['sections'][$parent_id] = $child_section;
                    unset($child_sections[$child_id]);
                    break;
                    
                }
            }
            
        }
        
    }
    
    $data['sections'] = array_merge($data['sections'], (array)$child_sections);
    
    return $data;
}, 11, 3);

add_filter('mesmerize_available_sections', function ($args, $companion) {
    
    /** @var \Mesmerize\Companion $companion */
    $availableSectionsChild = $companion->loadPHPConfig(highlight_get_stylesheet_directory() . "/customizer/sections/sections.php");
    
    foreach ($availableSectionsChild as $ai => $section) {
        $availableSectionsChild[$ai]['content'] = highlight_replace_theme_tag($section['content']);
    }
    
    return array_merge($args, $availableSectionsChild);
    
}, 10, 2);


add_filter('cloudpress\companion\front_page_content', function ($content) {
    
    $side_nav_sections = highlight_sidenav_default_sections();
    
    foreach ($side_nav_sections as $section) {
        $content = str_replace('data-export-id="' . $section . '"', 'data-export-id="' . $section . '" data-bullets-visibility="true"', $content);
    }
    
    return $content;
    
}, 40);


add_filter('mesmerize_companion_show_popup_screenshot', '__return_false');

add_action('mesmerize_companion_popup_screenshot', function () {
    ?>
    <div class='image-scroll'></div>
    <?php
});
