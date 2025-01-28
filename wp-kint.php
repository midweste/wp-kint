<?php

/*
 *
 * @link              https://github.com/midweste
 * @since             1.0.0
 * @package           Wordpress Kint
 *
 * @wordpress-plugin
 * Plugin Name:       Wordpress Kint
 * Plugin URI:        https://github.com/midweste/wp-kint/
 * Description:       Show kint output for posts/pages/products etc
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Midweste
 * Author URI:        https://github.com/midweste/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://api.github.com/repos/midweste/wp-kint/commits/main
 * License:           MIT
 * Text Domain:       wp-kint
 * Domain Path:       /languages
 */
is_file(__DIR__ . '/vendor/autoload.php') && require_once __DIR__ . '/vendor/autoload.php';

use WPTrait\Plugin;

class WordpressKint extends Plugin
{

    public $main;

    public function __construct()
    {
        parent::__construct('wp-kint', ['main_file' => __FILE__]);
    }

    public function instantiate()
    {
        $this->main = new \WordpressKint\Main($this->plugin);
    }

    public function register_activation_hook() {}

    public function register_deactivation_hook() {}

    public static function register_uninstall_hook() {}
}

function wp_kint(): WordpressKint
{
    global $wp_kint;
    if (!$wp_kint instanceof WordpressKint) {
        $GLOBALS['wp_kint'] = new WordpressKint();
    }
    return $GLOBALS['wp_kint'];
}

call_user_func(function () {
    if (is_admin()) {
        wp_kint();
    }
});
