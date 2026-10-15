<?php
/**
 * Piezas de interfaz reutilizables.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imprime un icono del sprite.
 *
 * @param string $id    Identificador del símbolo.
 * @param string $clase Clase CSS (ico para pictogramas, ilo para ilustraciones).
 * @param string $estilo Estilos en línea opcionales.
 */
function medmovil_icono( $id, $clase = 'ico', $estilo = '' ) {
	medmovil_marcar_icono( $id );

	printf(
		'<svg class="%1$s"%3$s aria-hidden="true"><use href="#%2$s"/></svg>',
		esc_attr( $clase ),
		esc_attr( $id ),
		$estilo ? ' style="' . esc_attr( $estilo ) . '"' : ''
	);
}

/**
 * Etiquetas permitidas al imprimir el titular.
 *
 * wp_kses_post() borra <svg> y <path> porque no están en su lista blanca, y así
 * se perdía la onda del subrayado. Esta lista deja pasar solo lo que dibuja el
 * propio tema: el texto que escribe el administrador ya va escapado con
 * esc_html() dentro de medmovil_titular(), así que no entra HTML de fuera.
 *
 * @return array
 */
function medmovil_html_titular() {
	return array(
		'span' => array( 'class' => array() ),
		'br'   => array(),
		'svg'  => array(
			'viewbox'             => array(),
			'preserveaspectratio' => array(),
			'aria-hidden'         => array(),
			'class'               => array(),
		),
		'path' => array( 'd' => array() ),
	);
}

/**
 * Convierte "Tu ecografía [en tu casa], con informe" en un titular con la parte
 * entre corchetes resaltada y subrayada con la onda de la marca.
 *
 * @param string $texto Titular con corchetes.
 * @return string HTML seguro.
 */
function medmovil_titular( $texto ) {
	$texto = wp_strip_all_tags( $texto );
	$onda  = '<svg viewBox="0 0 300 20" preserveAspectRatio="none" aria-hidden="true"><path d="M2 14C40 3 60 3 96 14s56 11 92 0 56-11 108 0"/></svg>';

	return preg_replace_callback(
		'/\[([^\]]+)\]/u',
		static function ( $m ) use ( $onda ) {
			return '<span class="hl">' . esc_html( $m[1] ) . $onda . '</span>';
		},
		esc_html( $texto )
	);
}

/**
 * URL del formulario de solicitud de hora.
 *
 * @param string $mensaje Mensaje para WhatsApp.
 * @return string
 */
function medmovil_url_agenda( $mensaje = '' ) {
	return medmovil_url_reserva();
}

/**
 * Clase de color según la línea de atención.
 *
 * @param WP_Term|null $linea Término.
 * @return string Variable CSS.
 */
function medmovil_color_linea( $linea ) {
	$mapa = array(
		'imagenologia'         => 'var(--c-img)',
		'ecografias-generales' => 'var(--c-img)',
		'musculoesqueletica'   => 'var(--c-msk)',
		'doppler'              => 'var(--c-dop)',
		'cardiologia'          => 'var(--c-car)',
		'laboratorio'          => 'var(--c-lab)',
		'enfermeria'           => 'var(--c-enf)',
		'radiologia'           => 'var(--c-img)',
		'funcion-pulmonar'     => 'var(--c-dop)',
	);
	if ( $linea && isset( $mapa[ $linea->slug ] ) ) {
		return $mapa[ $linea->slug ];
	}
	return 'var(--blue)';
}

/**
 * Tarjeta de prestación para grillas.
 *
 * @param int $post_id ID de la prestación.
 */
