<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class CMS_License_Client {
	private string $api_url;
	public function __construct( string $api_url = '' ) { $this->api_url = untrailingslashit( $api_url ?: 'https://wnat.ir/api' ); }
	public function activate( string $code ): array { return $this->request( $code, false ); }
	public function validate( string $code ): array { return $this->request( $code, false ); }
	public function get_remaining_time( string $code ): array { return $this->request( $code, true ); }
	private function request( string $code, bool $time ): array {
		$url = trailingslashit( $this->api_url ) . rawurlencode( $code );
		if ( $time ) { $url = add_query_arg( 'time', '1', $url ); }
		$response = wp_remote_get( $url, array( 'timeout' => 12, 'redirection' => 2, 'headers' => array( 'Accept' => 'application/json' ) ) );
		if ( is_wp_error( $response ) ) { return array( 'ok' => false, 'error' => 'network', 'message' => $response->get_error_message() ); }
		$status = (int) wp_remote_retrieve_response_code( $response );
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) || ! array_key_exists( 'ok', $decoded ) || ! is_bool( $decoded['ok'] ) ) { return array( 'ok' => false, 'error' => 'malformed' ); }
		if ( 200 > $status || 299 < $status ) { return array( 'ok' => false, 'error' => 'remote', 'status_code' => $status ); }
		$result = array( 'ok' => $decoded['ok'], 'raw' => $decoded );
		if ( $time && $decoded['ok'] ) { $result['remaining'] = $this->parse_remaining( $decoded ); }
		return $result;
	}
	private function parse_remaining( array $data ): array {
		$keys = array( 'days', 'hours', 'minutes', 'seconds' ); $remaining = array();
		foreach ( $keys as $key ) { $remaining[ $key ] = isset( $data[ $key ] ) && is_numeric( $data[ $key ] ) ? max( 0, (int) $data[ $key ] ) : 0; }
		if ( isset( $data['expires_at'] ) && is_scalar( $data['expires_at'] ) ) { $timestamp = strtotime( (string) $data['expires_at'] ); if ( $timestamp ) { $remaining['expires_at'] = $timestamp; } }
		return $remaining;
	}
	public function clear_cache(): void { delete_transient( 'kadad_cms_license_check' ); }
}
