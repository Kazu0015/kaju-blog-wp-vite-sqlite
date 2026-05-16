<?php
/**
 * 栽培記録 本文セクション1件
 *
 * @package kaju-blog
 * @var array  $section
 * @var int    $number  表示用番号（1始まり）
 */

defined( 'ABSPATH' ) || exit;

$section = $args['section'] ?? array();
$number  = isset( $args['number'] ) ? (int) $args['number'] : 1;

if ( empty( $section ) ) {
	return;
}

$title      = isset( $section['section_title'] ) ? trim( (string) $section['section_title'] ) : '';
$body       = isset( $section['section_body'] ) ? (string) $section['section_body'] : '';
$list_lines = kaju_blog_lines_from_textarea( $section['section_list'] ?? '' );
$image_url  = kaju_blog_acf_image_url( $section['section_image'] ?? null );
$style      = $section['section_image_style'] ?? 'default';
$point_t    = isset( $section['section_point_title'] ) ? trim( (string) $section['section_point_title'] ) : '';
$point_b    = isset( $section['section_point_text'] ) ? trim( (string) $section['section_point_text'] ) : '';
?>
<section class="single-section">
	<?php if ( $title ) : ?>
		<div class="single-section__heading">
			<span class="single-section__number" aria-hidden="true"><?php echo (int) $number; ?></span>
			<h2 class="single-section__title"><?php echo esc_html( $title ); ?></h2>
		</div>
	<?php endif; ?>
	<div class="single-section__body">
		<div class="single-section__text-area">
			<?php if ( $body ) : ?>
				<div class="single-section__text"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
			<?php endif; ?>

			<?php if ( $list_lines || $image_url ) : ?>
				<div class="single-section__list-wrapper">
					<?php if ( $list_lines ) : ?>
						<ul class="single-section__list">
							<?php foreach ( $list_lines as $line ) : ?>
								<li class="single-section__list-item"><?php echo esc_html( $line ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( $image_url && 'circle' === $style ) : ?>
						<div class="single-section__image-wrapper">
							<img class="single-section__image-circle" src="<?php echo esc_url( $image_url ); ?>" alt="" width="400" height="400" loading="lazy" decoding="async">
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $point_t || $point_b || ( $image_url && 'circle' !== $style ) ) : ?>
				<div class="single-section__point-wrapper">
					<?php if ( $point_t || $point_b ) : ?>
						<div class="single-section__point">
							<?php if ( $point_t ) : ?>
								<p class="single-section__point-title"><?php echo esc_html( $point_t ); ?></p>
							<?php endif; ?>
							<?php if ( $point_b ) : ?>
								<p class="single-section__point-text"><?php echo esc_html( $point_b ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ( $image_url && 'circle' !== $style ) : ?>
						<div class="single-section__image-wrapper">
							<img class="single-section__image" src="<?php echo esc_url( $image_url ); ?>" alt="" width="1165" height="912" loading="lazy" decoding="async">
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
