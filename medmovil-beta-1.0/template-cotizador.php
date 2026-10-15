<?php
/**
 * Template Name: Cotizador
 *
 * Presupuesto en una sola página: el paciente marca los exámenes que necesita,
 * ve el total y se lo lleva por WhatsApp o impreso. No hay valores Fonasa —
 * MedMóvil trabaja solo con valores particulares—, pero sí se puede buscar por
 * el código que trae impresa la orden médica.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$lineas = get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => true ) );
$total  = 0;
$grupos = array();

foreach ( $lineas as $linea ) {
	$items = get_posts( array(
		'post_type'      => 'mm_prestacion',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'tax_query'      => array( array( 'taxonomy' => 'mm_linea', 'field' => 'term_id', 'terms' => $linea->term_id ) ),
	) );
	if ( ! $items ) {
		continue;
	}
	$grupos[] = array( 'linea' => $linea, 'items' => $items );
	$total   += count( $items );
}

$desde = 0;
foreach ( $grupos as $g ) {
	foreach ( $g['items'] as $p ) {
		$v = (int) get_post_meta( $p->ID, '_mm_precio', true );
		if ( $v && ( ! $desde || $v < $desde ) ) {
			$desde = $v;
		}
	}
}
?>

<section class="cz-hero">
	<div class="wrap">
		<?php medmovil_migas( array( array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ), array( get_the_title(), '' ) ) ); ?>

		<span class="cz-badge"><?php medmovil_icono( 'i-coin' ); ?> <?php esc_html_e( 'Cotizador MedMóvil', 'medmovil' ); ?></span>
		<h1><?php esc_html_e( 'Arma tu presupuesto antes de agendar', 'medmovil' ); ?></h1>
		<p class="lede">
			<?php esc_html_e( 'Marca los exámenes que necesitas y ve el total al instante. Puedes buscarlos por nombre, por zona del cuerpo o escribiendo el código que trae tu orden médica.', 'medmovil' ); ?>
		</p>

		<div class="cz-pills">
			<span><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Valores particulares publicados', 'medmovil' ); ?></span>
			<span><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Sin cobro de traslado', 'medmovil' ); ?></span>
			<span><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Varios exámenes en una sola visita', 'medmovil' ); ?></span>
		</div>

		<div class="cz-stats">
			<div class="cz-stat">
				<b><?php echo esc_html( $total ); ?></b>
				<span><?php esc_html_e( 'Exámenes disponibles', 'medmovil' ); ?></span>
			</div>
			<div class="cz-stat">
				<b id="czN">0</b>
				<span><?php esc_html_e( 'Seleccionados', 'medmovil' ); ?></span>
			</div>
			<div class="cz-stat">
				<b id="czT"><?php echo esc_html( medmovil_precio( 0 ) ); ?></b>
				<span><?php esc_html_e( 'Total estimado', 'medmovil' ); ?></span>
			</div>
		</div>
	</div>
</section>

<section class="band" style="padding-block:34px 70px">
	<div class="wrap">

		<div class="cz-bar">
			<div class="cz-buscar">
				<?php medmovil_icono( 'il-search', 'ilo' ); ?>
				<input type="search" id="czQ" autocomplete="off"
				       placeholder="<?php esc_attr_e( 'Busca por nombre, zona o código de la orden médica', 'medmovil' ); ?>"
				       aria-label="<?php esc_attr_e( 'Buscar un examen', 'medmovil' ); ?>">
				<button type="button" class="fl-x" id="czX" hidden aria-label="<?php esc_attr_e( 'Limpiar', 'medmovil' ); ?>">
					<?php medmovil_icono( 'i-x' ); ?>
				</button>
			</div>
			<p class="cz-cuenta" id="czCuenta" aria-live="polite"></p>
		</div>

		<div class="cz-grid">
			<div class="cz-lista" id="czLista">
				<?php foreach ( $grupos as $g ) :
					$color = medmovil_color_linea( $g['linea'] );
					?>
					<section class="cz-grupo" data-linea="<?php echo esc_attr( $g['linea']->slug ); ?>" style="--ac:<?php echo esc_attr( $color ); ?>">
						<h2><?php echo esc_html( $g['linea']->name ); ?></h2>

						<?php foreach ( $g['items'] as $p ) :
							$precio = (int) get_post_meta( $p->ID, '_mm_precio', true );
							$min    = (int) get_post_meta( $p->ID, '_mm_duracion', true );
							$codigo = (string) get_post_meta( $p->ID, '_mm_codigo', true );
							$prep   = (string) get_post_meta( $p->ID, '_mm_preparacion', true );
							$ayuno  = medmovil_ayuno( $prep );
							$buscar = mb_strtolower( remove_accents( $p->post_title . ' ' . get_post_meta( $p->ID, '_mm_resumen', true ) . ' ' . $codigo . ' ' . $g['linea']->name ) );
							$id     = 'cz-' . $p->ID;
							?>
							<div class="cz-it" data-buscar="<?php echo esc_attr( $buscar ); ?>">
								<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" class="cz-chk"
								       data-id="<?php echo (int) $p->ID; ?>"
								       data-n="<?php echo esc_attr( $p->post_title ); ?>"
								       data-p="<?php echo esc_attr( $precio ); ?>"
								       data-m="<?php echo esc_attr( $min ); ?>"
								       data-u="<?php echo esc_url( get_permalink( $p ) ); ?>"
								       data-i="<?php echo esc_attr( get_post_meta( $p->ID, '_mm_icono', true ) ?: 'il-eco' ); ?>"
								       data-c="<?php echo esc_attr( $color ); ?>"
								       data-l="<?php echo esc_attr( $g['linea']->name ); ?>">
								<label for="<?php echo esc_attr( $id ); ?>">
									<span class="cz-it-n"><?php echo esc_html( $p->post_title ); ?></span>
									<span class="cz-it-tags">
										<?php if ( $codigo ) : ?>
											<span class="cz-cod" title="<?php esc_attr_e( 'Código de la orden médica', 'medmovil' ); ?>"><?php echo esc_html( $codigo ); ?></span>
										<?php endif; ?>
										<?php if ( $min ) : ?><span><?php echo esc_html( $min . ' min' ); ?></span><?php endif; ?>
										<span class="<?php echo 'no' === $ayuno ? '' : 'cz-ayuno'; ?>">
											<?php echo esc_html( medmovil_etiqueta_ayuno( $prep ) ); ?>
										</span>
									</span>
								</label>
								<span class="cz-it-p"><?php echo esc_html( $precio ? medmovil_precio( $precio ) : __( 'Consultar', 'medmovil' ) ); ?></span>
								<a class="cz-it-v" href="<?php echo esc_url( get_permalink( $p ) ); ?>"
								   aria-label="<?php echo esc_attr( sprintf( __( 'Ver la página de %s', 'medmovil' ), $p->post_title ) ); ?>">
									<?php medmovil_icono( 'i-arr' ); ?>
								</a>
							</div>
						<?php endforeach; ?>
					</section>
				<?php endforeach; ?>

				<div class="fl-vacio" id="czVacio" hidden>
					<?php medmovil_icono( 'il-search', 'ilo' ); ?>
					<b><?php esc_html_e( 'No encontramos ese examen', 'medmovil' ); ?></b>
					<p><?php esc_html_e( 'Puede que lo tengamos con otro nombre, o que lo coordinemos a pedido. Escríbenos y te decimos al tiro.', 'medmovil' ); ?></p>
					<a class="btn btn-p" href="<?php echo esc_url( medmovil_whatsapp( __( 'Hola, busco un examen que no encontré en el cotizador.', 'medmovil' ) ) ); ?>" target="_blank" rel="noopener">
						<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Preguntar por WhatsApp', 'medmovil' ); ?>
					</a>
				</div>
			</div>

			<aside class="cz-res" id="czRes">
				<div class="cz-res-in">
					<h2><?php medmovil_icono( 'il-bag', 'ilo' ); ?> <?php esc_html_e( 'Tu presupuesto', 'medmovil' ); ?></h2>

					<ul class="cz-sel" id="czSel"></ul>
					<p class="cz-vacio" id="czVacioSel">
						<?php esc_html_e( 'Marca los exámenes de la lista y aparecen acá con su valor.', 'medmovil' ); ?>
					</p>

					<div class="cz-com">
						<label for="czCo"><?php esc_html_e( '¿En qué comuna te atendemos?', 'medmovil' ); ?></label>
						<div class="cot-sel cz-sel-com">
							<select id="czCo">
								<option value=""><?php esc_html_e( 'Elige tu comuna', 'medmovil' ); ?></option>
								<?php foreach ( medmovil_comunas( 60 ) as $c ) : ?>
									<option value="<?php echo esc_attr( $c->post_title ); ?>"
									        data-min="<?php echo esc_attr( get_post_meta( $c->ID, '_mm_tiempo', true ) ); ?>">
										<?php echo esc_html( $c->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<?php medmovil_icono( 'i-chev' ); ?>
						</div>
					</div>

					<div class="cz-total">
						<span><?php esc_html_e( 'Total de referencia', 'medmovil' ); ?></span>
						<b id="czTotal"><?php echo esc_html( medmovil_precio( 0 ) ); ?></b>
					</div>

					<p class="cz-nota">
						<?php medmovil_icono( 'i-info' ); ?>
						<span><?php esc_html_e( 'El valor incluye la visita, el examen y el informe. Sin cobro de traslado dentro de la Región Metropolitana. Pagas al momento del examen: efectivo, transferencia o tarjeta.', 'medmovil' ); ?></span>
					</p>

					<div class="cz-btns">
						<button type="button" class="btn btn-g" id="czWa" disabled style="justify-content:center">
							<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Enviar por WhatsApp', 'medmovil' ); ?>
						</button>
						<button type="button" class="btn btn-o" id="czImp" disabled style="justify-content:center">
							<?php medmovil_icono( 'i-dl' ); ?> <?php esc_html_e( 'Descargar presupuesto', 'medmovil' ); ?>
						</button>
						<button type="button" class="cart-clear" id="czClear" hidden><?php esc_html_e( 'Limpiar selección', 'medmovil' ); ?></button>
					</div>
				</div>
			</aside>
		</div>

		<?php if ( $desde ) : ?>
			<p class="cz-pie">
				<?php
				printf(
					/* translators: %s: precio más bajo del catálogo */
					esc_html__( 'Valores particulares vigentes, desde %s. Los códigos que aparecen junto a cada examen son los de la orden médica y sirven solo para buscar: MedMóvil no trabaja con Fonasa ni con isapres, todos nuestros valores son particulares.', 'medmovil' ),
					esc_html( medmovil_precio( $desde ) )
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
