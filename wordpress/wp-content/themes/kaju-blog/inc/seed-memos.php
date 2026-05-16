<?php
/**
 * 作業メモ（work_memo）サンプル投稿
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

const KAJU_BLOG_SEED_MEMOS_VERSION = '1.0.0';

add_action( 'init', 'kaju_blog_maybe_seed_sample_memos', 35 );

function kaju_blog_maybe_seed_sample_memos(): void {
	if ( KAJU_BLOG_SEED_MEMOS_VERSION === get_option( 'kaju_blog_seed_memos_version', '' ) ) {
		return;
	}

	$created = kaju_blog_seed_sample_memos();
	if ( $created > 0 || kaju_blog_configure_memo_landing_page() ) {
		update_option( 'kaju_blog_seed_memos_version', KAJU_BLOG_SEED_MEMOS_VERSION, false );
	}
}

/**
 * @param bool $force_version WP-CLI 用
 * @return int 新規作成件数
 */
function kaju_blog_seed_sample_memos( bool $force_version = false ): int {
	if ( ! post_type_exists( 'work_memo' ) ) {
		return 0;
	}

	$created = 0;
	foreach ( kaju_blog_sample_memo_definitions() as $def ) {
		if ( kaju_blog_upsert_sample_memo( $def ) ) {
			++$created;
		}
	}

	kaju_blog_configure_memo_landing_page();

	if ( $created > 0 && ! $force_version ) {
		update_option( 'kaju_blog_seed_memos_version', KAJU_BLOG_SEED_MEMOS_VERSION, false );
	}

	return $created;
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
			'slug'    => 'spring-fertilizer',
			'title'   => '春の追肥メモ',
			'date'    => '2024-03-18 09:00:00',
			'excerpt' => '開花前の追肥量と、根に当てないための注意点をメモ。',
			'content' => '<p>3月中旬、気温が安定してきたタイミングで有機質肥料を少量ずつ与えました。</p><p>根から離れた位置に環状に撒き、浅く土に混ぜる程度に留めています。雨予報の前日に実施すると浸透しやすい印象です。</p>',
		),
		array(
			'slug'    => 'pruning-disinfect',
			'title'   => '剪定後の消毒',
			'date'    => '2024-02-05 14:00:00',
			'excerpt' => '剪定跡の保護と、道具の消毒手順のメモ。',
			'content' => '<p>冬の剪定後、切り口にカルス剤を薄く塗布しました。風の強い日は避け、乾燥するまで触らないようにしています。</p><p>剪定ばさみは都度アルコール消毒し、次の枝に移る前に拭き取る習慣をつけました。</p>',
		),
		array(
			'slug'    => 'weekly-garden-check',
			'title'   => '週次の庭点検',
			'date'    => '2024-06-01 07:30:00',
			'excerpt' => '毎週日曜の朝に見るチェックリスト。',
			'content' => '<p>葉の色変化、害虫の食害跡、土の乾き具合を中心に確認。異常があれば栽培記録に詳しく書く、軽微ならこの作業メモに残す運用にしています。</p><ul><li>葉裏のハダニ</li><li>土表面のカビ</li><li>支柱のゆるみ</li></ul>',
		),
		array(
			'slug'    => 'mulch-refresh',
			'title'   => 'マルチの張り替え',
			'date'    => '2024-05-12 16:00:00',
			'excerpt' => '草抑えマルチを新しくしたときの作業メモ。',
			'content' => '<p>古いマルチを撤去してから、雑草を取り除き、新しいマルチを重ねました。端を土で軽く押さえると風でめくれにくくなります。</p>',
		),
	);
}

/**
 * @param array<string, mixed> $def
 */
function kaju_blog_upsert_sample_memo( array $def ): bool {
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

	if ( ! empty( $existing ) ) {
		return false;
	}

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

	return ! is_wp_error( $post_id ) && (bool) $post_id;
}
