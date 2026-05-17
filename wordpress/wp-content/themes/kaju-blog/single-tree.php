<?php
/**
 * 庭の木 詳細（スケルトン）
 *
 * 今後 ACF プロフィール（植えた年・台木・受粉樹・開花期・収穫期など）の表示を実装する。
 *
 * @package kaju-blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$archive = get_post_type_archive_link( 'tree' ) ?: home_url( '/trees/' );
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
				<h1 class="single-header__title"><?php the_title(); ?></h1>
			</header>

			<div class="single-intro">
				<?php the_content(); ?>
			</div>

			<div class="single-back">
				<a href="<?php echo esc_url( $archive ); ?>" class="single-back__link">← 庭の木一覧に戻る</a>
			</div>
		</article>
	</div>
</main>

	<?php
endwhile;

get_footer();
