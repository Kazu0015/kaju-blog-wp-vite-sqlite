<?php
/**
 * プライバシーポリシー固定ページ
 *
 * @package kaju-blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>

<main>
	<div class="l-container-s">
		<?php
		get_template_part(
			'template-parts/breadcrumb',
			null,
			array(
				'prefix' => 'privacy-policy-breadcrumb',
				'items'  => array(
					array(
						'label' => 'ホーム',
						'url'   => home_url( '/' ),
					),
					array( 'label' => get_the_title() ),
				),
			)
		);
		?>

		<article class="privacy-policy-content">
			<div class="privacy-policy-content__card">
				<?php
				get_template_part(
					'template-parts/privacy-policy/card',
					null,
					array(
						'title'       => get_the_title(),
						'contact_url' => kaju_blog_page_url( 'contact' ),
						'home_url'    => home_url( '/' ),
					)
				);
				?>
			</div>
		</article>
	</div>
</main>

	<?php
endwhile;

get_footer();
