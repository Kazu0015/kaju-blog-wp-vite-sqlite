<?php
/**
 * 作業メモ（work_memo）サンプル投稿
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

const KAJU_BLOG_SEED_MEMOS_VERSION = '2.0.0';

add_action( 'init', 'kaju_blog_maybe_seed_sample_memos', 35 );

function kaju_blog_maybe_seed_sample_memos(): void {
	if ( KAJU_BLOG_SEED_MEMOS_VERSION === get_option( 'kaju_blog_seed_memos_version', '' ) ) {
		return;
	}

	$had_previous = '' !== (string) get_option( 'kaju_blog_seed_memos_version', '' );
	$created      = kaju_blog_seed_sample_memos( false, $had_previous );

	if ( $had_previous ) {
		kaju_blog_retire_obsolete_sample_memos();
	}

	if ( $created > 0 || kaju_blog_configure_memo_landing_page() || $had_previous ) {
		update_option( 'kaju_blog_seed_memos_version', KAJU_BLOG_SEED_MEMOS_VERSION, false );
	}
}

/**
 * @param bool $force_version WP-CLI 用
 * @param bool $update_existing 既存スラッグを本文・アイキャッチで更新
 * @return int 新規作成・更新件数
 */
function kaju_blog_seed_sample_memos( bool $force_version = false, bool $update_existing = false ): int {
	if ( ! post_type_exists( 'work_memo' ) ) {
		return 0;
	}

	$count = 0;
	foreach ( kaju_blog_sample_memo_definitions() as $def ) {
		if ( kaju_blog_upsert_sample_memo( $def, $update_existing ) ) {
			++$count;
		}
	}

	kaju_blog_configure_memo_landing_page();

	if ( $count > 0 && ! $force_version ) {
		update_option( 'kaju_blog_seed_memos_version', KAJU_BLOG_SEED_MEMOS_VERSION, false );
	}

	return $count;
}

/**
 * 固定ページ /memo/ のリード文を設定
 */
function kaju_blog_configure_memo_landing_page(): bool {
	$page = get_page_by_path( 'memo', OBJECT, 'page' );
	if ( ! $page instanceof WP_Post ) {
		return false;
	}

	$lead = '<p>庭仕事のちょっとしたメモを残しています。施肥・剪定・収穫前後の記録など、栽培記録ほど長くない内容をまとめています。</p>';

	if ( str_contains( (string) $page->post_content, '準備中' ) || '' === trim( (string) $page->post_content ) ) {
		wp_update_post(
			array(
				'ID'           => (int) $page->ID,
				'post_content' => $lead,
			)
		);
		return true;
	}

	return false;
}

/**
 * @return list<array<string, mixed>>
 */
