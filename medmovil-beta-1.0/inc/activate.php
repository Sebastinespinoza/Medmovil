<?php
/**
 * Al activar el tema: crea las páginas, el menú, las líneas de atención y
 * carga el catálogo completo. Se ejecuta una sola vez.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Busca una entrada por título sin usar la función obsoleta de WordPress.
 *
 * @param string $titulo Título exacto.
 * @param string $tipo   Tipo de contenido.
 * @return WP_Post|null
 */
function medmovil_buscar_por_titulo( $titulo, $tipo ) {
	$q = new WP_Query( array(
		'post_type'              => $tipo,
		'title'                  => $titulo,
		'posts_per_page'         => 1,
		'post_status'            => 'any',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	) );
	return $q->have_posts() ? $q->posts[0] : null;
}

/**
 * Punto de entrada de la instalación.
 */
function medmovil_activar() {
	if ( get_option( 'medmovil_instalado' ) ) {
		medmovil_actualizar();
		flush_rewrite_rules();
		return;
	}

	medmovil_crear_lineas();
	medmovil_crear_catalogo();
	medmovil_crear_comunas();
	medmovil_crear_packs();
	medmovil_crear_testimonios();
	medmovil_crear_aliados();
	$paginas = medmovil_crear_paginas();
	medmovil_crear_menus( $paginas );
	medmovil_ajustes_por_defecto();
	medmovil_crear_codigos();
	medmovil_crear_roles();

	update_option( 'medmovil_instalado', MEDMOVIL_VERSION );
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'medmovil_activar' );

/**
 * Crea las líneas de atención.
 */
function medmovil_crear_lineas() {
	$orden = 0;
	foreach ( medmovil_lineas_atencion() as $nombre => $descripcion ) {
		$orden++;
		if ( term_exists( $nombre, 'mm_linea' ) ) {
			continue;
		}
		$term = wp_insert_term( $nombre, 'mm_linea', array( 'description' => $descripcion ) );
		if ( ! is_wp_error( $term ) ) {
			update_term_meta( $term['term_id'], '_mm_orden', $orden );
		}
	}
}

/**
 * Carga el catálogo de prestaciones.
 */
function medmovil_crear_catalogo() {
	$orden = 0;
	foreach ( medmovil_catalogo() as $item ) {
		$orden += 10;

		if ( medmovil_buscar_por_titulo( $item['titulo'], 'mm_prestacion' ) ) {
			continue;
		}

		$id = wp_insert_post( array(
			'post_type'    => 'mm_prestacion',
			'post_status'  => 'publish',
			'post_title'   => $item['titulo'],
			'post_content' => isset( $item['contenido'] ) ? $item['contenido'] : '',
			'post_excerpt' => isset( $item['resumen'] ) ? $item['resumen'] : '',
			'menu_order'   => $orden,
		) );

		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}

		wp_set_object_terms( $id, $item['linea'], 'mm_linea' );

		$mapa = array(
			'_mm_precio'      => 'precio',
			'_mm_duracion'    => 'duracion',
			'_mm_edad'        => 'edad',
			'_mm_icono'       => 'icono',
			'_mm_resumen'     => 'resumen',
			'_mm_incluye'     => 'incluye',
			'_mm_ventajas'    => 'ventajas',
			'_mm_condiciones' => 'condiciones',
			'_mm_preparacion' => 'preparacion',
			'_mm_faq'         => 'faq',
		);
		foreach ( $mapa as $meta => $clave ) {
			if ( isset( $item[ $clave ] ) ) {
				update_post_meta( $id, $meta, $item[ $clave ] );
			}
		}

		update_post_meta( $id, '_mm_seo_title', sprintf(
			/* translators: %s: nombre del examen */
			__( '%s a domicilio | MedMóvil', 'medmovil' ),
			$item['titulo']
		) );
	}
}

/**
 * Crea las comunas.
 */
