<?php
/**
 * WP-CLI: wp eval-file wp-content/themes/kaju-blog/bin/seed-memos.php --allow-root
 *
 * @package kaju-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Run via WP-CLI inside WordPress.\n" );
	exit( 1 );
}

require_once get_template_directory() . '/inc/seed-memos.php';

$count = kaju_blog_seed_sample_memos( true );

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::success( sprintf( 'Created %d work_memo post(s).', $count ) );
} else {
	echo sprintf( "Created %d work_memo post(s).\n", $count );
}
