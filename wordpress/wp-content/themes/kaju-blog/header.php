<?php
/**
 * サイトヘッダー + HTML 開始
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

$kaju_blog_nav_icons = array(
	'records'   => 'img/top/img-diary.png',
	'varieties' => 'img/top/img-variety.png',
	'memo'      => 'img/top/img-memo.png',
	'profile'   => 'img/top/img-profile.png',
	'contact'   => 'img/top/img-contact.png',
);
$kaju_blog_current_nav = kaju_blog_current_nav_slug();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#3d2b1f">
	<meta name="format-detection" content="telephone=no, email=no">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="header">
	<img class="header__bg" src="<?php echo esc_url( kaju_blog_asset_uri( 'img/top/img-top-header.webp' ) ); ?>" alt="" width="8256" height="2048" fetchpriority="high" decoding="async">
	<div class="header__inner">
		<div class="header__logo-wrapper">
			<?php if ( is_front_page() ) : ?>
				<h1>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img class="header__logo" src="<?php echo esc_url( kaju_blog_asset_uri( 'img/header/img-logo.png' ) ); ?>" alt="" width="657" height="648" decoding="async">
					</a>
				</h1>
			<?php else : ?>
				<div>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img class="header__logo" src="<?php echo esc_url( kaju_blog_asset_uri( 'img/header/img-logo.png' ) ); ?>" alt="" width="657" height="648" decoding="async">
					</a>
				</div>
			<?php endif; ?>
			<p class="header__title">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span>果樹と暮らす</span>
					<span>小さな庭の栽培記</span>
				</a>
			</p>
		</div>
		<nav class="header__nav l-container" aria-label="<?php esc_attr_e( 'メインナビゲーション', 'kaju-blog' ); ?>">
			<ul class="header__nav-list">
				<?php foreach ( kaju_blog_nav_items() as $item ) : ?>
					<?php
					$is_current = ( $kaju_blog_current_nav === $item['slug'] );
					$icon       = $kaju_blog_nav_icons[ $item['slug'] ] ?? 'img/top/img-diary.png';
					?>
					<li class="header__nav-item<?php echo $is_current ? ' header__nav-item--current' : ''; ?>">
						<a href="<?php echo esc_url( $item['url'] ); ?>" class="header__nav-link"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
							<img class="header__nav-icon" src="<?php echo esc_url( kaju_blog_asset_uri( $icon ) ); ?>" alt="" width="128" height="128" decoding="async">
							<span class="header__nav-label"><?php echo esc_html( $item['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
</header>
