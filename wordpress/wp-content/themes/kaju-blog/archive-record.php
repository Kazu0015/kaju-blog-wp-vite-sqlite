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
				if ( is_tax( 'fruit' ) ) {
					$term = get_queried_object();
					echo esc_html( $term instanceof WP_Term ? $term->name : '' );
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
				$pagination = paginate_links(
					array(
						'type'      => 'array',
						'prev_text' => '<span aria-hidden="true">&lt;</span>',
						'next_text' => '<span aria-hidden="true">&gt;</span>',
					)
				);
				if ( $pagination ) :
					?>
					<nav class="c-pagination" aria-label="<?php esc_attr_e( 'ページナビゲーション', 'kaju-blog' ); ?>">
						<ul class="c-pagination__list">
							<?php foreach ( $pagination as $link ) : ?>
								<li class="c-pagination__item"><?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
			<?php else : ?>
				<p>栽培記録はまだありません。</p>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php
get_footer();
