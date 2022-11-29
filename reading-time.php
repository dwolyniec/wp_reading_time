<?php

/**
 * @package reading-time
 * Plugin name: Post Reading Time 
 * Description: plugin for calculating and displaying post reading time
 * Version: 1.0
 * Author: Damian W
 * Author URI: https://github.com/dwolyniec
 * Text Domain: wrtdomain
 * Domain Path: /languages
 * License: GPL2
 */

class ReadingTime{
    function __construct()
    {
        add_action('admin_menu', [$this, 'adminPage']);
        add_action('admin_init', [$this, 'settings']);
        add_filter('the_content', [$this, 'checkFilterConditions']);
        add_action('init', [$this, 'loadLang']);
    }

    function loadLang(){
        load_plugin_textdomain('wrtdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function checkFilterConditions($content){
        if(is_main_query() && is_single()) {
            return $this->createTimeHtml($content);
        }
        return $content;
    }

    function createTimeHtml($content){
        $time_html = '<p><b>' . esc_html(get_option('wrt_headline')) . '</b></p>';
        $reading_time_in_minutes = ceil(str_word_count(strip_tags($content))/240); //assuming reading average 4 words per second
        $time_html .= '<p style="margin-top:0">' . __('Reading Time', 'wrtdomain') . ': ' . $reading_time_in_minutes . ($reading_time_in_minutes > 1 ? __('minutes', 'wrtdomain') : __('minute', 'wrtdomain')) . '</p>';

        if(get_option('wrt_position', '0')){
            return $content . $time_html;
        }
        return $time_html . $content;
    }

    function settings(){
        add_settings_section('wrt_first_section', null, null, 'reading-time-settings-page');
        
        add_settings_field('wrt_headline', __('Headline text', 'wrtdomain'), [$this,'headlineHtml'],'reading-time-settings-page', 'wrt_first_section');
        register_setting('readingtimeplugin', 'wrt_headline', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'Reading Time']);
        
        add_settings_field('wrt_position', __('Position display', 'wrtdomain'), [$this,'positionHtml'],'reading-time-settings-page', 'wrt_first_section');
        register_setting('readingtimeplugin', 'wrt_position', ['sanitize_callback' => [$this, 'sanitizePosition'], 'default' => '0']);
    }

    function adminPage(){
        add_options_page(
        'Reading Time Settings',
        __('Reading Time', 'wrtdomain'), 
        'manage_options', 
        'reading-time-settings-page', 
        [$this, 'settingsHtml']
        );
    }

    function sanitizePosition($input){
        if($input != '0' && $input != '1'){
            add_settings_error('wrt_position', 'wrt_position_error', "Reading time position must be set to beginning or end of the post");
            return get_option('wrt_position');
        }
        return $input;
    }

    function settingsHtml(){ ?>
        <h1><?=__('Reading Time Settings', 'wrtdomain')?></h1>
        <form action="options.php" method="POST">
            <?php
                do_settings_sections('reading-time-settings-page');
                settings_fields('readingtimeplugin');
                submit_button();
            ?>
        </form>
    <?php
    }

    function positionHtml(){ ?>
        <select name="wrt_position">
            <option <?=selected(get_option('wrt_position', '0')) ?> value="0"><?=__('Beginning', 'wrtdomain')?></option>
            <option <?=selected(get_option('wrt_position', '1')) ?> value="1"><?=__('End', 'wrtdomain')?></option>
        </select>
    <?php
    }

    function headlineHtml(){ ?>
        <input type="text" name="wrt_headline" value="<?= esc_attr(get_option('wrt_headline')) ?> "> 
    <?php
    }
}

$readingTime = new ReadingTime();

