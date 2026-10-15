<?php
/**
 * Selector de zona del cuerpo: el paciente marca dónde le molesta y le
 * mostramos los exámenes de esa zona.
 *
 * La figura anatómica se adaptó del pen "Body_selector" de Keyhole
 * (https://codepen.io/yarrumk/pen/WbzvoM), publicado bajo licencia MIT.
 * Ver CREDITOS.md en la raíz del tema.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Qué examen corresponde a cada zona del cuerpo.
 * Editable: si sumas una prestación nueva, agrégala al grupo que le toque.
 *
 * @return array
 */
function medmovil_zonas() {
	return array(
		'tiroides' => array( 'ecografia-de-tiroides', 'doppler-carotideo', 'ecografia-de-partes-blandas' ),
		'torax'    => array( 'radiografia-de-torax', 'electrocardiograma-de-reposo', 'ecografia-mamaria', 'espirometria', 'holter-de-ritmo-de-24-horas' ),
		'columna'  => array( 'radiografia-de-columna', 'ecografia-de-partes-blandas' ),
		'abdomen'  => array( 'ecografia-abdominal', 'ecografia-renal-y-de-vias-urinarias', 'toma-de-muestras-a-domicilio' ),
		'pelvis'   => array( 'ecografia-ginecologica', 'ecografia-de-prostata-y-vejiga', 'ecografia-obstetrica', 'ecografia-testicular', 'radiografia-de-pelvis' ),
		'cadera'   => array( 'ecografia-de-cadera', 'radiografia-de-pelvis', 'ecografia-de-cadera-del-lactante' ),
		'hombro'   => array( 'ecografia-de-hombro', 'radiografia-de-extremidades' ),
		'brazo'    => array( 'ecografia-de-partes-blandas', 'doppler-venoso-de-extremidades-superiores', 'doppler-arterial-de-extremidades-superiores' ),
		'codo'     => array( 'ecografia-de-codo', 'radiografia-de-extremidades' ),
		'muneca'   => array( 'ecografia-de-muneca', 'radiografia-de-extremidades' ),
		'mano'     => array( 'ecografia-de-mano-y-dedos', 'radiografia-de-extremidades' ),
		'muslo'    => array( 'ecografia-de-partes-blandas', 'doppler-venoso-de-extremidades-inferiores', 'radiografia-de-extremidades' ),
		'rodilla'  => array( 'ecografia-de-rodilla', 'radiografia-de-extremidades' ),
		'pierna'   => array( 'doppler-venoso-de-extremidades-inferiores', 'doppler-arterial-de-extremidades-inferiores', 'ecografia-de-partes-blandas' ),
		'pie'      => array( 'ecografia-de-tobillo-y-pie', 'radiografia-de-extremidades', 'doppler-arterial-de-extremidades-inferiores' ),
	);
}

/**
 * Arma el índice de zonas con los datos reales de cada prestación.
 *
 * @return array
 */
function medmovil_zonas_datos() {
	$salida = array();

	foreach ( medmovil_zonas() as $grupo => $slugs ) {
		$items = array();
		foreach ( $slugs as $slug ) {
			$post = get_page_by_path( $slug, OBJECT, 'mm_prestacion' );
			if ( ! $post ) {
				continue;
			}
			$linea   = medmovil_linea_de( $post->ID );
			$items[] = array(
				'n'   => $post->post_title,
				'u'   => get_permalink( $post ),
				'p'   => medmovil_precio( get_post_meta( $post->ID, '_mm_precio', true ) ),
				'm'   => (int) get_post_meta( $post->ID, '_mm_duracion', true ),
				'i'   => get_post_meta( $post->ID, '_mm_icono', true ) ?: 'il-eco',
				'c'   => medmovil_color_linea( $linea ),
				'l'   => $linea ? $linea->name : '',
			);
		}
		if ( $items ) {
			$salida[ $grupo ] = $items;
		}
	}

	return $salida;
}

/**
 * Imprime la sección completa.
 */