function medmovil_tarjeta_prestacion( $post_id ) {
	$linea   = medmovil_linea_de( $post_id );
	$color   = medmovil_color_linea( $linea );
	$icono   = get_post_meta( $post_id, '_mm_icono', true ) ?: 'il-eco';
	$precio  = (int) get_post_meta( $post_id, '_mm_precio', true );
	$antes   = (int) get_post_meta( $post_id, '_mm_precio_antes', true );
	$min     = (int) get_post_meta( $post_id, '_mm_duracion', true );
	$codigo  = (string) get_post_meta( $post_id, '_mm_codigo', true );
	$prep    = (string) get_post_meta( $post_id, '_mm_preparacion', true );
	$ayuno   = medmovil_ayuno( $prep );
	$titulo  = get_the_title( $post_id );
	$resumen = (string) get_post_meta( $post_id, '_mm_resumen', true );
	$buscar  = mb_strtolower( remove_accents( $titulo . ' ' . $resumen . ' ' . $codigo . ' ' . ( $linea ? $linea->name : '' ) ) );
	?>
	<article class="prod" style="--ac:<?php echo esc_attr( $color ); ?>"
	         data-linea="<?php echo esc_attr( $linea ? $linea->slug : '' ); ?>"
	         data-precio="<?php echo esc_attr( $precio ); ?>"
	         data-min="<?php echo esc_attr( $min ); ?>"
	         data-buscar="<?php echo esc_attr( $buscar ); ?>">

		<div class="prod-media">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); ?>
			<?php else : ?>
				<svg class="prod-w" viewBox="0 0 300 120" preserveAspectRatio="none" aria-hidden="true">
					<path d="M-10 70C40 20 70 20 110 70s60 45 110 0 60-45 100 0"/>
					<path d="M-10 92C40 42 70 42 110 92s60 40 110 0 60-40 100 0"/>
				</svg>
				<span class="prod-ic"><?php medmovil_icono( $icono, 'ilo' ); ?></span>
			<?php endif; ?>

			<span class="prod-tags">
				<?php if ( $min ) : ?><span class="prod-tag"><?php echo esc_html( $min . ' min' ); ?></span><?php endif; ?>
				<span class="prod-tag prod-tag-<?php echo esc_attr( $ayuno ); ?>">
					<?php echo esc_html( medmovil_etiqueta_ayuno( $prep ) ); ?>
				</span>
			</span>
			<?php if ( $antes && $antes > $precio ) : ?>
				<span class="prod-off">
					<?php echo esc_html( '-' . round( ( 1 - $precio / $antes ) * 100 ) . '%' ); ?>
				</span>
			<?php endif; ?>
		</div>

		<div class="prod-body">
			<span class="prod-linea"><?php echo esc_html( $linea ? $linea->name : __( 'A domicilio', 'medmovil' ) ); ?></span>
			<h3><a class="prod-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( $titulo ); ?></a></h3>
			<p><?php echo esc_html( wp_trim_words( $resumen, 18 ) ); ?></p>

			<div class="prod-pie">
				<span class="prod-precio">
					<?php echo esc_html( $precio ? medmovil_precio( $precio ) : __( 'Consultar', 'medmovil' ) ); ?>
					<?php if ( $antes && $antes > $precio ) : ?>
						<s><?php echo esc_html( medmovil_precio( $antes ) ); ?></s>
					<?php endif; ?>
					<small><?php esc_html_e( 'todo incluido', 'medmovil' ); ?></small>
				</span>
				<button type="button" class="prod-add mm-add"
				        data-mm-add="<?php echo esc_attr( wp_json_encode( medmovil_datos_carrito( $post_id ) ) ); ?>"
				        aria-label="<?php echo esc_attr( sprintf( __( 'Agregar %s a mi reserva', 'medmovil' ), $titulo ) ); ?>">
					<?php medmovil_icono( 'i-plus' ); ?> <span><?php esc_html_e( 'Agregar', 'medmovil' ); ?></span>
				</button>
			</div>
			<a class="prod-cta" href="<?php echo esc_url( medmovil_url_reserva( $post_id ) ); ?>">
				<?php medmovil_icono( 'i-calcheck' ); ?> <?php esc_html_e( 'Solicitar hora', 'medmovil' ); ?>
			</a>
		</div>
	</article>
	<?php
}

/**
 * Botón flotante de WhatsApp.
 */
