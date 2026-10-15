<?php
/**
 * MedMóvil → Iconos: cambiar cualquier icono del tema por uno propio.
 *
 * La gracia es que no haya que tocar código: se sube un SVG (de Flaticon, del
 * Noun Project, del diseñador que sea) y reemplaza al que trae el tema. El
 * archivo se limpia antes de guardarlo, porque un SVG puede traer JavaScript
 * adentro y eso en un sitio con datos de pacientes no se negocia.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Los iconos que se pueden reemplazar desde el panel, agrupados.
 *
 * No se listan los 128: los de interfaz (flechas, candados) no tiene sentido
 * cambiarlos uno por uno. Estos son los que se ven grandes y los que definen
 * de qué se trata cada examen.
 *
 * @return array
 */
function medmovil_iconos_editables() {
	return array(
		__( 'Exámenes: los que eliges en cada prestación', 'medmovil' ) => array(
			'il-eco'    => __( 'Ecografía general', 'medmovil' ),
			'il-msk'    => __( 'Musculoesquelética', 'medmovil' ),
			'il-dop'    => __( 'Doppler', 'medmovil' ),
			'il-cardio' => __( 'Cardiología', 'medmovil' ),
			'il-lab'    => __( 'Laboratorio', 'medmovil' ),
			'il-rx'     => __( 'Radiografía', 'medmovil' ),
			'il-nurse'  => __( 'Enfermería', 'medmovil' ),
			'il-clockx' => __( 'Monitoreo (Holter, presión)', 'medmovil' ),
			'il-lung'   => __( 'Función pulmonar', 'medmovil' ),
		),
		__( 'Cómo funciona y portada', 'medmovil' ) => array(
			'il-search'  => __( 'Paso: elegir el examen', 'medmovil' ),
			'il-book'    => __( 'Paso: agendar la hora', 'medmovil' ),
			'il-arrive'  => __( 'Paso: llegamos a tu casa', 'medmovil' ),
			'il-report'  => __( 'Paso: recibes el informe', 'medmovil' ),
			'il-carhome' => __( 'Traslado incluido', 'medmovil' ),
			'il-shield'  => __( 'Garantía / respaldo', 'medmovil' ),
			'il-elder'   => __( 'Adulto mayor', 'medmovil' ),
			'il-body'    => __( 'Selector de zona del cuerpo', 'medmovil' ),
		),
		__( 'Tienda y asistente', 'medmovil' ) => array(
			'il-bag'  => __( 'Bolsa / carrito de la cabecera', 'medmovil' ),
			'il-cart' => __( 'Carrito del cajón', 'medmovil' ),
			'il-bot'  => __( 'Asistente', 'medmovil' ),
			'il-doc'  => __( 'Documento / informe', 'medmovil' ),
			'il-cal'  => __( 'Calendario', 'medmovil' ),
			'il-pick' => __( 'Selección / checklist', 'medmovil' ),
		),
	);
}

/**
 * Los iconos propios que ya subió el dueño.
 *
 * @return array Clave: id del icono. Valor: array con 'vb' y 'html'.
 */
function medmovil_iconos_propios() {
	$guardados = get_option( 'medmovil_iconos', array() );

	return is_array( $guardados ) ? $guardados : array();
}

/**
 * Limpia un SVG subido y deja solo lo que se puede dibujar.
 *
 * Un SVG es XML: puede traer <script>, atributos onload, referencias a
 * archivos de afuera y entidades que leen archivos del servidor. Acá se
 * descarta todo eso y se conservan únicamente las formas.
 *
 * @param string $svg      Contenido del archivo.
 * @param bool   $del_tema Si es true, los colores se cambian por currentColor.
 * @return array|WP_Error  array con 'vb' (viewBox) y 'html' (el contenido).
 */
