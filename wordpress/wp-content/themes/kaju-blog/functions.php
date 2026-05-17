<?php
/**
 * Kaju Blog — テーマ設定
 *
 * Vite アセットの読み込みは mu-plugins/kaju-blog/vite.php が担う。
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/pagination.php';
require_once get_template_directory() . '/inc/cpt.php';
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/cf7.php';
require_once get_template_directory() . '/inc/cf7-setup.php';
require_once get_template_directory() . '/inc/seed-records.php';
require_once get_template_directory() . '/inc/admin-record-edit.php';
require_once get_template_directory() . '/inc/acf-record-fields.php';
require_once get_template_directory() . '/inc/cpt-memo.php';
require_once get_template_directory() . '/inc/cpt-tree.php';
require_once get_template_directory() . '/inc/seed-trees.php';
require_once get_template_directory() . '/inc/seed-memos.php';

add_action( 'after_setup_theme', 'kaju_blog_setup' );
add_action( 'wp_enqueue_scripts', 'kaju_blog_enqueue_fonts', 1 );
add_action( 'init', 'kaju_blog_maybe_seed_fruit_terms', 20 );

/**
 * テーマサポート・ACF JSON
 */
function kaju_blog_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	if ( function_exists( 'acf_add_options_page' ) ) {
		// ACF Pro の Options Page は使わない（Free 方針）
	}

	add_filter(
		'acf/settings/save_json',
		static function () {
			return get_template_directory() . '/acf-json';
		}
	);

	add_filter(
		'acf/settings/load_json',
		static function ( $paths ) {
			$paths[] = get_template_directory() . '/acf-json';
			return $paths;
		}
	);
}

function kaju_blog_enqueue_fonts(): void {
	wp_enqueue_style(
		'kaju-blog-fonts',
		'https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@400;500;700&family=Noto+Serif+JP:wght@400;700&display=swap',
		array(),
		null
	);
}

/**
 * 既にテーマ有効化済みの環境でも果樹タームを1回だけ投入
 */
function kaju_blog_maybe_seed_fruit_terms(): void {
	if ( get_option( 'kaju_blog_fruit_seeded' ) ) {
		return;
	}
	kaju_blog_seed_fruit_terms();
	update_option( 'kaju_blog_fruit_seeded', true, false );
}

/**
 * Contact Form 7 ショートコード（最初のフォーム）
 */
function kaju_blog_contact_form_shortcode(): string {
	$form_id = (int) get_option( 'kaju_blog_cf7_form_id', 0 );
	if ( $form_id > 0 && 'wpcf7_contact_form' === get_post_type( $form_id ) ) {
		return '[contact-form-7 id="' . $form_id . '"]';
	}

	$forms = get_posts(
		array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);
	if ( empty( $forms ) ) {
		return '';
	}
	return '[contact-form-7 id="' . (int) $forms[0] . '"]';
}