function medmovil_boton_whatsapp() {
	?>
	<a class="wa" href="<?php echo esc_url( medmovil_whatsapp() ); ?>" target="_blank" rel="noopener"
	   aria-label="<?php esc_attr_e( 'Escribir por WhatsApp', 'medmovil' ); ?>">
		<?php medmovil_icono( 'i-wa' ); ?><span><?php esc_html_e( 'Agenda por WhatsApp', 'medmovil' ); ?></span>
	</a>
	<?php
}

/**
 * Bloque de preguntas frecuentes.
 *
 * @param array $faq Lista de pares pregunta/respuesta.
 */
function medmovil_faq( $faq ) {
	if ( ! $faq ) {
		return;
	}
	echo '<div class="faq">';
	foreach ( $faq as $i => $par ) {
		printf(
			'<details%1$s><summary>%2$s</summary><div class="ans">%3$s</div></details>',
			0 === $i ? ' open' : '',
			esc_html( $par[0] ),
			esc_html( $par[1] )
		);
	}
	echo '</div>';
}

/**
 * Migas de pan.
 *
 * @param array $items Pares de texto y URL. La última sin URL.
 */
function medmovil_migas( $items ) {
	$items = array_values( array_filter( $items ) );
	echo '<nav class="crumbs" aria-label="' . esc_attr__( 'Ruta de navegación', 'medmovil' ) . '">';
	$total = count( $items );
	foreach ( $items as $i => $item ) {
		if ( ! empty( $item[1] ) ) {
			printf( '<a href="%s">%s</a>', esc_url( $item[1] ), esc_html( $item[0] ) );
		} else {
			printf( '<span style="color:var(--ink-2)">%s</span>', esc_html( $item[0] ) );
		}
		if ( $i < $total - 1 ) {
			medmovil_icono( 'i-arr' );
		}
	}
	echo '</nav>';
}

/**
 * Comunas destacadas para el bloque de cobertura.
 *
 * @param int $cantidad Número de comunas.
 * @return WP_Post[]
 */
