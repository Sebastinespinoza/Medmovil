<?php
/** Bloque comercial compacto y secciones originales. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
get_template_part( 'partials/storefront' );
?>
<section class="strip">
	<div class="wrap">
		<div class="strip-i"><?php medmovil_icono( 'il-nurse', 'ilo' ); ?>
			<div><b><?php esc_html_e( 'En tu domicilio', 'medmovil' ); ?></b>
			<span><?php esc_html_e( 'Atendemos en casa, en el trabajo o en el lugar de reposo del paciente.', 'medmovil' ); ?></span></div></div>
		<div class="strip-i"><?php medmovil_icono( 'il-doc', 'ilo' ); ?>
			<div><b><?php esc_html_e( 'Informe médico incluido', 'medmovil' ); ?></b>
			<span><?php esc_html_e( 'Validado por médico radiólogo y disponible en menos de 24 horas.', 'medmovil' ); ?></span></div></div>
		<div class="strip-i"><?php medmovil_icono( 'il-shield', 'ilo' ); ?>
			<div><b><?php esc_html_e( 'Profesionales acreditados', 'medmovil' ); ?></b>
			<span><?php esc_html_e( 'Con registro vigente en la Superintendencia de Salud.', 'medmovil' ); ?></span></div></div>
		<div class="strip-i"><?php medmovil_icono( 'il-carhome', 'ilo' ); ?>
			<div><b><?php echo esc_html( sprintf( __( 'Toda la %s', 'medmovil' ), medmovil_opt( 'region', 'Región Metropolitana' ) ) ); ?></b>
			<span><?php echo esc_html( medmovil_opt( 'horario', '' ) ); ?></span></div></div>
	</div>
</section>

<section class="band" id="prestaciones">
	<div class="wrap">
		<div class="sec-head" data-rv>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Catálogo', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Todo lo que llevamos hasta la puerta de tu casa', 'medmovil' ); ?></h2>
				<p class="lede"><?php esc_html_e( 'Ocho líneas de atención, con el mismo equipamiento que encontrarías en un centro médico, montado sobre equipos portátiles.', 'medmovil' ); ?></p>
			</div>
			<a class="btn btn-o" href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>">
				<?php esc_html_e( 'Ver todo el catálogo', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
			</a>
		</div>

		<div class="rail-head">
			<p class="fl-cuenta" style="margin:0">
				<?php
				printf(
					/* translators: %d: cantidad de exámenes */
					esc_html__( 'Explora por línea de atención · %d exámenes en total', 'medmovil' ),
					(int) wp_count_posts( 'mm_prestacion' )->publish
				);
				?>
			</p>
			<div class="rail-nav" id="railNav">
				<button type="button" class="prev" aria-label="<?php esc_attr_e( 'Anterior', 'medmovil' ); ?>"><?php medmovil_icono( 'i-arr' ); ?></button>
				<button type="button" class="next" aria-label="<?php esc_attr_e( 'Siguiente', 'medmovil' ); ?>"><?php medmovil_icono( 'i-arr' ); ?></button>
			</div>
		</div>

		<div class="rail" id="rail">
			<?php
			foreach ( get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => true ) ) as $linea_r ) :
				$icono_r = medmovil_icono_linea( $linea_r );
				?>
				<a class="rail-i" href="<?php echo esc_url( get_term_link( $linea_r ) ); ?>"
				   style="--ac:<?php echo esc_attr( medmovil_color_linea( $linea_r ) ); ?>">
					<span class="rail-ic"><?php medmovil_icono( $icono_r, 'ilo' ); ?></span>
					<b><?php echo esc_html( $linea_r->name ); ?></b>
					<span>
						<?php
						printf(
							/* translators: %d: cantidad */
							esc_html( _n( '%d examen', '%d exámenes', $linea_r->count, 'medmovil' ) ),
							(int) $linea_r->count
						);
						?>
					</span>
				</a>
			<?php endforeach; ?>
			<a class="rail-i" href="<?php echo esc_url( home_url( '/cotizador/' ) ); ?>" style="--ac:var(--ok)">
				<span class="rail-ic"><?php medmovil_icono( 'i-coin', 'ilo' ); ?></span>
				<b><?php esc_html_e( 'Cotizador', 'medmovil' ); ?></b>
				<span><?php esc_html_e( 'arma tu total', 'medmovil' ); ?></span>
			</a>
		</div>

		<div class="cats" style="margin-top:26px">
			<?php
			foreach ( get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => true ) ) as $linea ) :
				$icono = medmovil_icono_linea( $linea );
				$ejemplos = get_posts( array(
					'post_type'      => 'mm_prestacion',
					'posts_per_page' => 4,
					'orderby'        => array( 'menu_order' => 'ASC' ),
					'tax_query'      => array( array( 'taxonomy' => 'mm_linea', 'field' => 'term_id', 'terms' => $linea->term_id ) ),
				) );
				?>
				<a class="cat" style="--ac:<?php echo esc_attr( medmovil_color_linea( $linea ) ); ?>"
				   href="<?php echo esc_url( get_term_link( $linea ) ); ?>" data-rv>
					<span class="cat-top">
						<span class="cat-ic"><?php medmovil_icono( $icono, 'ilo' ); ?></span>
						<h3><?php echo esc_html( $linea->name ); ?></h3>
					</span>
					<p><?php echo esc_html( $linea->description ); ?></p>
					<ul>
						<?php foreach ( $ejemplos as $e ) : ?>
							<li><?php echo esc_html( wp_trim_words( str_replace( array( 'Ecografía de ', 'Ecografía ', 'Radiografía de ', 'Doppler ' ), '', $e->post_title ), 3, '' ) ); ?></li>
						<?php endforeach; ?>
					</ul>
					<span class="cat-go">
						<?php
						printf(
							/* translators: %d: cantidad de exámenes */
							esc_html( _n( 'Ver %d examen', 'Ver los %d exámenes', $linea->count, 'medmovil' ) ),
							(int) $linea->count
						);
						?>
						<?php medmovil_icono( 'i-arr' ); ?>
					</span>
					<span class="cat-wave"><svg viewBox="0 0 300 22" preserveAspectRatio="none"><path d="M0 11C36 0 54 0 90 11s54 11 90 0 54-11 120 0"/></svg></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php medmovil_selector_cuerpo(); ?>

