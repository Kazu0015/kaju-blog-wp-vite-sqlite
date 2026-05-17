<?php
/**
 * 栽培記録一覧
 *
 * @package kaju-blog
 */

get_header();
?>

<main>
	<div class="l-container">
		<?php
		get_template_part(
			'template-parts/breadcrumb',
			null,
			array(
				'prefix' => 'record-breadcrumb',
				'items'  => array(
					array(
						'label' => 'ホーム',
						'url'   => home_url( '/' ),
					),
					array( 'label' => '栽培記録' ),
				),
			)
		);
		?>

		<?php get_template_part( 'template-parts/record', 'filter' ); ?>

		<header class="record-header">
			<h1 class="record-header__title">
				<?php
				$filter_label = kaju_blog_get_record_fruit_filter_label();
				if ( '' !== $filter_label ) {
					echo esc_html( $filter_label );
				} else {
					post_type_archive_title();
				}
				?>
			</h1>
		</header>

		<section class="record-list" aria-labelledby="record-list-heading">
			<h2 id="record-list-heading" class="u-visually-hidden">栽培記録一覧</h2>
			<?php if ( have_posts() ) : ?>
				<ul class="record-list__card-list">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<li class="record-list__card-item">
							<?php
							get_template_part(
								'template-parts/log',
								'card',
								array( 'post' => get_post() )
							);
							?>
						</li>
					<?php endwhile; ?>
				</ul>

				<?php
				get_template_part(
					'template-parts/pagination',
					null,
					array( 'paginate_args' => array() )
				);
				?>
			<?php else : ?>
				<p>
					<?php
					if ( '' !== kaju_blog_get_record_fruit_filter_slug() ) {
						esc_html_e( 'この果樹の栽培記録はまだありません。', 'kaju-blog' );
					} else {
						esc_html_e( '栽培記録はまだありません。', 'kaju-blog' );
					}
					?>
				</p>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php
get_footer();
