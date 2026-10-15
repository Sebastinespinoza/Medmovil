<?php
/**
 * Blog.
 *
 * El blog es la palanca de SEO más barata que tiene MedMóvil: el paciente busca
 * "para qué sirve una ecografía de rodilla" meses antes de necesitarla. Cada
 * artículo enlaza a la prestación que corresponde, así que el tráfico
 * informativo aterriza en una página que vende.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minutos de lectura de una entrada.
 *
 * @param int $post_id ID.
 * @return int
 */
function medmovil_minutos_lectura( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$guardado = (int) get_post_meta( $post_id, '_mm_lectura', true );
	if ( $guardado ) {
		return $guardado;
	}

	$palabras = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );

	return max( 1, (int) round( $palabras / 200 ) );
}

/**
 * Ilustración por categoría, para las entradas sin imagen destacada.
 *
 * @param int $post_id ID.
 * @return string Identificador del símbolo.
 */
function medmovil_icono_categoria( $post_id = 0 ) {
	$mapa = array(
		'imagenologia'      => 'il-eco',
		'ecografias'        => 'il-eco',
		'musculoesqueletica' => 'il-msk',
		'doppler'           => 'il-dop',
		'cardiologia'       => 'il-cardio',
		'laboratorio'       => 'il-lab',
		'radiologia'        => 'il-rx',
		'enfermeria'        => 'il-nurse',
		'funcion-pulmonar'  => 'il-lung',
		'adulto-mayor'      => 'il-elder',
		'salud-preventiva'  => 'il-shield',
		'a-domicilio'       => 'il-arrive',
	);

	foreach ( (array) get_the_category( $post_id ) as $cat ) {
		if ( isset( $mapa[ $cat->slug ] ) ) {
			return $mapa[ $cat->slug ];
		}
	}

	return 'il-doc';
}

/**
 * Tarjeta de entrada para la grilla.
 *
 * @param WP_Post|int $post Entrada.
 */
function medmovil_tarjeta_entrada( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}
	$cats = get_the_category( $post->ID );
	?>
	<article class="bpost" data-rv>
		<a class="bpost-img" href="<?php echo esc_url( get_permalink( $post ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<?php echo get_the_post_thumbnail( $post, 'medium_large' ); ?>
			<?php else : ?>
				<span class="bpost-ph"><?php medmovil_icono( medmovil_icono_categoria( $post->ID ), 'ilo' ); ?></span>
			<?php endif; ?>
		</a>
		<div class="bpost-body">
			<?php if ( $cats ) : ?>
				<span class="bpost-cat"><?php echo esc_html( $cats[0]->name ); ?></span>
			<?php endif; ?>
			<h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 22 ) ); ?></p>
			<span class="bpost-meta">
				<?php medmovil_icono( 'i-clock' ); ?>
				<?php
				printf(
					/* translators: %d: minutos */
					esc_html__( '%d min de lectura', 'medmovil' ),
					(int) medmovil_minutos_lectura( $post->ID )
				);
				?>
				<i></i>
				<?php echo esc_html( get_the_date( 'j M Y', $post ) ); ?>
			</span>
		</div>
	</article>
	<?php
}

/**
 * Chips de categorías del blog.
 *
 * @param string $activa Slug activo.
 */