function medmovil_crear_comunas() {
	$orden = 0;
	foreach ( medmovil_comunas_iniciales() as $comuna ) {
		list( $nombre, $zona, $minutos, $sectores ) = $comuna;
		$orden += 10;

		if ( medmovil_buscar_por_titulo( $nombre, 'mm_comuna' ) ) {
			continue;
		}

		$texto = sprintf(
			/* translators: 1: comuna, 2: minutos, 3: sectores */
			__( 'Realizamos ecografías, radiografías, electrocardiogramas y toma de muestras a domicilio en %1$s. Un profesional de la salud llega a tu casa con equipo digital portátil y el informe queda disponible en menos de 24 horas. El tiempo estimado de llegada en esta comuna es de %2$d minutos, y cubrimos sectores como %3$s.', 'medmovil' ),
			$nombre,
			$minutos,
			$sectores
		);

		$id = wp_insert_post( array(
			'post_type'    => 'mm_comuna',
			'post_status'  => 'publish',
			'post_title'   => $nombre,
			'post_content' => $texto,
			'menu_order'   => $orden,
		) );

		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}

		update_post_meta( $id, '_mm_zona', $zona );
		update_post_meta( $id, '_mm_tiempo', $minutos );
		update_post_meta( $id, '_mm_sectores', $sectores );
		update_post_meta( $id, '_mm_seo_title', sprintf(
			/* translators: %s: comuna */
			__( 'Ecografía a domicilio en %s | MedMóvil', 'medmovil' ),
			$nombre
		) );
		update_post_meta( $id, '_mm_seo_desc', sprintf(
			/* translators: 1: comuna, 2: minutos */
			__( 'Ecografías, radiografías y exámenes a domicilio en %1$s. Llegamos en unos %2$d minutos, con informe validado por médico radiólogo en 24 horas.', 'medmovil' ),
			$nombre,
			$minutos
		) );
	}
}

/**
 * Crea los packs.
 */
function medmovil_crear_packs() {
	$orden = 0;
	foreach ( medmovil_packs_iniciales() as $pack ) {
		$orden += 10;
		if ( medmovil_buscar_por_titulo( $pack['titulo'], 'mm_pack' ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'mm_pack',
			'post_status'  => 'publish',
			'post_title'   => $pack['titulo'],
			'post_content' => $pack['texto'],
			'menu_order'   => $orden,
		) );
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
		update_post_meta( $id, '_mm_etiqueta', $pack['etiqueta'] );
		update_post_meta( $id, '_mm_precio', $pack['precio'] );
		update_post_meta( $id, '_mm_precio_antes', $pack['antes'] );
		update_post_meta( $id, '_mm_incluye', $pack['incluye'] );
		update_post_meta( $id, '_mm_destacado', $pack['destacado'] );
	}
}

/**
 * Crea los testimonios.
 */
function medmovil_crear_testimonios() {
	$orden = 0;
	foreach ( medmovil_testimonios_iniciales() as $t ) {
		$orden += 10;
		$id = wp_insert_post( array(
			'post_type'    => 'mm_testimonio',
			'post_status'  => 'publish',
			'post_title'   => $t[0],
			'post_content' => $t[2],
			'menu_order'   => $orden,
		) );
		if ( ! is_wp_error( $id ) && $id ) {
			update_post_meta( $id, '_mm_autor', $t[0] );
			update_post_meta( $id, '_mm_lugar', $t[1] );
		}
	}
}

/**
 * Crea las alianzas y sube sus logos a la biblioteca.
 */
function medmovil_crear_aliados() {
	$orden = 0;
	foreach ( medmovil_aliados_iniciales() as $a ) {
		$orden += 10;
		if ( medmovil_buscar_por_titulo( $a[0], 'mm_aliado' ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'mm_aliado',
			'post_status'  => 'publish',
			'post_title'   => $a[0],
			'post_content' => $a[1],
			'menu_order'   => $orden,
		) );
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
		$adjunto = medmovil_subir_imagen( $a[2], $a[0] );
		if ( $adjunto ) {
			set_post_thumbnail( $id, $adjunto );
		}
	}
}

/**
 * Copia una imagen del tema a la biblioteca de medios.
 *
 * @param string $archivo Nombre del archivo dentro de assets/img.
 * @param string $titulo  Título del adjunto.
 * @return int|false ID del adjunto.
 */
function medmovil_subir_imagen( $archivo, $titulo ) {
	$origen = MEDMOVIL_DIR . '/assets/img/' . $archivo;
	if ( ! file_exists( $origen ) ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$subida = wp_upload_bits( $archivo, null, file_get_contents( $origen ) ); // phpcs:ignore
	if ( ! empty( $subida['error'] ) ) {
		return false;
	}

	$tipo = wp_check_filetype( $subida['file'] );
	$id   = wp_insert_attachment( array(
		'post_mime_type' => $tipo['type'],
		'post_title'     => $titulo,
		'post_status'    => 'inherit',
	), $subida['file'] );

	if ( is_wp_error( $id ) || ! $id ) {
		return false;
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $subida['file'] ) );
	return $id;
}

/**
 * Crea las páginas del sitio y define la portada.
 *
 * @return array Slugs con su ID.
 */
