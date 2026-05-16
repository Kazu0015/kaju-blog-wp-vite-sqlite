<?php
/**
 * Docker: Site Health / REST 用ループバック
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param false|array|WP_Error $pre   Response short-circuit.
 * @param array                $args  Request args.
 * @param string               $url   Request URL.
 * @return false|array|WP_Error
 */
function kaju_blog_docker_pre_http_request( $pre, $args, $url ) {
	$bases = array();
	if ( defined( 'WP_HOME' ) && WP_HOME ) {
		$bases[] = rtrim( (string) WP_HOME, '/' );
	}
	if ( defined( 'WP_SITEURL' ) && WP_SITEURL ) {
		$bases[] = rtrim( (string) WP_SITEURL, '/' );
	}
	$bases = array_unique( array_filter( $bases ) );

	$public_base = null;
	foreach ( $bases as $base ) {
		if ( strpos( $url, $base ) === 0 ) {
			$public_base = $base;
			break;
		}
	}
	if ( ! $public_base ) {
		return $pre;
	}

	$parsed = wp_parse_url( $public_base );
	$host   = isset( $parsed['host'] ) ? $parsed['host'] : '';
	if ( ! in_array( $host, array( 'localhost', '127.0.0.1' ), true ) ) {
		return $pre;
	}

	$port = isset( $parsed['port'] ) ? (int) $parsed['port'] : 80;
	if ( 80 === $port ) {
		return $pre;
	}

	$internal     = defined( 'KAJU_BLOG_WP_LOOPBACK_HTTP_BASE' ) ? rtrim( (string) KAJU_BLOG_WP_LOOPBACK_HTTP_BASE, '/' ) : 'http://127.0.0.1';
	$internal_url = $internal . substr( $url, strlen( $public_base ) );

	$args = is_array( $args ) ? $args : array();
	if ( ! isset( $args['headers'] ) || ! is_array( $args['headers'] ) ) {
		$args['headers'] = array();
	}
	$args['headers']['Host'] = isset( $parsed['port'] ) ? $host . ':' . $parsed['port'] : $host;

	return wp_remote_request( $internal_url, $args );
}

add_filter( 'pre_http_request', 'kaju_blog_docker_pre_http_request', 10, 3 );
