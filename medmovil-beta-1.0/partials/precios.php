<?php
/**
 * Tabla de precios destacados en la portada.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$destacados = get_posts( array(
	'post_type'      => 'mm_prestacion',
	'posts_per_page' => 8,
	'orderby'        => array( 'menu_order' => 'ASC' ),
	'meta_query'     => array( array( 'key' => '_mm_precio', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ) ),
) );
if ( ! $destacados ) {
	return;
}
?>
<section class="band" id="precios">
	<div class="wrap">
		<div class="sec-head" data-rv>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Precios particulares', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Un solo precio, publicado. Sin sorpresas al llegar', 'medmovil' ); ?></h2>
				<p class="lede"><?php esc_html_e( 'El valor incluye la visita, el examen y el informe firmado por médico radiólogo. No cobramos traslado dentro de la Región Metropolitana.', 'medmovil' ); ?></p>
			</div>
			<a class="btn btn-p" href="<?php echo esc_url( get_permalink( get_page_by_path( 'precios' ) ) ); ?>">
				<?php esc_html_e( 'Ver todos los precios', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
			</a>
		</div>
		<div class="tbl-wrap" data-rv>
			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Prestación', 'medmovil' ); ?></th>
						<th><?php esc_html_e( 'Línea', 'medmovil' ); ?></th>
						<th><?php esc_html_e( 'Duración', 'medmovil' ); ?></th>
						<th class="num"><?php esc_html_e( 'Valor particular', 'medmovil' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $destacados as $p ) :
					$linea = medmovil_linea_de( $p->ID );
					?>
					<tr>
						<td>
							<b><?php echo esc_html( $p->post_title ); ?></b><br>
							<span style="font-size:.82rem;color:var(--ink-3)"><?php echo esc_html( wp_trim_words( get_post_meta( $p->ID, '_mm_resumen', true ), 12 ) ); ?></span>
						</td>
						<td><?php if ( $linea ) : ?>
							<span class="chip" style="background:color-mix(in srgb,<?php echo esc_attr( medmovil_color_linea( $linea ) ); ?> 14%,transparent);color:<?php echo esc_attr( medmovil_color_linea( $linea ) ); ?>"><?php echo esc_html( $linea->name ); ?></span>
						<?php endif; ?></td>
						<td><?php echo esc_html( get_post_meta( $p->ID, '_mm_duracion', true ) . ' min' ); ?></td>
						<td class="num"><?php echo esc_html( medmovil_precio( get_post_meta( $p->ID, '_mm_precio', true ) ) ); ?></td>
						<td><a class="btn btn-o btn-sm" href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php esc_html_e( 'Ver', 'medmovil' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
