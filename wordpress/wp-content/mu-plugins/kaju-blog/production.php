<?php
/**
 * 本番向け: 開発用プラグイン・管理バーの無効化
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/**
 * 本番環境か（compose 本番の KAJU_BLOG_VITE_STRATEGY=prod 等）
 */
function kaju_blog_is_production(): bool {
	if ( defined( 'KAJU_BLOG_PRODUCTION' ) ) {
		return (bool) KAJU_BLOG_PRODUCTION;
	}
	return defined( 'KAJU_BLOG_VITE_STRATEGY' ) && 'prod' === KAJU_BLOG_VITE_STRATEGY;
}

/** @var string[] */
function kaju_blog_production_disabled_plugins(): array {
	return array(
		'show-current-template/show-current-template.php',
	);
}

/**
 * 本番では開発用プラグインを読み込まない（DB の active_plugins は変更しない）
 *
 * @param string[]|false $plugins Active plugins.
 * @return string[]|false
 */
function kaju_blog_filter_active_plugins_for_production( $plugins ) {
	if ( ! kaju_blog_is_production() || ! is_array( $plugins ) ) {
		return $plugins;
	}

	$disabled = kaju_blog_production_disabled_plugins();

	return array_values(
		array_filter(
			$plugins,
			static function ( $plugin ) use ( $disabled ) {
				return ! in_array( $plugin, $disabled, true );
			}
		)
	);
}

add_filter( 'option_active_plugins', 'kaju_blog_filter_active_plugins_for_production' );
add_filter( 'site_option_active_sitewide_plugins', 'kaju_blog_filter_active_sitewide_plugins_for_production' );

/**
 * @param array<string, int>|false $plugins Network active plugins.
 * @return array<string, int>|false
 */
function kaju_blog_filter_active_sitewide_plugins_for_production( $plugins ) {
	if ( ! kaju_blog_is_production() || ! is_array( $plugins ) ) {
		return $plugins;
	}

	$disabled = kaju_blog_production_disabled_plugins();

	foreach ( $disabled as $plugin ) {
		unset( $plugins[ $plugin ] );
	}

	return $plugins;
}

/**
 * SQLite プラグインの管理バー「Database: SQLite」を非表示
 */
function kaju_blog_hide_sqlite_admin_bar(): void {
	if ( ! kaju_blog_is_production() ) {
		return;
	}
	remove_action( 'admin_bar_menu', 'sqlite_plugin_adminbar_item', 999 );
}

add_action( 'plugins_loaded', 'kaju_blog_hide_sqlite_admin_bar', 20 );
