<?php
/** Kadad CMS subscription service. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
define( 'KADAD_CMS_VERSION', '1.0.0' );
define( 'KADAD_CMS_DIR', __DIR__ );
define( 'KADAD_CMS_URL', WPMU_PLUGIN_URL . '/cms-core' );
require_once KADAD_CMS_DIR . '/includes/class-license-client.php';
require_once KADAD_CMS_DIR . '/includes/class-license-manager.php';
require_once KADAD_CMS_DIR . '/includes/class-admin.php';
require_once KADAD_CMS_DIR . '/includes/class-api.php';

function kadad_cms() { static $manager = null; if ( null === $manager ) { $manager = new Kadad_CMS_License_Manager(); } return $manager; }
add_action( 'plugins_loaded', static function () { new Kadad_CMS_Admin( kadad_cms() ); new Kadad_CMS_API( kadad_cms() ); } );
kadad_cms();
