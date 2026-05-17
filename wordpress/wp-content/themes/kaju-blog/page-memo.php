<?php
/**
 * 作業メモ一覧（固定ページ memo）
 *
 * @package kaju-blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$memo_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$memo_query = new WP_Query(
		array(
			'post_type'      => 'work_memo',
			'posts_per_page' => kaju_blog_archive_posts_per_page(),
			'paged'          => $memo_paged,
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
				get_template_part(
					'template-parts/pagination',
					null,
					array(
						'paginate_args' => kaju_blog_build_paginate_args(
							(int) $memo_query->max_num_pages,
							$memo_paged
						),
					)
				);
				?>
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
