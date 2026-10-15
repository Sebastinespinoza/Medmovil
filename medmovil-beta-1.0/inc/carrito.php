<?php
/**
 * Carrito de reserva.
 *
 * Junta los exámenes, calcula el presupuesto y conecta con la solicitud de hora.
 * El cobro sigue siendo presencial; WhatsApp permanece como alternativa.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Datos mínimos de una prestación para el carrito.
 *
 * @param int $post_id ID de la prestación.
 * @return array
 */
function medmovil_datos_carrito( $post_id ) {
	$linea = medmovil_linea_de( $post_id );

	return array(
		'id' => (int) $post_id,
		'n'  => get_the_title( $post_id ),
		'p'  => (int) get_post_meta( $post_id, '_mm_precio', true ),
		'm'  => (int) get_post_meta( $post_id, '_mm_duracion', true ),
		'u'  => get_permalink( $post_id ),
		'i'  => get_post_meta( $post_id, '_mm_icono', true ) ?: 'il-eco',
		'c'  => medmovil_color_linea( $linea ),
		'l'  => $linea ? $linea->name : '',
	);
}

/**
 * Botón "agregar a mi reserva".
 *
 * @param int    $post_id ID de la prestación.
 * @param string $clase   Clases del botón.
 * @param string $texto   Texto visible.
 */
function medmovil_boton_agregar( $post_id, $clase = 'btn btn-p', $texto = '' ) {
	$texto = $texto ? $texto : __( 'Agregar a mi reserva', 'medmovil' );
	medmovil_marcar_icono( 'i-plus' );

	printf(
		'<button type="button" class="%1$s mm-add" data-mm-add="%2$s"><svg class="ico" aria-hidden="true"><use href="#i-plus"/></svg><span>%3$s</span></button>',
		esc_attr( $clase ),
		esc_attr( wp_json_encode( medmovil_datos_carrito( $post_id ) ) ),
		esc_html( $texto )
	);
}

/**
 * Botón del carrito para la cabecera.
 */
function medmovil_boton_carrito() {
	?>
	<button type="button" class="cartbtn" id="cartBtn" aria-label="<?php esc_attr_e( 'Ver mi reserva', 'medmovil' ); ?>">
		<?php medmovil_icono( 'il-bag', 'ilo' ); ?>
		<span class="cartn" id="cartN" hidden>0</span>
	</button>
	<?php
}

/**
 * Cajón lateral con el detalle de la reserva.
 */
