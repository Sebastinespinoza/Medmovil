<?php
/**
 * Panel de la portada: "arma tu visita".
 *
 * Reemplaza la agenda simulada que había antes. Aquí no se inventa nada: el
 * precio sale del examen, el tiempo de llegada y el nivel de cobertura salen de
 * la comuna. Es lo primero que pregunta un paciente por teléfono —qué examen y
 * dónde vive— y lo resuelve en dos clics.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Datos de exámenes y comunas para el panel y el chat.
 *
 * @return array
 */
function medmovil_datos_publicos() {
	$cache = wp_cache_get( 'medmovil_datos_publicos' );
	if ( is_array( $cache ) ) {
		return $cache;
	}

	// Las URLs completas y los datos de la línea se repetían en los 39 exámenes
	// y las 40 comunas: 5 KB en cada página. Ahora la base va una vez y el
	// JavaScript rearma el resto (ver window.mmDatos en main.js).
	$base_ex = trailingslashit( (string) get_post_type_archive_link( 'mm_prestacion' ) );
	$base_co = '';

	$lineas = array();
	$indice = array();

	$examenes = array();
	foreach ( get_posts( array(
		'post_type'      => 'mm_prestacion',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) ) as $p ) {
		$linea = medmovil_linea_de( $p->ID );
		$prep  = (string) get_post_meta( $p->ID, '_mm_preparacion', true );
		$clave = $linea ? $linea->slug : '';

		if ( ! isset( $indice[ $clave ] ) ) {
			$indice[ $clave ] = count( $lineas );
			$lineas[] = array(
				'n' => $linea ? $linea->name : '',
				's' => $clave,
				'c' => medmovil_color_linea( $linea ),
			);
		}

		$examenes[] = array(
			'id' => (int) $p->ID,
			'n'  => $p->post_title,
			's'  => $p->post_name,
			'p'  => (int) get_post_meta( $p->ID, '_mm_precio', true ),
			'm'  => (int) get_post_meta( $p->ID, '_mm_duracion', true ),
			'i'  => get_post_meta( $p->ID, '_mm_icono', true ) ?: 'il-eco',
			'li' => $indice[ $clave ],
			'a'  => medmovil_ayuno( $prep ),
			'ae' => medmovil_etiqueta_ayuno( $prep ),
			'cod' => (string) get_post_meta( $p->ID, '_mm_codigo', true ),
		);
	}

	$comunas = array();
	foreach ( medmovil_comunas( -1 ) as $c ) {
		if ( '' === $base_co ) {
			// La base sale del primer permalink: así respeta la estructura de
			// enlaces que tenga el sitio, sea la que sea.
			$base_co = preg_replace( '#[^/]+/?$#', '', (string) get_permalink( $c ) );
		}
		$comunas[] = array(
			'n' => $c->post_title,
			's' => $c->post_name,
			't' => (int) get_post_meta( $c->ID, '_mm_tiempo', true ),
			'z' => get_post_meta( $c->ID, '_mm_zona', true ) ?: 'c',
		);
	}

	$datos = array(
		'bx' => $base_ex,
		'bc' => $base_co,
		'ln' => $lineas,
		'ex' => $examenes,
		'co' => $comunas,
	);
	wp_cache_set( 'medmovil_datos_publicos', $datos );

	return $datos;
}

/**
 * Panel interactivo del hero.
 */
