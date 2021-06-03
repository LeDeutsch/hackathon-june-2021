<?php

function highlight_theme_defaults($old_defaults = array())
{
    
    $gradients = mesmerize_get_parsed_gradients();
    
    $defaults = array(
        
        'header_nav_sticked'       => false,
        'inner_header_nav_sticked' => true,
        
        'header_nav_transparent'       => true,
        'inner_header_nav_transparent' => false,
        
        'header_nav_border'       => false,
        'inner_header_nav_border' => false,
        
        'header_nav_border_thickness'       => 2,
        'inner_header_nav_border_thickness' => 2,
        
        'header_nav_border_color'       => "rgba(255, 255, 255, 1)",
        'inner_header_nav_border_color' => "rgba(255, 255, 255, 1)",
        
        'header_text_box_text_width' => 80,
        "header_text_box_text_align" => "center",
        "header_content_partial"     => "content-on-center",
        
        "header_spacing"        => array(
            "top"    => "20%",
            "bottom" => "20%",
        ),
        "header_spacing_mobile" => array(
            "top"    => "15%",
            "bottom" => "15%",
        ),
        "inner_header_spacing"  => array(
            "top"    => "8%",
            "bottom" => "8%",
        ),
        
        "enable_top_bar" => false,
        
        "header_background_type"       => 'slideshow',
        "inner_header_background_type" => 'image',
        
        "header_slideshow"       => array(
            array("url" => highlight_get_stylesheet_directory_uri() . "/assets/images/hero-1.jpg"),
            array("url" => highlight_get_stylesheet_directory_uri() . "/assets/images/hero-2.jpg"),
            array("url" => highlight_get_stylesheet_directory_uri() . "/assets/images/hero-3.jpg"),
            array("url" => highlight_get_stylesheet_directory_uri() . "/assets/images/hero-4.jpg"),
        ),
        "inner_header_slideshow" => array(
            array("url" => highlight_get_stylesheet_directory_uri() . "/assets/images/hero-1.jpg"),
            array("url" => highlight_get_stylesheet_directory_uri() . "/assets/images/hero-2.jpg"),
        ),
        
        "header_front_page_image"       => highlight_get_stylesheet_directory_uri() . "/assets/images/hero-1.jpg",
        "inner_header_front_page_image" => highlight_get_stylesheet_directory_uri() . "/assets/images/hero-inner.jpg",
        
        "header_overlay_type"       => 'color',
        "inner_header_overlay_type" => 'gradient',
        
        'header_overlay_color'       => "#000000",
        'inner_header_overlay_color' => "#000000",
        
        'header_overlay_gradient_colors'       => $gradients['red_salvation'],
        'inner_header_overlay_gradient_colors' => $gradients['plum_plate'],
        
        'header_overlay_opacity'       => 0.7,
        'inner_header_overlay_opacity' => 0.5,
        
        'header_overlay_shape'       => "none",
        'inner_header_overlay_shape' => "none",
        
        'header_show_separator'       => false,
        'inner_header_show_separator' => false,
        
        'header_separator'       => 'mesmerize/1.wave-and-line',
        'inner_header_separator' => 'mesmerize/6.triple-waves-2',
        
        'header_separator_color'       => '#ffffff',
        'inner_header_separator_color' => '#ffffff',
        
        'header_separator_height'       => 154,
        'inner_header_separator_height' => 140,
        
        'header_show_bottom_arrow' => true,
        
        'full_height_header' => true,
        'header_overlap'     => false,
        
        'blog_posts_per_row'            => 1,
        "side_navigation_design_preset" => "preset-2",
        
        "header_slideshow_duration"       => "3000",
        "inner_header_slideshow_duration" => "3000",
    );
    
    
    return $defaults;
}


add_filter('mesmerize_theme_defaults', 'highlight_theme_defaults');
