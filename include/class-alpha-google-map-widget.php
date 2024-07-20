<?php

namespace Elementor_Alpha_Google_Map_Addon;

if (!defined('ABSPATH')) {
    exit; // If this file is called directly, abort.
}

/**
 * Alpha Google Map Widget.
 *
 *  */

// Elementor Classes.
use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Settings;
use Elementor\Core\Schemes\Color;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;


/**
 * Class Alpha_Google_Map_Widget
 *
 * @package Elementor
 */
class Alpha_Google_Map_Widget extends Widget_Base
{
    /**
     * Id of the widget.
     *
     * @return string
     */
    public function get_name()
    {
        return 'alpha-google-map';
    }

    /**
     * Widget title.
     *
     * @return string|void
     */
    public function get_title()
    {
        return __('Alpha Google Map', 'alpha-google-map-for-elementor');
    }

    /**
     * Widget Icon.
     *
     * @return string
     */
    public function get_icon()
    {
        return 'eicon-google-maps';
    }

    /**
     * Widget keywords.
     *
     * @return array
     */
    public function get_keywords()
    {
        return array('google', 'marker', 'pin');
    }

    /**
     * Register widget controls.
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'section_header',
            array(
                'label' => __('Map Window Location', 'alpha-google-map-for-elementor'),
            )
        );

        $api_key = get_option('elementor_google_maps_api_key');
        if (!$api_key) {
            $this->add_control(
                'api_key_notification',
                [
                    'type' => Controls_Manager::RAW_HTML,
                    'raw' => sprintf(
                        /* translators: 1: Integration settings link open tag, 2: Create API key link open tag, 3: Link close tag. */
                        esc_html__('Set your Google Maps API Key in Elementor\'s %1$sIntegrations Settings%3$s page. Create your key %2$shere.%3$s', 'alpha-google-map-for-elementor'),
                        '<a href="' . Settings::get_url() . '#tab-integrations" target="_blank">',
                        '<a href="https://developers.google.com/maps/documentation/embed/get-api-key" target="_blank">',
                        '</a>'
                    ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );
        }

        $this->add_control(
            'check_demo',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => sprintf(
                    /* translators: 1: Demo link open tag, 2: Link close tag. */
                    esc_html__('Check this widget demo %1$shere%2$s.', 'alpha-google-map-for-elementor'),
                    '<a href="https://alphatrio.net/alpha-google-map-for-elementor/" target="_blank">',
                    '</a>'
                ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'alpha_location_lat',
            array(
                'label'       => __('Location Latitude', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => array('active' => true),
                'description' => __('Enter your location latitude', 'alpha-google-map-for-elementor'),
                'default'     => '51.501156639895136',
                'label_block' => true,
            )
        );

        $this->add_control(
            'alpha_location_long',
            array(
                'label'       => __('Location Longitude', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => array('active' => true),
                'description' => __('Enter your location longitude', 'alpha-google-map-for-elementor'),
                'default'     => '-0.12479706299020504',
                'label_block' => true,
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'alpha_map_pins_settings',
            array(
                'label' => __('Markers', 'alpha-google-map-for-elementor'),
            )
        );

        $this->add_control(
            'alpha_markers_width',
            array(
                'label' => __('Max Width', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::NUMBER,
                'title' => __('Set the Maximum width for markers description box', 'alpha-google-map-for-elementor'),
            )
        );

        $repeater   = new REPEATER();
        $upload_dir = wp_upload_dir();
        $repeater->add_control(
            'pin_icon',
            array(
                'label'   => __('Custom Icon', 'alpha-google-map-for-elementor'),
                'type'    => Controls_Manager::MEDIA,
                'default' => array(
                    'url' => $upload_dir['baseurl'] . '/alpha-map/alpha-pin.png',
                ),
                'dynamic' => array('active' => true),
            )
        );

        $repeater->add_control(
            'pin_active_icon',
            array(
                'label'       => __('Icon On Active Pin', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::MEDIA,
                'default'     => array(
                    'url' => $upload_dir['baseurl'] . '/alpha-map/alpha-pin-hover.png',
                ),
                'dynamic'     => array('active' => true),
                'label_block' => true,
            )
        );

        $repeater->add_control(
            'pin_icon_size',
            array(
                'label'      => __('Size', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em'),
                'range'      => array(
                    'px' => array(
                        'min' => 1,
                        'max' => 200,
                    ),
                    'em' => array(
                        'min' => 1,
                        'max' => 20,
                    ),
                ),
            )
        );

        $repeater->add_control(
            'map_latitude',
            array(
                'label'       => __('Pin Latitude', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => array('active' => true),
                'label_block' => true,
            )
        );

        $repeater->add_control(
            'map_longitude',
            array(
                'label'       => __('Pin Longitude', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => array('active' => true),
                'label_block' => true,
            )
        );

        $repeater->add_control(
            'pin_title',
            array(
                'label'       => __('Title', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => array('active' => true),
                'label_block' => true,
            )
        );

        $repeater->add_control(
            'pin_desc',
            array(
                'label'       => __('Description', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::WYSIWYG,
                'dynamic'     => array('active' => true),
                'label_block' => true,
            )
        );

        $repeater->add_control(
            'pin_time_desc',
            array(
                'label'       => __('Time Table', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::WYSIWYG,
                'dynamic'     => array('active' => true),
                'label_block' => true,
            )
        );

        $repeater->add_control(
            'pin_desc_gallery',
            array(
                'label'       => __('Pin Gallery', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::GALLERY,
                'dynamic'     => array('active' => true),
                'label_block' => true,
            )
        );

        $this->add_control(
            'alpha_map_pins',
            array(
                'label'       => __('Map Pins', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::REPEATER,
                'default'     => array(
                    'map_latitude'  => '51.501156639895136',
                    'map_longitude' => '-0.12479706299020504',
                    'pin_title'     => __('Alpha Google Maps', 'alpha-google-map-for-elementor'),
                    'pin_desc'      => __('Add an optional description to your map pin', 'alpha-google-map-for-elementor'),
                    'pin_time_desc' => __('Add a time table for the location pin', 'alpha-google-map-for-elementor'),
                ),
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ pin_title }}}',
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'alpha_map_controls_section',
            array(
                'label' => __('Controls', 'alpha-google-map-for-elementor'),
            )
        );

        $this->add_control(
            'alpha_map_type',
            array(
                'label'   => __('Map Type', 'alpha-google-map-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'roadmap'   => __('Road Map', 'alpha-google-map-for-elementor'),
                    'satellite' => __('Satellite', 'alpha-google-map-for-elementor'),
                    'terrain'   => __('Terrain', 'alpha-google-map-for-elementor'),
                    'hybrid'    => __('Hybrid', 'alpha-google-map-for-elementor'),
                ),
                'default' => 'roadmap',
            )
        );

        $this->add_responsive_control(
            'alpha_map_height',
            array(
                'label'     => __('Height', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::SLIDER,
                'default'   => array(
                    'size' => 500,
                ),
                'range'     => array(
                    'px' => array(
                        'min' => 80,
                        'max' => 1400,
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .alpha_map_height' => 'height: {{SIZE}}px;',
                ),
            )
        );

        $this->add_control(
            'alpha_map_zoom',
            array(
                'label'   => __('Zoom', 'alpha-google-map-for-elementor'),
                'type'    => Controls_Manager::SLIDER,
                'default' => array(
                    'size' => 12,
                ),
                'range'   => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 22,
                    ),
                ),
            )
        );

        $this->add_control(
            'disable_drag',
            array(
                'label' => __('Disable Map Drag', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::SWITCHER,
            )
        );

        $this->add_control(
            'alpha_map_option_map_type_control',
            array(
                'label' => __('Map Type Controls', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::SWITCHER,
            )
        );

        $this->add_control(
            'alpha_map_option_zoom_controls',
            array(
                'label' => __('Zoom Controls', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::SWITCHER,
            )
        );

        $this->add_control(
            'alpha_map_option_streeview',
            array(
                'label' => __('Street View Control', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::SWITCHER,
            )
        );

        $this->add_control(
            'alpha_map_option_fullscreen_control',
            array(
                'label' => __('Fullscreen Control', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::SWITCHER,
            )
        );

        $this->add_control(
            'alpha_map_option_mapscroll',
            array(
                'label' => __('Scroll Wheel Zoom', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::SWITCHER,
            )
        );

        $this->add_control(
            'alpha_map_marker_open',
            array(
                'label' => __('Info Container Always Opened', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::SWITCHER,
            )
        );

        $this->add_control(
            'alpha_map_marker_hover_open',
            array(
                'label' => __('Info Container Opened when Hovered', 'alpha-google-map-for-elementor'),
                'type'  => Controls_Manager::SWITCHER,
            )
        );

        $this->add_control(
            'alpha_map_marker_mouse_out',
            array(
                'label'     => __('Info Container Closed when Mouse Out', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::SWITCHER,
                'condition' => array(
                    'alpha_map_marker_hover_open' => 'yes',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'alpha_pin_title_style',
            array(
                'label' => __('Title', 'alpha-google-map-for-elementor'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'alpha_pin_title_color',
            array(
                'label'     => __('Color', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::COLOR,
                'scheme'    => array(
                    'type'  => Color::get_type(),
                    'value' => Color::COLOR_1,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .alpha-map-info-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'pin_title_typography',
                'scheme'   => Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .alpha-map-info-title',
            )
        );

        $this->add_responsive_control(
            'alpha_pin_title_margin',
            array(
                'label'      => __('Margin', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-info-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'alpha_pin_title_padding',
            array(
                'label'      => __('Padding', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-info-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'alpha_pin_title_align',
            array(
                'label'     => __('Alignment', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'   => array(
                        'title' => __('Left', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-left',
                    ),
                    'center' => array(
                        'title' => __('Center', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-center',
                    ),
                    'right'  => array(
                        'title' => __('Right', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-right',
                    ),
                ),
                'default'   => 'center',
                'selectors' => array(
                    '{{WRAPPER}} .alpha-map-info-title' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'alpha_pin_text_style',
            array(
                'label' => __('Description', 'alpha-google-map-for-elementor'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'alpha_pin_text_color',
            array(
                'label'     => __('Color', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::COLOR,
                'scheme'    => array(
                    'type'  => Color::get_type(),
                    'value' => Color::COLOR_2,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .alpha-map-info-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'pin_text_typo',
                'scheme'   => Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .alpha-map-info-desc',
            )
        );

        $this->add_responsive_control(
            'alpha_pin_text_margin',
            array(
                'label'      => __('Margin', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-info-desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'alpha_pin_text_padding',
            array(
                'label'      => __('Padding', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-info-desc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'alpha_pin_description_align',
            array(
                'label'     => __('Alignment', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'   => array(
                        'title' => __('Left', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-left',
                    ),
                    'center' => array(
                        'title' => __('Center', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-center',
                    ),
                    'right'  => array(
                        'title' => __('Right', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-right',
                    ),
                ),
                'default'   => 'center',
                'selectors' => array(
                    '{{WRAPPER}} .alpha-map-info-desc' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'alpha_pin_time_style',
            array(
                'label' => __('Time Table', 'alpha-google-map-for-elementor'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'alpha_pin_time_color',
            array(
                'label'     => __('Color', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::COLOR,
                'scheme'    => array(
                    'type'  => Color::get_type(),
                    'value' => Color::COLOR_2,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .alpha-map-info-time-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'pin_time_typo',
                'scheme'   => Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .alpha-map-info-time-desc',
            )
        );

        $this->add_responsive_control(
            'alpha_pin_time_margin',
            array(
                'label'      => __('Margin', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-info-time-desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'alpha_pin_time_padding',
            array(
                'label'      => __('Padding', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-info-time-desc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'alpha_pin_time_align',
            array(
                'label'     => __('Alignment', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'   => array(
                        'title' => __('Left', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-left',
                    ),
                    'center' => array(
                        'title' => __('Center', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-center',
                    ),
                    'right'  => array(
                        'title' => __('Right', 'alpha-google-map-for-elementor'),
                        'icon'  => 'fa fa-align-right',
                    ),
                ),
                'default'   => 'center',
                'selectors' => array(
                    '{{WRAPPER}} .alpha-map-info-time-desc' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'alpha_box_style',
            array(
                'label' => __('Map', 'alpha-google-map-for-elementor'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'map_border',
                'selector' => '{{WRAPPER}} .alpha-map-container',
            )
        );

        $this->add_control(
            'alpha_box_radius',
            array(
                'label'      => __('Border Radius', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', '%', 'em'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-container,{{WRAPPER}} .alpha_map_height' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'label'    => __('Shadow', 'alpha-google-map-for-elementor'),
                'name'     => 'alpha_map_box_shadow',
                'selector' => '{{WRAPPER}} .alpha-map-container',
            )
        );

        $this->add_responsive_control(
            'alpha_box_margin',
            array(
                'label'      => __('Margin', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ),
            )
        );

        $this->add_responsive_control(
            'alpha_box_padding',
            array(
                'label'      => __('Padding', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ),
            )
        );

        $this->end_controls_section();

        // Map Title Controls.
        $this->start_controls_section(
            'section_title',
            array(
                'label' => __('Map Title', 'alpha-google-map-for-elementor'),
            )
        );

        $this->add_control(
            'title',
            array(
                'label'       => __('Map Title', 'alpha-google-map-for-elementor'),
                'type'        => Controls_Manager::TEXTAREA,
                'dynamic'     => array(
                    'active' => true,
                ),
                'placeholder' => __('Enter your title', 'alpha-google-map-for-elementor'),
                'default'     => __('Add Your Title Text Here', 'alpha-google-map-for-elementor'),
            )
        );

        $this->add_control(
            'link',
            array(
                'label'     => __('Link', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::URL,
                'dynamic'   => array(
                    'active' => true,
                ),
                'default'   => array(
                    'url' => '',
                ),
                'separator' => 'before',
            )
        );

        $this->add_control(
            'size',
            array(
                'label'   => __('Size', 'alpha-google-map-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => array(
                    'default' => __('Default', 'alpha-google-map-for-elementor'),
                    'small'   => __('Small', 'alpha-google-map-for-elementor'),
                    'medium'  => __('Medium', 'alpha-google-map-for-elementor'),
                    'large'   => __('Large', 'alpha-google-map-for-elementor'),
                    'xl'      => __('XL', 'alpha-google-map-for-elementor'),
                    'xxl'     => __('XXL', 'alpha-google-map-for-elementor'),
                ),
            )
        );

        $this->add_control(
            'header_size',
            array(
                'label'   => __('HTML Tag', 'alpha-google-map-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'h1'   => 'H1',
                    'h2'   => 'H2',
                    'h3'   => 'H3',
                    'h4'   => 'H4',
                    'h5'   => 'H5',
                    'h6'   => 'H6',
                    'div'  => 'div',
                    'span' => 'span',
                    'p'    => 'p',
                ),
                'default' => 'h2',
            )
        );

        $this->add_responsive_control(
            'align',
            array(
                'label'     => __('Alignment', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'    => array(
                        'title' => __('Left', 'alpha-google-map-for-elementor'),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center'  => array(
                        'title' => __('Center', 'alpha-google-map-for-elementor'),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'   => array(
                        'title' => __('Right', 'alpha-google-map-for-elementor'),
                        'icon'  => 'eicon-text-align-right',
                    ),
                    'justify' => array(
                        'title' => __('Justified', 'alpha-google-map-for-elementor'),
                        'icon'  => 'eicon-text-align-justify',
                    ),
                ),
                'default'   => '',
                'selectors' => array(
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'view',
            array(
                'label'   => __('View', 'alpha-google-map-for-elementor'),
                'type'    => Controls_Manager::HIDDEN,
                'default' => 'traditional',
            )
        );

        $this->end_controls_section();

        // Map Title Style.
        $this->start_controls_section(
            'section_title_style',
            array(
                'label' => __('Map Title', 'alpha-google-map-for-elementor'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => __('Text Color', 'alpha-google-map-for-elementor'),
                'type'      => Controls_Manager::COLOR,
                'global'    => array(
                    'default' => Global_Colors::COLOR_PRIMARY,
                ),
                'selectors' => array(
                    '{{WRAPPER}}  .alpha-map-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'typography',
                'global'   => array(
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ),
                'selector' => '{{WRAPPER}} .alpha-map-title',
            )
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            array(
                'name'     => 'text_shadow',
                'selector' => '{{WRAPPER}}  .alpha-map-title',
            )
        );

        $this->add_responsive_control(
            'title_margin',
            array(
                'label'      => __('Margin', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'title_padding',
            array(
                'label'      => __('Padding', 'alpha-google-map-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .alpha-map-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Show the count of how many images left in the gallery
     *
     * @param string $link_html  link for an image.
     * @param string $id     the id of an image.
     *
     * @return string|string[]|null
     */
    public function add_lightbox_data_to_image_link($link_html, $id)
    {
        $settings      = $this->get_settings_for_display();
        $open_lightbox = isset($settings['open_lightbox']) ? $settings['open_lightbox'] : null;

        if (Plugin::$instance->editor->is_edit_mode()) {
            $this->add_render_attribute('link', 'class', 'elementor-clickable', true);
        }

        $this->add_lightbox_data_attributes('link', $id, $open_lightbox, $this->get_id(), true);
        return preg_replace('/^<a/', '<a ' . $this->get_render_attribute_string('link'), $link_html);
    }

    /**
     * Render the widget on the frontend.
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $map_pins = $settings['alpha_map_pins'];

        $street_view = 'yes' === $settings['alpha_map_option_streeview'] ? 'true' : 'false';

        $scroll_wheel = 'yes' === $settings['alpha_map_option_mapscroll'] ? 'true' : 'false';

        $full_screen = 'yes' === $settings['alpha_map_option_fullscreen_control'] ? 'true' : 'false';

        $zoom_control = 'yes' === $settings['alpha_map_option_zoom_controls'] ? 'true' : 'false';

        $type_control = 'yes' === $settings['alpha_map_option_map_type_control'] ? 'true' : 'false';

        $automatic_open = 'yes' === $settings['alpha_map_marker_open'] ? 'true' : 'false';

        $hover_open = 'yes' === $settings['alpha_map_marker_hover_open'] ? 'true' : 'false';

        $hover_close = 'yes' === $settings['alpha_map_marker_mouse_out'] ? 'true' : 'false';

        $locationlat = !empty($settings['alpha_location_lat']) ? $settings['alpha_location_lat'] : 18.591212;

        $locationlong = !empty($settings['alpha_location_long']) ? $settings['alpha_location_long'] : 73.741261;

        $marker_width = !empty($settings['alpha_markers_width']) ? $settings['alpha_markers_width'] : 1000;

        $map_settings = array(
            'zoom'              => $settings['alpha_map_zoom']['size'],
            'maptype'           => $settings['alpha_map_type'],
            'streetViewControl' => $street_view,
            'locationlat'       => $locationlat,
            'locationlong'      => $locationlong,
            'scrollwheel'       => $scroll_wheel,
            'fullScreen'        => $full_screen,
            'zoomControl'       => $zoom_control,
            'typeControl'       => $type_control,
            'automaticOpen'     => $automatic_open,
            'hoverOpen'         => $hover_open,
            'hoverClose'        => $hover_close,
            'drag'              => $settings['disable_drag'],
        );

        $this->add_render_attribute(
            'style_wrapper',
            array(
                'class'         => 'alpha_map_height',
                'data-settings' => wp_json_encode($map_settings),
            )
        );

?>
        <div class="alpha-map-container" id="alpha-map-container">
            <div class="alpha-google-map-title">
                <?php
                if ('' !== $settings['title']) {
                    $this->add_render_attribute('title', 'class', 'alpha-map-title');

                    if (!empty($settings['size'])) {
                        $this->add_render_attribute('title', 'class', 'elementor-size-' . $settings['size']);
                    }

                    $this->add_inline_editing_attributes('title');

                    $title = $settings['title'];

                    if (!empty($settings['link']['url'])) {
                        $this->add_link_attributes('url', $settings['link']);

                        $title = sprintf('<a %1$s>%2$s</a>', $this->get_render_attribute_string('url'), $title);
                    }

                    $title_html = sprintf('<%1$s %2$s>%3$s</%1$s>', Utils::validate_html_tag($settings['header_size']), $this->get_render_attribute_string('title'), $title);

                    echo $title_html;
                }
                ?>
            </div>
            <?php if (count($map_pins)) { ?>
                <div <?php echo wp_kses_post($this->get_render_attribute_string('style_wrapper')); ?>>
                    <?php
                    foreach ($map_pins as $index => $pin) {
                        $key = 'map_marker_' . $index;

                        $this->add_render_attribute(
                            $key,
                            array(
                                'class'            => 'alpha-pin',
                                'data-lng'         => $pin['map_longitude'],
                                'data-lat'         => $pin['map_latitude'],
                                'data-icon'        => $pin['pin_icon']['url'],
                                'data-icon-active' => $pin['pin_active_icon']['url'],
                                'data-icon-size'   => $pin['pin_icon_size']['size'],
                                'data-max-width'   => $marker_width,
                                'data-id'          => $index,
                            )
                        );

                        $ids   = wp_list_pluck($pin['pin_desc_gallery'], 'id');
                        $count = count($ids);
                        $this->add_render_attribute('shortcode' . $index, 'ids', implode(',', $ids));

                    ?>
                        <div <?php echo wp_kses_post($this->get_render_attribute_string($key)); ?>>
                            <?php if (!empty($pin['pin_title']) || !empty($pin['pin_desc']) || !empty($pin['pin_time_desc'])) : ?>
                                <div class='alpha-map-info-container'>
                                    <p class='alpha-map-info-title'><?php echo wp_kses_post($pin['pin_title']); ?></p>
                                    <div class='alpha-map-info-desc'><?php echo wp_kses_post($pin['pin_desc']); ?></div>
                                    <div class='alpha-map-info-time-desc'><?php echo wp_kses_post($pin['pin_time_desc']); ?></div>
                                    <?php if (!empty($pin['pin_desc_gallery'])) : ?>
                                        <div class="alpha-image-gallery" <?php echo 'data-count=' . abs($count - 4); ?>>
                                            <?php
                                            add_filter('wp_get_attachment_link', array($this, 'add_lightbox_data_to_image_link'), 10, 2);
                                            echo do_shortcode('[gallery link="file"  ' . $this->get_render_attribute_string('shortcode' . $index) . ']');
                                            remove_filter('wp_get_attachment_link', array($this, 'add_lightbox_data_to_image_link'));
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            <?php
            }
            ?>
        </div>
<?php
    }
}
