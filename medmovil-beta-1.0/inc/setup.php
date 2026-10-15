<?php
/**
 * Configuración base del tema: soportes, menús, estilos y scripts.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Soportes del tema y menús.
 */
function medmovil_setup() {
	load_theme_textdomain( 'medmovil', MEDMOVIL_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 280,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	add_image_size( 'medmovil-card', 720, 480, true );

	register_nav_menus( array(
		'principal'  => __( 'Menú principal', 'medmovil' ),
		'pie-servicios' => __( 'Pie — Prestaciones', 'medmovil' ),
		'pie-pacientes' => __( 'Pie — Pacientes', 'medmovil' ),
		'pie-empresa'   => __( 'Pie — MedMóvil', 'medmovil' ),
	) );
}
add_action( 'after_setup_theme', 'medmovil_setup' );

/**
 * Estilos y scripts del sitio público.
 */
function medmovil_assets() {
	wp_enqueue_style(
		'medmovil-fuentes',
		'https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Instrument+Sans:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'medmovil', MEDMOVIL_URI . '/assets/css/main.css', array(), MEDMOVIL_VERSION );
	wp_add_inline_style( 'medmovil', medmovil_colores_css() );

	// El CSS de cada página va aparte: la portada no tiene por qué cargar la
	// tabla del arancel ni los estilos del blog. Ver assets/css/pagina-*.css.
	foreach ( medmovil_hojas_de_pagina() as $hoja ) {
		wp_enqueue_style(
			'medmovil-' . $hoja,
			MEDMOVIL_URI . '/assets/css/' . $hoja . '.css',
			array( 'medmovil' ),
			MEDMOVIL_VERSION
		);
	}

	wp_enqueue_script(
		'medmovil',
		MEDMOVIL_URI . '/assets/js/main.js',
		array(),
		MEDMOVIL_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	// El panel pesa, así que solo se carga donde se usa.
	if ( is_page_template( 'template-portal.php' ) ) {
		wp_enqueue_style( 'medmovil-portal', MEDMOVIL_URI . '/assets/css/portal.css', array( 'medmovil' ), MEDMOVIL_VERSION );
		if ( in_array( medmovil_rol(), array( 'paciente', 'profesional', 'admin' ), true ) ) {
			wp_enqueue_style( 'medmovil-portal-pulse', MEDMOVIL_URI . '/assets/css/portal-pulse.css', array( 'medmovil-portal' ), '1.8.5-portal.6' );
			wp_enqueue_script( 'medmovil-portal-pulse', MEDMOVIL_URI . '/assets/js/portal-pulse.js', array( 'medmovil-portal' ), '1.8.5-portal.6', true );
		}
		wp_enqueue_script(
			'medmovil-portal',
			MEDMOVIL_URI . '/assets/js/portal.js',
			array(),
			'1.8.5-portal.6',
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'medmovil_assets' );

/**
 * Qué hojas de estilo extra necesita la página que se está viendo.
 *
 * @return array Nombres de archivo, sin la extensión.
 */
function medmovil_hojas_de_pagina() {
	$hojas = array();

	if ( is_front_page() ) {
		$hojas[] = 'pagina-inicio';
	}
	if ( is_page_template( 'template-precios.php' ) ) {
		$hojas[] = 'pagina-precios';
	}
	if ( is_page_template( 'template-cotizador.php' ) ) {
		$hojas[] = 'pagina-cotizador';
	}
	if ( is_page_template( 'template-reserva.php' ) ) {
		$hojas[] = 'pagina-reserva';
	}
	if ( is_page_template( 'template-como-funciona.php' ) ) {
		$hojas[] = 'pagina-comofunciona';
	}
	if ( is_singular( 'mm_comuna' ) ) {
		$hojas[] = 'pagina-comuna';
	}
	// El blog: el listado, cada artículo y los archivos por categoría o fecha.
	if ( is_page_template( 'template-blog.php' ) || is_home() || is_singular( 'post' )
		|| is_category() || is_tag() || is_date() || is_author() || is_search() ) {
		$hojas[] = 'pagina-blog';
	}

	return apply_filters( 'medmovil_hojas_de_pagina', $hojas );
}

/**
 * Saca de la página lo que WordPress carga por defecto y el tema no usa.
 *
 * El CSS del editor de bloques son 110 KB en todas las páginas: la mitad del
 * peso del sitio. El tema no usa bloques —el contenido son plantillas propias—,
 * así que solo se deja donde de verdad hay bloques: si algún día escribes una
 * entrada con el editor nuevo, esa entrada sí los carga.
 */
function medmovil_aligerar() {
	if ( is_admin() ) {
		return;
	}

	$hay_bloques = is_singular() && function_exists( 'has_blocks' ) && has_blocks( get_queried_object_id() );

	if ( ! $hay_bloques ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-block-style' );
	}

	// Estos dos no los ocupa nunca: el tema define sus propios colores y
	// tipografías, y no hay estilos del editor clásico que preservar.
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'medmovil_aligerar', 100 );

/**
 * Y de la cabecera, las etiquetas que WordPress imprime sin que nadie las pida.
 */
function medmovil_limpiar_cabecera() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'medmovil_limpiar_cabecera' );

/**
 * Precarga de las fuentes para que el texto no parpadee.
 */
function medmovil_preconnect( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'medmovil_preconnect', 10, 2 );

/**
 * Genera las variables CSS de marca a partir de las opciones del panel.
 *
 * @return string
 */
function medmovil_colores_css() {
	$navy    = medmovil_opt( 'color_navy', '#0B3560' );
	$blue    = medmovil_opt( 'color_blue', '#2F7FD4' );
	$bright  = medmovil_opt( 'color_bright', '#4890E0' );
	$deep    = medmovil_opt( 'color_deep', '#061A3A' );

	return sprintf(
		':root{--navy:%1$s;--blue:%2$s;--blue-bright:%3$s;--navy-deep:%4$s}',
		esc_attr( $navy ),
		esc_attr( $blue ),
		esc_attr( $bright ),
		esc_attr( $deep )
	);
}

/**
 * Clases útiles en el body.
 */
function medmovil_body_class( $classes ) {
	if ( is_singular( 'mm_prestacion' ) ) {
		$linea = medmovil_linea_de( get_the_ID() );
		if ( $linea ) {
			$classes[] = 'linea-' . sanitize_html_class( $linea->slug );
		}
	}
	return $classes;
}
add_filter( 'body_class', 'medmovil_body_class' );

/**
 * Quita la barra de administración del frontend para no descuadrar el header fijo.
 */
add_filter( 'show_admin_bar', '__return_false' );

/**
 * Limpia la cabecera de cosas que no usamos.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

/**
 * Excerpt más largo y sin puntos suspensivos feos.
 */
add_filter( 'excerpt_length', function () { return 32; } );
add_filter( 'excerpt_more', function () { return '…'; } );

/**
 * Formatea un precio en pesos chilenos.
 *
 * @param int|string $valor Monto.
 * @return string
 */
function medmovil_precio( $valor ) {
	$valor = (int) preg_replace( '/[^0-9]/', '', (string) $valor );
	if ( ! $valor ) {
		return __( 'Consultar', 'medmovil' );
	}
	return '$' . number_format( $valor, 0, ',', '.' );
}

/**
 * Enlace de WhatsApp con mensaje prellenado.
 *
 * @param string $mensaje Texto inicial.
 * @return string
 */
function medmovil_whatsapp( $mensaje = '' ) {
	$fono = preg_replace( '/[^0-9]/', '', medmovil_opt( 'whatsapp', '56947158235' ) );
	if ( '' === $mensaje ) {
		$mensaje = __( 'Hola, quiero agendar un examen a domicilio.', 'medmovil' );
	}
	return 'https://wa.me/' . $fono . '?text=' . rawurlencode( $mensaje );
}
