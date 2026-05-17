<?php
/**
 * 庭の木（tree）サンプル投稿
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

const KAJU_BLOG_SEED_TREES_VERSION = '1.0.0';

add_action( 'init', 'kaju_blog_maybe_seed_sample_trees', 36 );

function kaju_blog_maybe_seed_sample_trees(): void {
	if ( KAJU_BLOG_SEED_TREES_VERSION === get_option( 'kaju_blog_seed_trees_version', '' ) ) {
		return;
	}

	$created = kaju_blog_seed_sample_trees();
	if ( $created > 0 ) {
		update_option( 'kaju_blog_seed_trees_version', KAJU_BLOG_SEED_TREES_VERSION, false );
	}
}

/**
 * @param bool $force_version WP-CLI / run-seed-trees.php 用
 * @return int 新規作成件数
 */
function kaju_blog_seed_sample_trees( bool $force_version = false ): int {
	if ( ! post_type_exists( 'tree' ) ) {
		return 0;
	}

	$created = 0;
	foreach ( kaju_blog_sample_tree_definitions() as $def ) {
		if ( kaju_blog_upsert_sample_tree( $def ) ) {
			++$created;
		}
	}

	if ( $created > 0 && ! $force_version ) {
		update_option( 'kaju_blog_seed_trees_version', KAJU_BLOG_SEED_TREES_VERSION, false );
	}

	return $created;
}

/**
 * @return list<array<string, mixed>>
 */
function kaju_blog_sample_tree_definitions(): array {
	return array(
		array(
			'slug'            => 'garden-plum',
			'title'           => '庭のすもも',
			'fruit'           => 'plum',
			'featured_image'  => 'img/trees/tree-plum.png',
			'planted_year'    => '20190101',
			'rootstock'       => 'セイヨウスモモ系',
			'pollinator'      => '近くの梅（受粉補助）',
			'bloom_season'    => '3月下旬〜4月上旬',
			'harvest_season'  => '6月下旬〜7月',
			'status_note'     => '扇状に主枝を立てた株。実は小粒だが着果が多い。夏の剪定後、風通しを意識して枝を整理中。',
			'content'         => '<p>ブロック塀沿いに植えたすもも。主枝を扇状に広げ、日当たりと風通しを確保しています。</p>',
		),
		array(
			'slug'            => 'garden-cherry',
			'title'           => '庭のさくらんぼ',
			'fruit'           => 'cherry',
			'featured_image'  => 'img/trees/tree-cherry.png',
			'planted_year'    => '20210315',
			'rootstock'       => 'セイヨウミザクラ系',
			'pollinator'      => '近隣のさくらんぼ2品種',
			'bloom_season'    => '4月中旬',
			'harvest_season'  => '5月下旬〜6月上旬',
			'status_note'     => 'フェンス際の南西向き。着果が多い年は摘果で粒をそろえる予定。',
			'content'         => '<p>家の角に植えたさくらんぼ。赤い実が枝いっぱいに付いた様子を記録用に撮影しました。</p>',
		),
	);
}

/**
 * @param array<string, mixed> $def
 */
function kaju_blog_upsert_sample_tree( array $def ): bool {
	$slug = (string) ( $def['slug'] ?? '' );
	if ( '' === $slug ) {
		return false;
	}

	$existing = get_posts(
		array(
			'post_type'              => 'tree',
			'name'                   => $slug,
			'posts_per_page'         => 1,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
		)
	);

	if ( ! empty( $existing ) ) {
		return false;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'tree',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => (string) ( $def['title'] ?? $slug ),
			'post_content' => (string) ( $def['content'] ?? '' ),
		),
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return false;
	}

	$post_id = (int) $post_id;

	$fruit = (string) ( $def['fruit'] ?? '' );
	if ( $fruit && taxonomy_exists( 'fruit' ) ) {
		wp_set_object_terms( $post_id, $fruit, 'fruit' );
	}

	if ( ! empty( $def['featured_image'] ) && is_string( $def['featured_image'] ) && function_exists( 'kaju_blog_import_theme_image' ) ) {
		$featured_id = kaju_blog_import_theme_image( $def['featured_image'] );
		if ( $featured_id > 0 ) {
			set_post_thumbnail( $post_id, $featured_id );
		}
	}

	if ( function_exists( 'update_field' ) ) {
		$acf_map = array(
			'planted_year'    => $def['planted_year'] ?? '',
			'rootstock'       => $def['rootstock'] ?? '',
			'pollinator'      => $def['pollinator'] ?? '',
			'bloom_season'    => $def['bloom_season'] ?? '',
			'harvest_season'  => $def['harvest_season'] ?? '',
			'status_note'     => $def['status_note'] ?? '',
		);
		foreach ( $acf_map as $key => $value ) {
			if ( '' !== (string) $value ) {
				update_field( $key, $value, $post_id );
			}
		}
	}

	return true;
}
