<?php
/**
 * 庭の木 詳細
 *
 * @package kaju-blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id      = get_the_ID();
	$has_thumb    = kaju_blog_post_has_thumbnail( $post_id );
	$thumb        = kaju_blog_post_thumbnail_url( $post_id, 'large' );
	$thumb_class  = 'single-featured__image' . ( $has_thumb ? '' : ' single-featured__image--no-image' );
	$archive      = get_post_type_archive_link( 'tree' ) ?: home_url( '/trees/' );
	$modifier     = kaju_blog_record_fruit_modifier( $post_id );
	$label        = kaju_blog_record_fruit_label( $post_id );
	$meta_parts   = array_filter(
		array(
			kaju_blog_tree_planted_year_label( $post_id ),
			kaju_blog_tree_age_label( $post_id ),
		)
	);
	$profile_rows = kaju_blog_tree_profile_rows( $post_id );
	$prev         = kaju_blog_get_adjacent_post( true, $post_id );
	$next         = kaju_blog_get_adjacent_post( false, $post_id );
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
						'label' => '庭の木',
						'url'   => $archive,
					),
					array( 'label' => get_the_title() ),
				),
			)
		);
		?>

		<article>
			<header class="single-header">
				<?php if ( ( $modifier && $label ) || ! empty( $meta_parts ) ) : ?>
					<div class="single-header__meta">
						<?php if ( $modifier && $label ) : ?>
							<span class="c-log-card__meta-badge c-log-card__meta-badge--<?php echo esc_attr( $modifier ); ?>"><?php echo esc_html( $label ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $meta_parts ) ) : ?>
							<p class="single-header__date"><?php echo esc_html( implode( ' / ', $meta_parts ) ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<h1 class="single-header__title"><?php the_title(); ?></h1>
			</header>

			<div class="single-featured">
				<img class="<?php echo esc_attr( $thumb_class ); ?>" src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $has_thumb ? get_the_title() : '' ); ?>" width="800" height="450" loading="lazy" decoding="async"<?php echo $has_thumb ? '' : ' role="presentation"'; ?>>
			</div>

			<?php if ( $profile_rows ) : ?>
				<div class="tree-profile">
					<?php foreach ( $profile_rows as $row ) : ?>
						<div class="tree-profile__row">
							<p class="tree-profile__term"><?php echo esc_html( $row['label'] ); ?></p>
							<p class="tree-profile__desc"><?php echo esc_html( $row['value'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="single-intro tree-single__content">
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
				<a href="<?php echo esc_url( $archive ); ?>" class="single-back__link">← 庭の木一覧に戻る</a>
			</div>
		</article>
	</div>
</main>

	<?php
endwhile;

get_footer();
