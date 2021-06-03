<?php

function highlight_side_navigation_controls($priority, $section)
{
    
    mesmerize_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => esc_html__('Side navigation options', 'highlight'),
        'section'  => $section,
        'settings' => "side_navigation_options_separator",
        'priority' => $priority,
    ));
    
    mesmerize_add_kirki_field(array(
        'type'      => 'checkbox',
        'label'     => esc_html__('Enable side navigation', 'highlight'),
        'section'   => $section,
        'settings'  => 'enable_side_navigation',
        'priority'  => $priority,
        'default'   => true,
        'transport' => 'refresh',
    ));
    
    mesmerize_add_kirki_field(array(
        'type'            => 'checkbox',
        'label'           => esc_html__('Show page hero bullet', 'highlight'),
        'section'         => $section,
        'settings'        => 'show_hero_bullet_on_navigation',
        'priority'        => $priority,
        'default'         => true,
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'enable_side_navigation',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
    
    mesmerize_add_kirki_field(array(
        'type'            => 'checkbox',
        'label'           => esc_html__('Show only after scroll', 'highlight'),
        'section'         => $section,
        'settings'        => 'show_side_navigation_after_scroll',
        'priority'        => $priority,
        'default'         => true,
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'enable_side_navigation',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
    
    mesmerize_add_kirki_field(array(
        'type'            => 'text',
        'label'           => esc_html__('Page hero bullet label', 'highlight'),
        'settings'        => "hero_bullet_label",
        'section'         => $section,
        'priority'        => $priority,
        'transport'       => 'postMessage',
        'default'         => esc_html__('Top', 'highlight'),
        'active_callback' => array(
            array(
                'setting'  => 'enable_side_navigation',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'show_hero_bullet_on_navigation',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
    
    mesmerize_add_kirki_field(array(
        'type'              => 'select',
        'label'             => esc_html__('Visible labels', 'highlight'),
        'section'           => $section,
        'settings'          => 'side_navigation_visible_labels',
        'choices'           => array(
            'active' => __('Active section only', 'highlight'),
            'all'    => __('All sections', 'highlight'),
            'none'   => __('None', 'highlight'),
        ),
        'default'           => 'active',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
        'priority'          => $priority,
        'active_callback'   => array(
            array(
                'setting'  => 'enable_side_navigation',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
    
}

function highlight_side_navigation_toggle_control($priority, $section, $prefix)
{
    
    mesmerize_add_kirki_field(array(
        'type'      => 'checkbox',
        'label'     => esc_html__('Hide main menu', 'highlight'),
        'section'   => $section,
        'priority'  => $priority,
        'settings'  => "{$prefix}_nav_enabled",
        'default'   => false,
        'transport' => 'postMessage',
    ));
    
}

if (apply_filters('mesmerize_is_companion_installed', false)) {
    
    // add side navigation section in customizer
    add_action('mesmerize_add_sections', function ($wp_customize) {
        
        $sections = array(
            'side_navigation' => esc_html__('Side navigation', 'highlight'),
        );
        
        foreach ($sections as $id => $title) {
            $wp_customize->add_section($id, array(
                'title'    => $title,
                'priority' => 2,
                'panel'    => 'navigation_panel',
            ));
        }
        
    });
    
    // add side navigation controls in customizer
    add_action('customize_register', function () {
        
        highlight_side_navigation_toggle_control(1, 'inner_page_navigation', 'inner_header');
        highlight_side_navigation_toggle_control(1, 'front_page_navigation', 'header');
        
        
    });
    
}

highlight_side_navigation_controls(1, 'side_navigation');
