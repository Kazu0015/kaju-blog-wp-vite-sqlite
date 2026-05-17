<?php
/**
 * CPT / タクソノミー登録
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'kaju_blog_register_record_cpt' );
add_action( 'init', 'kaju_blog_register_fruit_taxonomy' );
add_filter( 'query_vars', 'kaju_blog_register_record_fruit_query_var' );
add_filter( 'redirect_canonical', 'kaju_blog_disable_record_fruit_canonical_redirect', 10, 2 );
add_action( 'pre_get_posts', 'kaju_blog_record_archive_query' );
add_action( 'template_redirect', 'kaju_blog_redirect_legacy_record_fruit_query', 0 );
add_action( 'template_redirect', 'kaju_blog_redirect_fruit_tax_path_to_record_filter', 1 );
add_action( 'template_redirect', 'kaju_blog_redirect_fruit_tax_to_record_filter', 2 );
add_action( 'admin_menu', 'kaju_blog_remove_posts_menu' );

/**
 * @param list<string> $vars
 * @return list<string>
 */
function kaju_blog_register_record_fruit_query_var( array $vars ): array {
	// タクソノミー fruit と同名にすると redirect_canonical がループするため別名
	$vars[] = 'record_fruit';
	return $vars;
}

/**
 * /records/?record_fruit=slug の canonical リダイレクトを抑止（/records/fruit/slug とのループ防止）
 *
 * @param string|false $redirect_url
 * @param string       $requested_url
 * @return string|false
 */
function kaju_blog_disable_record_fruit_canonical_redirect( $redirect_url, $requested_url ) {
	unset( $requested_url );

	if ( is_post_type_archive( 'record' ) && '' !== kaju_blog_get_record_fruit_filter_slug() ) {
		return false;
	}

	return $redirect_url;
}

/**
 * 栽培記録一覧・果樹絞り込み（?record_fruit=slug / 旧タクソノミーURL）
 */
function kaju_blog_record_archive_query( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$per_page = kaju_blog_archive_posts_per_page();

	if ( $query->is_post_type_archive( 'record' ) ) {
		$query->set( 'posts_per_page', $per_page );

		$fruit_slug = kaju_blog_get_record_fruit_filter_slug();
		if ( '' !== $fruit_slug ) {
			$query->set(
				'tax_query',
				array(
					array(
						'taxonomy' => 'fruit',
						'field'    => 'slug',
						'terms'    => $fruit_slug,
					),
				)
			);
		}
		return;
	}

	if ( $query->is_tax( 'fruit' ) ) {
		$query->set( 'posts_per_page', $per_page );
		$query->set( 'post_type', 'record' );
	}
}

/**
 * 旧クエリ ?fruit= を ?record_fruit= へ（1回だけ）
 */
function kaju_blog_redirect_legacy_record_fruit_query(): void {
	if ( ! is_post_type_archive( 'record' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['fruit'] ) || ! empty( $_GET['record_fruit'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$slug = sanitize_title( wp_unslash( (string) $_GET['fruit'] ) );
	if ( '' === $slug ) {
		return;
	}

	wp_safe_redirect( kaju_blog_record_filter_url( $slug ), 301 );
	exit;
}

/**
 * /records/fruit/{slug}/ を一覧絞り込みへ（WP が単一 record と解釈するケースを含む）
 */
function kaju_blog_redirect_fruit_tax_path_to_record_filter(): void {
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '';
	if ( ! preg_match( '#/records/fruit/([^/]+)/?#', $uri, $matches ) ) {
		return;
	}

	$slug = sanitize_title( $matches[1] );
	if ( '' === $slug ) {
		return;
	}

	wp_safe_redirect( kaju_blog_record_filter_url( $slug ), 301 );
	exit;
}

/**
 * 果樹タクソノミーアーカイブ URL を一覧絞り込みへ
 */
function kaju_blog_redirect_fruit_tax_to_record_filter(): void {
	if ( ! is_tax( 'fruit' ) ) {
		return;
	}

	$term = get_queried_object();
	if ( ! $term instanceof WP_Term ) {
		return;
	}

	wp_safe_redirect( kaju_blog_record_filter_url( $term->slug ), 301 );
	exit;
}

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
		array( 'record', 'tree' ),
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
