<?php
/**
 * Profesionales registrados.
 *
 * Cada tecnólogo, enfermero o técnico elige las comunas donde quiere trabajar.
 * Con eso, la página de cada comuna muestra quién atiende ahí y el paciente
 * puede pedir a alguien en particular. Es la base del vínculo paciente-profesional
 * que después se cierra con la agenda y las cuentas.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra el tipo de contenido.
 */
function medmovil_cpt_profesional() {
	register_post_type( 'mm_profesional', array(
		'labels' => array(
			'name'          => __( 'Profesionales', 'medmovil' ),
			'singular_name' => __( 'Profesional', 'medmovil' ),
			'add_new_item'  => __( 'Añadir profesional', 'medmovil' ),
			'edit_item'     => __( 'Editar profesional', 'medmovil' ),
			'not_found'     => __( 'Todavía no hay profesionales registrados', 'medmovil' ),
			'menu_name'     => __( 'Profesionales', 'medmovil' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-businessperson',
		'menu_position' => 22,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
	) );
}
add_action( 'init', 'medmovil_cpt_profesional' );

/**
 * Campos del profesional.
 *
 * @return array
 */
function medmovil_campos_profesional() {
	return array(
		'_mm_rol'      => array( 'label' => __( 'Profesión', 'medmovil' ), 'ph' => __( 'Tecnólogo Médico', 'medmovil' ) ),
		'_mm_registro' => array( 'label' => __( 'Registro Superintendencia de Salud', 'medmovil' ), 'ph' => '' ),
		'_mm_anios'    => array( 'label' => __( 'Años de experiencia', 'medmovil' ), 'tipo' => 'number', 'ph' => '' ),
		'_mm_hace'     => array( 'label' => __( 'Qué exámenes realiza', 'medmovil' ), 'tipo' => 'textarea', 'ayuda' => __( 'Uno por línea. Ejemplo: Ecografía abdominal', 'medmovil' ) ),
		'_mm_comunas'  => array( 'label' => __( 'Comunas donde atiende', 'medmovil' ), 'tipo' => 'textarea', 'ayuda' => __( 'Una por línea, escrita igual que en el menú Comunas. Ejemplo: Las Condes', 'medmovil' ) ),
		'_mm_bio'      => array( 'label' => __( 'Presentación breve', 'medmovil' ), 'tipo' => 'textarea', 'ayuda' => __( 'Dos o tres líneas, en primera persona. Es lo que lee el paciente antes de pedirlo.', 'medmovil' ) ),
	);
}

/**
 * Caja de edición.
 */
function medmovil_meta_profesional() {
	add_meta_box(
		'mm_profesional',
		__( 'Ficha del profesional', 'medmovil' ),
		'medmovil_caja_profesional',
		'mm_profesional',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'medmovil_meta_profesional' );

/**
 * Dibuja la caja.
 *
 * @param WP_Post $post Entrada.
 */
function medmovil_caja_profesional( $post ) {
	wp_nonce_field( 'mm_prof', 'mm_prof_nonce' );
	echo '<table class="form-table" role="presentation">';

	foreach ( medmovil_campos_profesional() as $clave => $campo ) {
		$valor = get_post_meta( $post->ID, $clave, true );
		$tipo  = isset( $campo['tipo'] ) ? $campo['tipo'] : 'text';

		echo '<tr><th scope="row"><label for="' . esc_attr( $clave ) . '">' . esc_html( $campo['label'] ) . '</label></th><td>';

		if ( 'textarea' === $tipo ) {
			printf(
				'<textarea id="%1$s" name="%1$s" rows="5" class="large-text">%2$s</textarea>',
				esc_attr( $clave ),
				esc_textarea( $valor )
			);
		} else {
			printf(
				'<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" class="regular-text" placeholder="%4$s">',
				esc_attr( $tipo ),
				esc_attr( $clave ),
				esc_attr( $valor ),
				esc_attr( isset( $campo['ph'] ) ? $campo['ph'] : '' )
			);
		}

		if ( ! empty( $campo['ayuda'] ) ) {
			echo '<p class="description">' . esc_html( $campo['ayuda'] ) . '</p>';
		}
		echo '</td></tr>';
	}

	echo '</table>';
	echo '<p class="description">' . esc_html__( 'La foto se sube en "Imagen destacada", a la derecha. Si no hay foto, se muestran las iniciales.', 'medmovil' ) . '</p>';
}

/**
 * Guarda la ficha.
 *
 * @param int $post_id ID.
 */
function medmovil_guardar_profesional( $post_id ) {
	if ( ! isset( $_POST['mm_prof_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mm_prof_nonce'] ), 'mm_prof' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( medmovil_campos_profesional() as $clave => $campo ) {
		if ( ! isset( $_POST[ $clave ] ) ) {
			continue;
		}
		$valor = wp_unslash( $_POST[ $clave ] );
		$tipo  = isset( $campo['tipo'] ) ? $campo['tipo'] : 'text';

		update_post_meta(
			$post_id,
			$clave,
			'textarea' === $tipo ? sanitize_textarea_field( $valor ) : sanitize_text_field( $valor )
		);
	}
}
add_action( 'save_post_mm_profesional', 'medmovil_guardar_profesional' );

/**
 * Profesionales que atienden en una comuna.
 *
 * @param string $comuna Nombre de la comuna.
 * @return WP_Post[]
 */
function medmovil_profesionales_de( $comuna ) {
	$todos = get_posts( array(
		'post_type'      => 'mm_profesional',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );

	if ( ! $comuna ) {
		return $todos;
	}

	$buscada = mb_strtolower( remove_accents( $comuna ) );
	$coinciden = array();

	foreach ( $todos as $p ) {
		foreach ( medmovil_lineas( get_post_meta( $p->ID, '_mm_comunas', true ) ) as $fila ) {
			if ( mb_strtolower( remove_accents( trim( $fila[0] ) ) ) === $buscada ) {
				$coinciden[] = $p;
				break;
			}
		}
	}

	return $coinciden;
}

/**
 * Bloque de profesionales de una comuna.
 *
 * @param string $comuna Nombre de la comuna.
 */
function medmovil_bloque_profesionales( $comuna ) {
	$equipo = medmovil_profesionales_de( $comuna );
	if ( ! $equipo ) {
		return;
	}
	?>
	<section class="band" style="padding-block:56px">
		<div class="wrap">
			<div class="sec-head" data-rv>
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Quién te atiende', 'medmovil' ); ?></p>
					<h2 style="margin-top:14px">
						<?php
						printf(
							/* translators: %s: comuna */
							esc_html__( 'Profesionales que trabajan en %s', 'medmovil' ),
							esc_html( $comuna )
						);
						?>
					</h2>
					<p class="lede"><?php esc_html_e( 'Puedes pedir que te atienda siempre la misma persona. Si te acomodó, dinos su nombre al agendar y coordinamos su disponibilidad.', 'medmovil' ); ?></p>
				</div>
			</div>

			<div class="team">
				<?php foreach ( $equipo as $p ) :
					$rol   = get_post_meta( $p->ID, '_mm_rol', true );
					$anios = (int) get_post_meta( $p->ID, '_mm_anios', true );
					$reg   = get_post_meta( $p->ID, '_mm_registro', true );
					$bio   = get_post_meta( $p->ID, '_mm_bio', true );
					$hace  = medmovil_lineas( get_post_meta( $p->ID, '_mm_hace', true ) );
					$ini   = mb_substr( $p->post_title, 0, 1 );
					$msg   = sprintf(
						/* translators: 1: profesional, 2: comuna */
						__( 'Hola, quiero agendar un examen a domicilio en %2$s y me gustaría que me atienda %1$s.', 'medmovil' ),
						$p->post_title,
						$comuna
					);
					?>
					<article class="tm" data-rv>
						<div class="tm-top">
							<?php if ( has_post_thumbnail( $p->ID ) ) : ?>
								<span class="tm-foto"><?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?></span>
							<?php else : ?>
								<span class="tm-foto tm-ini"><?php echo esc_html( $ini ); ?></span>
							<?php endif; ?>
							<div>
								<b><?php echo esc_html( $p->post_title ); ?></b>
								<span><?php echo esc_html( $rol ? $rol : __( 'Profesional de la salud', 'medmovil' ) ); ?></span>
							</div>
						</div>

						<?php if ( $bio ) : ?>
							<p class="tm-bio"><?php echo esc_html( $bio ); ?></p>
						<?php endif; ?>

						<ul class="tm-datos">
							<?php if ( $anios ) : ?>
								<li><?php medmovil_icono( 'i-check' ); ?>
									<?php
									printf(
										/* translators: %d: años */
										esc_html( _n( '%d año de experiencia', '%d años de experiencia', $anios, 'medmovil' ) ),
										(int) $anios
									);
									?>
								</li>
							<?php endif; ?>
							<?php if ( $reg ) : ?>
								<li><?php medmovil_icono( 'i-shield' ); ?> <?php echo esc_html( __( 'Registro ', 'medmovil' ) . $reg ); ?></li>
							<?php endif; ?>
						</ul>

						<?php if ( $hace ) : ?>
							<div class="tm-hace">
								<?php foreach ( array_slice( $hace, 0, 5 ) as $h ) : ?>
									<span><?php echo esc_html( $h[0] ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<a class="btn btn-o btn-sm" href="<?php echo esc_url( medmovil_whatsapp( $msg ) ); ?>" target="_blank" rel="noopener">
							<?php esc_html_e( 'Pedir a este profesional', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Recordatorio en el panel: sin profesionales cargados, la comuna no muestra equipo.
 */
function medmovil_aviso_profesionales() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$pantalla = get_current_screen();
	if ( ! $pantalla || 'dashboard' !== $pantalla->id ) {
		return;
	}

	$hay = get_posts( array( 'post_type' => 'mm_profesional', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $hay ) {
		return;
	}

	printf(
		'<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>',
		esc_html__( 'Todavía no hay profesionales cargados. Mientras no los agregues, las páginas de comuna no muestran quién atiende ahí.', 'medmovil' ),
		esc_url( admin_url( 'post-new.php?post_type=mm_profesional' ) ),
		esc_html__( 'Agregar el primero', 'medmovil' )
	);
}
add_action( 'admin_notices', 'medmovil_aviso_profesionales' );
