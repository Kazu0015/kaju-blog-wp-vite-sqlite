<?php
/**
 * ブラウザ / CLI から作業メモサンプルを投入・更新
 * docker compose exec wordpress php wp-content/themes/kaju-blog/bin/run-seed-memos.php
 *
 * @package kaju-blog
 */

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found.\n" );
	exit( 1 );
}

require $wp_load;

require_once get_template_directory() . '/inc/seed-memos.php';

delete_option( 'kaju_blog_seed_memos_version' );
$count = kaju_blog_seed_sample_memos( true, true );
kaju_blog_retire_obsolete_sample_memos();
update_option( 'kaju_blog_seed_memos_version', KAJU_BLOG_SEED_MEMOS_VERSION, false );

echo sprintf( "Seeded %d work_memo post(s) with images.\n", $count );
