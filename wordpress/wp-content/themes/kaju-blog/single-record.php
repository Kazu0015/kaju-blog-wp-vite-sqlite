<?php
/**
 * 栽培記録 詳細
 *
 * @package kaju-blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id  = get_the_ID();
	$modifier = kaju_blog_record_fruit_modifier( $post_id );
	$label    = kaju_blog_record_fruit_label( $post_id );
	$intro    = function_exists( 'get_field' ) ? (string) get_field( 'intro', $post_id ) : '';
	$sections = kaju_blog_get_record_sections( $post_id );
	$has_thumb = kaju_blog_record_has_thumbnail( $post_id );
	$thumb     = kaju_blog_record_thumbnail_url( $post_id, 'large' );
	$thumb_class = 'single-featured__image' . ( $has_thumb ? '' : ' single-featured__image--no-image' );
	$archive  = get_post_type_archive_link( 'record' ) ?: home_url( '/records/' );

	// 一覧と同じく全栽培記録を公開日順で辿る（果樹タームで絞らない）
	$prev = kaju_blog_get_adjacent_post( true, $post_id );
	$next = kaju_blog_get_adjacent_post( false, $post_id );
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
						'label' => '最近の栽培記録',
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
					<?php if ( $modifier && $label ) : ?>
						<span class="c-log-card__meta-badge c-log-card__meta-badge--<?php echo esc_attr( $modifier ); ?>"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
					<time class="single-header__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( kaju_blog_format_date( $post_id ) ); ?></time>
				</div>
				<h1 class="single-header__title"><?php the_title(); ?></h1>
			</header>

			<div class="single-featured">
				<img class="<?php echo esc_attr( $thumb_class ); ?>" src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $has_thumb ? get_the_title() : '' ); ?>" width="800" height="450" loading="lazy" decoding="async"<?php echo $has_thumb ? '' : ' role="presentation"'; ?>>
			</div>

			<?php if ( $intro ) : ?>
				<div class="single-intro">
					<p class="single-intro__text"><?php echo esc_html( $intro ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $sections ) : ?>
				<div class="single-sections">
					<?php
					$num = 1;
					foreach ( $sections as $section ) :
						get_template_part(
							'template-parts/record',
							'section',
							array(
								'section' => $section,
								'number'  => $num,
							)
						);
						++$num;
					endforeach;
					?>
				</div>
			<?php elseif ( get_the_content() ) : ?>
				<div class="single-intro">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<nav class="single-nav" aria-label="<?php esc_attr_e( '前後の記事', 'kaju-blog' ); ?>">
				<?php if ( $prev instanceof WP_Post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="single-nav__link">
						<span class="single-nav__arrow">&lt;</span>
						以前の記事へ
					</a>
				<?php else : ?>
					<span class="single-nav__link" aria-hidden="true"></span>
				<?php endif; ?>
				<?php if ( $next instanceof WP_Post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="single-nav__link">
						新しい記事へ
						<span class="single-nav__arrow">&gt;</span>
					</a>
				<?php else : ?>
					<span class="single-nav__link" aria-hidden="true"></span>
				<?php endif; ?>
			</nav>

			<div class="single-back">
				<a href="<?php echo esc_url( $archive ); ?>" class="single-back__link">← 一覧に戻る</a>
			</div>
		</article>
	</div>
</main>

	<?php
endwhile;

get_footer();