function medmovil_chips_blog( $activa = '' ) {
	$cats = get_categories( array( 'hide_empty' => true ) );
	if ( count( $cats ) < 2 ) {
		return;
	}
	$base = get_permalink( get_option( 'page_for_posts' ) );
	if ( ! $base ) {
		$base = home_url( '/blog/' );
	}
	?>
	<div class="wrap fl-chips" role="group" aria-label="<?php esc_attr_e( 'Filtrar por tema', 'medmovil' ); ?>">
		<a class="chip<?php echo '' === $activa ? ' on' : ''; ?>" href="<?php echo esc_url( $base ); ?>">
			<?php esc_html_e( 'Todos los temas', 'medmovil' ); ?>
		</a>
		<?php foreach ( $cats as $cat ) : ?>
			<a class="chip<?php echo $activa === $cat->slug ? ' on' : ''; ?>"
			   href="<?php echo esc_url( add_query_arg( 'tema', $cat->slug, $base ) ); ?>">
				<?php echo esc_html( $cat->name ); ?>
				<i><?php echo (int) $cat->count; ?></i>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Prestaciones que menciona un artículo, para cerrar con enlaces al catálogo.
 *
 * @param int $post_id ID.
 * @return WP_Post[]
 */
function medmovil_prestaciones_del_articulo( $post_id ) {
	$guardadas = medmovil_lineas( get_post_meta( $post_id, '_mm_prestaciones', true ) );
	$salida    = array();

	foreach ( $guardadas as $fila ) {
		$p = get_page_by_path( sanitize_title( $fila[0] ), OBJECT, 'mm_prestacion' );
		if ( $p ) {
			$salida[] = $p;
		}
	}

	if ( $salida ) {
		return array_slice( $salida, 0, 4 );
	}

	// Sin lista guardada, buscamos por el texto del artículo.
	$texto = mb_strtolower( remove_accents( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) . ' ' . get_the_title( $post_id ) ) ) );

	foreach ( get_posts( array( 'post_type' => 'mm_prestacion', 'posts_per_page' => -1 ) ) as $p ) {
		$nombre = mb_strtolower( remove_accents( $p->post_title ) );
		if ( false !== mb_strpos( $texto, $nombre ) ) {
			$salida[] = $p;
		}
		if ( count( $salida ) >= 4 ) {
			break;
		}
	}

	return $salida;
}

/**
 * Caja para elegir a mano las prestaciones que enlaza el artículo.
 */
function medmovil_meta_articulo() {
	add_meta_box(
		'mm_articulo',
		__( 'MedMóvil — enlaces al catálogo', 'medmovil' ),
		'medmovil_caja_articulo',
		'post',
		'side'
	);
}
add_action( 'add_meta_boxes', 'medmovil_meta_articulo' );

/**
 * Dibuja la caja.
 *
 * @param WP_Post $post Entrada.
 */
function medmovil_caja_articulo( $post ) {
	wp_nonce_field( 'mm_articulo', 'mm_articulo_nonce' );
	$valor = get_post_meta( $post->ID, '_mm_prestaciones', true );
	?>
	<p>
		<label for="_mm_prestaciones"><?php esc_html_e( 'Exámenes que ofrece este artículo', 'medmovil' ); ?></label>
		<textarea id="_mm_prestaciones" name="_mm_prestaciones" rows="4" class="widefat" placeholder="ecografia-de-rodilla"><?php echo esc_textarea( $valor ); ?></textarea>
	</p>
	<p class="description">
		<?php esc_html_e( 'Un slug por línea. Si lo dejas vacío, el tema busca solo los exámenes que el artículo nombre en su texto.', 'medmovil' ); ?>
	</p>
	<?php
}

/**
 * Guarda la caja.
 *
 * @param int $post_id ID.
 */
function medmovil_guardar_articulo( $post_id ) {
	if ( ! isset( $_POST['mm_articulo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mm_articulo_nonce'] ), 'mm_articulo' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['_mm_prestaciones'] ) ) {
		update_post_meta( $post_id, '_mm_prestaciones', sanitize_textarea_field( wp_unslash( $_POST['_mm_prestaciones'] ) ) );
	}
}
add_action( 'save_post_post', 'medmovil_guardar_articulo' );

/**
 * Aviso en las entradas escritas con el generador, para que nadie publique sin leer.
 */
function medmovil_aviso_borrador_ia() {
	$pantalla = get_current_screen();
	if ( ! $pantalla || 'post' !== $pantalla->id ) {
		return;
	}
	$post = get_post();
	if ( ! $post || ! get_post_meta( $post->ID, '_mm_ia', true ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'Borrador escrito con el generador.', 'medmovil' ),
		esc_html__( 'Léelo completo y corrige lo que haga falta antes de publicar: es contenido de salud y sale con tu nombre.', 'medmovil' )
	);
}
add_action( 'admin_notices', 'medmovil_aviso_borrador_ia' );