function medmovil_panel_hero() {
	$datos = medmovil_datos_publicos();
	if ( ! $datos['ex'] ) {
		return;
	}

	$lineas = array();
	foreach ( $datos['ex'] as $e ) {
		$indice = isset( $e['li'] ) ? (int) $e['li'] : -1;
		$nombre = isset( $datos['ln'][ $indice ]['n'] ) && '' !== $datos['ln'][ $indice ]['n']
			? $datos['ln'][ $indice ]['n'] : __( 'Otras prestaciones', 'medmovil' );
		$lineas[ $nombre ][] = $e;
	}
	?>
	<aside class="cot" id="cot">
		<svg class="scanwave" viewBox="0 0 400 300" preserveAspectRatio="none" aria-hidden="true">
			<path d="M-10 150C50 60 90 60 150 150s90 90 150 0 90-90 130 0"/>
			<path d="M-10 175C50 95 90 95 150 175s90 80 150 0 90-80 130 0"/>
			<path d="M-10 125C50 45 90 45 150 125s90 100 150 0 90-100 130 0"/>
		</svg>

		<div class="cot-in">
			<div class="cot-top">
				<b><?php esc_html_e( 'Arma tu visita', 'medmovil' ); ?></b>
				<span class="cot-pasos" aria-hidden="true"><i class="on"></i><i></i><i></i></span>
			</div>

			<div class="cot-f">
				<label for="cotEx"><span class="cot-n">1</span> <?php esc_html_e( '¿Qué examen necesitas?', 'medmovil' ); ?></label>
				<div class="cot-sel">
					<select id="cotEx">
						<?php foreach ( $lineas as $nombre => $items ) : ?>
							<optgroup label="<?php echo esc_attr( $nombre ); ?>">
								<?php foreach ( $items as $e ) : ?>
									<option value="<?php echo esc_attr( $e['s'] ); ?>"><?php echo esc_html( $e['n'] ); ?></option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
					</select>
					<?php medmovil_icono( 'i-chev' ); ?>
				</div>
			</div>

			<div class="cot-f">
				<label for="cotCo"><span class="cot-n">2</span> <?php esc_html_e( '¿En qué comuna vives?', 'medmovil' ); ?></label>
				<div class="cot-sel">
					<select id="cotCo">
						<option value=""><?php esc_html_e( 'Elige tu comuna', 'medmovil' ); ?></option>
						<?php foreach ( $datos['co'] as $c ) : ?>
							<option value="<?php echo esc_attr( $c['s'] ); ?>"><?php echo esc_html( $c['n'] ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php medmovil_icono( 'i-chev' ); ?>
				</div>
			</div>

			<div class="cot-res" id="cotRes">
				<div class="cot-linea">
					<span class="cot-ic" id="cotIc"><?php medmovil_marcar_icono( 'il-eco' ); ?><svg class="ilo" aria-hidden="true"><use href="#il-eco" id="cotUse"/></svg></span>
					<div>
						<b id="cotNom">—</b>
						<span id="cotSub"></span>
					</div>
				</div>

				<div class="cot-precio">
					<span id="cotP">—</span>
					<small><?php esc_html_e( 'todo incluido, sin cobro de traslado', 'medmovil' ); ?></small>
				</div>

				<ul class="cot-meta">
					<li><?php medmovil_icono( 'i-clock' ); ?> <span id="cotDur">—</span></li>
					<li><?php medmovil_icono( 'i-car' ); ?> <span id="cotLleg"><?php esc_html_e( 'elige tu comuna', 'medmovil' ); ?></span></li>
					<li><?php medmovil_icono( 'i-info' ); ?> <span id="cotPrep">—</span></li>
				</ul>

				<div class="cot-btns">
					<button type="button" class="btn btn-w mm-add" id="cotAdd">
						<?php medmovil_icono( 'i-plus' ); ?> <span><?php esc_html_e( 'Agregar a mi reserva', 'medmovil' ); ?></span>
					</button>
					<a class="btn btn-gh" id="cotVer" href="#">
						<?php esc_html_e( 'Ver el examen', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
					</a>
				</div>
			</div>

			<p class="cot-note">
				<?php medmovil_icono( 'i-clock' ); ?>
				<span><?php esc_html_e( 'Reservamos bloques de una hora completa, con el traslado ya considerado. Nunca llegamos apurados.', 'medmovil' ); ?></span>
			</p>
		</div>

	</aside>
	<?php
}

/**
 * Deja el catálogo y las comunas disponibles para el navegador.
 * Se imprime una vez por página, en el pie: lo usan el cotizador de la portada
 * y el asistente para armar sus tarjetas sin pedir nada al servidor.
 */
function medmovil_datos_json() {
	$json = wp_json_encode( medmovil_datos_publicos() );
	$json = str_replace( array( '</', '<!--' ), array( '<\\/', '<\\!--' ), $json );

	echo '<script type="application/json" id="mm-datos">' . $json . '</script>';
}
