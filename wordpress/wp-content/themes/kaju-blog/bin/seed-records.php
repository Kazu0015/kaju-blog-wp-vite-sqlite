<?php
/**
 * WP-CLI: wp eval-file wp-content/themes/kaju-blog/bin/seed-records.php --allow-root
 *
 * @package kaju-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Run via WP-CLI inside WordPress.\n" );
	exit( 1 );
}

require_once get_template_directory() . '/inc/seed-records.php';

delete_option( 'kaju_blog_seed_records_version' );
$count = kaju_blog_seed_sample_records( true );
update_option( 'kaju_blog_seed_records_version', KAJU_BLOG_SEED_RECORDS_VERSION, false );

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::success( sprintf( 'Seeded %d sample record(s) with images.', $count ) );
} else {
	echo sprintf( "Seeded %d sample record(s) with images.\n", $count );
}
