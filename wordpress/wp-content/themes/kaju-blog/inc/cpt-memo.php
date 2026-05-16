<?php
/**
 * CPT: 作業メモ（work_memo）
 *
 * 一覧: 固定ページ /memo/（page-memo.php）
 * 詳細: /memo/{slug}/
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

const KAJU_BLOG_MEMO_CPT_VERSION = '1.0.0';

add_action( 'init', 'kaju_blog_register_work_memo_cpt', 5 );
add_action( 'init', 'kaju_blog_maybe_flush_memo_rewrites', 99 );

function kaju_blog_register_work_memo_cpt(): void {
	register_post_type(
		'work_memo',
		array(
			'labels'              => array(
				'name'          => '作業メモ',
				'singular_name' => '作業メモ',
				'add_new_item'  => '作業メモを追加',
				'edit_item'     => '作業メモを編集',
				'view_item'     => '作業メモを表示',
			),
			'public'              => true,
			'has_archive'         => false,
			'rewrite'             => array(
				'slug'       => 'memo',
				'with_front' => false,
			),
			'menu_icon'           => 'dashicons-clipboard',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'show_in_rest'        => true,
			'exclude_from_search' => false,
		)
	);
}

function kaju_blog_maybe_flush_memo_rewrites(): void {
	if ( KAJU_BLOG_MEMO_CPT_VERSION === get_option( 'kaju_blog_memo_cpt_version', '' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'kaju_blog_memo_cpt_version', KAJU_BLOG_MEMO_CPT_VERSION, false );
}

/**
 * 作業メモ一覧 URL（固定ページ memo）
 */
function kaju_blog_memo_archive_url(): string {
	$page = get_page_by_path( 'memo', OBJECT, 'page' );
	if ( $page instanceof WP_Post ) {
		return get_permalink( $page );
	}
	return home_url( '/memo/' );
}
