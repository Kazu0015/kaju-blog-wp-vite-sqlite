<?php
/**
 * CPT / タクソノミー登録
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'kaju_blog_register_record_cpt' );
add_action( 'init', 'kaju_blog_register_fruit_taxonomy' );
add_action( 'admin_menu', 'kaju_blog_remove_posts_menu' );

function kaju_blog_register_record_cpt(): void {
	register_post_type(
		'record',
		array(
			'labels'              => array(
				'name'          => '栽培記録',
				'singular_name' => '栽培記録',
				'add_new_item'  => '栽培記録を追加',
				'edit_item'     => '栽培記録を編集',
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => 'records',
				'with_front' => false,
			),
			'menu_icon'           => 'dashicons-carrot',
			'supports'            => array( 'title', 'thumbnail', 'excerpt', 'revisions' ),
			'show_in_rest'        => true,
			'exclude_from_search' => false,
		)
	);
}

function kaju_blog_register_fruit_taxonomy(): void {
	register_taxonomy(
		'fruit',
		array( 'record' ),
		array(
			'labels'            => array(
				'name'          => '果樹',
				'singular_name' => '果樹',
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'records/fruit',
				'with_front' => false,
			),
			'show_in_rest'      => true,
		)
	);
}

function kaju_blog_remove_posts_menu(): void {
	remove_menu_page( 'edit.php' );
}

/**
 * テーマ有効化時: 果樹ターム初期投入・パーマリンクフラッシュ
 */
add_action( 'after_switch_theme', 'kaju_blog_theme_activation' );

function kaju_blog_theme_activation(): void {
	kaju_blog_seed_fruit_terms();
	flush_rewrite_rules();
}

function kaju_blog_seed_fruit_terms(): void {
	if ( ! taxonomy_exists( 'fruit' ) ) {
		return;
	}
	foreach ( kaju_blog_fruit_map() as $slug => $data ) {
		if ( ! term_exists( $slug, 'fruit' ) ) {
			wp_insert_term( $data['label'], 'fruit', array( 'slug' => $slug ) );
		}
	}
}
