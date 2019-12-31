<?php
/**
 * Plugin Name: Mini APPI
 */

require_once 'src/MiniAppi.php';

$plugin = new MiniAppiPlugin();
register_activation_hook( __FILE__, array($plugin, 'activation') );
register_deactivation_hook( __FILE__, array($plugin, 'deactivation') );
$plugin->init();
?>
