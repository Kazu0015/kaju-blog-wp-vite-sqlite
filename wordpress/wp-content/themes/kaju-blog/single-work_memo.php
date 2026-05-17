<?php
/**
 * 作業メモ 詳細
 *
 * @package kaju-blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id   = get_the_ID();
	$has_thumb = kaju_blog_post_has_thumbnail( $post_id );
	$thumb     = kaju_blog_post_thumbnail_url( $post_id, 'large' );
	$thumb_class = 'single-featured__image' . ( $has_thumb ? '' : ' single-featured__image--no-image' );
	$archive   = kaju_blog_memo_archive_url();
	$prev      = kaju_blog_get_adjacent_post( true, $post_id );
	$next      = kaju_blog_get_adjacent_post( false, $post_id );
	?>

<main>
	<div class="l-container-s">
		<?php
		get_template_part(
			'template-parts/breadcrumb',
			null,
			array(
				'prefix' => 'single-breadcrumb',
				'items'  => array(
					array(
						'label' => 'ホーム',
						'url'   => home_url( '/' ),
					),
					array(
						'label' => '作業メモ',
						'url'   => $archive,
					),
					array( 'label' => get_the_title() ),
				),
			)
		);
		?>

		<article>
			<header class="single-header">
				<div class="single-header__meta">
					<time class="single-header__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( kaju_blog_format_date( $post_id ) ); ?></time>
				</div>
				<h1 class="single-header__title"><?php the_title(); ?></h1>
			</header>

			<div class="single-featured">
				<img class="<?php echo esc_attr( $thumb_class ); ?>" src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $has_thumb ? get_the_title() : '' ); ?>" width="800" height="450" loading="lazy" decoding="async"<?php echo $has_thumb ? '' : ' role="presentation"'; ?>>
			</div>

			<div class="single-intro memo-single__content">
				<?php the_content(); ?>
			</div>

			<nav class="single-nav" aria-label="<?php esc_attr_e( '前後の記事', 'kaju-blog' ); ?>">
				<?php if ( $prev instanceof WP_Post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="single-nav__link">
						<span class="single-nav__arrow">&lt;</span>
						前のメモへ
					</a>
				<?php else : ?>
					<span class="single-nav__link" aria-hidden="true"></span>
				<?php endif; ?>
				<?php if ( $next instanceof WP_Post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="single-nav__link">
						次のメモへ
						<span class="single-nav__arrow">&gt;</span>
					</a>
				<?php else : ?>
					<span class="single-nav__link" aria-hidden="true"></span>
				<?php endif; ?>
			</nav>

			<div class="single-back">
				<a href="<?php echo esc_url( $archive ); ?>" class="single-back__link">← 作業メモ一覧に戻る</a>
			</div>
		</article>
	</div>
</main>

	<?php
endwhile;

get_footer();
