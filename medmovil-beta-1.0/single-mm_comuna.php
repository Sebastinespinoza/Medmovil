<?php
/**
 * Página de una comuna — SEO local.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$id       = get_the_ID();
	$comuna   = get_the_title();
	$minutos  = (int) get_post_meta( $id, '_mm_tiempo', true );
	$zona     = get_post_meta( $id, '_mm_zona', true );
	$sectores = get_post_meta( $id, '_mm_sectores', true );
	$etiqueta = array(
		'a' => __( 'Atendemos todos los días', 'medmovil' ),
		'b' => __( 'Cobertura varios días por semana', 'medmovil' ),
		'c' => __( 'Cobertura coordinada a pedido', 'medmovil' ),
	);
	$mensaje = sprintf( __( 'Hola, necesito un examen a domicilio en %s.', 'medmovil' ), $comuna );
	?>

	<div class="svc-top">
		<div class="wrap" style="padding-block:34px 50px">
			<?php
			medmovil_migas( array(
				array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ),
				array( __( 'Cobertura', 'medmovil' ), get_permalink( get_page_by_path( 'cobertura' ) ) ),
				array( $comuna, '' ),
			) );
			?>
			<p class="eyebrow" style="margin-top:20px"><?php echo esc_html( medmovil_opt( 'region', '' ) ); ?></p>
			<h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:14px">
				<?php echo esc_html( sprintf( __( 'Exámenes a domicilio en %s', 'medmovil' ), $comuna ) ); ?>
			</h1>
			<div class="badge-row">
				<span class="badge"><?php medmovil_icono( 'i-clock' ); ?> <?php echo esc_html( sprintf( __( 'Llegada estimada %d minutos', 'medmovil' ), $minutos ) ); ?></span>
				<span class="badge"><?php medmovil_icono( 'i-cal' ); ?> <?php echo esc_html( $etiqueta[ $zona ] ?? $etiqueta['c'] ); ?></span>
				<span class="badge"><?php medmovil_icono( 'i-doc' ); ?> <?php esc_html_e( 'Informe en 24 horas', 'medmovil' ); ?></span>
			</div>
			<div class="prose" style="margin-top:26px"><?php the_content(); ?></div>
			<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:26px">
				<a class="btn btn-p" href="<?php echo esc_url( medmovil_url_agenda( $mensaje ) ); ?>">
					<?php medmovil_icono( 'i-cal' ); ?> <?php echo esc_html( sprintf( __( 'Agendar en %s', 'medmovil' ), $comuna ) ); ?>
				</a>
				<a class="btn btn-g" href="<?php echo esc_url( medmovil_whatsapp( $mensaje ) ); ?>" target="_blank" rel="noopener">
					<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Consultar por WhatsApp', 'medmovil' ); ?>
				</a>
			</div>
		</div>
	</div>

	<section class="band">
		<div class="wrap">
			<div class="sec-head" data-rv>
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Disponible en tu comuna', 'medmovil' ); ?></p>
					<h2 style="margin-top:14px"><?php echo esc_html( sprintf( __( 'Qué realizamos en %s', 'medmovil' ), $comuna ) ); ?></h2>
					<p class="lede"><?php esc_html_e( 'Todo el catálogo está disponible. Estos son los exámenes que más se solicitan.', 'medmovil' ); ?></p>
				</div>
			</div>
			<div class="cats">
				<?php
				foreach ( get_posts( array(
					'post_type'      => 'mm_prestacion',
					'posts_per_page' => 6,
					'orderby'        => array( 'menu_order' => 'ASC' ),
				) ) as $p ) {
					medmovil_tarjeta_prestacion( $p->ID );
				}
				?>
			</div>
		</div>
	</section>

	<?php if ( $sectores ) : ?>
		<section class="band" style="background:var(--surface);border-block:1px solid var(--line);padding-block:50px">
			<div class="wrap">
				<h3 style="margin-bottom:12px"><?php echo esc_html( sprintf( __( 'Sectores de %s donde atendemos', 'medmovil' ), $comuna ) ); ?></h3>
				<p class="lede"><?php echo esc_html( $sectores ); ?><?php esc_html_e( ', y el resto de la comuna. Si tu dirección es difícil de encontrar, indícalo al agendar y coordinamos las referencias con el profesional.', 'medmovil' ); ?></p>
			</div>
		</section>
	<?php endif; ?>

	<section class="band">
		<div class="wrap">
			<h3 style="margin-bottom:16px"><?php esc_html_e( 'Otras comunas con cobertura', 'medmovil' ); ?></h3>
			<div class="comunas">
				<?php
				foreach ( get_posts( array(
					'post_type'      => 'mm_comuna',
					'posts_per_page' => 20,
					'post__not_in'   => array( $id ),
					'orderby'        => array( 'menu_order' => 'ASC' ),
				) ) as $c ) :
					?>
					<a class="comuna" href="<?php echo esc_url( get_permalink( $c ) ); ?>">
						<?php medmovil_icono( 'i-pin' ); ?> <?php echo esc_html( $c->post_title ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php medmovil_bloque_profesionales( $comuna ); ?>

<?php
endwhile;
get_footer();
