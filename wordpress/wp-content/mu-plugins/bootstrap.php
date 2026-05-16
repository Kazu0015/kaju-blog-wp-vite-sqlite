<?php
/**
 * Plugin Name: Kaju Blog MU bootstrap
 * Description: 開発用 mu-plugins の入口（kaju-blog/*.php を読み込む）
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

$kaju_blog_mu_dir = __DIR__ . '/kaju-blog';

if ( is_readable( $kaju_blog_mu_dir . '/docker.php' ) ) {
	require_once $kaju_blog_mu_dir . '/docker.php';
}
if ( is_readable( $kaju_blog_mu_dir . '/vite.php' ) ) {
	require_once $kaju_blog_mu_dir . '/vite.php';
}