function medmovil_limpiar_svg( $svg, $del_tema = true ) {
	$svg = trim( (string) $svg );

	if ( '' === $svg || false === stripos( $svg, '<svg' ) ) {
		return new WP_Error( 'no_svg', __( 'Eso no parece un archivo SVG.', 'medmovil' ) );
	}
	if ( strlen( $svg ) > 256 * KB_IN_BYTES ) {
		return new WP_Error( 'grande', __( 'El SVG no puede pesar más de 256 KB. Si pesa más, casi seguro es una imagen incrustada y no un dibujo.', 'medmovil' ) );
	}
	// Entidades externas: el vector clásico para leer archivos del servidor.
	if ( preg_match( '/<!(DOCTYPE|ENTITY)/i', $svg ) ) {
		return new WP_Error( 'doctype', __( 'El archivo trae declaraciones que no se aceptan. Ábrelo en un editor y guárdalo como SVG simple.', 'medmovil' ) );
	}

	$formas = array(
		'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon',
	);
	$atributos = array(
		'd', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin',
		'stroke-miterlimit', 'stroke-dasharray', 'stroke-dashoffset',
		'fill-rule', 'clip-rule', 'opacity', 'fill-opacity', 'stroke-opacity',
		'cx', 'cy', 'r', 'rx', 'ry', 'x', 'y', 'width', 'height',
		'x1', 'y1', 'x2', 'y2', 'points', 'transform',
	);

	$previo = libxml_use_internal_errors( true );
	$doc    = new DOMDocument();
	$ok     = $doc->loadXML( $svg, LIBXML_NONET | LIBXML_NOENT );
	libxml_clear_errors();
	libxml_use_internal_errors( $previo );

	if ( ! $ok || ! $doc->documentElement || 'svg' !== strtolower( $doc->documentElement->nodeName ) ) {
		return new WP_Error( 'roto', __( 'No pude leer el SVG. Puede estar mal formado.', 'medmovil' ) );
	}

	$raiz = $doc->documentElement;
	$vb   = $raiz->getAttribute( 'viewBox' );

	if ( ! $vb ) {
		// Sin viewBox se arma uno con el ancho y alto declarados.
		$w = (float) preg_replace( '/[^0-9.]/', '', $raiz->getAttribute( 'width' ) );
		$h = (float) preg_replace( '/[^0-9.]/', '', $raiz->getAttribute( 'height' ) );
		if ( $w <= 0 || $h <= 0 ) {
			return new WP_Error( 'sin_vb', __( 'El SVG no trae viewBox ni medidas, así que no sé a qué tamaño dibujarlo.', 'medmovil' ) );
		}
		$vb = "0 0 $w $h";
	}
	if ( ! preg_match( '/^[\d\s.,+-]+$/', $vb ) ) {
		return new WP_Error( 'vb_raro', __( 'El viewBox del SVG tiene un formato que no entiendo.', 'medmovil' ) );
	}

	$salida = '';

	$limpiar = function ( DOMNode $nodo ) use ( &$limpiar, $formas, $atributos, $del_tema ) {
		if ( XML_ELEMENT_NODE !== $nodo->nodeType ) {
			return '';
		}
		$etiqueta = strtolower( $nodo->nodeName );
		if ( ! in_array( $etiqueta, $formas, true ) ) {
			return '';
		}

		$partes = array();
		foreach ( iterator_to_array( $nodo->attributes ) as $attr ) {
			$nombre = strtolower( $attr->nodeName );
			if ( ! in_array( $nombre, $atributos, true ) ) {
				continue;
			}
			$valor = trim( $attr->nodeValue );
			// Nada de url(), expression() ni referencias externas.
			if ( preg_match( '/(url\s*\(|javascript:|expression\s*\()/i', $valor ) ) {
				continue;
			}
			if ( $del_tema && in_array( $nombre, array( 'fill', 'stroke' ), true ) ) {
				$bajo = strtolower( $valor );
				if ( 'none' !== $bajo && 'transparent' !== $bajo && '' !== $bajo ) {
					$valor = 'currentColor';
				}
			}
			$partes[] = $nombre . '="' . esc_attr( $valor ) . '"';
		}

		$dentro = '';
		foreach ( $nodo->childNodes as $hijo ) {
			$dentro .= $limpiar( $hijo );
		}

		$abre = '<' . $etiqueta . ( $partes ? ' ' . implode( ' ', $partes ) : '' );

		return $dentro ? $abre . '>' . $dentro . '</' . $etiqueta . '>' : $abre . '/>';
	};

	foreach ( $raiz->childNodes as $hijo ) {
		$salida .= $limpiar( $hijo );
	}

	if ( '' === trim( $salida ) ) {
		return new WP_Error( 'vacio', __( 'El SVG quedó vacío después de limpiarlo. Puede que sea una imagen incrustada (un PNG dentro de un SVG) y no un dibujo vectorial.', 'medmovil' ) );
	}

	return array( 'vb' => $vb, 'html' => $salida );
}

