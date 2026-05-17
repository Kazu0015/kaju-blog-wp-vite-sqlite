<?php
/**
 * 庭の木サンプルを投入
 * docker compose exec wordpress php wp-content/themes/kaju-blog/bin/run-seed-trees.php
 *
 * @package kaju-blog
 */

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found.\n" );
	exit( 1 );
}

require $wp_load;

require_once get_template_directory() . '/inc/seed-trees.php';

delete_option( 'kaju_blog_seed_trees_version' );
$count = kaju_blog_seed_sample_trees( true );
update_option( 'kaju_blog_seed_trees_version', KAJU_BLOG_SEED_TREES_VERSION, false );

echo sprintf( "Seeded %d tree post(s) with images.\n", $count );
