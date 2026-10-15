<?php
/**
 * Catálogo completo de prestaciones, con filtros que no recargan la página.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$lineas = get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => true ) );
$todas  = get_posts( array(
	'post_type'      => 'mm_prestacion',
	'posts_per_page' => -1,
	'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
) );

$iconos_linea = array( 'il-eco', 'il-msk', 'il-dop', 'il-cardio', 'il-lung', 'il-rx', 'il-lab', 'il-nurse' );
?>
<div class="svc-top">
	<div class="wrap" style="padding-block:34px 40px">
		<?php medmovil_migas( array( array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ), array( __( 'Prestaciones', 'medmovil' ), '' ) ) ); ?>
		<p class="eyebrow" style="margin-top:20px"><?php esc_html_e( 'Catálogo completo', 'medmovil' ); ?></p>
		<h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:14px"><?php esc_html_e( 'Todos nuestros exámenes a domicilio', 'medmovil' ); ?></h1>
		<p class="lede" style="margin-top:18px;max-width:62ch">
			<?php
			printf(
				/* translators: 1: cantidad de exámenes, 2: cantidad de líneas */
				esc_html__( '%1$d exámenes en %2$d líneas de atención. Todos incluyen la visita, el examen y el informe: el precio que ves es el que pagas.', 'medmovil' ),
				count( $todas ),
				count( $lineas )
			);
			?>
		</p>
	</div>
</div>

<div class="filtros" id="filtros">
	<div class="wrap">
		<div class="fl-buscar">
			<?php medmovil_icono( 'il-search', 'ilo' ); ?>
			<input type="search" id="flQ" autocomplete="off"
			       placeholder="<?php esc_attr_e( 'Busca por nombre o por zona: rodilla, abdomen, corazón…', 'medmovil' ); ?>"
			       aria-label="<?php esc_attr_e( 'Buscar un examen', 'medmovil' ); ?>">
			<button type="button" class="fl-x" id="flX" hidden aria-label="<?php esc_attr_e( 'Limpiar', 'medmovil' ); ?>">
				<?php medmovil_icono( 'i-x' ); ?>
			</button>
		</div>

		<div class="fl-orden">
			<label for="flO"><?php esc_html_e( 'Ordenar', 'medmovil' ); ?></label>
			<select id="flO">
				<option value="def"><?php esc_html_e( 'Los más pedidos', 'medmovil' ); ?></option>
				<option value="pa"><?php esc_html_e( 'Precio: de menor a mayor', 'medmovil' ); ?></option>
				<option value="pd"><?php esc_html_e( 'Precio: de mayor a menor', 'medmovil' ); ?></option>
				<option value="md"><?php esc_html_e( 'Duración: los más rápidos', 'medmovil' ); ?></option>
				<option value="az"><?php esc_html_e( 'Nombre A–Z', 'medmovil' ); ?></option>
			</select>
		</div>
	</div>

	<div class="wrap fl-chips" role="group" aria-label="<?php esc_attr_e( 'Filtrar por línea de atención', 'medmovil' ); ?>">
		<button type="button" class="chip on" data-linea=""><?php esc_html_e( 'Todos', 'medmovil' ); ?></button>
		<?php
		$n = 0;
		foreach ( $lineas as $linea ) :
			$icono = $iconos_linea[ $n % count( $iconos_linea ) ];
			$n++;
			?>
			<button type="button" class="chip" data-linea="<?php echo esc_attr( $linea->slug ); ?>"
			        style="--ac:<?php echo esc_attr( medmovil_color_linea( $linea ) ); ?>">
				<?php medmovil_icono( $icono, 'ilo' ); ?>
				<?php echo esc_html( $linea->name ); ?>
				<i><?php echo (int) $linea->count; ?></i>
			</button>
		<?php endforeach; ?>
	</div>
</div>

<section class="band" style="padding-block:36px 56px">
	<div class="wrap">
		<p class="fl-cuenta" id="flCuenta" aria-live="polite"></p>

		<div class="prods" id="grilla">
			<?php foreach ( $todas as $p ) { medmovil_tarjeta_prestacion( $p->ID ); } ?>
		</div>

		<div class="fl-vacio" id="flVacio" hidden>
			<?php medmovil_icono( 'il-search', 'ilo' ); ?>
			<b><?php esc_html_e( 'No encontramos ese examen', 'medmovil' ); ?></b>
			<p><?php esc_html_e( 'Puede que lo tengamos con otro nombre, o que lo coordinemos a pedido. Escríbenos y te decimos al tiro.', 'medmovil' ); ?></p>
			<a class="btn btn-p" href="<?php echo esc_url( medmovil_whatsapp( __( 'Hola, busco un examen que no encontré en la página.', 'medmovil' ) ) ); ?>" target="_blank" rel="noopener">
				<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Preguntar por WhatsApp', 'medmovil' ); ?>
			</a>
		</div>
	</div>
</section>

<?php medmovil_selector_cuerpo(); ?>
<?php get_template_part( 'partials/convenios' ); ?>
<?php get_footer(); ?>
