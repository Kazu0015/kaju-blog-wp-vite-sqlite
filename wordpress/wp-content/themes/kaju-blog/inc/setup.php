<?php
/**
 * 初回セットアップ: 固定ページ作成・表示設定・リライトルール
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/** セットアップ内容を変えたらバージョンを上げる（リライト再フラッシュ・ページ再チェック） */
const KAJU_BLOG_SETUP_VERSION = '1.2.1';

add_action( 'init', 'kaju_blog_run_setup', 20 );

/**
 * 必要な固定ページの定義
 *
 * @return array<string, array{title: string, content?: string}>
 */
function kaju_blog_required_pages(): array {
	return array(
		'front'           => array(
			'title' => 'トップ',
		),
		'profile'         => array(
			'title' => 'プロフィール',
		),
		'contact'         => array(
			'title' => 'お問い合わせ',
		),
		'privacy-policy'  => array(
			'title'   => 'プライバシーポリシー',
			'content' => '<p>プライバシーポリシーの本文を編集してください。</p>',
		),
		'memo'            => array(
			'title'   => '作業メモ',
			'content' => '<p>準備中です。</p>',
		),
		'category'        => array(
			'title'   => 'カテゴリ',
			'content' => '<p>準備中です。</p>',
		),
	);
}

function kaju_blog_run_setup(): void {
	// 本番 DB にページが無い場合でも毎回不足分だけ作成する
	kaju_blog_create_required_pages();

	$stored = get_option( 'kaju_blog_setup_version', '' );
	if ( KAJU_BLOG_SETUP_VERSION === $stored ) {
		return;
	}

	kaju_blog_configure_permalinks();
	kaju_blog_configure_reading_settings();
	kaju_blog_seed_fruit_terms();

	flush_rewrite_rules( false );

	update_option( 'kaju_blog_setup_version', KAJU_BLOG_SETUP_VERSION, false );
}

/**
 * パーマリンクを「投稿名」に（未設定・デフォルトの ?p= だと下層 URL が 404）
 */
function kaju_blog_configure_permalinks(): void {
	$structure = (string) get_option( 'permalink_structure', '' );
	if ( '' === $structure ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
}

/**
 * 固定ページを自動作成（既存は上書きしない）
 */
function kaju_blog_create_required_pages(): void {
	foreach ( kaju_blog_required_pages() as $slug => $def ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );

		if ( $existing instanceof WP_Post ) {
			kaju_blog_ensure_required_page_published( $existing, $def );
			if ( 'privacy-policy' === $slug ) {
				kaju_blog_assign_privacy_policy_page( (int) $existing->ID );
			}
			continue;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $def['title'],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $def['content'] ?? '',
			),
			true
		);

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			continue;
		}

		if ( 'privacy-policy' === $slug ) {
			kaju_blog_assign_privacy_policy_page( (int) $page_id );
		}
	}
}

/**
 * 必須固定ページを公開状態にする（WP 初期の privacy-policy 下書き対策）
 *
 * @param WP_Post               $page 既存ページ.
 * @param array<string, mixed>  $def  kaju_blog_required_pages() の定義.
 */
function kaju_blog_ensure_required_page_published( WP_Post $page, array $def ): void {
	if ( 'publish' === $page->post_status ) {
		return;
	}

	wp_update_post(
		array(
			'ID'          => (int) $page->ID,
			'post_status' => 'publish',
			'post_title'  => (string) ( $def['title'] ?? $page->post_title ),
		)
	);
}

/**
 * WordPress の「プライバシーポリシー」固定ページ設定に紐づける
 */
function kaju_blog_assign_privacy_policy_page( int $page_id ): void {
	if ( $page_id <= 0 ) {
		return;
	}

	$current = (int) get_option( 'wp_page_for_privacy_policy', 0 );
	if ( $current !== $page_id ) {
		update_option( 'wp_page_for_privacy_policy', $page_id );
	}
}

/**
 * ホームを固定ページ front に設定
 */
function kaju_blog_configure_reading_settings(): void {
	$front = get_page_by_path( 'front', OBJECT, 'page' );
	if ( ! $front instanceof WP_Post ) {
		return;
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $front->ID );
}
