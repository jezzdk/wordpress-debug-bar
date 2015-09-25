<?php
/**
 * Plugin Name: Wordpress Debug Bar
 * Version: 0.1
 * Plugin URI:
 * Description: Utilizing the PHP Debug Bar framework, this plugin enables extended debug info for your theme.
 * Author: Jess Johannessen
 * Author URI: https://jezz.dk
 * Text Domain: wp-debug-bar
 * Domain Path: /languages/
 * License: GPL v3
 */

require_once 'vendor/autoload.php';
require_once 'collectors/WPActionsCollector.php';
require_once 'collectors/WPFiltersCollector.php';

use DebugBar\StandardDebugBar;

class Wordpress_Debug_Bar {

    protected static $debugbar;

    public function __construct() {
        self::$debugbar = new StandardDebugBar();
        self::$debugbar->addCollector(new WPActionsCollector());
        self::$debugbar->addCollector(new WPFiltersCollector());

        if ( defined('DOING_AJAX') && DOING_AJAX ) {
            add_action( 'admin_init', array( &$this, 'init_ajax' ) );
        }

        add_action( 'init', array( &$this, 'init' ) );
    }

    public function init() {
        if ( ! is_super_admin() || $this->is_wp_login() ) {
            return;
        }

        add_action( 'wp_footer', array( &$this, 'render' ), 1000 );
        add_action( 'wp_head', array( &$this, 'header' ), 1 );
    }

    public function init_ajax() {
        if ( ! is_super_admin() )
            return;

        self::$debugbar->sendDataInHeaders();
    }

    public function render() {
        $debugbarRenderer = self::$debugbar->getJavascriptRenderer();
        echo $debugbarRenderer->render();
    }

    public function header() {
        $path = plugin_dir_path( __FILE__ );
        $url = plugins_url( '',  __FILE__ );

        $debugbarRenderer = self::$debugbar->getJavascriptRenderer(
            $url . '/vendor/maximebf/debugbar/src/DebugBar/Resources/',
            $path . '/vendor/maximebf/debugbar/src/DebugBar/Resources/'
        );

        echo $debugbarRenderer->renderHead();
    }

    protected function is_wp_login() {
        return 'wp-login.php' == basename( $_SERVER['SCRIPT_NAME'] );
    }

    public function __call($name, $args) {
        if ($name == 'startMeasure') {
            self::$debugbar['time']->startMeasure($args[0], $args[1]);
        }
        elseif ($name == 'stopMeasure') {
            self::$debugbar['time']->stopMeasure($args[0]);
        }
        elseif ($name == 'addException') {
            self::$debugbar['exceptions']->addException($args[0]);
        }
        elseif ($name == 'info' || $name == 'debug') {
            self::$debugbar['messages']->info($args[0]);
        }
    }
}

$GLOBALS['wp_debug_bar'] = new Wordpress_Debug_Bar();
