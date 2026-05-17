<?php
/**
 * CPT: 庭の木（tree）
 *
 * 一覧: /trees/
 * 詳細: /trees/{slug}/
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

const KAJU_BLOG_TREE_CPT_VERSION = '1.0.0';

add_action( 'init', 'kaju_blog_register_tree_cpt', 5 );
add_action( 'init', 'kaju_blog_maybe_flush_tree_rewrites', 99 );
add_action( 'pre_get_posts', 'kaju_blog_tree_archive_query' );

function kaju_blog_register_tree_cpt(): void {
	register_post_type(
		'tree',
		array(
			'labels'              => array(
				'name'          => '庭の木',
				'singular_name' => '庭の木',
				'add_new_item'  => '庭の木を追加',
				'edit_item'     => '庭の木を編集',
				'view_item'     => '庭の木を表示',
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => 'trees',
				'with_front' => false,
			),
			'menu_icon'           => 'dashicons-palmtree',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ),
			'show_in_rest'        => true,
			'exclude_from_search' => false,
		)
	);
}

function kaju_blog_maybe_flush_tree_rewrites(): void {
	if ( KAJU_BLOG_TREE_CPT_VERSION === get_option( 'kaju_blog_tree_cpt_version', '' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'kaju_blog_tree_cpt_version', KAJU_BLOG_TREE_CPT_VERSION, false );
}

/**
 * アーカイブ: 植えた年昇順 → menu_order → 日付
 */
function kaju_blog_tree_archive_query( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! $query->is_post_type_archive( 'tree' ) ) {
		return;
	}

	$query->set( 'posts_per_page', kaju_blog_archive_posts_per_page() );
	$query->set(
		'orderby',
		array(
			'meta_value_num' => 'ASC',
			'menu_order'     => 'ASC',
			'date'           => 'ASC',
		)
	);
	$query->set( 'meta_key', 'planted_year' );
}
