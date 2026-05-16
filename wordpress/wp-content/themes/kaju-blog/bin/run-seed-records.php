<?php
/**
 * ブラウザ / CLI からサンプル栽培記録を投入
 * docker compose exec wordpress php wp-content/themes/kaju-blog/bin/run-seed-records.php
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

delete_option( 'kaju_blog_seed_records_version' );
$count = kaju_blog_seed_sample_records( true );
update_option( 'kaju_blog_seed_records_version', KAJU_BLOG_SEED_RECORDS_VERSION, false );

echo sprintf( "Seeded %d sample record(s) with images.\n", $count );
