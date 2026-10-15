<?php
/**
 * Pie del sitio.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<?php if ( medmovil_en_panel() ) : ?>

	<?php
	// El panel cierra con su propio pie (dentro de medmovil_panel_cerrar) y no
	// necesita el carrito, la hoja de presupuesto ni los botones flotantes:
	// tapaban los gráficos.
	medmovil_datos_json();
	medmovil_sprite();
	wp_footer();
	?>
	</body>
	</html>
	<?php
	return;
	?>

<?php endif; ?>

<footer>
	<div class="wrap">
		<div class="f-grid">
			<div>
				<img class="flogo" src="<?php echo esc_url( MEDMOVIL_URI . '/assets/img/logo.png' ); ?>"
				     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="480" height="134" loading="lazy">
				<p style="font-size:.9rem; max-width:34ch"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
				<div class="f-contact">
					<a href="<?php echo esc_url( medmovil_whatsapp() ); ?>" target="_blank" rel="noopener">
						<?php medmovil_icono( 'i-wa' ); ?> <?php echo esc_html( medmovil_opt( 'telefono', '' ) ); ?>
					</a>
					<a href="<?php echo esc_url( 'mailto:' . medmovil_opt( 'email', '' ) ); ?>">
						<?php medmovil_icono( 'i-mail' ); ?> <?php echo esc_html( medmovil_opt( 'email', '' ) ); ?>
					</a>
					<a href="<?php echo esc_url( medmovil_opt( 'instagram_url', '#' ) ); ?>" target="_blank" rel="noopener">
						<?php medmovil_icono( 'i-ig' ); ?> <?php echo esc_html( medmovil_opt( 'instagram', '' ) ); ?>
					</a>
				</div>
			</div>

			<div>
				<h4><?php esc_html_e( 'Prestaciones', 'medmovil' ); ?></h4>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'pie-servicios',
					'container'      => false,
					'menu_class'     => '',
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Pacientes', 'medmovil' ); ?></h4>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'pie-pacientes',
					'container'      => false,
					'menu_class'     => '',
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</div>

			<div>
				<h4><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h4>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'pie-empresa',
					'container'      => false,
					'menu_class'     => '',
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</div>
		</div>

		<div class="f-bot">
			<span>
				<?php
				printf(
					/* translators: 1: año, 2: razón social, 3: región */
					esc_html__( '© %1$s %2$s · %3$s, Chile', 'medmovil' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( medmovil_opt( 'razon_social', get_bloginfo( 'name' ) ) ),
					esc_html( medmovil_opt( 'region', 'Región Metropolitana' ) )
				);
				?>
			</span>
			<span><?php esc_html_e( 'Profesionales con registro vigente en la Superintendencia de Salud', 'medmovil' ); ?></span>
		</div>

		<?php
		// La licencia gratuita de UIcons obliga a este crédito. Si algún día
		// contratas la suscripción Premium de Flaticon, se puede quitar.
		if ( medmovil_opt( 'credito_iconos', 'si' ) !== 'no' ) :
			?>
			<p class="f-cred">
				<?php
				printf(
					/* translators: %s: enlace a Flaticon */
					esc_html__( 'Iconos: %s', 'medmovil' ),
					'<a href="https://www.flaticon.com/uicons" target="_blank" rel="noopener nofollow">Uicons by Flaticon</a>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</footer>

<?php medmovil_dock(); ?>
<?php medmovil_cajon_carrito(); ?>
<?php medmovil_hoja_presupuesto(); ?>
<?php medmovil_datos_json(); ?>
<?php medmovil_sprite(); ?>
<?php wp_footer(); ?>
</body>
</html>