function medmovil_comunas( $cantidad = -1 ) {
	return get_posts( array(
		'post_type'      => 'mm_comuna',
		'posts_per_page' => $cantidad,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
}

/**
 * Walker del menú principal: los enlaces usan las clases del tema.
 */
class Medmovil_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Abre el elemento.
	 *
	 * @param string   $salida Acumulador.
	 * @param WP_Post  $item   Elemento del menú.
	 * @param int      $nivel  Profundidad.
	 * @param stdClass $args   Argumentos.
	 */
	public function start_el( &$salida, $item, $nivel = 0, $args = null, $id = 0 ) {
		$activo = in_array( 'current-menu-item', (array) $item->classes, true ) ? ' on' : '';
		$salida .= sprintf(
			'<div><a class="nl%s" href="%s">%s</a></div>',
			esc_attr( $activo ),
			esc_url( $item->url ),
			esc_html( $item->title )
		);
	}

	/**
	 * No cerramos nada: cada elemento es autónomo.
	 *
	 * @param string $salida Acumulador.
	 */
	public function end_el( &$salida, $item = null, $nivel = 0, $args = null ) {}
}

/**
 * Nombre corto de una prestación para etiquetas y fichas pequeñas.
 * Quita el prefijo del tipo de examen y deja lo que identifica la zona.
 *
 * @param string $titulo Título completo.
 * @return string
 */
function medmovil_nombre_corto( $titulo ) {
	$prefijos = array(
		'Ecografía de ', 'Ecografía del ', 'Ecografía ',
		'Radiografía de ', 'Doppler venoso de ', 'Doppler arterial de ', 'Doppler ',
		'Monitoreo de ', 'Toma de ', 'Administración de ', 'Instalación y ',
		'Control de ', 'Perfil ',
	);
	$corto = str_ireplace( $prefijos, '', $titulo );
	return wp_trim_words( trim( $corto ), 2, '' );
}

/**
 * ¿Este examen exige ayuno?
 *
 * Suena tonto necesitar una función para esto, pero la mitad de las
 * preparaciones dicen "No requiere ayuno" y buscar la palabra "ayuno" a secas
 * marcaba justamente lo contrario. Hay un tercer caso real: los que solo piden
 * ayuno si además se hace una ecografía abdominal.
 *
 * @param string $preparacion Texto de la preparación.
 * @return string 'si', 'no' o 'depende'.
 */
function medmovil_ayuno( $preparacion ) {
	$texto = mb_strtolower( remove_accents( (string) $preparacion ) );
	if ( '' === trim( $texto ) ) {
		return 'no';
	}

	$sin_negar = preg_replace(
		array(
			'/\bno\s+(requiere|necesita|hace falta|es necesario)(\s+el)?\s+ayuno\b/u',
			'/\bno\s+(requiere|necesita)\s+(estar\s+)?en\s+ayunas\b/u',
			'/\bsin\s+ayuno\b/u',
		),
		' ',
		$texto
	);

	$menciona_ahora  = (bool) preg_match( '/\bayun(o|as)\b/u', $sin_negar );
	$menciona_antes  = (bool) preg_match( '/\bayun(o|as)\b/u', $texto );

	if ( ! $menciona_ahora ) {
		return 'no';
	}
	// Quedó una mención después de haber negado otra: es condicional.
	if ( $menciona_antes && $sin_negar !== $texto ) {
		return 'depende';
	}

	return 'si';
}

/**
 * Etiqueta corta del ayuno.
 *
 * @param string $preparacion Texto de la preparación.
 * @return string
 */
function medmovil_etiqueta_ayuno( $preparacion ) {
	$estado = medmovil_ayuno( $preparacion );

	if ( 'si' === $estado ) {
		return __( 'Requiere ayuno', 'medmovil' );
	}
	if ( 'depende' === $estado ) {
		return __( 'Ayuno según el caso', 'medmovil' );
	}

	return __( 'Sin ayuno', 'medmovil' );
}

/**
 * Fecha en español, sin depender del idioma que tenga instalado WordPress.
 *
 * Muchos hostings dejan WordPress en inglés y salían textos mezclados como
 * "Wednesday 9 de September". Esta función arma los nombres de días y meses
 * a mano y deja el resto del formato en manos de wp_date(), que sí respeta
 * la zona horaria del sitio.
 *
 * Recorre el formato byte a byte: solo las letras ASCII son tokens, así que
 * los caracteres multibyte (·, á) pasan intactos.
 *
 * @param string   $formato Formato al estilo date().
 * @param int|null $ts      Marca de tiempo. Por defecto, ahora.
 * @return string
 */
function medmovil_fecha( $formato, $ts = null ) {
	if ( null === $ts ) {
		$ts = time();
	}

	$dias = array( 'domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado' );

	$meses = array(
		'', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
		'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
	);

	$meses_cortos = array(
		'', 'ene', 'feb', 'mar', 'abr', 'may', 'jun',
		'jul', 'ago', 'sep', 'oct', 'nov', 'dic',
	);

	$dias_cortos = array( 'dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb' );

	$salida = '';
	$largo  = strlen( $formato );

	for ( $i = 0; $i < $largo; $i++ ) {
		$c = $formato[ $i ];

		if ( '\\' === $c ) {
			$i++;
			if ( $i < $largo ) {
				$salida .= $formato[ $i ];
			}
			continue;
		}

		if ( ! ctype_alpha( $c ) ) {
			$salida .= $c;
			continue;
		}

		switch ( $c ) {
			case 'l':
				$salida .= $dias[ (int) wp_date( 'w', $ts ) ];
				break;
			case 'D':
				$salida .= $dias_cortos[ (int) wp_date( 'w', $ts ) ];
				break;
			case 'F':
				$salida .= $meses[ (int) wp_date( 'n', $ts ) ];
				break;
			case 'M':
				$salida .= $meses_cortos[ (int) wp_date( 'n', $ts ) ];
				break;
			default:
				$salida .= wp_date( $c, $ts );
		}
	}

	return $salida;
}