function medmovil_cajon_carrito() {
	$fono = preg_replace( '/[^0-9]/', '', medmovil_opt( 'whatsapp', '56947158235' ) );
	?>
	<div class="cart-bg" id="cartBg" hidden></div>
	<aside class="cart" id="cart" hidden aria-label="<?php esc_attr_e( 'Mi reserva', 'medmovil' ); ?>"
	       data-fono="<?php echo esc_attr( $fono ); ?>"
		   data-reserva="<?php echo esc_url( medmovil_url_reserva() ); ?>">

		<header class="cart-h">
			<b><?php medmovil_icono( 'il-bag', 'ilo' ); ?> <?php esc_html_e( 'Mi reserva', 'medmovil' ); ?></b>
			<button type="button" class="cart-x" id="cartX" aria-label="<?php esc_attr_e( 'Cerrar', 'medmovil' ); ?>">
				<?php medmovil_icono( 'i-x' ); ?>
			</button>
		</header>

		<div class="cart-body">
			<div class="cart-empty" id="cartEmpty">
				<?php medmovil_icono( 'il-bag', 'ilo' ); ?>
				<b><?php esc_html_e( 'Todavía no has agregado exámenes', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Puedes juntar varios exámenes en una sola visita: el profesional los realiza todos el mismo día, sin cobrar el traslado dos veces.', 'medmovil' ); ?></p>
				<a class="btn btn-o" href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>">
					<?php esc_html_e( 'Ver el catálogo', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
				</a>
			</div>

			<ul class="cart-list" id="cartList" hidden></ul>

			<div class="cart-com" id="cartCom" hidden>
				<label for="cartComuna"><?php esc_html_e( '¿En qué comuna te atendemos?', 'medmovil' ); ?></label>
				<select id="cartComuna">
					<option value=""><?php esc_html_e( 'Elige tu comuna', 'medmovil' ); ?></option>
					<?php foreach ( medmovil_comunas( 60 ) as $c ) : ?>
						<option value="<?php echo esc_attr( $c->post_title ); ?>"
						        data-min="<?php echo esc_attr( get_post_meta( $c->ID, '_mm_tiempo', true ) ); ?>">
							<?php echo esc_html( $c->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="cart-hint" id="cartHint"><?php esc_html_e( 'Con la comuna sabemos qué profesionales cubren tu sector y a qué hora podemos llegar.', 'medmovil' ); ?></p>
			</div>
		</div>

		<footer class="cart-f" id="cartFoot" hidden>
			<div class="cart-tot">
				<span><?php esc_html_e( 'Total de referencia', 'medmovil' ); ?></span>
				<b id="cartTotal">$0</b>
			</div>
			<p class="cart-nota">
				<?php medmovil_icono( 'i-info' ); ?>
				<span><?php esc_html_e( 'Sin cobro de traslado. El pago se hace al momento del examen: efectivo, transferencia o tarjeta.', 'medmovil' ); ?></span>
			</p>
			<a class="btn btn-p" id="cartGo" href="<?php echo esc_url( medmovil_url_reserva() ); ?>" style="justify-content:center; width:100%">
				<?php medmovil_icono( 'i-calcheck' ); ?> <?php esc_html_e( 'Solicitar una hora', 'medmovil' ); ?>
			</a>
			<button type="button" class="btn btn-o" id="cartWa" style="justify-content:center; width:100%">
				<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Consultar por WhatsApp', 'medmovil' ); ?>
			</button>
			<button type="button" class="btn btn-o" id="cartImp" style="justify-content:center; width:100%">
				<?php medmovil_icono( 'i-dl' ); ?> <?php esc_html_e( 'Descargar presupuesto', 'medmovil' ); ?>
			</button>
			<button type="button" class="cart-clear" id="cartClear"><?php esc_html_e( 'Vaciar la reserva', 'medmovil' ); ?></button>
		</footer>
	</aside>

	<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
	<?php
}

/**
 * Hoja del presupuesto para imprimir o guardar como PDF.
 *
 * Va en el pie y no dentro de <main> a propósito: el CSS de impresión oculta
 * todos los hijos directos de <body>, así que si esta hoja cuelga de <main>, se
 * oculta junto con él y sale la página en blanco. Aquí es hija directa de <body>
 * y sobrevive.
 *
 * La llenan tanto el cotizador como el cajón del carrito.
 */
function medmovil_hoja_presupuesto() {
	?>
	<div class="cz-print" id="czPrint" hidden aria-hidden="true">
		<header>
			<img src="<?php echo esc_url( MEDMOVIL_URI . '/assets/img/logo.png' ); ?>"
			     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="480" height="134">
			<div>
				<b><?php esc_html_e( 'Presupuesto referencial', 'medmovil' ); ?></b>
				<span id="czPrFecha"></span>
				<span><?php echo esc_html( medmovil_opt( 'region', 'Región Metropolitana' ) ); ?></span>
			</div>
		</header>

		<table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Examen', 'medmovil' ); ?></th>
					<th><?php esc_html_e( 'Duración', 'medmovil' ); ?></th>
					<th class="der"><?php esc_html_e( 'Valor', 'medmovil' ); ?></th>
				</tr>
			</thead>
			<tbody id="czPrCuerpo"></tbody>
			<tfoot>
				<tr>
					<td colspan="2"><?php esc_html_e( 'Total de referencia', 'medmovil' ); ?></td>
					<td class="der" id="czPrTotal"></td>
				</tr>
			</tfoot>
		</table>

		<p class="cz-print-comuna" id="czPrComuna"></p>

		<div class="cz-print-caja">
			<b><?php esc_html_e( 'Qué incluye el valor', 'medmovil' ); ?></b>
			<ul>
				<li><?php esc_html_e( 'La visita del profesional a tu domicilio', 'medmovil' ); ?></li>
				<li><?php esc_html_e( 'La realización del examen con equipo portátil', 'medmovil' ); ?></li>
				<li><?php esc_html_e( 'El informe firmado por médico radiólogo, en menos de 24 horas', 'medmovil' ); ?></li>
				<li><?php esc_html_e( 'Sin cobro de traslado dentro de la región', 'medmovil' ); ?></li>
			</ul>
			<b><?php esc_html_e( 'Formas de pago', 'medmovil' ); ?></b>
			<p><?php esc_html_e( 'Al momento del examen: efectivo, transferencia o tarjeta.', 'medmovil' ); ?></p>
		</div>

		<p class="cz-print-legal">
			<?php esc_html_e( 'Este documento es un presupuesto referencial: no es una boleta, una factura ni una orden de atención, y no reserva hora. Los valores corresponden a atención particular y pueden cambiar; el vigente es siempre el publicado en el sitio. Los exámenes se realizan con indicación médica.', 'medmovil' ); ?>
		</p>

		<p class="cz-print-pie">
			<?php echo esc_html( medmovil_opt( 'razon_social', get_bloginfo( 'name' ) ) ); ?>
			<?php if ( medmovil_opt( 'rut', '' ) ) : ?> · <?php echo esc_html( medmovil_opt( 'rut', '' ) ); ?><?php endif; ?>
			· <?php echo esc_html( medmovil_opt( 'telefono', '' ) ); ?>
			· <?php echo esc_html( medmovil_opt( 'email', '' ) ); ?>
			· <?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?>
		</p>
	</div>
	<?php
}
