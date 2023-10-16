<?php
/**
 * Plugin Name: Typewriter Effect
 * Description: Adds a typewriter effect to elements using a shortcode.
 * Version: 1.0
 * Author: Brad Zacharias
 **/
function typewriter_enqueue_scripts() {
    wp_enqueue_style('typewriter-css', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('typewriter-js', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'typewriter_enqueue_scripts');

function typewriter_shortcode($atts, $content = null) {
    return '<div class="typewriter-text">' . esc_html($content) . '</div>';
}
add_shortcode('typewriter', 'typewriter_shortcode');