<section class="band steps-band" id="como">
	<svg class="route" viewBox="0 0 1200 400" preserveAspectRatio="none" aria-hidden="true">
		<path d="M-20 330C180 330 220 90 420 90s240 240 440 240 240-190 400-190"/>
	</svg>
	<div class="wrap">
		<div class="sec-head" style="margin-bottom:40px">
			<div>
				<p class="eyebrow" style="color:var(--blue-bright)"><?php esc_html_e( 'Cómo funciona', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Cuatro pasos y listo.', 'medmovil' ); ?><br>
					<span class="serif" style="color:var(--blue-bright)"><?php esc_html_e( 'sin salir de casa', 'medmovil' ); ?></span></h2>
			</div>
			<p class="lede" style="max-width:38ch"><?php esc_html_e( 'Pensado para que lo pueda hacer un adulto mayor solo, o un hijo desde el trabajo.', 'medmovil' ); ?></p>
		</div>
		<div class="steps">
			<article class="step">
				<div class="step-n"><?php esc_html_e( 'Paso 01', 'medmovil' ); ?></div>
				<?php medmovil_icono( 'il-pick', 'ilo' ); ?>
				<h3><?php esc_html_e( 'Eliges tu examen', 'medmovil' ); ?></h3>
				<p><?php esc_html_e( 'Ves de inmediato el precio, la duración y la preparación que necesita.', 'medmovil' ); ?></p>
			</article>
			<article class="step">
				<div class="step-n"><?php esc_html_e( 'Paso 02', 'medmovil' ); ?></div>
				<?php medmovil_icono( 'il-book', 'ilo' ); ?>
				<h3><?php esc_html_e( 'Reservas tu hora', 'medmovil' ); ?></h3>
				<p><?php esc_html_e( 'Escoges el bloque que te acomode según la disponibilidad en tu comuna.', 'medmovil' ); ?></p>
			</article>
			<article class="step">
				<div class="step-n"><?php esc_html_e( 'Paso 03', 'medmovil' ); ?></div>
				<?php medmovil_icono( 'il-arrive', 'ilo' ); ?>
				<h3><?php esc_html_e( 'Llegamos a tu domicilio', 'medmovil' ); ?></h3>
				<p><?php esc_html_e( 'El profesional llega con el equipo portátil. Pagas al momento del examen.', 'medmovil' ); ?></p>
			</article>
			<article class="step">
				<div class="step-n"><?php esc_html_e( 'Paso 04', 'medmovil' ); ?></div>
				<?php medmovil_icono( 'il-report', 'ilo' ); ?>
				<h3><?php esc_html_e( 'Recibes tu informe', 'medmovil' ); ?></h3>
				<p><?php esc_html_e( 'En menos de 24 horas, en PDF, firmado por médico radiólogo.', 'medmovil' ); ?></p>
			</article>
		</div>
	</div>
</section>

<?php
$packs = get_posts( array( 'post_type' => 'mm_pack', 'posts_per_page' => 4, 'orderby' => array( 'menu_order' => 'ASC' ) ) );
if ( $packs ) :
	?>
	<section class="band">
		<div class="wrap">
			<div class="sec-head" data-rv>
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Chequeos completos', 'medmovil' ); ?></p>
					<h2 style="margin-top:14px"><?php esc_html_e( 'Packs pensados para quien no puede salir', 'medmovil' ); ?></h2>
					<p class="lede"><?php esc_html_e( 'Un solo agendamiento, una sola visita, y un precio menor que contratando cada examen por separado.', 'medmovil' ); ?></p>
				</div>
			</div>
			<div class="packs">
				<?php foreach ( $packs as $pack ) :
					$destacado = get_post_meta( $pack->ID, '_mm_destacado', true );
					$antes     = get_post_meta( $pack->ID, '_mm_precio_antes', true );
					?>
					<article class="pack<?php echo $destacado ? ' feat' : ''; ?>" data-rv>
						<span class="pack-tag"><?php echo esc_html( get_post_meta( $pack->ID, '_mm_etiqueta', true ) ); ?></span>
						<h3><?php echo esc_html( $pack->post_title ); ?></h3>
						<ul>
							<?php foreach ( medmovil_lineas( get_post_meta( $pack->ID, '_mm_incluye', true ) ) as $item ) : ?>
								<li><?php medmovil_icono( 'i-check' ); ?> <?php echo esc_html( $item[0] ); ?></li>
							<?php endforeach; ?>
						</ul>
						<div class="price">
							<?php echo esc_html( medmovil_precio( get_post_meta( $pack->ID, '_mm_precio', true ) ) ); ?>
							<?php if ( $antes ) : ?><span class="strike"><?php echo esc_html( medmovil_precio( $antes ) ); ?></span><?php endif; ?>
						</div>
						<a class="btn <?php echo $destacado ? 'btn-p' : 'btn-o'; ?>"
						   href="<?php echo esc_url( medmovil_url_agenda( sprintf( __( 'Hola, quiero agendar el %s.', 'medmovil' ), $pack->post_title ) ) ); ?>">
							<?php esc_html_e( 'Agendar este pack', 'medmovil' ); ?>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php get_template_part( 'partials/cobertura' ); ?>
<?php get_template_part( 'partials/precios' ); ?>
<?php get_template_part( 'partials/testimonios' ); ?>
<?php get_template_part( 'partials/instagram' ); ?>
<?php get_template_part( 'partials/convenios' ); ?>

<?php
get_footer();
