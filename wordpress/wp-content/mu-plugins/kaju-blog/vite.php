<?php
/**
 * Vite アセット（開発: モジュール直読 / 本番: manifest）
 *
 * 本番は get_theme_file_* を使い、エントリキー・CSS 抜けを防ぐ。
 * 前提: 有効テーマが assets/.vite/manifest.json を持つこと。
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/**
 * manifest のエントリキー（vite の rollup input と一致させる）
 */
function kaju_blog_vite_entry_key(): string {
	return defined( 'KAJU_BLOG_VITE_ENTRY' ) ? (string) KAJU_BLOG_VITE_ENTRY : 'src/js/main.js';
}

/**
 * @return string dev|prod|auto
 */
function kaju_blog_vite_strategy(): string {
	if ( defined( 'KAJU_BLOG_VITE_STRATEGY' ) && in_array( KAJU_BLOG_VITE_STRATEGY, array( 'dev', 'prod', 'auto' ), true ) ) {
		return KAJU_BLOG_VITE_STRATEGY;
	}
	return 'auto';
}

/**
 * manifest 内パス（outDir 基準）→ テーマルートからの相対パス
 */
function kaju_blog_manifest_theme_rel( string $manifest_path ): string {
	return 'assets/' . ltrim( $manifest_path, '/' );
}

/**
 * manifest の絶対パス（有効テーマ基準）
 */
function kaju_blog_vite_manifest_path(): string {
	return get_theme_file_path( 'assets/.vite/manifest.json' );
}

function kaju_blog_vite_use_dev_server(): bool {
	$strategy = kaju_blog_vite_strategy();
	if ( 'prod' === $strategy ) {
		return false;
	}
	if ( 'dev' === $strategy ) {
		return defined( 'KAJU_BLOG_VITE_ORIGIN' ) && KAJU_BLOG_VITE_ORIGIN;
	}
	// auto: ビルド済み manifest があれば CSS/JS はテーマ assets から（Vite 未起動でも表示される）
	if ( is_readable( kaju_blog_vite_manifest_path() ) ) {
		if ( defined( 'KAJU_BLOG_VITE_FORCE_DEV' ) && KAJU_BLOG_VITE_FORCE_DEV ) {
			return defined( 'KAJU_BLOG_VITE_ORIGIN' ) && KAJU_BLOG_VITE_ORIGIN;
		}
		return false;
	}
	return defined( 'KAJU_BLOG_VITE_ORIGIN' ) && KAJU_BLOG_VITE_ORIGIN;
}

/**
 * フロント用アセット（dev: echo / prod: manifest + filemtime）
 */
function kaju_blog_enqueue_assets(): void {
	if ( kaju_blog_vite_use_dev_server() ) {
		$origin = rtrim( (string) KAJU_BLOG_VITE_ORIGIN, '/' );
		$entry  = ltrim( kaju_blog_vite_entry_key(), '/' );
		echo '<script type="module" src="' . esc_url( $origin . '/@vite/client' ) . '"></script>' . "\n";
		echo '<script type="module" src="' . esc_url( $origin . '/' . $entry ) . '"></script>' . "\n";
		return;
	}

	$manifest_path = kaju_blog_vite_manifest_path();
	if ( ! is_readable( $manifest_path ) ) {
		return;
	}

	$raw = file_get_contents( $manifest_path );
	if ( false === $raw ) {
		return;
	}

	$manifest = json_decode( $raw, true );
	if ( ! is_array( $manifest ) ) {
		return;
	}

	$key   = kaju_blog_vite_entry_key();
	$entry = $manifest[ $key ] ?? null;
	if ( ! is_array( $entry ) || empty( $entry['file'] ) ) {
		return;
	}

	$js_rel = kaju_blog_manifest_theme_rel( (string) $entry['file'] );
	$js_abs = get_theme_file_path( $js_rel );
	$ver    = is_readable( $js_abs ) ? (string) filemtime( $js_abs ) : null;

	wp_enqueue_script(
		'kaju-blog-main',
		get_theme_file_uri( $js_rel ),
		array(),
		$ver,
		true
	);
	wp_script_add_data( 'kaju-blog-main', 'type', 'module' );

	if ( ! empty( $entry['css'] ) && is_array( $entry['css'] ) ) {
		foreach ( $entry['css'] as $i => $css ) {
			$css_rel = kaju_blog_manifest_theme_rel( (string) $css );
			$css_abs = get_theme_file_path( $css_rel );
			if ( ! is_readable( $css_abs ) ) {
				continue;
			}
			$css_ver = (string) filemtime( $css_abs );
			wp_enqueue_style(
				'kaju-blog-style-' . $i,
				get_theme_file_uri( $css_rel ),
				array( 'kaju-blog-fonts' ),
				$css_ver
			);
		}
	}
}

add_action( 'wp_enqueue_scripts', 'kaju_blog_enqueue_assets', 5 );