function medmovil_crear_paginas() {
	$ids = array();

	$portada = get_page_by_path( 'inicio' );
	if ( ! $portada ) {
		$id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => __( 'Inicio', 'medmovil' ),
			'post_name'   => 'inicio',
		) );
		if ( ! is_wp_error( $id ) ) {
			$portada = get_post( $id );
		}
	}
	if ( $portada ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $portada->ID );
		$ids['inicio'] = $portada->ID;
	}

	foreach ( medmovil_paginas_iniciales() as $slug => $pagina ) {
		if ( get_page_by_path( $slug ) ) {
			$ids[ $slug ] = get_page_by_path( $slug )->ID;
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $pagina['titulo'],
			'post_name'    => $slug,
			'post_content' => $pagina['contenido'],
		) );
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
		if ( $pagina['plantilla'] ) {
			update_post_meta( $id, '_wp_page_template', $pagina['plantilla'] );
		}
		$ids[ $slug ] = $id;
	}

	return $ids;
}

/**
 * Arma los menús del sitio.
 *
 * @param array $paginas Slugs con su ID.
 */
function medmovil_crear_menus( $paginas ) {
	$menus = array(
		'principal' => array(
			'nombre' => __( 'Menú principal', 'medmovil' ),
			'items'  => array(
				array( 'tipo' => 'pagina', 'slug' => 'cobertura', 'titulo' => __( 'Cobertura', 'medmovil' ) ),
				array( 'tipo' => 'pagina', 'slug' => 'como-funciona', 'titulo' => __( 'Cómo funciona', 'medmovil' ) ),
				array( 'tipo' => 'pagina', 'slug' => 'precios', 'titulo' => __( 'Precios', 'medmovil' ) ),
				array( 'tipo' => 'pagina', 'slug' => 'cotizador', 'titulo' => __( 'Cotizador', 'medmovil' ) ),
				array( 'tipo' => 'pagina', 'slug' => 'blog', 'titulo' => __( 'Blog', 'medmovil' ) ),
				array( 'tipo' => 'pagina', 'slug' => 'convenios', 'titulo' => __( 'Convenios', 'medmovil' ) ),
			),
		),
		'pie-pacientes' => array(
			'nombre' => __( 'Pie — Pacientes', 'medmovil' ),
			'items'  => array(
				array( 'tipo' => 'pagina', 'slug' => 'como-funciona' ),
				array( 'tipo' => 'pagina', 'slug' => 'precios' ),
				array( 'tipo' => 'pagina', 'slug' => 'cotizador' ),
				array( 'tipo' => 'pagina', 'slug' => 'blog' ),
				array( 'tipo' => 'pagina', 'slug' => 'cobertura' ),
				array( 'tipo' => 'pagina', 'slug' => 'portal' ),
				array( 'tipo' => 'pagina', 'slug' => 'contacto' ),
			),
		),
		'pie-empresa' => array(
			'nombre' => __( 'Pie — MedMóvil', 'medmovil' ),
			'items'  => array(
				array( 'tipo' => 'pagina', 'slug' => 'nosotros' ),
				array( 'tipo' => 'pagina', 'slug' => 'convenios' ),
				array( 'tipo' => 'pagina', 'slug' => 'privacidad' ),
			),
		),
	);

	$ubicaciones = get_theme_mod( 'nav_menu_locations', array() );

	foreach ( $menus as $ubicacion => $def ) {
		$menu = wp_get_nav_menu_object( $def['nombre'] );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $def['nombre'] );
			if ( is_wp_error( $menu_id ) ) {
				continue;
			}
		} else {
			$menu_id = $menu->term_id;
		}

		if ( ! empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			$ubicaciones[ $ubicacion ] = $menu_id;
			continue;
		}

		foreach ( $def['items'] as $item ) {
			if ( 'archivo' === $item['tipo'] ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'  => $item['titulo'],
					'menu-item-url'    => get_post_type_archive_link( $item['objeto'] ),
					'menu-item-status' => 'publish',
				) );
				continue;
			}
			if ( empty( $paginas[ $item['slug'] ] ) ) {
				continue;
			}
			$args = array(
				'menu-item-object-id' => $paginas[ $item['slug'] ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			);
			if ( ! empty( $item['titulo'] ) ) {
				$args['menu-item-title'] = $item['titulo'];
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}

		$ubicaciones[ $ubicacion ] = $menu_id;
	}

	// El menú de prestaciones del pie se arma con las líneas de atención.
	$menu = wp_get_nav_menu_object( __( 'Pie — Prestaciones', 'medmovil' ) );
	$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( __( 'Pie — Prestaciones', 'medmovil' ) );
	if ( ! is_wp_error( $menu_id ) && empty( wp_get_nav_menu_items( $menu_id ) ) ) {
		foreach ( get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => false ) ) as $linea ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object-id' => $linea->term_id,
				'menu-item-object'    => 'mm_linea',
				'menu-item-type'      => 'taxonomy',
				'menu-item-status'    => 'publish',
			) );
		}
		$ubicaciones['pie-servicios'] = $menu_id;
	}

	set_theme_mod( 'nav_menu_locations', $ubicaciones );
}

