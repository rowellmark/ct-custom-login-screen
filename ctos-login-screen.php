<?php
/**
 * Plugin Name: CTOS Login Screen
 * Description: Replace Login Screen Background, Logo and Additional Login Security.
 * Version: 1.0.0
 * Author: Rowell Blanca
 * Author URI: https://www.codetrajectory.com/
 * License: Free
 */

namespace CTOSLS;

define('AIOS_LOGIN_SCREEN_URL', plugin_dir_url( __FILE__ ));
define('AIOS_LOGIN_SCREEN_DIR', realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR);
define('AIOS_LOGIN_SCREEN_RESOURCES', AIOS_LOGIN_SCREEN_URL . 'resources/');
define('AIOS_LOGIN_SCREEN_VIEWS', AIOS_LOGIN_SCREEN_DIR . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);
define('AIOS_LOGIN_SCREEN_NAME', 'CTOS Login Screen');
define('AIOS_LOGIN_SCREEN_SLUG', 'ctos-login-screen');

require 'FileLoader.php';

$fileLoader = new FileLoader();

// Load Core
$fileLoader->load_files(['app/App']);
new App\App(__FILE__);

// Load Files
$fileLoader->load_directory('helpers');
$fileLoader->load_directory('config');
$fileLoader->load_directory('app/controllers');
