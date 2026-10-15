<?php
/**
 * Listado por defecto.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="svc-top">
	<div class="wrap" style="padding-block:34px 40px">
		<h1 style="font-size:clamp(2rem,4vw,3rem)">
			<?php
			if ( is_search() ) {
				echo esc_html( sprintf( __( 'Resultados para "%s"', 'medmovil' ), get_search_query() ) );
			} elseif ( is_archive() ) {
				the_archive_title();
			} else {
				esc_html_e( 'Últimas publicaciones', 'medmovil' );
			}
			?>
		</h1>
	</div>
</div>
<section class="band" style="padding-block:44px 70px">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="cats">
				<?php while ( have_posts() ) : the_post(); ?>
					<a class="cat" style="--ac:var(--blue)" href="<?php the_permalink(); ?>">
						<span class="cat-top"><h3><?php the_title(); ?></h3></span>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<span class="cat-go"><?php esc_html_e( 'Leer', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?></span>
						<span class="cat-wave"><svg viewBox="0 0 300 22" preserveAspectRatio="none"><path d="M0 11C36 0 54 0 90 11s54 11 90 0 54-11 120 0"/></svg></span>
					</a>
				<?php endwhile; ?>
			</div>
			<div style="margin-top:32px"><?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?></div>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'No encontramos nada con esa búsqueda. Prueba con el nombre del examen, por ejemplo "ecografía abdominal".', 'medmovil' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