/**
 * Deja los ajustes por defecto y afina permalinks.
 */
function medmovil_ajustes_por_defecto() {
	$opts = get_option( 'medmovil_opts', array() );
	if ( empty( $opts ) ) {
		$defecto = array();
		foreach ( medmovil_ajustes() as $grupo ) {
			foreach ( $grupo['campos'] as $clave => $campo ) {
				$defecto[ $clave ] = $campo['def'];
			}
		}
		update_option( 'medmovil_opts', $defecto );
	}

	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	if ( ! get_option( 'blogdescription' ) || 'Otro sitio realizado con WordPress' === get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', __( 'Ecografías y exámenes a domicilio en la Región Metropolitana', 'medmovil' ) );
	}
}

/**
 * Botón para recargar el catálogo si el usuario borró contenido y se arrepintió.
 */
function medmovil_recargar_catalogo() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para hacer esto.', 'medmovil' ) );
	}
	check_admin_referer( 'medmovil_recargar' );

	medmovil_crear_lineas();
	medmovil_crear_catalogo();
	medmovil_crear_comunas();
	flush_rewrite_rules();

	wp_safe_redirect( admin_url( 'edit.php?post_type=mm_prestacion&medmovil_recargado=1' ) );
	exit;
}
add_action( 'admin_post_medmovil_recargar', 'medmovil_recargar_catalogo' );

/**
 * Actualización de un sitio que ya tenía el tema.
 *
 * Solo agrega lo que falta: nunca reescribe precios, textos ni páginas que el
 * administrador haya editado. Se puede correr las veces que sea.
 */
function medmovil_actualizar() {
	$paginas = medmovil_crear_paginas();
	medmovil_completar_menus( $paginas );
	medmovil_crear_codigos();
	medmovil_asignar_plantillas();
	medmovil_crear_roles();

	update_option( 'medmovil_instalado', MEDMOVIL_VERSION );
}

/**
 * Agrega al menú principal las entradas nuevas que todavía no estén.
 *
 * @param array $paginas Slugs con su ID.
 */
function medmovil_completar_menus( $paginas ) {
	$ubicaciones = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $ubicaciones['principal'] ) ) {
		medmovil_crear_menus( $paginas );
		return;
	}

	$menu_id = (int) $ubicaciones['principal'];
	$actuales = array();
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
		$actuales[] = (int) $item->object_id;
	}

	foreach ( array( 'cotizador', 'blog' ) as $slug ) {
		if ( empty( $paginas[ $slug ] ) || in_array( (int) $paginas[ $slug ], $actuales, true ) ) {
			continue;
		}
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-object-id' => (int) $paginas[ $slug ],
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-title'     => get_the_title( $paginas[ $slug ] ),
		) );
	}

	// "Mi cuenta" al pie, en la columna de pacientes: quien crea una cuenta
	// tiene que poder volver a ella desde cualquier página.
	if ( ! empty( $ubicaciones['pie-pacientes'] ) && ! empty( $paginas['portal'] ) ) {
		$pie = (int) $ubicaciones['pie-pacientes'];
		$hay = false;
		foreach ( (array) wp_get_nav_menu_items( $pie ) as $item ) {
			if ( (int) $item->object_id === (int) $paginas['portal'] ) {
				$hay = true;
				break;
			}
		}
		if ( ! $hay ) {
			wp_update_nav_menu_item( $pie, 0, array(
				'menu-item-object-id' => (int) $paginas['portal'],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-title'     => get_the_title( $paginas['portal'] ),
			) );
		}
	}
}

/**
 * Códigos de prestación, para que el paciente pueda buscar por el número que
 * trae su orden médica.
 *
 * Solo rellena los que estén vacíos: si el administrador corrigió uno, se respeta.
 * Son códigos de referencia y hay que verificarlos contra el arancel vigente.
 */
