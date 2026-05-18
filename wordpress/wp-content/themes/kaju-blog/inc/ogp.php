<?php
/**
 * OGP / Twitter Card（共通画像 ogp.jpg）
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'kaju_blog_output_ogp_meta', 5 );

/**
 * 共通 OGP 画像 URL（絶対パス）
 */
function kaju_blog_ogp_image_uri(): string {
	return kaju_blog_asset_uri( 'ogp.jpg' );
}

/**
 * 現在ページの OGP 用 URL
 */
function kaju_blog_ogp_page_url(): string {
	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	global $wp;

	$request = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : '';

	if ( '' === $request ) {
		return home_url( '/' );
	}

	return home_url( '/' . $request . '/' );
}

/**
 * OGP 用説明文
 */
function kaju_blog_ogp_description(): string {
	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {
			$excerpt = get_the_excerpt( $post );

			if ( is_string( $excerpt ) && '' !== trim( $excerpt ) ) {
				return wp_strip_all_tags( $excerpt );
			}
		}
	}

	$tagline = get_bloginfo( 'description', 'display' );

	if ( is_string( $tagline ) && '' !== trim( $tagline ) ) {
		return wp_strip_all_tags( $tagline );
	}

	return '果樹と暮らす小さな庭の栽培記';
}

/**
 * head に OGP / Twitter Card を出力
 */
function kaju_blog_output_ogp_meta(): void {
	if ( is_admin() ) {
		return;
	}

	$title       = wp_get_document_title();
	$description = kaju_blog_ogp_description();
	$url         = kaju_blog_ogp_page_url();
	$image       = kaju_blog_ogp_image_uri();
	$site_name   = get_bloginfo( 'name', 'display' );
	$type        = is_singular() ? 'article' : 'website';

	$meta = array(
		array( 'property', 'og:site_name', $site_name ),
		array( 'property', 'og:url', $url ),
		array( 'property', 'og:type', $type ),
		array( 'property', 'og:title', $title ),
		array( 'property', 'og:description', $description ),
		array( 'property', 'og:image', $image ),
		array( 'property', 'og:image:width', '1200' ),
		array( 'property', 'og:image:height', '630' ),
		array( 'property', 'og:locale', 'ja_JP' ),
		array( 'name', 'twitter:card', 'summary_large_image' ),
		array( 'name', 'twitter:title', $title ),
		array( 'name', 'twitter:description', $description ),
		array( 'name', 'twitter:image', $image ),
	);

	foreach ( $meta as $item ) {
		printf(
			'<meta %1$s="%2$s" content="%3$s" />' . "\n",
			esc_attr( $item[0] ),
			esc_attr( $item[1] ),
			esc_attr( $item[2] )
		);
	}
}