function kaju_blog_sample_memo_definitions(): array {
	return array(
		array(
			'slug'           => 'spring-fertilizer',
			'title'          => '施肥注意',
			'date'           => '2024-03-18 09:00:00',
			'excerpt'        => '若木への追肥で、根に直接当てないための注意をメモ。',
			'content'        => '<p>若木の枝に「施肥注意・根に当てない」と書いた札を付けておきました。開花前の追肥は、根元ではなく幹から少し離れた位置に環状に撒き、浅く土に混ぜる程度に留めています。</p><p>肥料が根に直接触れると、若い根を傷めやすいので、雨の前日に少量ずつ与える運用にしています。</p>',
			'featured_image' => 'img/memo/memo-fertilizer.png',
		),
		array(
			'slug'           => 'pruning-disinfect',
			'title'          => '剪定の準備',
			'date'           => '2024-02-05 14:00:00',
			'excerpt'        => '剪定ばさみと枝の状態を確認してから作業に入るメモ。',
			'content'        => '<p>剪定に入る前に、ばさみの刃の開き具合と切り口の状態、枝の太さを確認しました。すでに剪定した小枝の跡も見ながら、次に切る位置を決めています。</p><p>枝ごとに移る前にばさみをアルコールで消毒し、切り口は乾燥するまで触らないようにしています。</p>',
			'featured_image' => 'img/memo/memo-pruning.png',
		),
		array(
			'slug'           => 'grafting-bud-check',
			'title'          => '接ぎ木の芽出し確認',
			'date'           => '2024-04-22 11:00:00',
			'excerpt'        => 'テープで保護した接ぎ木から、新芽が出始めた記録。',
			'content'        => '<p>接ぎ木テープで保護していた部分の上から、小さな緑の芽が出てきました。活着の目安になるので、無理に触らず様子を見ることにしています。</p><p>接ぎ木ナイフは作業後に拭き取り、次の株に移る前に刃先を確認する習慣をつけました。</p>',
			'featured_image' => 'img/memo/memo-grafting.png',
		),
		array(
			'slug'           => 'weekly-garden-check',
			'title'          => '庭のチェックリスト',
			'date'           => '2024-06-01 07:30:00',
			'excerpt'        => '土・施肥・病害虫・剪定・支柱・灌水の6項目をまとめて確認。',
			'content'        => '<p>日曜の朝に、庭のチェックリストを一通り確認しました。異常があれば栽培記録に詳しく書き、軽微ならこの作業メモに残す運用です。</p><ul><li>土の状態 — 確認済み</li><li>施肥 — 確認済み</li><li>病害虫の有無 — 確認済み</li><li>剪定の必要性 — 確認済み</li><li>支柱の確認 — 確認済み</li><li>灌水の確認 — 確認済み</li></ul>',
			'featured_image' => 'img/memo/memo-checklist.png',
		),
	);
}

/**
 * @param array<string, mixed> $def
 */
function kaju_blog_upsert_sample_memo( array $def, bool $update_existing = false ): bool {
	$slug = (string) ( $def['slug'] ?? '' );
	if ( '' === $slug ) {
		return false;
	}

	$existing = get_posts(
		array(
			'post_type'              => 'work_memo',
			'name'                   => $slug,
			'posts_per_page'         => 1,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
		)
	);

	$is_update = ! empty( $existing );

	if ( $is_update && ! $update_existing ) {
		return false;
	}

	if ( $is_update ) {
		$post_id = (int) $existing[0];
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_title'   => (string) ( $def['title'] ?? $slug ),
				'post_excerpt' => (string) ( $def['excerpt'] ?? '' ),
				'post_content' => (string) ( $def['content'] ?? '' ),
				'post_date'    => (string) ( $def['date'] ?? current_time( 'mysql' ) ),
			)
		);
	} else {
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'work_memo',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => (string) ( $def['title'] ?? $slug ),
				'post_excerpt' => (string) ( $def['excerpt'] ?? '' ),
				'post_content' => (string) ( $def['content'] ?? '' ),
				'post_date'    => (string) ( $def['date'] ?? current_time( 'mysql' ) ),
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return false;
		}
		$post_id = (int) $post_id;
	}

	if ( ! empty( $def['featured_image'] ) && is_string( $def['featured_image'] ) && function_exists( 'kaju_blog_import_theme_image' ) ) {
		$featured_id = kaju_blog_import_theme_image( $def['featured_image'] );
		if ( $featured_id > 0 ) {
			set_post_thumbnail( $post_id, $featured_id );
		}
	}

	return true;
}

/**
 * 画像付きサンプルに置き換えた旧スラッグを下書き化
 */
function kaju_blog_retire_obsolete_sample_memos(): void {
	$obsolete_slugs = array( 'mulch-refresh' );

	foreach ( $obsolete_slugs as $slug ) {
		$existing = get_posts(
			array(
				'post_type'      => 'work_memo',
				'name'           => $slug,
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
			)
		);

		if ( empty( $existing ) ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'          => (int) $existing[0],
				'post_status' => 'draft',
			)
		);
	}
}