function medmovil_crear_codigos() {
	$codigos = array(
		'ecografia-abdominal'                        => '0404003',
		'ecografia-obstetrica'                       => '0404002',
		'ecografia-ginecologica'                     => '0404006',
		'ecografia-de-prostata-y-vejiga'             => '0404009',
		'ecografia-renal-y-de-vias-urinarias'        => '0404010',
		'ecografia-mamaria'                          => '0404012',
		'ecografia-testicular'                       => '0404014',
		'ecografia-de-tiroides'                      => '0404015',
		'ecografia-de-partes-blandas'                => '0404016',
		'ecografia-de-hombro'                        => '0404016',
		'ecografia-de-codo'                          => '0404016',
		'ecografia-de-muneca'                        => '0404016',
		'ecografia-de-mano-y-dedos'                  => '0404016',
		'ecografia-de-cadera'                        => '0404016',
		'ecografia-de-rodilla'                       => '0404016',
		'ecografia-de-tobillo-y-pie'                 => '0404016',
		'ecografia-de-cadera-del-lactante'           => '0404016',
		'doppler-venoso-de-extremidades-inferiores'  => '0404118',
		'doppler-arterial-de-extremidades-inferiores' => '0404118',
		'doppler-venoso-de-extremidades-superiores'  => '0404118',
		'doppler-arterial-de-extremidades-superiores' => '0404118',
		'doppler-testicular'                         => '0404118',
		'doppler-renal'                              => '0404121',
		'doppler-carotideo'                          => '0404121',
		'radiografia-de-torax'                       => '0401070',
		'radiografia-de-columna'                     => '0401046',
		'radiografia-de-pelvis'                      => '0401051',
		'radiografia-de-extremidades'                => '0401054',
		'electrocardiograma-de-reposo'               => '1701001',
		'holter-de-ritmo-de-24-horas'                => '1701006',
		'monitoreo-de-presion-arterial-de-24-horas'  => '1701009',
	);

	foreach ( $codigos as $slug => $codigo ) {
		$post = get_page_by_path( $slug, OBJECT, 'mm_prestacion' );
		if ( ! $post ) {
			continue;
		}
		if ( '' === (string) get_post_meta( $post->ID, '_mm_codigo', true ) ) {
			update_post_meta( $post->ID, '_mm_codigo', $codigo );
		}
	}
}

/**
 * Asigna la plantilla a las páginas que se crearon antes de que existiera.
 *
 * Solo toca las que no tengan ninguna puesta: si el administrador eligió otra a
 * mano, se respeta.
 */
function medmovil_asignar_plantillas() {
	$mapa = array(
		'reservar'     => 'template-reserva.php',
		'como-funciona' => 'template-como-funciona.php',
		'portal'        => 'template-portal.php',
		'cotizador'     => 'template-cotizador.php',
		'blog'          => 'template-blog.php',
		'precios'       => 'template-precios.php',
		'cobertura'     => 'template-cobertura.php',
		'convenios'     => 'template-convenios.php',
	);

	foreach ( $mapa as $slug => $plantilla ) {
		$pagina = get_page_by_path( $slug );
		if ( ! $pagina ) {
			continue;
		}
		$actual = (string) get_post_meta( $pagina->ID, '_wp_page_template', true );
		if ( '' === $actual || 'default' === $actual ) {
			update_post_meta( $pagina->ID, '_wp_page_template', $plantilla );
		}
	}
}

/** Crea la página de reserva al actualizar un tema ya activo, sin tocar páginas editadas. */
function medmovil_actualizacion_reservas() {
	if ( get_option( 'medmovil_instalado' ) && version_compare( (string) get_option( 'medmovil_instalado' ), MEDMOVIL_VERSION, '<' ) ) {
		$pagina = get_page_by_path( 'reservar' );
		if ( ! $pagina ) {
			$id = wp_insert_post( array(
				'post_type' => 'page', 'post_status' => 'publish',
				'post_title' => __( 'Solicitar hora', 'medmovil' ), 'post_name' => 'reservar',
			) );
			$pagina = $id && ! is_wp_error( $id ) ? get_post( $id ) : null;
		}
		if ( $pagina ) {
			$plantilla = (string) get_post_meta( $pagina->ID, '_wp_page_template', true );
			if ( '' === $plantilla || 'default' === $plantilla ) {
				update_post_meta( $pagina->ID, '_wp_page_template', 'template-reserva.php' );
			}
		}
		medmovil_crear_roles();
		update_option( 'medmovil_instalado', MEDMOVIL_VERSION );
	}
}
add_action( 'init', 'medmovil_actualizacion_reservas', 30 );
