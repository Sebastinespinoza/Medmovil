<?php
/**
 * Template Name: Lista de precios
 *
 * Arancel completo con buscador y filtro por línea. Todos los valores son
 * particulares. El código que acompaña a cada examen es el de la orden médica y
 * sirve solo para buscar.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$prestaciones = get_posts( array(
	'post_type'      => 'mm_prestacion',
	'posts_per_page' => -1,
	'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
) );

$lineas = get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => true ) );
$desde  = 0;
foreach ( $prestaciones as $p ) {
	$v = (int) get_post_meta( $p->ID, '_mm_precio', true );
	if ( $v && ( ! $desde || $v < $desde ) ) {
		$desde = $v;
	}
}
?>
<div class="svc-top">
	<div class="wrap" style="padding-block:34px 40px">
		<?php medmovil_migas( array( array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ), array( get_the_title(), '' ) ) ); ?>
		<p class="eyebrow" style="margin-top:20px"><?php esc_html_e( 'Valores particulares', 'medmovil' ); ?></p>
		<h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:14px"><?php esc_html_e( 'Arancel completo, publicado', 'medmovil' ); ?></h1>
		<div class="prose" style="margin-top:18px"><?php while ( have_posts() ) { the_post(); the_content(); } ?></div>

		<div class="cz-stats" style="margin-top:26px">
			<div class="cz-stat">
				<b><?php echo esc_html( count( $prestaciones ) ); ?></b>
				<span><?php esc_html_e( 'Prestaciones', 'medmovil' ); ?></span>
			</div>
			<div class="cz-stat">
				<b><?php echo esc_html( $desde ? medmovil_precio( $desde ) : '—' ); ?></b>
				<span><?php esc_html_e( 'Valor más bajo', 'medmovil' ); ?></span>
			</div>
			<div class="cz-stat">
				<b><?php esc_html_e( '$0', 'medmovil' ); ?></b>
				<span><?php esc_html_e( 'Cobro de traslado', 'medmovil' ); ?></span>
			</div>
		</div>
	</div>
</div>

<div class="filtros" id="filtros">
	<div class="wrap">
		<div class="fl-buscar">
			<?php medmovil_icono( 'il-search', 'ilo' ); ?>
			<input type="search" id="arQ" autocomplete="off"
			       placeholder="<?php esc_attr_e( 'Busca por nombre, zona o código de la orden médica', 'medmovil' ); ?>"
			       aria-label="<?php esc_attr_e( 'Buscar en el arancel', 'medmovil' ); ?>">
			<button type="button" class="fl-x" id="arX" hidden aria-label="<?php esc_attr_e( 'Limpiar', 'medmovil' ); ?>">
				<?php medmovil_icono( 'i-x' ); ?>
			</button>
		</div>
		<div class="fl-orden">
			<label for="arL"><?php esc_html_e( 'Línea', 'medmovil' ); ?></label>
			<select id="arL">
				<option value=""><?php esc_html_e( 'Todas las líneas', 'medmovil' ); ?></option>
				<?php foreach ( $lineas as $linea ) : ?>
					<option value="<?php echo esc_attr( $linea->slug ); ?>"><?php echo esc_html( $linea->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<p class="fl-cuenta" id="arCuenta" style="margin:0" aria-live="polite"></p>
	</div>
</div>

<section class="band" style="padding-block:30px 70px">
	<div class="wrap">
		<div class="tbl-wrap">
			<table class="ar-tabla" id="arTabla">
				<colgroup>
					<col class="c-ex"><col class="c-cod"><col class="c-li">
					<col class="c-du"><col class="c-va"><col class="c-pr">
				</colgroup>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Examen', 'medmovil' ); ?></th>
						<th class="ar-cod"><?php esc_html_e( 'Código', 'medmovil' ); ?></th>
						<th><?php esc_html_e( 'Línea', 'medmovil' ); ?></th>
						<th class="ar-num"><?php esc_html_e( 'Duración', 'medmovil' ); ?></th>
						<th class="ar-num"><?php esc_html_e( 'Valor', 'medmovil' ); ?></th>
						<th><?php esc_html_e( 'Preparación', 'medmovil' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $prestaciones as $p ) :
						$linea  = medmovil_linea_de( $p->ID );
						$precio = (int) get_post_meta( $p->ID, '_mm_precio', true );
						$min    = (int) get_post_meta( $p->ID, '_mm_duracion', true );
						$codigo = (string) get_post_meta( $p->ID, '_mm_codigo', true );
						$prep   = trim( (string) get_post_meta( $p->ID, '_mm_preparacion', true ) );
						$buscar = mb_strtolower( remove_accents( $p->post_title . ' ' . $codigo . ' ' . ( $linea ? $linea->name : '' ) . ' ' . get_post_meta( $p->ID, '_mm_resumen', true ) ) );
						?>
						<tr data-linea="<?php echo esc_attr( $linea ? $linea->slug : '' ); ?>" data-buscar="<?php echo esc_attr( $buscar ); ?>">
							<td data-th="<?php esc_attr_e( 'Examen', 'medmovil' ); ?>">
								<a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a>
							</td>
							<td class="ar-cod" data-th="<?php esc_attr_e( 'Código', 'medmovil' ); ?>">
								<?php echo $codigo ? '<span class="cz-cod">' . esc_html( $codigo ) . '</span>' : '—'; ?>
							</td>
							<td data-th="<?php esc_attr_e( 'Línea', 'medmovil' ); ?>">
								<?php if ( $linea ) : ?>
									<span class="ar-linea" style="--ac:<?php echo esc_attr( medmovil_color_linea( $linea ) ); ?>"><?php echo esc_html( $linea->name ); ?></span>
								<?php endif; ?>
							</td>
							<td class="ar-num" data-th="<?php esc_attr_e( 'Duración', 'medmovil' ); ?>"><?php echo esc_html( $min ? $min . ' min' : '—' ); ?></td>
							<td class="ar-num ar-precio" data-th="<?php esc_attr_e( 'Valor', 'medmovil' ); ?>"><?php echo esc_html( $precio ? medmovil_precio( $precio ) : __( 'Consultar', 'medmovil' ) ); ?></td>
							<td class="ar-prep" data-th="<?php esc_attr_e( 'Preparación', 'medmovil' ); ?>">
								<span class="ar-ayuno ar-ayuno-<?php echo esc_attr( medmovil_ayuno( $prep ) ); ?>">
									<?php echo esc_html( medmovil_etiqueta_ayuno( $prep ) ); ?>
								</span>
								<?php if ( $prep ) : ?>
									<span class="ar-prep-t" title="<?php echo esc_attr( $prep ); ?>"><?php echo esc_html( $prep ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="fl-vacio" id="arVacio" hidden>
			<?php medmovil_icono( 'il-search', 'ilo' ); ?>
			<b><?php esc_html_e( 'No hay resultados', 'medmovil' ); ?></b>
			<p><?php esc_html_e( 'Prueba con otra palabra, o escríbenos y lo revisamos contigo.', 'medmovil' ); ?></p>
		</div>

		<div class="cz-cta">
			<div>
				<h2><?php esc_html_e( '¿Necesitas más de un examen?', 'medmovil' ); ?></h2>
				<p><?php esc_html_e( 'En el cotizador los marcas todos y ves el total de la visita. El profesional los realiza el mismo día, sin cobrar el traslado dos veces.', 'medmovil' ); ?></p>
			</div>
			<a class="btn btn-p" href="<?php echo esc_url( home_url( '/cotizador/' ) ); ?>">
				<?php medmovil_icono( 'i-coin' ); ?> <?php esc_html_e( 'Ir al cotizador', 'medmovil' ); ?>
			</a>
		</div>

		<p class="cz-pie">
			<?php esc_html_e( 'Todos los valores son particulares e incluyen la visita del profesional, el examen y el informe. No cobramos traslado dentro de la Región Metropolitana. MedMóvil no trabaja con Fonasa ni con isapres: el código que acompaña a cada examen es el de la orden médica y sirve solo para que lo encuentres más rápido.', 'medmovil' ); ?>
		</p>
	</div>
</section>

<?php
get_footer();
