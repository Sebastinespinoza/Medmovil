<?php
/**
 * Página de una prestación.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$id          = get_the_ID();
	$linea       = medmovil_linea_de( $id );
	$color       = medmovil_color_linea( $linea );
	$icono       = get_post_meta( $id, '_mm_icono', true ) ?: 'il-eco';
	$precio      = get_post_meta( $id, '_mm_precio', true );
	$antes       = get_post_meta( $id, '_mm_precio_antes', true );
	$duracion    = get_post_meta( $id, '_mm_duracion', true );
	$edad        = get_post_meta( $id, '_mm_edad', true );
	$resumen     = get_post_meta( $id, '_mm_resumen', true );
	$incluye     = medmovil_lineas( get_post_meta( $id, '_mm_incluye', true ) );
	$ventajas    = medmovil_lineas( get_post_meta( $id, '_mm_ventajas', true ), 2 );
	$condiciones = medmovil_lineas( get_post_meta( $id, '_mm_condiciones', true ), 3 );
	$preparacion = get_post_meta( $id, '_mm_preparacion', true );
	$faq         = medmovil_lineas( get_post_meta( $id, '_mm_faq', true ), 2 );
	$mensaje_wa  = sprintf( __( 'Hola, quiero agendar %s a domicilio.', 'medmovil' ), get_the_title() );
	$colores_fw  = array( '#1B5FA8|#0B3560', '#2E8F63|#14523A', '#14798B|#083B47' );
	?>

	<div class="svc-top">
		<div class="wrap">
			<div class="svc-hero">
				<div>
					<?php
					medmovil_migas( array(
						array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ),
						array( __( 'Prestaciones', 'medmovil' ), get_post_type_archive_link( 'mm_prestacion' ) ),
						$linea ? array( $linea->name, get_term_link( $linea ) ) : null,
						array( get_the_title(), '' ),
					) );
					?>
					<p class="eyebrow" style="margin-top:20px">
						<?php echo esc_html( $linea ? $linea->name : __( 'Prestación', 'medmovil' ) ); ?>
						<?php esc_html_e( 'a domicilio', 'medmovil' ); ?>
					</p>
					<h1><?php the_title(); ?><br><span style="color:var(--ink-2);font-weight:600"><?php esc_html_e( 'en tu casa', 'medmovil' ); ?></span></h1>
					<?php if ( $resumen ) : ?>
						<p class="lede" style="margin-top:20px"><?php echo esc_html( $resumen ); ?></p>
					<?php endif; ?>

					<div class="badge-row">
						<?php if ( $duracion ) : ?>
							<span class="badge"><?php medmovil_icono( 'i-clock' ); ?> <?php echo esc_html( $duracion . ' minutos' ); ?></span>
						<?php endif; ?>
						<span class="badge"><?php medmovil_icono( 'i-doc' ); ?> <?php esc_html_e( 'Informe en 24 horas', 'medmovil' ); ?></span>
						<?php if ( $linea && 'radiologia' !== $linea->slug ) : ?>
							<span class="badge"><?php medmovil_icono( 'i-shield' ); ?> <?php esc_html_e( 'Sin radiación', 'medmovil' ); ?></span>
						<?php endif; ?>
						<?php if ( $edad ) : ?>
							<span class="badge"><?php medmovil_icono( 'i-user' ); ?> <?php echo esc_html( $edad ); ?></span>
						<?php endif; ?>
						<span class="badge"><?php medmovil_icono( 'i-pin' ); ?> <?php echo esc_html( medmovil_opt( 'region', '' ) ); ?></span>
					</div>

					<div class="svc-card" style="--ac:<?php echo esc_attr( $color ); ?>">
						<span class="svc-card-ic"><?php medmovil_icono( $icono, 'ilo' ); ?></span>
						<dl>
							<dt><?php esc_html_e( 'Línea', 'medmovil' ); ?></dt>
							<dt><?php esc_html_e( 'Duración', 'medmovil' ); ?></dt>
							<dt><?php esc_html_e( 'Preparación', 'medmovil' ); ?></dt>
							<dd><?php echo esc_html( $linea ? $linea->name : '—' ); ?></dd>
							<dd><?php echo esc_html( $duracion ? $duracion . ' min' : '—' ); ?></dd>
							<dd><?php echo esc_html( medmovil_etiqueta_ayuno( $preparacion ) ); ?></dd>
						</dl>
					</div>
				</div>

				<div class="svc-side">
					<figure class="svc-shot" style="--ac:<?php echo esc_attr( $color ); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medmovil-card', array( 'class' => 'svc-foto' ) ); ?>
						<?php else : ?>
							<svg class="svc-shot-w" viewBox="0 0 400 220" preserveAspectRatio="none" aria-hidden="true">
								<path d="M-10 120C50 40 90 40 150 120s90 80 150 0 90-80 130 0"/>
								<path d="M-10 150C50 70 90 70 150 150s90 70 150 0 90-70 130 0"/>
							</svg>
							<span class="svc-shot-ic"><?php medmovil_icono( $icono, 'ilo' ); ?></span>
						<?php endif; ?>
						<?php medmovil_boton_subir_imagen( get_the_ID() ); ?>
						<figcaption>
							<span class="svc-shot-tag"><?php echo esc_html( $linea ? $linea->name : __( 'A domicilio', 'medmovil' ) ); ?></span>
							<span class="svc-shot-tag"><?php echo esc_html( $duracion ? $duracion . ' min' : __( 'A domicilio', 'medmovil' ) ); ?></span>
						</figcaption>
					</figure>

					<aside class="buybox">
						<div>
							<p style="font-size:.72rem; color:var(--ink-3); font-family:var(--f-d); font-weight:700; letter-spacing:.11em; text-transform:uppercase">
								<?php esc_html_e( 'Valor particular', 'medmovil' ); ?>
							</p>
							<div class="price">
								<?php echo esc_html( medmovil_precio( $precio ) ); ?>
								<?php if ( $antes ) : ?><span class="strike"><?php echo esc_html( medmovil_precio( $antes ) ); ?></span><?php endif; ?>
								<small><?php esc_html_e( 'todo incluido', 'medmovil' ); ?></small>
							</div>
						</div>
						<?php if ( $incluye ) : ?>
							<ul class="bb-list">
								<?php foreach ( $incluye as $item ) : ?>
									<li><?php medmovil_icono( 'i-check' ); ?> <?php echo esc_html( $item[0] ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<div class="bb-sep"></div>
						<?php medmovil_boton_agregar( $id, 'btn btn-p bb-add' ); ?>
						<a class="btn btn-o" style="justify-content:center" href="<?php echo esc_url( medmovil_url_reserva( $id ) ); ?>">
							<?php medmovil_icono( 'i-cal' ); ?> <?php esc_html_e( 'Solicitar este examen', 'medmovil' ); ?>
						</a>
						<a class="btn btn-g" style="justify-content:center" href="<?php echo esc_url( medmovil_whatsapp( $mensaje_wa ) ); ?>" target="_blank" rel="noopener">
							<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Preguntar por WhatsApp', 'medmovil' ); ?>
						</a>
						<p style="font-size:.8rem; color:var(--ink-3); text-align:center">
							<?php esc_html_e( 'Sin cobro de traslado. Pagas al momento del examen: efectivo, transferencia o tarjeta.', 'medmovil' ); ?>
						</p>
					</aside>
				</div>
			</div>
		</div>
	</div>

	<?php if ( $ventajas ) : ?>
		<section class="band" style="padding-block:60px">
			<div class="wrap">
				<div class="sec-head" data-rv>
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Por qué a domicilio', 'medmovil' ); ?></p>
						<h2 style="margin-top:14px"><?php esc_html_e( 'Salir de casa suele ser lo más difícil del examen', 'medmovil' ); ?></h2>
					</div>
				</div>
				<div class="forwho">
					<?php foreach ( array_slice( $ventajas, 0, 3 ) as $n => $v ) :
						list( $g1, $g2 ) = explode( '|', $colores_fw[ $n % 3 ] );
						?>
						<article class="fw" style="--g1:<?php echo esc_attr( $g1 ); ?>;--g2:<?php echo esc_attr( $g2 ); ?>" data-rv>
							<?php medmovil_icono( array( 'il-elder', 'il-cast', 'il-clockx' )[ $n % 3 ], 'ilo' ); ?>
							<b><?php echo esc_html( $v[0] ); ?></b>
							<p><?php echo esc_html( $v[1] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( get_the_content() ) : ?>
		<section class="band" style="padding-block:0 60px">
			<div class="wrap">
				<div class="prose" data-rv>
					<h2><?php esc_html_e( 'Qué es y qué permite ver', 'medmovil' ); ?></h2>
					<?php the_content(); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $condiciones ) : ?>
		<section class="band" style="background:var(--surface); border-block:1px solid var(--line); padding-block:60px">
			<div class="wrap">
				<div class="sec-head" data-rv>
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Motivos de consulta', 'medmovil' ); ?></p>
						<h2 style="margin-top:14px">
							<?php
							printf(
								/* translators: %s: nombre del examen */
								esc_html__( 'Cuándo tu médico pide %s', 'medmovil' ),
								esc_html( mb_strtolower( mb_substr( get_the_title(), 0, 1 ) ) . mb_substr( get_the_title(), 1 ) )
							);
							?>
						</h2>
						<p class="lede"><?php esc_html_e( 'Si alguno de estos cuadros te suena, es probable que esta sea la primera imagen que te pidan.', 'medmovil' ); ?></p>
					</div>
				</div>
				<div class="cond">
					<?php
					$paleta = array( 'var(--c-img)', 'var(--c-msk)', 'var(--c-dop)', 'var(--c-car)', 'var(--c-lab)', '#A9741A' );
					foreach ( $condiciones as $n => $c ) :
						?>
						<article class="cond-c" style="--cc:<?php echo esc_attr( $paleta[ $n % 6 ] ); ?>" data-rv>
							<span class="tagx"><?php echo esc_html( $c[0] ); ?></span>
							<b><?php echo esc_html( $c[1] ); ?></b>
							<p><?php echo esc_html( $c[2] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="band" style="padding-block:60px">
		<div class="wrap">
			<?php if ( $preparacion ) : ?>
				<div class="prep" style="margin-top:0; margin-bottom:48px" data-rv>
					<b><?php medmovil_icono( 'i-info' ); ?> <?php esc_html_e( 'Preparación', 'medmovil' ); ?></b>
					<p style="color:var(--ink-2); font-size:.95rem"><?php echo esc_html( $preparacion ); ?></p>
				</div>
			<?php endif; ?>

			<div class="sec-head" data-rv>
				<div>
					<p class="eyebrow"><?php esc_html_e( 'La visita', 'medmovil' ); ?></p>
					<h2 style="margin-top:14px"><?php esc_html_e( 'Cómo es, paso a paso', 'medmovil' ); ?></h2>
				</div>
			</div>
			<div class="walk">
				<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'il-carhome', 'ilo' ); ?></div>
					<b><?php esc_html_e( 'Llega el profesional', 'medmovil' ); ?></b>
					<p><?php esc_html_e( 'Con el equipo portátil, dentro del bloque horario que reservaste.', 'medmovil' ); ?></p></div>
				<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'il-portal', 'ilo' ); ?></div>
					<b><?php esc_html_e( 'Revisa tu orden', 'medmovil' ); ?></b>
					<p><?php esc_html_e( 'Confirma la indicación médica y tus antecedentes antes de empezar.', 'medmovil' ); ?></p></div>
				<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( $icono, 'ilo' ); ?></div>
					<b><?php esc_html_e( 'Realiza el examen', 'medmovil' ); ?></b>
					<p><?php echo esc_html( sprintf( __( 'En unos %s minutos, en la posición que te resulte cómoda.', 'medmovil' ), $duracion ? $duracion : '25' ) ); ?></p></div>
				<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'il-doc', 'ilo' ); ?></div>
					<b><?php esc_html_e( 'Envía el estudio', 'medmovil' ); ?></b>
					<p><?php esc_html_e( 'Las imágenes van al médico informante el mismo día.', 'medmovil' ); ?></p></div>
				<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'il-shield', 'ilo' ); ?></div>
					<b><?php esc_html_e( 'Recibes el informe', 'medmovil' ); ?></b>
					<p><?php esc_html_e( 'En PDF, en menos de 24 horas.', 'medmovil' ); ?></p></div>
			</div>
		</div>
	</section>

	<section class="band" style="padding-block:0 60px">
		<div class="wrap">
			<div class="vs" data-rv>
				<div class="vs-col">
					<div class="vs-h" style="color:var(--blue)"><?php medmovil_icono( 'i-home' ); ?> <?php esc_html_e( 'En tu casa, con MedMóvil', 'medmovil' ); ?></div>
					<ul class="vs-yes">
						<li><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Hora reservada: el examen empieza cuando dijimos', 'medmovil' ); ?></li>
						<li><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Cero traslados, cero estacionamiento, cero acompañante pidiendo permiso en el trabajo', 'medmovil' ); ?></li>
						<li><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Informe en menos de 24 horas, sin volver a retirarlo', 'medmovil' ); ?></li>
						<li><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Precio publicado, sin cobro de traslado', 'medmovil' ); ?></li>
					</ul>
				</div>
				<div class="vs-col">
					<div class="vs-h" style="color:var(--ink-3)"><?php medmovil_icono( 'i-clock' ); ?> <?php esc_html_e( 'El camino de siempre', 'medmovil' ); ?></div>
					<ul class="vs-no">
						<li><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'Llegar 40 minutos antes y esperar de pie', 'medmovil' ); ?></li>
						<li><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'Trasladarse justo cuando el cuerpo no ayuda', 'medmovil' ); ?></li>
						<li><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'Volver otro día a retirar el informe impreso', 'medmovil' ); ?></li>
						<li><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'Buscar el sobre con los exámenes viejos en la casa', 'medmovil' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $faq ) : ?>
		<section class="band" style="background:var(--surface); border-block:1px solid var(--line); padding-block:60px">
			<div class="wrap">
				<p class="eyebrow" data-rv><?php esc_html_e( 'Preguntas frecuentes', 'medmovil' ); ?></p>
				<h2 style="margin:14px 0 26px" data-rv><?php esc_html_e( 'Lo que más nos preguntan sobre este examen', 'medmovil' ); ?></h2>
				<?php medmovil_faq( $faq ); ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="band" style="padding-block:60px">
		<div class="wrap">
			<div class="cta-band" data-rv>
				<svg class="cta-wave" viewBox="0 0 1200 300" preserveAspectRatio="none" aria-hidden="true">
					<path d="M-20 210C120 60 220 60 380 210s220 150 400 0 240-150 440 0"/>
				</svg>
				<div style="position:relative; z-index:2">
					<p class="eyebrow" style="color:var(--blue-bright)"><?php esc_html_e( 'Agenda hoy', 'medmovil' ); ?></p>
					<h2 style="margin-top:14px"><?php echo esc_html( sprintf( __( '%s, esta misma semana', 'medmovil' ), get_the_title() ) ); ?></h2>
					<p><?php esc_html_e( 'Escríbenos y coordinamos el bloque que te acomode. Pagas al momento del examen: efectivo, transferencia o tarjeta.', 'medmovil' ); ?></p>
				</div>
				<div style="position:relative; z-index:2; display:flex; flex-direction:column; gap:11px">
					<a class="btn btn-w" style="justify-content:center" href="<?php echo esc_url( medmovil_url_agenda( $mensaje_wa ) ); ?>">
						<?php medmovil_icono( 'i-cal' ); ?> <?php esc_html_e( 'Agendar ahora', 'medmovil' ); ?>
					</a>
					<a class="btn btn-g" style="justify-content:center" href="<?php echo esc_url( medmovil_whatsapp( $mensaje_wa ) ); ?>" target="_blank" rel="noopener">
						<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Escribir por WhatsApp', 'medmovil' ); ?>
					</a>
					<p style="font-size:.8rem; color:var(--on-dark-3); text-align:center">
						<?php echo esc_html( sprintf( __( 'Valor %s · Sin cobro de traslado', 'medmovil' ), medmovil_precio( $precio ) ) ); ?>
					</p>
				</div>
			</div>

			<?php
			$relacionadas = $linea ? get_posts( array(
				'post_type'      => 'mm_prestacion',
				'posts_per_page' => 4,
				'post__not_in'   => array( $id ),
				'orderby'        => array( 'menu_order' => 'ASC' ),
				'tax_query'      => array( array( 'taxonomy' => 'mm_linea', 'field' => 'term_id', 'terms' => $linea->term_id ) ),
			) ) : array();
			if ( $relacionadas ) :
				?>
				<div style="margin-top:48px">
					<h3 style="margin-bottom:18px"><?php esc_html_e( 'Otros exámenes de esta línea', 'medmovil' ); ?></h3>
					<div class="rel">
						<?php foreach ( $relacionadas as $r ) : ?>
							<a href="<?php echo esc_url( get_permalink( $r ) ); ?>">
								<b><?php echo esc_html( $r->post_title ); ?></b>
								<span><?php echo esc_html( get_post_meta( $r->ID, '_mm_duracion', true ) . ' min · ' . medmovil_precio( get_post_meta( $r->ID, '_mm_precio', true ) ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php
			$comunas_top = medmovil_comunas( 8 );
			if ( $comunas_top ) :
				?>
				<div style="margin-top:44px">
					<h3 style="margin-bottom:14px">
						<?php echo esc_html( sprintf( __( '%s a domicilio por comuna', 'medmovil' ), get_the_title() ) ); ?>
					</h3>
					<div class="comunas">
						<?php foreach ( $comunas_top as $c ) : ?>
							<a class="comuna" href="<?php echo esc_url( get_permalink( $c ) ); ?>">
								<?php medmovil_icono( 'i-pin' ); ?> <?php echo esc_html( $c->post_title ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<div class="buybar" id="buybar">
		<div>
			<b><?php echo esc_html( medmovil_precio( $precio ) ); ?></b>
			<span><?php echo esc_html( get_the_title() . ( $duracion ? ' · ' . $duracion . ' min' : '' ) ); ?></span>
		</div>
		<?php medmovil_boton_agregar( $id, 'btn btn-p btn-sm', __( 'Agregar', 'medmovil' ) ); ?>
	</div>

<?php
endwhile;

get_footer();
