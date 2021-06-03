<?php


function highlight_side_navigation_preset()
{
    $preset = mesmerize_mod_default('side_navigation_design_preset');
    
    return apply_filters('side_navigation_design_preset', $preset);
    
}

function highlight_create_side_navigation()
{
    
    if ( ! apply_filters('mesmerize_is_companion_installed', false)) {
        return;
    }
    
    if ( ! Mesmerize\Companion::instance()->isMaintainable()) {
        return;
    }
    
    $show_side_nav = get_theme_mod('enable_side_navigation', true);
    
    if ($show_side_nav) {
        $show_hero_bullet     = get_theme_mod('show_hero_bullet_on_navigation', true);
        $hero_bullet_label    = get_theme_mod('hero_bullet_label', __('Top', 'highlight'));
        $side_nav_labels_type = get_theme_mod('side_navigation_visible_labels', 'active');
        $class                = '';
        if ( ! $show_hero_bullet) {
            $class = 'hidden-bullet';
        }
        $showAfterScroll = get_theme_mod('show_side_navigation_after_scroll', true);
        if ($showAfterScroll) {
            $afterScrollData = 'true';
        } else {
            $afterScrollData = 'false';
        }
        
        ?>

        <div id="side-navigation" class="side-navigation">
            
            <?php if (mesmerize_is_customize_preview()): ?>
                <style>
                    #side-navigation ul[data-preset] li a {
                        pointer-events: none;
                    }
                </style>
            <?php endif; ?>

            <ul data-type="<?php echo esc_attr($side_nav_labels_type); ?>" data-preset="<?php echo highlight_side_navigation_preset(); ?>" data-after-scroll="<?php echo $afterScrollData; ?>">
                <li class="hero-bullet <?php echo $class; ?>">
                    <a href="#page-top"><?php echo esc_html($hero_bullet_label); ?></a>
                </li>
            </ul>
        </div>
        
        <?php
    }
    
}

add_action('wp_head', 'highlight_toggle_main_navigation');


function highlight_toggle_main_navigation()
{
    
    $prefix   = mesmerize_is_inner() ? 'inner_header' : 'header';
    $selector = mesmerize_is_inner() ? '.mesmerize-inner-page' : '.mesmerize-front-page';
    
    $mainNavHidden = get_theme_mod($prefix . '_nav_enabled', false);
    
    if ($mainNavHidden) {
        ?>
        <style data-name="main_navigation_toggle_style">
            <?php echo $selector; ?>
            #mainmenu_container {
                display: none;
            }
        </style>
        <?php
    }
    
}


function highlight_print_blog_list_attrs($atts)
{
    
    $atts['data-no-masonry'] = true;
    
    return $atts;
}

add_filter('mesmerize_print_blog_list_attrs', 'highlight_print_blog_list_attrs');
