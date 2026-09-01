<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Kadad_CMS_API {
	private Kadad_CMS_License_Manager $license;
	public function __construct( Kadad_CMS_License_Manager $license ) { $this->license = $license; add_action( 'rest_api_init', array( $this, 'routes' ) ); }
	public function routes(): void { register_rest_route( 'cms/v1', '/subscription', array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'subscription' ), 'permission_callback' => static fn() => current_user_can( 'manage_options' ) ) ); }
	public function subscription(): WP_REST_Response { $r = $this->license->remaining(); return new WP_REST_Response( array( 'active' => $this->license->is_active(), 'status' => $this->license->get_status(), 'remaining' => array_intersect_key( $r, array_flip( array( 'days', 'hours', 'minutes', 'seconds' ) ) ) ) ); }
}
