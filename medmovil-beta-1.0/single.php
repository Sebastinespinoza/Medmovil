<?php
/**
 * Entrada del blog.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$blog = get_page_by_path( 'blog' );
	$url_blog = $blog ? get_permalink( $blog ) : home_url( '/' );
	$cats = get_the_category();
	$relacionadas = medmovil_prestaciones_del_articulo( get_the_ID() );
	?>
	<div class="svc-top">
		<div class="wrap" style="padding-block:34px 40px">
			<?php
			medmovil_migas( array(
				array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ),
				array( __( 'Blog', 'medmovil' ), $url_blog ),
				$cats ? array( $cats[0]->name, add_query_arg( 'tema', $cats[0]->slug, $url_blog ) ) : null,
				array( get_the_title(), '' ),
			) );
			?>

			<p class="eyebrow" style="margin-top:20px">
				<?php echo esc_html( $cats ? $cats[0]->name : __( 'Artículo', 'medmovil' ) ); ?>
			</p>
			<h1 style="font-size:clamp(1.9rem,3.6vw,2.7rem);margin-top:14px"><?php the_title(); ?></h1>

			<p class="bmeta">
				<span><?php medmovil_icono( 'i-clock' ); ?>
					<?php
					printf(
						/* translators: %d: minutos */
						esc_html__( '%d min de lectura', 'medmovil' ),
						(int) medmovil_minutos_lectura()
					);
					?>
				</span>
				<span><?php medmovil_icono( 'i-cal' ); ?> <?php echo esc_html( get_the_date() ); ?></span>
				<span><?php medmovil_icono( 'i-shield' ); ?> <?php esc_html_e( 'Revisado por tecnólogo médico', 'medmovil' ); ?></span>
			</p>
		</div>
	</div>

	<section class="band" style="padding-block:44px 60px">
		<div class="wrap bwrap">
			<article>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="bhero"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>

				<div class="prose"><?php the_content(); ?></div>

				<?php if ( has_tag() ) : ?>
					<div class="btags"><?php the_tags( '', '' ); ?></div>
				<?php endif; ?>

				<p class="bdisc">
					<?php medmovil_icono( 'i-info' ); ?>
					<span><?php esc_html_e( 'Este artículo es informativo y no reemplaza la evaluación de tu médico. Los exámenes se realizan con indicación médica.', 'medmovil' ); ?></span>
				</p>
			</article>

			<aside class="baside">
				<?php if ( $relacionadas ) : ?>
					<div class="bbox">
						<b><?php esc_html_e( 'Exámenes de este artículo', 'medmovil' ); ?></b>
						<p><?php esc_html_e( 'Los hacemos en tu casa, con informe en menos de 24 horas.', 'medmovil' ); ?></p>
						<?php foreach ( $relacionadas as $p ) :
							$linea = medmovil_linea_de( $p->ID );
							?>
							<a class="brel" href="<?php echo esc_url( get_permalink( $p ) ); ?>"
							   style="--ac:<?php echo esc_attr( medmovil_color_linea( $linea ) ); ?>">
								<span class="brel-ic"><?php medmovil_icono( get_post_meta( $p->ID, '_mm_icono', true ) ?: 'il-eco', 'ilo' ); ?></span>
								<span>
									<b><?php echo esc_html( $p->post_title ); ?></b>
									<span><?php echo esc_html( medmovil_precio( get_post_meta( $p->ID, '_mm_precio', true ) ) ); ?></span>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="bbox bbox-cta">
					<b><?php esc_html_e( '¿Te lo pidieron y no puedes salir?', 'medmovil' ); ?></b>
					<p><?php esc_html_e( 'Vamos a tu casa con el equipo portátil. Escríbenos y coordinamos el bloque que te acomode.', 'medmovil' ); ?></p>
					<a class="btn btn-p" style="justify-content:center" href="<?php echo esc_url( medmovil_url_agenda() ); ?>">
						<?php medmovil_icono( 'i-cal' ); ?> <?php esc_html_e( 'Agendar a domicilio', 'medmovil' ); ?>
					</a>
					<a class="btn btn-o" style="justify-content:center" href="<?php echo esc_url( home_url( '/cotizador/' ) ); ?>">
						<?php esc_html_e( 'Ver precios', 'medmovil' ); ?>
					</a>
				</div>
			</aside>
		</div>
	</section>

	<?php
	$otras = get_posts( array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array( get_the_ID() ),
		'category__in'   => $cats ? array( $cats[0]->term_id ) : array(),
	) );
	if ( ! $otras ) {
		$otras = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ) ) );
	}
	if ( $otras ) :
		?>
		<section class="band" style="background:var(--surface);border-block:1px solid var(--line);padding-block:56px">
			<div class="wrap">
				<div class="sec-head" data-rv>
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Seguir leyendo', 'medmovil' ); ?></p>
						<h2 style="margin-top:14px"><?php esc_html_e( 'Otros artículos', 'medmovil' ); ?></h2>
					</div>
					<a class="btn btn-o" href="<?php echo esc_url( $url_blog ); ?>">
						<?php esc_html_e( 'Ver el blog', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
					</a>
				</div>
				<div class="bgrid">
					<?php foreach ( $otras as $o ) { medmovil_tarjeta_entrada( $o ); } ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

<?php
endwhile;

get_footer();
