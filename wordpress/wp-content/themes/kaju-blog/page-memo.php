<?php
/**
 * 作業メモ一覧（固定ページ memo）
 *
 * @package kaju-blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$memo_query = new WP_Query(
		array(
			'post_type'      => 'work_memo',
			'posts_per_page' => 12,
			'paged'          => max( 1, (int) get_query_var( 'paged', 1 ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
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
					array( 'label' => '作業メモ' ),
				),
			)
		);
		?>

		<header class="record-header">
			<h1 class="record-header__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( get_the_content() ) : ?>
			<div class="memo-list__lead l-container-s">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

		<section class="record-list memo-list" aria-labelledby="memo-list-heading">
			<h2 id="memo-list-heading" class="u-visually-hidden">作業メモ一覧</h2>
			<?php if ( $memo_query->have_posts() ) : ?>
				<ul class="record-list__card-list memo-list__grid">
					<?php
					while ( $memo_query->have_posts() ) :
						$memo_query->the_post();
						?>
						<li class="record-list__card-item">
							<?php
							get_template_part(
								'template-parts/memo',
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
						'total'     => (int) $memo_query->max_num_pages,
						'current'   => max( 1, (int) get_query_var( 'paged', 1 ) ),
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
				<p class="memo-list__empty">作業メモはまだありません。</p>
			<?php endif; ?>
		</section>
	</div>
</main>

	<?php
	wp_reset_postdata();
endwhile;

get_footer();
