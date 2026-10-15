<?php
/**
 * Publicaciones de Instagram y alianzas.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$publicaciones = array(
	array( 'ig-ecografias.webp', __( 'Catálogo completo de ecografías a domicilio', 'medmovil' ), 'il-eco' ),
	array( 'ig-doppler.webp', __( 'Doppler: qué detecta y por qué conviene a tiempo', 'medmovil' ), 'il-dop' ),
	array( 'ig-radiografias.webp', __( 'Radiografía digital portátil en toda la región', 'medmovil' ), 'il-rx' ),
);
$aliados = get_posts( array( 'post_type' => 'mm_aliado', 'posts_per_page' => 6, 'orderby' => array( 'menu_order' => 'ASC' ) ) );
?>
<section class="band">
	<div class="wrap">
		<div class="sec-head" data-rv>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'En Instagram', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Lo que hacemos, y hasta dónde llegamos', 'medmovil' ); ?></h2>
				<p class="lede"><?php esc_html_e( 'Publicamos cada prestación con su detalle clínico y las comunas donde estamos atendiendo esa semana.', 'medmovil' ); ?></p>
			</div>
			<a class="btn btn-o" href="<?php echo esc_url( medmovil_opt( 'instagram_url', '#' ) ); ?>" target="_blank" rel="noopener">
				<?php medmovil_icono( 'i-ig' ); ?> <?php echo esc_html( sprintf( __( 'Seguir %s', 'medmovil' ), medmovil_opt( 'instagram', '' ) ) ); ?>
			</a>
		</div>
		<div class="ig">
			<?php foreach ( $publicaciones as $p ) : ?>
				<figure data-rv>
					<img src="<?php echo esc_url( MEDMOVIL_URI . '/assets/img/' . $p[0] ); ?>" alt="<?php echo esc_attr( $p[1] ); ?>" loading="lazy" width="620" height="620">
					<figcaption><?php medmovil_icono( $p[2], 'ilo', 'width:16px;height:16px;color:var(--blue)' ); ?> <?php echo esc_html( $p[1] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( $aliados ) : ?>
			<div style="margin-top:52px">
				<p class="eyebrow" data-rv><?php esc_html_e( 'Alianzas', 'medmovil' ); ?></p>
				<h3 style="margin:14px 0 20px" data-rv><?php esc_html_e( 'Trabajamos junto a quienes ya cuidan al paciente en su casa', 'medmovil' ); ?></h3>
				<div class="allies">
					<?php foreach ( $aliados as $a ) : ?>
						<div class="ally" data-rv>
							<?php if ( has_post_thumbnail( $a ) ) : ?>
								<?php echo get_the_post_thumbnail( $a, 'thumbnail', array( 'loading' => 'lazy' ) ); ?>
							<?php endif; ?>
							<div>
								<b><?php echo esc_html( $a->post_title ); ?></b>
								<span><?php echo esc_html( wp_strip_all_tags( $a->post_content ) ); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
					<div class="ally" style="border-style:dashed; justify-content:center; color:var(--ink-3)" data-rv>
						<div style="text-align:center">
							<b style="color:var(--ink-3)"><?php esc_html_e( '¿Quieres ser aliado?', 'medmovil' ); ?></b>
							<span><?php esc_html_e( 'Escríbenos y armamos el convenio', 'medmovil' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