function medmovil_selector_cuerpo() {
	$datos = medmovil_zonas_datos();
	if ( ! $datos ) {
		return;
	}
	?>
	<section class="band" id="donde" style="background:var(--surface); border-block:1px solid var(--line)">
		<div class="wrap">
			<div class="sec-head" data-rv>
				<div>
					<p class="eyebrow"><?php esc_html_e( 'No sabes qué examen pedir', 'medmovil' ); ?></p>
					<h2 style="margin-top:14px"><?php esc_html_e( 'Muéstranos dónde te molesta', 'medmovil' ); ?></h2>
					<p class="lede"><?php esc_html_e( 'Marca la zona en la figura y te decimos qué exámenes se hacen ahí, cuánto valen y cuánto demoran. Si tienes una orden médica, búscala directo en el catálogo.', 'medmovil' ); ?></p>
				</div>
				<a class="btn btn-o" href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>">
					<?php esc_html_e( 'Tengo mi orden médica', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
				</a>
			</div>

			<div class="zonas" data-rv>
				<div class="zonas-fig">
					<svg class="mmbody" viewBox="60 110 690 890" role="img" aria-label="Figura del cuerpo humano para elegir la zona del examen">
					  <path class="mmz" data-zona="right_foot" data-grupo="pie" d="M586 824c0 2 0 4 0 6 7 6 15 12 19 19 -7 8 -41 3 -54 3l0 -28 35 0zm-368 0l-37 33 0 0c-7 7 -12 13 -16 21 -2 5 -8 4 3 5 6 3 2 3 7 0 1 1 6 2 6 2 0 0 5 -1 5 -2 7 3 2 3 9 0 5 2 2 2 8 2 4 -3 3 -2 4 -5 6 0 1 0 5 -2 1 1 2 1 2 1l0 -22z"><title>Pie derecho</title></path>
					  <path class="mmz" data-zona="left_shin" data-grupo="pierna" d="M516 800c-4 -26 -13 -40 -17 -70 -2 -14 4 -31 6 -58l43 0 -1 16 2 19 3 15 1 15 -3 15 -6 19 -2 15 2 14 -28 0zm-295 -128l43 0c0 0 0 0 0 0 9 31 3 68 -7 98 -4 13 -7 23 -8 30l-28 0 3 -16 0 -17 -2 -14 -4 -12 0 -18 5 -21 0 -14 -2 -16z"><title>Pierna izquierda</title></path>
					  <path class="mmz" data-zona="left_foot" data-grupo="pie" d="M551 852c-12 0 -45 5 -52 -3 5 -7 16 -11 19 -19 0 -2 0 -4 0 -6l33 0 0 28zm-333 -28l33 0c4 5 10 8 14 21 2 5 8 4 -4 5 -5 3 -1 3 -6 0 -9 3 -3 3 -9 0 -10 2 -3 3 -11 0 -4 2 -3 1 -7 2 -3 -1 -1 0 -5 -3 -1 -1 -3 -2 -5 -3l0 -22z"><title>Pie izquierdo</title></path>
					  <path class="mmz" data-zona="right_shin" data-grupo="pierna" d="M169 672l43 0 -3 7 4 31 5 13 0 18 -6 11 -3 17 0 14 4 17 -28 0c-5 -37 -30 -76 -16 -128zm430 0c0 3 1 6 1 9 2 16 8 33 5 49 -6 29 -13 46 -17 71l-18 0 -12 0 0 -16 -7 -38 2 -7 0 -15 -2 -11 4 -13 1 -13 0 -16 43 0z"><title>Pierna derecha</title></path>
					  <path class="mmz" data-zona="right_ankle" data-grupo="pie" d="M588 800c-1 7 -2 15 -2 24l-35 0 0 -7 6 -8 1 -8 30 -1zm-403 0l28 0 5 9 0 15 -33 0c1 -8 1 -16 0 -24z"><title>Tobillo derecho</title></path>
					  <path class="mmz" data-zona="left_ankle" data-grupo="pie" d="M551 824l-33 0c0 -9 -1 -17 -2 -24l28 0 7 17 0 7zm-300 0l-33 0 0 -17 3 -7 28 0c-3 14 -2 20 2 24z"><title>Tobillo izquierdo</title></path>
					  <path class="mmz" data-zona="buttocks" data-grupo="cadera" d="M621 478c1 10 1 19 1 33l-13 3 -11 8 -16 4 -17 -2 -7 -5 -5 -9 0 0 -3 1 -6 11 -20 7 -12 -1 -10 -6 -8 -8 -12 -3c-1 -13 0 -23 1 -33l38 -3 61 0 39 3z"><title>Glúteos y cadera</title></path>
					  <path class="mmz" data-zona="right_thigh" data-grupo="muslo" d="M622 511c0 6 -1 12 -1 20 -2 39 -14 61 -20 91l-48 0 0 -112 5 9 7 5 17 2 16 -4 11 -8 13 -3zm-475 -33l38 -3 33 12 0 63 -4 26 1 12 3 7 0 27 -51 0c0 -6 -1 -11 -2 -14 -8 -24 -11 -35 -13 -61 -3 -23 -6 -46 -5 -69z"><title>Muslo derecho</title></path>
					  <path class="mmz" data-zona="left_thigh" data-grupo="muslo" d="M503 622c-4 -20 -16 -47 -20 -91 -1 -7 -1 -14 -1 -20l12 3 8 8 10 6 12 1 20 -7 6 -11 3 -1 0 112 -50 0zm-216 -144l-36 -3 -33 12 0 63 -2 13 4 8 1 12 -3 12 0 27 49 0c0 -18 10 -30 12 -47 3 -25 9 -63 8 -97z"><title>Muslo izquierdo</title></path>
					  <path class="mmz" data-zona="right_knee" data-grupo="rodilla" d="M601 622c-2 14 -3 30 -2 50l-43 0 -3 -10 0 -40 48 0zm-434 0l51 0 0 40 -6 10 -43 0c1 -2 1 -3 2 -5 -3 -12 -2 -31 -4 -45z"><title>Rodilla derecha</title></path>
					  <path class="mmz" data-zona="left_knee" data-grupo="rodilla" d="M505 672c1 -12 1 -25 -1 -41 0 -3 -1 -6 -1 -9l50 0 0 40 -5 10 -43 0zm-238 -50l-49 0 0 40 3 10 43 0c-1 -6 3 -37 3 -48 0 -1 0 -1 0 -2z"><title>Rodilla izquierda</title></path>
					  <path class="mmz" data-zona="torso" data-grupo="abdomen" d="M147 311l13 12 46 -10 23 0 46 10 9 -9 3 0 1 7 0 34c-4 9 -6 17 -7 25l0 0 0 0c-1 9 -1 17 -1 25l0 8 0 0 -2 12 -61 10 -60 -10 -2 -6c-1 -1 -1 -3 -2 -5 2 -4 2 -9 2 -14l0 0 0 0c0 -6 0 -13 -1 -20l0 0 0 0c-2 -10 -5 -19 -8 -25l1 -44z"><title>Abdomen</title></path>
					  <path class="mmz" data-zona="chest" data-grupo="torax" d="M160 240l0 0 0 0c10 -4 20 -9 30 -15l0 0 29 11 26 -12 0 0c6 5 13 9 21 13l0 0 0 0c3 1 7 3 10 5l8 72 -9 9 -48 -10 -21 0 -46 10 -14 -12 6 -67c3 -1 5 -2 8 -4z"><title>Tórax</title></path>
					  <path class="mmz" data-zona="upper_back" data-grupo="columna" d="M481 314l16 0 0 -72 0 0c8 -4 18 -9 30 -16l29 3 21 -5c3 3 7 5 11 8l0 0 0 0c9 6 12 7 23 12l0 70 15 0 0 16 -5 30 0 0c-4 5 -9 19 -12 26 2 6 4 12 5 18l-123 0c1 -5 3 -11 4 -18 -3 -7 -9 -24 -14 -28l0 -44z"><title>Espalda alta</title></path>
					  <path class="mmz" data-zona="lower_back" data-grupo="columna" d="M491 404l123 0c1 9 2 18 -2 28 1 2 1 4 1 5l-31 38 -61 0 -31 -36 0 0c1 -2 1 -5 2 -7 -4 -12 -3 -19 -1 -28z"><title>Espalda baja</title></path>
					  <path class="mmz" data-zona="right_hip" data-grupo="cadera" d="M613 437c4 18 6 30 8 41l-39 -3 31 -38zm-460 -3l32 41 -38 3c0 -15 2 -29 6 -44z"><title>Cadera derecha</title></path>
					  <path class="mmz" data-zona="left_hip" data-grupo="cadera" d="M483 478c2 -11 4 -23 7 -39l31 36 -38 3zm-201 -41l-33 38 38 3c0 -15 -1 -30 -5 -41z"><title>Cadera izquierda</title></path>
					  <path class="mmz" data-zona="pelvis" data-grupo="pelvis" d="M157 425l60 10 61 -10 0 0c0 0 -1 1 -1 1 2 3 3 7 5 10l-33 39 -31 12 -33 -12 -32 -41 0 0 4 -9z"><title>Pelvis</title></path>
					  <path class="mmz" data-zona="left_hand" data-grupo="mano" d="M455 494c4 13 9 30 0 37 -8 -6 -4 -7 -10 -16 -4 7 -3 7 -6 14l7 7c10 0 14 -1 13 9 -1 9 1 3 -4 7 -4 5 -2 6 -9 -2 -6 -1 0 2 -7 -3 -1 -2 -2 -3 -3 -5 -6 -4 -10 -12 -14 -18 2 -11 4 -20 5 -30l28 0zm-141 0l26 0c0 3 0 7 1 11 3 13 -2 34 -13 42 -9 3 -10 4 -14 0 -5 -5 -3 2 -4 -7 4 -1 6 -1 7 -2 2 -6 -1 -4 7 -9l0 -5c-2 -6 -3 -7 0 -12 2 -2 1 -2 2 -5 -4 1 -5 2 -9 8 -4 7 -2 8 -8 13 -6 -2 -2 0 -7 -4 5 -14 9 -23 12 -30z"><title>Mano izquierda</title></path>
					  <path class="mmz" data-zona="right_hand" data-grupo="mano" d="M677 494c1 9 3 18 5 28 -1 8 -9 17 -16 20 1 6 1 6 -8 8 -5 6 0 4 -7 2 -6 -4 -4 0 -6 -7 -1 -10 3 -9 13 -9l7 -7c-3 -8 0 -6 -6 -14 -6 9 -2 10 -10 16 -8 -7 -1 -17 -4 -26 1 -5 2 -8 4 -11l28 0zm-583 0l27 0c1 3 3 7 4 11 4 10 10 16 2 23 -12 -8 -5 -12 -17 -20 -8 2 -6 2 -6 13 0 7 0 7 2 14 6 -2 6 -3 11 3 -7 -3 -2 0 -6 0l4 4c3 -4 2 -3 5 -4 9 5 4 3 4 5 -2 5 1 2 -4 4 -5 6 -1 3 -10 2l-14 -14c-1 -11 -6 -18 -4 -30 1 -5 1 -8 2 -11zm16 23c3 7 3 6 3 14l-5 0c0 -7 -1 -7 2 -14z"><title>Mano derecha</title></path>
					  <path class="mmz" data-zona="left_wrist" data-grupo="muneca" d="M454 464c-2 6 -4 13 -4 20 1 2 3 6 5 10l-28 0c1 -9 2 -18 2 -30l25 0zm-138 0l26 0c0 4 0 8 0 11 -1 9 -2 13 -2 19l-26 0c7 -13 8 -14 2 -30z"><title>Muñeca izquierda</title></path>
					  <path class="mmz" data-zona="right_wrist" data-grupo="muneca" d="M675 464c0 12 1 21 2 30l-28 0c4 -6 9 -11 1 -30l25 0zm-583 0l27 0c-2 5 -4 10 -6 15 4 7 6 11 8 15l-27 0c0 -6 -1 -8 -2 -19 0 -3 0 -7 0 -11z"><title>Muñeca derecha</title></path>
					  <path class="mmz" data-zona="left_lower_arm" data-grupo="brazo" d="M474 397c-1 9 -2 18 -3 24 -3 16 -12 29 -17 43l-25 0c0 -2 0 -3 0 -4 0 -16 0 -47 2 -63l43 0zm-184 0l45 0c1 6 1 12 2 19 0 13 3 32 5 48l-26 0c-3 -9 -10 -23 -19 -46 -4 -9 -6 -15 -7 -21z"><title>Antebrazo izquierdo</title></path>
					  <path class="mmz" data-zona="right_lower_arm" data-grupo="brazo" d="M674 397c1 13 1 51 1 63 0 1 0 2 0 4l-25 0c-1 -3 -3 -7 -5 -11 -10 -21 -14 -40 -15 -56l44 0zm-574 0l43 0c-1 6 -2 13 -5 21 -7 15 -13 30 -19 46l-27 0c1 -16 5 -35 6 -48 0 -6 1 -13 2 -19z"><title>Antebrazo derecho</title></path>
					  <path class="mmz" data-zona="left_elbow" data-grupo="codo" d="M481 358c-4 7 -6 24 -7 39l-43 0 0 0c1 -14 10 -17 10 -33 0 -2 0 -4 0 -6l40 0zm-191 0l36 0c3 13 7 25 9 39l-45 0c-4 -23 9 -23 0 -39z"><title>Codo izquierdo</title></path>
					  <path class="mmz" data-zona="right_elbow" data-grupo="codo" d="M663 360c0 1 0 2 0 4 0 15 8 18 10 31 0 1 0 1 1 2l-44 0c-2 -16 -2 -29 -6 -37l39 0zm-42 0l0 0 0 0 0 0zm-477 0l-37 0c-2 12 -5 25 -7 37l43 0c1 -15 -4 -25 1 -37l0 0z"><title>Codo derecho</title></path>
					  <path class="mmz" data-zona="left_upper_arm" data-grupo="brazo" d="M441 358c0 -8 0 -19 0 -26 1 -7 1 -13 2 -18l38 0 0 44 -40 0zm-151 -44l35 0c-3 17 -1 31 1 44l-36 0 0 -44z"><title>Brazo izquierdo</title></path>
					  <path class="mmz" data-zona="right_upper_arm" data-grupo="brazo" d="M662 314c1 17 1 35 1 46l-39 0c-1 -1 -1 -1 -1 -2 0 0 0 0 -1 1l4 -31 0 -14 36 0zm-553 0l37 0 0 41c-1 2 -2 3 -2 5l-37 0c3 -16 5 -31 2 -46z"><title>Brazo derecho</title></path>
					  <path class="mmz" data-zona="right_shoulder" data-grupo="hombro" d="M611 244c12 6 32 16 41 24 6 6 8 25 10 46l-51 0 0 -70zm-458 0l-7 70 -37 0c-1 -6 -2 -11 -5 -17 5 -12 8 -23 13 -35 11 -6 23 -12 36 -18z"><title>Hombro derecho</title></path>
					  <path class="mmz" data-zona="left_shoulder_0" data-grupo="hombro" d="M443 314c5 -57 2 -45 54 -72l0 72 -54 0zm-118 0l-41 0 -8 -72c15 7 31 14 41 20 6 11 9 24 13 35 -2 6 -4 12 -5 17z"><title>Hombro izquierdo</title></path>
					  <path class="mmz" data-zona="neck" data-grupo="tiroides" d="M577 195l0 29 -21 5 -29 -3 0 -32c-1 -1 -2 -2 -2 -3l30 18 22 -14zm-387 30c0 0 1 -1 2 -1 2 -8 1 -12 1 -21 0 0 0 -1 0 -1l25 14 21 -12 0 0 0 0c3 4 2 10 2 16 1 2 2 3 4 4l-26 12 -29 -11z"><title>Cuello</title></path>
					  <path class="mmz" data-zona="head_x0020_" data-grupo="tiroides" d="M518 158c0 -60 68 -59 68 -2 1 1 3 6 4 7 4 16 -11 27 -13 31l0 1 -22 14 -30 -18c-7 -10 -10 -12 -10 -26 0 -2 2 -6 3 -7zm-325 44c-2 -3 -4 -9 -3 -14 -2 -2 -5 -2 -7 -4 -3 -4 -5 -10 -5 -23 1 0 5 -5 5 -5 0 -13 -2 -22 7 -32 12 -12 70 -18 61 30 2 3 3 5 5 7 1 11 -1 12 -5 23 -3 2 -4 3 -7 5 -1 7 -2 8 -5 14 0 0 0 0 0 1l-21 12 -25 -14z"><title>Cabeza y cuello</title></path>
					  <path class="mmz-borde" d="M117 533c0 0 1 1 2 2 0 0 0 0 0 0l1 -1 2 1c1 1 3 2 4 3 2 1 3 2 3 4 -1 2 -1 2 -2 3 0 0 0 0 -1 1l1 1 -1 -1c0 1 0 2 -1 3 -1 0 -1 0 -2 1 0 0 -1 0 -1 0 0 0 -1 1 -1 2 -1 1 -2 2 -3 2 -2 0 -3 0 -4 -1 -2 0 -3 -1 -5 -1l-1 0 -16 -16 0 -1c0 -4 -1 -8 -2 -11 -1 -7 -2 -13 -1 -20 1 -5 1 -10 1 -15 0 -1 0 -3 -1 -5 0 -3 0 -6 0 -9 -1 -4 0 -10 0 -14 1 -6 1 -11 2 -17 1 -5 1 -10 2 -15 0 -4 1 -9 1 -13 1 -15 4 -29 7 -44 5 -25 10 -49 0 -73l0 -2 0 -1c4 -12 8 -23 12 -35l1 -1 1 -1c24 -14 50 -23 74 -37 0 -3 1 -5 1 -8 0 -3 0 -7 0 -10 -2 -4 -4 -9 -4 -14 -2 -1 -3 -2 -5 -4l0 0 0 0c-6 -7 -6 -17 -6 -25l0 -2 1 -1c1 -1 2 -1 2 -2 0 0 1 -1 1 -2 0 -12 -1 -22 8 -32 4 -3 10 -6 14 -7 6 -2 13 -3 20 -3 8 1 17 3 23 8 11 8 13 21 11 33 1 2 3 4 4 6l1 1 0 1c0 4 0 8 -1 12 -1 2 -1 3 -2 5 -1 2 -2 5 -3 7l0 1 -1 1c-2 1 -4 3 -6 4 0 2 -1 5 -2 7 0 2 -1 3 -2 5 2 5 1 11 1 16 4 4 10 7 15 10 8 5 15 8 23 12 7 3 13 6 20 9 6 3 12 6 17 9l1 1 0 1c6 11 9 23 13 35l0 1 0 1c-4 11 -6 22 -6 33 0 9 1 18 3 28 2 9 4 17 6 26 2 10 4 21 4 31 0 4 0 9 1 13 0 5 1 10 2 15 1 6 1 11 2 17 0 4 1 10 0 14 0 3 0 5 0 8 -1 2 -1 4 -1 6 -1 5 0 10 1 15 2 15 -2 37 -15 46l-1 0 0 0c-2 1 -4 2 -6 2 -2 1 -4 1 -6 1 -2 0 -4 -2 -6 -4 0 0 0 0 -1 -1 -1 0 -1 0 -2 0 -1 -2 -1 -3 -1 -4 0 -1 0 -3 0 -4l0 -3 3 -1c1 0 3 0 4 -1 0 0 0 0 0 0 0 -1 1 -2 1 -3 1 -1 2 -2 3 -3 1 0 2 -1 3 -2l0 -2c-1 -2 -2 -4 -2 -6 0 1 -1 1 -1 2 0 1 -1 2 -1 3 -2 3 -4 4 -6 6l-2 1 -1 0c-1 0 -2 0 -3 0 0 -1 -1 -1 -2 -1 0 0 -1 -1 -1 -1 -1 -1 -2 -2 -2 -2l-2 -2 0 -2c4 -11 8 -21 13 -31 1 -2 3 -5 4 -7 1 -2 1 -4 2 -5 0 -5 -3 -12 -5 -16 -2 -6 -5 -12 -7 -18 -4 -9 -8 -18 -11 -27 -4 -10 -9 -23 -8 -34 0 -3 1 -6 2 -9 1 -2 1 -4 2 -6 0 -2 0 -5 -2 -8 -3 9 -4 18 -5 28 0 10 1 20 -1 29 0 3 0 5 -1 7 18 35 6 111 1 149 0 7 -2 13 -5 20 -3 9 -7 18 -7 29 0 3 0 6 0 10 -1 4 -1 8 -1 13 -1 5 -1 10 -1 16 -1 2 -1 4 -1 6 0 0 0 2 1 2 5 18 5 37 3 55 -2 15 -5 31 -10 45 -3 8 -5 16 -7 24 -2 5 -3 10 -3 15 0 5 0 9 3 13 1 1 2 2 3 3 1 1 3 2 4 4 3 4 6 9 7 14 1 1 1 1 2 2 0 0 0 0 0 0 2 1 2 3 2 4 -1 3 -3 3 -5 3 -1 1 -3 1 -5 1 0 0 -1 0 -1 1 -1 0 -2 1 -3 1 -1 0 -2 -1 -3 -1 0 0 0 -1 -1 -1 -1 0 -1 1 -2 1 -1 1 -2 1 -4 1 -1 -1 -1 -1 -1 -1 -1 -1 -1 -1 -1 -1 -2 0 -3 0 -4 1 0 0 -1 0 -2 0 -1 1 -1 0 -2 0 -1 0 -2 -1 -3 -1 -1 0 -2 1 -3 1 -1 0 -2 0 -3 1l-1 0 -1 -1c-1 0 -2 0 -2 -1 -2 0 -3 -1 -4 -2 -1 -1 -3 -2 -4 -3 -1 1 -2 1 -4 1 0 1 0 1 0 1 0 1 -2 3 -3 4l-1 1 -2 0c-1 0 -3 0 -5 -1 -1 0 -2 0 -3 -1 -1 0 -1 1 -2 1 -1 1 -2 1 -3 1 -1 0 -2 0 -2 -1 -1 0 -2 -1 -2 -1 -1 1 -3 1 -4 1l-1 1 -2 -1c0 0 -1 0 -2 0 0 -1 -1 -1 -1 -1 -1 0 -1 1 -1 1 -1 0 -2 1 -3 1 -1 0 -2 -1 -3 -1 0 -1 -1 -1 -1 -1 -2 0 -4 0 -5 -1 -2 0 -4 0 -5 -3 0 -1 0 -3 1 -4 1 0 1 0 1 0 1 -1 2 -1 2 -2l0 0 0 0c4 -8 9 -15 15 -21 1 -3 1 -7 1 -10 1 -10 -2 -21 -4 -31 -3 -11 -7 -22 -10 -34 -8 -28 -10 -53 -1 -81 -1 -4 -1 -8 -1 -12 -1 -5 -1 -9 -1 -14 0 -6 -1 -13 -1 -19 -1 -4 -1 -9 -2 -13 -4 -10 -7 -19 -9 -29 -2 -11 -4 -22 -5 -33 -4 -41 -10 -83 5 -122 -1 -4 -2 -7 -3 -10l0 -1 0 -1c4 -13 1 -34 -4 -49 -1 2 -1 4 -1 7 0 3 0 6 1 9 0 4 0 7 0 11 0 10 -1 19 -5 28 -8 20 -16 40 -24 60 2 3 4 7 6 11 2 4 4 9 6 14 1 3 2 6 3 9 2 2 3 5 3 9 -1 3 -3 6 -6 8l-2 2 -2 -1c-2 -2 -5 -4 -7 -6 -1 -2 -2 -4 -3 -6 0 -1 -1 -2 -2 -3 0 -1 0 -1 -1 -1l1 1c1 2 2 4 3 7 1 2 1 5 1 8l0 2zm460 -309c18 17 55 27 75 44 11 11 11 75 11 96 0 15 8 18 10 31 2 10 2 52 2 65 0 25 3 39 7 62 -1 8 -9 17 -16 20 1 6 1 6 -8 8 -5 6 0 4 -7 2 -6 -4 -4 0 -6 -7 -1 -10 3 -9 13 -9l7 -7c-3 -8 0 -6 -6 -14 -6 9 -2 10 -10 16 -8 -7 -1 -17 -4 -26 1 -17 20 -13 0 -52 -22 -44 -12 -80 -22 -95 -5 4 -10 21 -14 28 5 16 9 30 3 46 9 44 11 50 9 99 -3 60 -29 79 -21 150 2 16 8 33 5 49 -7 39 -19 56 -19 100 7 6 15 12 19 19 -7 8 -41 3 -54 3 -12 0 -45 5 -52 -3 5 -7 16 -11 19 -19 0 -48 -13 -60 -19 -100 -3 -21 10 -47 5 -99 -2 -22 -16 -50 -21 -100 -4 -46 1 -58 9 -99 -7 -19 0 -27 3 -46 -3 -7 -9 -24 -14 -28 -6 12 -7 47 -10 63 -5 24 -21 42 -21 63 5 10 16 38 5 47 -8 -6 -4 -7 -10 -16 -4 7 -3 7 -6 14l7 7c10 0 14 -1 13 9 -1 9 1 3 -4 7 -4 5 -2 6 -9 -2 -6 -1 0 2 -7 -3 -1 -2 -2 -3 -3 -5 -6 -4 -10 -12 -14 -18 5 -24 7 -38 7 -64 0 -17 0 -48 2 -63 1 -14 10 -17 10 -33 0 -9 -1 -24 0 -32 9 -89 -4 -53 86 -106l0 -32c-8 -12 -12 -13 -12 -29 0 -2 2 -6 3 -7 0 -60 68 -59 68 -2 1 1 3 6 4 7 4 16 -11 27 -13 31l0 30zm-467 293c3 7 3 6 3 14l-5 0c0 -7 -1 -7 2 -14zm80 -393c12 -12 70 -18 61 30 2 3 3 5 5 7 1 11 -1 12 -5 23 -3 2 -4 3 -7 5 -1 7 -2 8 -5 14 3 5 2 10 2 17 15 15 56 30 76 42 6 11 9 24 13 35 -17 49 6 74 7 119 0 17 6 43 5 59 -1 13 -3 16 -1 30 3 13 -2 34 -13 42 -9 3 -10 4 -14 0 -5 -5 -3 2 -4 -7 4 -1 6 -1 7 -2 2 -6 -1 -4 7 -9l0 -5c-2 -6 -3 -7 0 -12 2 -2 1 -2 2 -5 -4 1 -5 2 -9 8 -4 7 -2 8 -8 13 -6 -2 -2 0 -7 -4 22 -63 29 -23 -5 -106 -19 -48 7 -40 -9 -63 -14 29 -4 51 -11 71 19 33 7 109 2 149 -2 17 -12 30 -12 49 0 11 -4 43 -3 48 9 31 3 68 -7 98 -22 69 0 46 8 75 2 5 8 4 -4 5 -5 3 -1 3 -6 0 -9 3 -3 3 -9 0 -10 2 -3 3 -11 0 -4 2 -3 1 -7 2 -3 -1 -1 0 -5 -3 -2 -1 -5 -3 -7 -4 -4 2 1 2 -5 2 -1 3 0 2 -4 5 -6 0 -3 0 -8 -2 -7 3 -2 3 -9 0 0 1 -5 2 -5 2 0 0 -5 -1 -6 -2 -5 3 -1 3 -7 0 -11 -1 -5 0 -3 -5 4 -8 9 -14 16 -21 8 -46 -36 -91 -14 -157 -3 -16 -2 -47 -6 -59 -8 -24 -11 -35 -13 -61 -5 -42 -11 -83 5 -122 -1 -4 -3 -7 -4 -11 6 -16 0 -45 -7 -59 -11 19 4 31 -8 63 -9 20 -17 40 -25 61 6 11 8 14 12 26 4 10 10 16 2 23 -12 -8 -5 -12 -17 -20 -8 2 -6 2 -6 13 0 7 0 7 2 14 6 -2 6 -3 11 3 -7 -3 -2 0 -6 0l4 4c3 -4 2 -3 5 -4 9 5 4 3 4 5 -2 5 1 2 -4 4 -5 6 -1 3 -10 2l-14 -14c-1 -11 -6 -18 -4 -30 3 -16 1 -15 0 -30 -1 -16 5 -42 6 -59 1 -39 22 -79 6 -119 5 -12 8 -23 13 -35 23 -14 51 -24 75 -38 2 -8 1 -12 1 -21 -1 -3 -4 -9 -3 -15 -2 -2 -5 -2 -7 -4 -3 -4 -5 -10 -5 -23 1 0 5 -5 5 -5 0 -13 -2 -22 7 -32zm-82 389l0 -1c0 0 0 1 0 1 0 0 0 0 0 0zm514 -148c-2 3 -3 8 -4 10 -2 4 -4 8 -5 12 4 15 8 29 3 45 1 8 3 17 5 26 1 6 2 13 3 20 2 18 1 36 0 53 0 12 -2 24 -5 36 -2 9 -5 19 -7 29 -4 12 -7 24 -9 36 -2 16 -1 33 1 49 1 16 7 33 4 50 -1 8 -3 16 -6 25 -1 7 -3 14 -5 21 -2 7 -4 15 -5 23 -2 9 -2 19 -2 28 1 2 3 3 5 5 5 4 10 9 13 14l2 2 -2 2c-3 4 -9 5 -14 6 -6 0 -12 0 -18 0 -5 0 -9 -1 -14 -1 -4 0 -7 0 -11 0 -3 0 -7 0 -10 0 -5 0 -9 1 -14 1 -8 0 -25 1 -31 -6l-2 -2 2 -2c2 -4 6 -7 10 -9 3 -3 7 -6 9 -9 0 -11 -1 -21 -3 -31 -1 -9 -3 -16 -6 -24 -2 -7 -4 -13 -5 -20 -3 -7 -4 -15 -5 -23 -1 -6 -1 -12 0 -18 1 -7 2 -14 3 -21 1 -9 2 -18 3 -27 0 -11 0 -23 -1 -34 -1 -6 -2 -12 -4 -17 -2 -7 -4 -14 -6 -21 -2 -9 -4 -18 -6 -27 -2 -12 -4 -23 -5 -35 -1 -10 -2 -20 -2 -30 0 -8 1 -16 2 -24 1 -7 2 -14 3 -21 2 -8 4 -16 5 -24 -6 -19 0 -28 4 -45 -2 -5 -6 -16 -10 -22 -1 3 -1 6 -2 8 -1 6 -1 13 -2 19 -1 5 -1 11 -2 16 0 5 -1 9 -2 14 -2 10 -6 20 -11 30 -4 8 -8 17 -9 26 0 2 -1 3 -1 5 7 13 17 40 4 51l0 0c1 0 2 0 3 1 3 3 2 7 2 10 0 2 0 3 0 4 0 2 0 3 -1 4 -1 0 -1 1 -2 1 -1 0 -1 0 -1 1 -1 0 -2 1 -3 2 -1 1 -2 2 -3 2 -2 0 -4 -1 -5 -2 -1 -1 -2 -2 -2 -3 -1 -1 -1 -1 -1 -1 -1 1 -1 1 -2 1 -1 -1 -1 -1 -2 -2 -1 -1 -2 -2 -3 -3 -2 -1 -3 -3 -4 -5 -6 -5 -10 -12 -14 -18l-1 -1 1 -2c1 -9 3 -18 4 -28 2 -12 3 -24 3 -35 0 -11 0 -21 0 -32 0 -5 0 -11 0 -17 1 -5 1 -10 1 -15 1 -6 3 -10 6 -15 3 -6 5 -10 5 -17 0 -5 0 -10 -1 -15 0 -6 0 -12 1 -17 1 -9 1 -17 2 -26 1 -6 1 -12 2 -18 1 -8 2 -16 7 -22 3 -3 6 -6 9 -8 5 -3 10 -5 15 -7 7 -4 14 -7 21 -11 10 -5 20 -11 29 -16l0 -29c-1 -2 -3 -4 -5 -7 -1 -2 -2 -4 -4 -6 -2 -5 -3 -11 -3 -17l0 -1 0 -1c2 -2 2 -4 4 -6 0 -64 74 -62 75 -2 1 2 1 4 3 6l0 1 0 0c2 8 0 16 -4 23 -2 2 -4 5 -6 7 0 1 -2 2 -2 3l0 27c5 5 11 8 16 11 7 4 15 8 22 12 7 3 13 6 19 9 6 3 11 7 17 11l0 0 0 0c4 5 6 15 7 21 2 10 3 21 3 31 1 16 1 31 1 47 0 6 2 10 5 16 3 5 5 9 6 14 0 2 0 4 0 6 0 3 1 5 1 8 0 7 0 13 0 20 0 10 1 21 1 32 0 11 0 23 2 34 1 9 3 19 4 28l1 0 0 1c-1 7 -9 17 -16 21 0 1 0 3 -1 4 -1 2 -2 3 -4 4 -2 0 -4 1 -5 1 -1 1 -1 1 -1 2 0 0 0 0 0 0 -1 1 -2 2 -3 3 -1 0 -2 0 -3 -1 0 0 0 0 -1 0 0 -1 -1 -1 -2 -2l-1 0 0 0c-1 -1 -2 -1 -3 -2 -1 0 -2 -1 -2 -1 -1 -1 -1 -2 -1 -3 0 -1 -1 -2 -1 -3l0 0 0 -1c0 -3 0 -7 2 -10 1 -1 2 -1 3 -1l0 0c-2 -2 -4 -4 -5 -7 -2 -7 2 -14 0 -21l0 -1 0 0c0 -6 2 -9 5 -14 2 -4 4 -6 4 -11 0 -8 -6 -19 -9 -26 -5 -10 -9 -21 -12 -32 -2 -9 -3 -18 -4 -27 -1 -7 -1 -15 -2 -22 0 -2 -1 -5 -2 -8zm-170 168c-3 -2 -5 -4 -6 -7 0 -1 -1 -2 -1 -3 0 0 0 0 0 0 -1 2 -1 4 -2 5l5 5c1 0 2 0 4 0zm200 0c2 0 3 0 4 0l5 -5c-1 -1 -1 -2 -1 -3 0 -1 0 -2 0 -2 0 -1 -1 -1 -1 -1 0 0 0 0 0 1 0 1 -1 2 -1 3 -1 3 -3 5 -6 7z"/>
					</svg>
					<p class="zonas-pista" id="zonasSeleccion" role="status" aria-live="polite"><?php esc_html_e( 'Vista espejo: posterior a la izquierda y frontal a la derecha. Selecciona una zona.', 'medmovil' ); ?></p>
				</div>

				<div class="zonas-panel" id="zonasPanel">
					<div class="zonas-vacio" id="zonasVacio">
						<span class="zonas-vacio-ic"><?php medmovil_icono( 'il-body', 'ilo' ); ?></span>
						<b><?php esc_html_e( 'Elige una zona', 'medmovil' ); ?></b>
						<p><?php esc_html_e( 'Cuando marques una parte del cuerpo, aquí aparecen los exámenes que realizamos en esa zona a domicilio, con su valor y su duración.', 'medmovil' ); ?></p>
					</div>
					<div class="zonas-lista" id="zonasLista" hidden>
						<div class="zonas-cab">
							<span class="zonas-tag" id="zonasNombre"></span>
							<button type="button" class="zonas-x" id="zonasCerrar" aria-label="<?php esc_attr_e( 'Quitar la selección', 'medmovil' ); ?>">
								<?php medmovil_icono( 'i-x' ); ?>
							</button>
						</div>
						<div id="zonasItems"></div>
					</div>
				</div>
			</div>
		</div>

		<script type="application/json" id="mm-zonas"><?php
			echo wp_json_encode( $datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		?></script>
	</section>
	<?php
}
