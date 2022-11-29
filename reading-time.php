<?php

/**
 * @package reading-time
 * Plugin name: Post Reading Time 
 * Description: plugin for calculating and displaying post reading time
 * Version: 1.0
 * Author: Damian W
 * Author URI: https://github.com/dwolyniec
 */

class ReadingTime{
    function __construct()
    {
        add_action('admin_menu', [$this, 'adminPage']);
        add_action('admin_init', [$this, 'settings']);
        
    }

    function settings(){
        add_settings_section('wrt_first_section', null, null, 'reading-time-settings-page');
        
        add_settings_field('wrt_headline', 'Headline text', [$this,'headlineHtml'],'reading-time-settings-page', 'wrt_first_section');
        register_setting('readingtimeplugin', 'wrt_headline', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'Reading Time']);
        
        add_settings_field('wrt_position', 'Position display', [$this,'positionHtml'],'reading-time-settings-page', 'wrt_first_section');
        register_setting('readingtimeplugin', 'wrt_position', ['sanitize_callback' => [$this, 'sanitizePosition'], 'default' => '0']);
    }

    function adminPage(){
        add_options_page(
        'Reading Time Settings',
        'Reading Time', 
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
        <div class="wrap">
            <h1>Reading Time Settings</h1>
            <form action="options.php" method="POST">
                <?php
                    do_settings_sections('reading-time-settings-page');
                    settings_fields('readingtimeplugin');
                    submit_button();
                ?>
            </form>
        </div>

    <?php
    }

    function positionHtml(){ ?>
        <select name="wrt_position">
            <option <?=selected(get_option('wrt_position', '0')) ?> value="0">Beginning</option>
            <option <?=selected(get_option('wrt_position', '1')) ?> value="1">End</option>
        </select>
    <?php
    }

    function headlineHtml(){ ?>
        <input type="text" name="wrt_headline" value=" <?= esc_attr(get_option('wrt_headline'))?> "> 
    <?php
    }
}

$readingTime = new ReadingTime();

//var_dump(wp_remote_get('https://api.exchangerate.host/latest'));

