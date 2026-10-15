<?php
/**
 * Testimonios de pacientes.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$testimonios = get_posts( array( 'post_type' => 'mm_testimonio', 'posts_per_page' => 3, 'orderby' => array( 'menu_order' => 'ASC' ) ) );
if ( ! $testimonios ) {
	return;
}
?>
<section class="band" style="background:var(--surface); border-block:1px solid var(--line)">
	<div class="wrap">
		<div class="sec-head" data-rv>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Pacientes', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Lo que dicen quienes ya nos abrieron la puerta', 'medmovil' ); ?></h2>
			</div>
		</div>
		<div class="tst">
			<?php foreach ( $testimonios as $t ) :
				$autor = get_post_meta( $t->ID, '_mm_autor', true ) ?: $t->post_title;
				$ini   = '';
				foreach ( preg_split( '/\s+/', $autor ) as $palabra ) {
					$ini .= mb_substr( $palabra, 0, 1 );
				}
				?>
				<article class="quote" data-rv>
					<span class="mark">&ldquo;</span>
					<p><?php echo esc_html( wp_strip_all_tags( $t->post_content ) ); ?></p>
					<div class="who">
						<span class="av"><?php echo esc_html( mb_strtoupper( mb_substr( $ini, 0, 2 ) ) ); ?></span>
						<div>
							<b><?php echo esc_html( $autor ); ?></b>
							<span><?php echo esc_html( get_post_meta( $t->ID, '_mm_lugar', true ) ); ?></span>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
