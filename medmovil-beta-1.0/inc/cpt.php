<?php
/**
 * Tipos de contenido: prestaciones, comunas, packs, alianzas y testimonios.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra los tipos de contenido del sitio.
 */
function medmovil_registrar_cpt() {

	register_post_type( 'mm_prestacion', array(
		'labels' => array(
			'name'               => __( 'Prestaciones', 'medmovil' ),
			'singular_name'      => __( 'Prestación', 'medmovil' ),
			'add_new'            => __( 'Añadir prestación', 'medmovil' ),
			'add_new_item'       => __( 'Añadir prestación', 'medmovil' ),
			'edit_item'          => __( 'Editar prestación', 'medmovil' ),
			'search_items'       => __( 'Buscar prestaciones', 'medmovil' ),
			'not_found'          => __( 'Todavía no hay prestaciones', 'medmovil' ),
			'menu_name'          => __( 'Prestaciones', 'medmovil' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-heart',
		'menu_position' => 20,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		'rewrite'       => array( 'slug' => 'examenes', 'with_front' => false ),
		'show_in_rest'  => true,
	) );

	register_taxonomy( 'mm_linea', 'mm_prestacion', array(
		'labels' => array(
			'name'          => __( 'Líneas de atención', 'medmovil' ),
			'singular_name' => __( 'Línea', 'medmovil' ),
			'menu_name'     => __( 'Líneas', 'medmovil' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'linea', 'with_front' => false ),
		'show_in_rest'      => true,
	) );

	register_post_type( 'mm_comuna', array(
		'labels' => array(
			'name'          => __( 'Comunas', 'medmovil' ),
			'singular_name' => __( 'Comuna', 'medmovil' ),
			'add_new_item'  => __( 'Añadir comuna', 'medmovil' ),
			'edit_item'     => __( 'Editar comuna', 'medmovil' ),
			'menu_name'     => __( 'Comunas', 'medmovil' ),
		),
		'public'        => true,
		'has_archive'   => false,
		'menu_icon'     => 'dashicons-location-alt',
		'menu_position' => 21,
		'supports'      => array( 'title', 'editor', 'page-attributes' ),
		'rewrite'       => array( 'slug' => 'cobertura', 'with_front' => false ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'mm_pack', array(
		'labels' => array(
			'name'          => __( 'Packs y chequeos', 'medmovil' ),
			'singular_name' => __( 'Pack', 'medmovil' ),
			'menu_name'     => __( 'Packs', 'medmovil' ),
		),
		'public'        => true,
		'has_archive'   => false,
		'menu_icon'     => 'dashicons-screenoptions',
		'menu_position' => 22,
		'supports'      => array( 'title', 'editor', 'page-attributes' ),
		'rewrite'       => array( 'slug' => 'packs', 'with_front' => false ),
	) );

	register_post_type( 'mm_aliado', array(
		'labels' => array(
			'name'          => __( 'Alianzas', 'medmovil' ),
			'singular_name' => __( 'Alianza', 'medmovil' ),
			'menu_name'     => __( 'Alianzas', 'medmovil' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-groups',
		'menu_position' => 23,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'mm_testimonio', array(
		'labels' => array(
			'name'          => __( 'Testimonios', 'medmovil' ),
			'singular_name' => __( 'Testimonio', 'medmovil' ),
			'menu_name'     => __( 'Testimonios', 'medmovil' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-format-quote',
		'menu_position' => 24,
		'supports'      => array( 'title', 'editor', 'page-attributes' ),
	) );
}
add_action( 'init', 'medmovil_registrar_cpt' );

/**
 * Devuelve la línea de atención de una prestación.
 *
 * @param int $post_id ID de la prestación.
 * @return WP_Term|null
 */
function medmovil_linea_de( $post_id ) {
	$terms = get_the_terms( $post_id, 'mm_linea' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}
	return $terms[0];
}

/**
 * Columnas propias en el listado de prestaciones, para ver precios de un vistazo.
 */
function medmovil_columnas_prestacion( $cols ) {
	$nuevo = array();
	foreach ( $cols as $k => $v ) {
		$nuevo[ $k ] = $v;
		if ( 'title' === $k ) {
			$nuevo['mm_precio']   = __( 'Precio', 'medmovil' );
			$nuevo['mm_duracion'] = __( 'Duración', 'medmovil' );
		}
	}
	return $nuevo;
}
add_filter( 'manage_mm_prestacion_posts_columns', 'medmovil_columnas_prestacion' );

/**
 * Contenido de las columnas propias.
 */
function medmovil_columna_prestacion( $col, $post_id ) {
	if ( 'mm_precio' === $col ) {
		echo esc_html( medmovil_precio( get_post_meta( $post_id, '_mm_precio', true ) ) );
	}
	if ( 'mm_duracion' === $col ) {
		$m = get_post_meta( $post_id, '_mm_duracion', true );
		echo $m ? esc_html( $m . ' min' ) : '—';
	}
}
add_action( 'manage_mm_prestacion_posts_custom_column', 'medmovil_columna_prestacion', 10, 2 );

/**
 * Ordena el archivo de prestaciones por línea y luego por orden manual.
 */
function medmovil_orden_archivo( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'mm_prestacion' ) || $query->is_tax( 'mm_linea' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$query->set( 'posts_per_page', 60 );
	}
}
add_action( 'pre_get_posts', 'medmovil_orden_archivo' );
