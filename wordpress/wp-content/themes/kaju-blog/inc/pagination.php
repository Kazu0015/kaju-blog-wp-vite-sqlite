<?php
/**
 * ページネーション（静的 HTML の .c-pagination__link マークアップに合わせる）
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/**
 * 固定ページ一覧用を含む paginate_links 引数
 *
 * @param int $total_pages 総ページ数
 * @param int $current     現在ページ（0 のとき自動取得）
 * @return array<string, mixed>
 */
function kaju_blog_build_paginate_args( int $total_pages, int $current = 0 ): array {
	$current = $current > 0 ? $current : max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	$args = array(
		'total'   => max( 1, $total_pages ),
		'current' => $current,
	);

	if ( is_page() && get_queried_object() instanceof WP_Post ) {
		$args['base']   = trailingslashit( get_permalink() ) . '%_%';
		$args['format'] = 'page/%#%/';
	}

	return $args;
}

/**
 * @param array<string, mixed> $args paginate_links() に渡す引数
 * @return list<string>
 */
function kaju_blog_get_pagination_links( array $args = array() ): array {
	$defaults = array(
		'type'      => 'array',
		'prev_text' => '<span aria-hidden="true">&lt;</span>',
		'next_text' => '<span aria-hidden="true">&gt;</span>',
	);
	$links = paginate_links( array_merge( $defaults, $args ) );

	if ( ! is_array( $links ) ) {
		return array();
	}

	return array_map( 'kaju_blog_format_pagination_link', $links );
}

/**
 * WordPress 標準の page-numbers を c-pagination__link に変換
 */
function kaju_blog_format_pagination_link( string $link ): string {
	if ( preg_match( '/<span[^>]*class="[^"]*current[^"]*"[^>]*>(.*?)<\/span>/', $link, $matches ) ) {
		return sprintf(
			'<span class="c-pagination__link is-current" aria-current="page">%s</span>',
			wp_kses_post( $matches[1] )
		);
	}

	$link = preg_replace(
		'/class="prev page-numbers"/',
		'class="c-pagination__link" aria-label="前のページ"',
		$link
	);
	$link = preg_replace(
		'/class="next page-numbers"/',
		'class="c-pagination__link" aria-label="次のページ"',
		$link
	);
	$link = str_replace( 'class="page-numbers dots"', 'class="c-pagination__link c-pagination__link--dots"', $link );
	$link = str_replace( 'class="page-numbers"', 'class="c-pagination__link"', $link );

	return $link;
}