/**
 * Menú.
 */
function medmovil_menu_iconos() {
	add_submenu_page(
		'medmovil',
		__( 'Iconos', 'medmovil' ),
		__( 'Iconos', 'medmovil' ),
		'manage_options',
		'medmovil-iconos',
		'medmovil_pagina_iconos'
	);
}
add_action( 'admin_menu', 'medmovil_menu_iconos', 16 );

/**
 * La página de iconos.
 */
function medmovil_pagina_iconos() {
	$aviso = '';
	$clase = 'notice-success';

	if ( isset( $_POST['mm_iconos_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mm_iconos_nonce'] ), 'mm_iconos' ) && current_user_can( 'manage_options' ) ) {
		$propios = medmovil_iconos_propios();
		$id      = isset( $_POST['icono'] ) ? sanitize_key( wp_unslash( $_POST['icono'] ) ) : '';
		$validos = array();
		foreach ( medmovil_iconos_editables() as $grupo ) {
			$validos = array_merge( $validos, array_keys( $grupo ) );
		}

		if ( ! in_array( $id, $validos, true ) ) {
			$aviso = __( 'Ese icono no existe.', 'medmovil' );
			$clase = 'notice-error';
		} elseif ( isset( $_POST['restaurar'] ) ) {
			unset( $propios[ $id ] );
			update_option( 'medmovil_iconos', $propios );
			$aviso = __( 'Listo, ese icono volvió al que trae el tema.', 'medmovil' );
		} elseif ( ! empty( $_FILES['svg']['tmp_name'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- se lee del disco y se limpia en medmovil_limpiar_svg().
			$contenido = file_get_contents( $_FILES['svg']['tmp_name'] );
			$limpio    = medmovil_limpiar_svg( $contenido, ! empty( $_POST['del_tema'] ) );

			if ( is_wp_error( $limpio ) ) {
				$aviso = $limpio->get_error_message();
				$clase = 'notice-error';
			} else {
				$propios[ $id ] = $limpio;
				update_option( 'medmovil_iconos', $propios );
				$aviso = __( 'Icono cambiado. Recarga el sitio para verlo.', 'medmovil' );
			}
		} else {
			$aviso = __( 'Elige un archivo SVG.', 'medmovil' );
			$clase = 'notice-error';
		}
	}

	$propios = medmovil_iconos_propios();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Iconos', 'medmovil' ); ?></h1>

		<?php if ( $aviso ) : ?>
			<div class="notice <?php echo esc_attr( $clase ); ?> is-dismissible"><p><?php echo esc_html( $aviso ); ?></p></div>
		<?php endif; ?>

		<p style="max-width:70ch">
			<?php esc_html_e( 'Acá cambias cualquier icono del tema por uno tuyo. Sube el SVG y listo: no hay que tocar código ni volver a subir el tema.', 'medmovil' ); ?>
		</p>
		<p style="max-width:70ch">
			<strong><?php esc_html_e( 'Dónde conseguirlos:', 'medmovil' ); ?></strong>
			<?php
			printf(
				/* translators: 1: enlace a Flaticon, 2: enlace al Noun Project */
				esc_html__( 'en %1$s o en %2$s. Descarga el icono en formato SVG y súbelo acá. Ojo con la licencia: si el sitio de donde lo sacas pide crédito, hay que ponerlo.', 'medmovil' ),
				'<a href="https://www.flaticon.com/uicons" target="_blank" rel="noopener">Flaticon</a>',
				'<a href="https://thenounproject.com" target="_blank" rel="noopener">Noun Project</a>'
			);
			?>
		</p>

		<style>
			.mm-ic-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:14px;margin-top:14px}
			.mm-ic{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px;display:flex;gap:14px}
			.mm-ic-vista{width:56px;height:56px;flex:none;border:1px solid #f0f0f1;border-radius:10px;
				display:grid;place-items:center;background:#f6f7f7;color:#0B1B33}
			.mm-ic-vista svg{width:30px;height:30px}
			.mm-ic-datos{flex:1;min-width:0}
			.mm-ic-datos b{display:block;font-size:13px}
			.mm-ic-datos code{font-size:11px;color:#787c82}
			.mm-ic form{margin-top:8px;display:flex;flex-direction:column;gap:6px}
			.mm-ic input[type=file]{font-size:12px;max-width:100%}
			.mm-ic-acc{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
			.mm-ic-propio{font-size:11px;color:#00a32a;font-weight:600}
			.mm-ic-chk{font-size:11px;color:#50575e}
		</style>

		<?php foreach ( medmovil_iconos_editables() as $grupo => $iconos ) : ?>
			<h2><?php echo esc_html( $grupo ); ?></h2>
			<div class="mm-ic-grid">
				<?php foreach ( $iconos as $id => $nombre ) : ?>
					<div class="mm-ic">
						<span class="mm-ic-vista">
							<svg viewBox="<?php echo esc_attr( medmovil_viewbox_icono( $id ) ); ?>" aria-hidden="true">
								<?php echo medmovil_cuerpo_icono( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ya limpiado. ?>
							</svg>
						</span>
						<div class="mm-ic-datos">
							<b><?php echo esc_html( $nombre ); ?></b>
							<code><?php echo esc_html( $id ); ?></code>
							<?php if ( isset( $propios[ $id ] ) ) : ?>
								<span class="mm-ic-propio"><?php esc_html_e( '· icono tuyo', 'medmovil' ); ?></span>
							<?php endif; ?>

							<form method="post" enctype="multipart/form-data">
								<?php wp_nonce_field( 'mm_iconos', 'mm_iconos_nonce' ); ?>
								<input type="hidden" name="icono" value="<?php echo esc_attr( $id ); ?>">
								<input type="file" name="svg" accept=".svg,image/svg+xml">
								<label class="mm-ic-chk">
									<input type="checkbox" name="del_tema" value="1" checked>
									<?php esc_html_e( 'pintarlo con el color del tema', 'medmovil' ); ?>
								</label>
								<div class="mm-ic-acc">
									<button type="submit" class="button button-primary button-small"><?php esc_html_e( 'Subir', 'medmovil' ); ?></button>
									<?php if ( isset( $propios[ $id ] ) ) : ?>
										<button type="submit" name="restaurar" value="1" class="button button-small"><?php esc_html_e( 'Volver al original', 'medmovil' ); ?></button>
									<?php endif; ?>
								</div>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * viewBox del icono, sea el del tema o el subido.
 *
 * @param string $id Id.
 * @return string
 */
function medmovil_viewbox_icono( $id ) {
	$propios = medmovil_iconos_propios();
	if ( isset( $propios[ $id ]['vb'] ) ) {
		return $propios[ $id ]['vb'];
	}
	$simbolos = medmovil_simbolos( false );
	if ( isset( $simbolos[ $id ] ) && preg_match( '/viewBox="([^"]+)"/', $simbolos[ $id ], $m ) ) {
		return $m[1];
	}

	return '0 0 24 24';
}

/**
 * Contenido del icono, sin la etiqueta <symbol>.
 *
 * @param string $id Id.
 * @return string
 */
function medmovil_cuerpo_icono( $id ) {
	$propios = medmovil_iconos_propios();
	if ( isset( $propios[ $id ]['html'] ) ) {
		return $propios[ $id ]['html'];
	}
	$simbolos = medmovil_simbolos( false );
	if ( isset( $simbolos[ $id ] ) ) {
		return preg_replace( '#^<symbol[^>]*>|</symbol>$#', '', $simbolos[ $id ] );
	}

	return '';
}
