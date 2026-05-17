<?php
/**
 * 庭の木一覧
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
					array( 'label' => '庭の木' ),
				),
			)
		);
		?>

		<?php get_template_part( 'template-parts/record', 'filter' ); ?>

		<header class="record-header tree-archive__header">
			<h1 class="record-header__title"><?php post_type_archive_title(); ?></h1>
			<p class="tree-archive__lead">庭に植わっている木々の素性をまとめています。</p>
		</header>

		<section class="record-list tree-archive" aria-labelledby="tree-archive-heading">
			<h2 id="tree-archive-heading" class="u-visually-hidden">庭の木一覧</h2>
			<?php if ( have_posts() ) : ?>
				<ul class="record-list__card-list tree-archive__card-list">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<li class="record-list__card-item">
							<?php
							get_template_part(
								'template-parts/tree',
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
				<p class="tree-archive__empty">庭の木はまだ登録されていません。</p>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php
get_footer();
