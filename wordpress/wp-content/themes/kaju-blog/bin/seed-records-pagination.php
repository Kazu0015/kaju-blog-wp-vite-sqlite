<?php
/**
 * ページネーション確認用の栽培記録を追加投入
 * docker compose exec wordpress php wp-content/themes/kaju-blog/bin/seed-records-pagination.php
 *
 * @package kaju-blog
 */

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found.\n" );
	exit( 1 );
}

require $wp_load;

require_once get_template_directory() . '/inc/seed-records.php';

delete_option( 'kaju_blog_seed_pagination_records_version' );
$upserted = kaju_blog_seed_pagination_records();
update_option( 'kaju_blog_seed_pagination_records_version', KAJU_BLOG_SEED_PAGINATION_RECORDS_VERSION, false );
$total    = (int) wp_count_posts( 'record' )->publish;

echo sprintf( "Upserted %d pagination record(s). Total published: %d\n", $upserted, $total );
