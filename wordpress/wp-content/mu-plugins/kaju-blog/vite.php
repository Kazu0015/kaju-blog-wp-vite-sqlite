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

/**
 * Vite base（vite.config.js の base と一致させる）
 */
function kaju_blog_vite_base_path(): string {
	$base = defined( 'KAJU_BLOG_VITE_BASE' ) && KAJU_BLOG_VITE_BASE
		? (string) KAJU_BLOG_VITE_BASE
		: '/wp-content/themes/kaju-blog/assets/';

	return '/' . trim( $base, '/' ) . '/';
}

/**
 * Vite 死活確認用 URL（コンテナ内は KAJU_BLOG_VITE_INTERNAL_ORIGIN を推奨）
 */
function kaju_blog_vite_dev_ping_url(): string {
	$origin = defined( 'KAJU_BLOG_VITE_INTERNAL_ORIGIN' ) && KAJU_BLOG_VITE_INTERNAL_ORIGIN
		? (string) KAJU_BLOG_VITE_INTERNAL_ORIGIN
		: ( defined( 'KAJU_BLOG_VITE_ORIGIN' ) ? (string) KAJU_BLOG_VITE_ORIGIN : '' );

	if ( '' === $origin ) {
		return '';
	}

	return rtrim( $origin, '/' ) . kaju_blog_vite_base_path() . '@vite/client';
}

/**
 * Vite dev サーバが応答しているか（短時間キャッシュ）
 */
function kaju_blog_vite_dev_server_is_running(): bool {
	static $runtime_cache = null;

	if ( null !== $runtime_cache ) {
		return $runtime_cache;
	}

	$ping_url = kaju_blog_vite_dev_ping_url();
	if ( '' === $ping_url ) {
		$runtime_cache = false;
		return false;
	}

	$transient_key = 'kaju_blog_vite_dev_up_' . md5( $ping_url );
	$cached        = get_transient( $transient_key );
	if ( false !== $cached ) {
		$runtime_cache = (bool) $cached;
		return $runtime_cache;
	}

	$response = wp_remote_get(
		$ping_url,
		array(
			'timeout'   => 1,
			'sslverify' => false,
		)
	);

	$code  = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
	$is_up = $code >= 200 && $code < 500;
	set_transient( $transient_key, $is_up ? 1 : 0, 30 );
	$runtime_cache = $is_up;

	return $runtime_cache;
}

/**
 * ブラウザ向け Vite オリジン（スクリプト読み込み用）
 */
function kaju_blog_vite_public_origin(): string {
	return defined( 'KAJU_BLOG_VITE_ORIGIN' ) && KAJU_BLOG_VITE_ORIGIN
		? rtrim( (string) KAJU_BLOG_VITE_ORIGIN, '/' )
		: '';
}

/**
 * Vite dev を使うか（未起動時は false → manifest にフォールバック）
 */
function kaju_blog_vite_use_dev_server(): bool {
	$strategy = kaju_blog_vite_strategy();

	if ( 'prod' === $strategy ) {
		return false;
	}

	if ( ! kaju_blog_vite_dev_server_is_running() ) {
		return false;
	}

	if ( 'dev' === $strategy ) {
		return '' !== kaju_blog_vite_public_origin();
	}

	if ( defined( 'KAJU_BLOG_VITE_FORCE_DEV' ) && KAJU_BLOG_VITE_FORCE_DEV ) {
		return '' !== kaju_blog_vite_public_origin();
	}

	// auto: Vite が動いていれば HMR、止まっていれば manifest
	return '' !== kaju_blog_vite_public_origin();
}

/**
 * manifest から CSS/JS を wp_enqueue
 */
function kaju_blog_enqueue_manifest_assets(): bool {
	$manifest_path = kaju_blog_vite_manifest_path();
	if ( ! is_readable( $manifest_path ) ) {
		return false;
	}

	$raw = file_get_contents( $manifest_path );
	if ( false === $raw ) {
		return false;
	}

	$manifest = json_decode( $raw, true );
	if ( ! is_array( $manifest ) ) {
		return false;
	}

	$key   = kaju_blog_vite_entry_key();
	$entry = $manifest[ $key ] ?? null;
	if ( ! is_array( $entry ) || empty( $entry['file'] ) ) {
		return false;
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

	return true;
}

/**
 * フロント用アセット（dev: Vite モジュール / それ以外: manifest）
 */
function kaju_blog_enqueue_assets(): void {
	if ( kaju_blog_vite_use_dev_server() ) {
		$origin = kaju_blog_vite_public_origin();
		$base   = kaju_blog_vite_base_path();
		$entry  = ltrim( kaju_blog_vite_entry_key(), '/' );
		echo '<script type="module" src="' . esc_url( $origin . $base . '@vite/client' ) . '"></script>' . "\n";
		echo '<script type="module" src="' . esc_url( $origin . $base . $entry ) . '"></script>' . "\n";
		return;
	}

	kaju_blog_enqueue_manifest_assets();
}

add_action( 'wp_enqueue_scripts', 'kaju_blog_enqueue_assets', 5 );
