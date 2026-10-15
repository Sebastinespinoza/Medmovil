<?php
/**
 * MedMóvil — arranque del tema.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MEDMOVIL_VERSION', '1.8.5' );
define( 'MEDMOVIL_DIR', get_template_directory() );
define( 'MEDMOVIL_URI', get_template_directory_uri() );

require_once MEDMOVIL_DIR . '/inc/setup.php';
require_once MEDMOVIL_DIR . '/inc/cpt.php';
require_once MEDMOVIL_DIR . '/inc/meta.php';
require_once MEDMOVIL_DIR . '/inc/options.php';
require_once MEDMOVIL_DIR . '/inc/render.php';
require_once MEDMOVIL_DIR . '/inc/seo.php';
require_once MEDMOVIL_DIR . '/inc/icons.php';
require_once MEDMOVIL_DIR . '/inc/storefront.php';
require_once MEDMOVIL_DIR . '/inc/iconos-panel.php';
require_once MEDMOVIL_DIR . '/inc/cuerpo.php';
require_once MEDMOVIL_DIR . '/inc/subir-imagen.php';
require_once MEDMOVIL_DIR . '/inc/hero.php';
require_once MEDMOVIL_DIR . '/inc/carrito.php';
require_once MEDMOVIL_DIR . '/inc/chat.php';
require_once MEDMOVIL_DIR . '/inc/profesionales.php';
require_once MEDMOVIL_DIR . '/inc/blog.php';
require_once MEDMOVIL_DIR . '/inc/ia-blog.php';
require_once MEDMOVIL_DIR . '/inc/portal/base.php';
require_once MEDMOVIL_DIR . '/inc/portal/reservas.php';
require_once MEDMOVIL_DIR . '/inc/portal/ajustes.php';
require_once MEDMOVIL_DIR . '/inc/portal/ui.php';
require_once MEDMOVIL_DIR . '/inc/portal/pulse-ui.php';
require_once MEDMOVIL_DIR . '/inc/portal/availability.php';
require_once MEDMOVIL_DIR . '/inc/portal/auth.php';
require_once MEDMOVIL_DIR . '/inc/portal/paciente.php';
require_once MEDMOVIL_DIR . '/inc/portal/profesional.php';
require_once MEDMOVIL_DIR . '/inc/portal/admin.php';
require_once MEDMOVIL_DIR . '/inc/seed-data.php';
require_once MEDMOVIL_DIR . '/inc/activate.php';
