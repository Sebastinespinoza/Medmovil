<?php
/**
 * Cabecera del sitio.
 *
 * Estructura de tienda: barra de anuncio, fila con logo + buscador + carrito, y
 * fila de navegación. El buscador de arriba es el que hace que el sitio se
 * comporte como un catálogo y no como un folleto.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip" href="#contenido" style="position:absolute;left:-9999px;top:0;background:#fff;padding:12px 18px;z-index:999"
   onfocus="this.style.left='12px'" onblur="this.style.left='-9999px'"><?php esc_html_e( 'Saltar al contenido', 'medmovil' ); ?></a>

<?php
// Dentro del panel no se dibuja la cabecera de la tienda: el panel ya trae su
// propia barra superior y su menú lateral, y las dos juntas roban pantalla.
if ( medmovil_en_panel() ) {
	echo '<main id="contenido">';
	return;
}

$promo     = medmovil_opt( 'promo_texto' );
$promo_url = medmovil_opt( 'promo_url' );
if ( $promo ) :
	?>
	<div class="promo">
		<div class="wrap">
			<p class="promo-msg">
				<?php medmovil_icono( 'i-bolt' ); ?>
				<span><?php echo esc_html( $promo ); ?></span>
				<?php if ( $promo_url ) : ?>
					<a href="<?php echo esc_url( $promo_url ); ?>"><?php esc_html_e( 'Ver más', 'medmovil' ); ?></a>
				<?php endif; ?>
			</p>
			<div class="promo-r">
				<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', medmovil_opt( 'telefono', '' ) ) ); ?>">
					<?php medmovil_icono( 'i-phone' ); ?> <?php echo esc_html( medmovil_opt( 'telefono', '' ) ); ?>
				</a>
				<span class="sep"></span>
				<a href="<?php echo esc_url( medmovil_opt( 'instagram_url', '#' ) ); ?>" target="_blank" rel="noopener">
					<?php medmovil_icono( 'i-ig' ); ?> <?php echo esc_html( medmovil_opt( 'instagram', '' ) ); ?>
				</a>
				<span class="sep"></span>
				<span><?php medmovil_icono( 'i-clock' ); ?> <?php echo esc_html( medmovil_opt( 'horario', '' ) ); ?></span>
			</div>
		</div>
	</div>
<?php endif; ?>

<header class="head" id="head">

	<div class="head-1">
		<div class="wrap">
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( MEDMOVIL_URI . '/assets/img/logo.png' ); ?>"
					     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="480" height="134">
				<?php endif; ?>
			</a>

			<form class="hsearch" id="hsearch" role="search" autocomplete="off"
			      action="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>">
				<?php medmovil_icono( 'il-search', 'ilo' ); ?>
				<input type="search" id="hq" name="q"
				       placeholder="<?php esc_attr_e( 'Busca tu examen: rodilla, abdomen, o el código de tu orden…', 'medmovil' ); ?>"
				       aria-label="<?php esc_attr_e( 'Buscar un examen', 'medmovil' ); ?>"
				       aria-expanded="false" aria-controls="hsug">
				<button type="submit" class="hsearch-go"><?php esc_html_e( 'Buscar', 'medmovil' ); ?></button>
				<div class="hsug" id="hsug" role="listbox" hidden></div>
			</form>

			<div class="head-acc">
				<?php
				// Entrada al portal. Sin esto, quien crea una cuenta no tiene
				// cómo volver a ella desde el sitio.
				$mm_yo = wp_get_current_user();
				if ( $mm_yo && $mm_yo->ID ) :
					?>
					<a class="cartbtn cuentabtn cuentabtn-on" href="<?php echo esc_url( medmovil_url_portal() ); ?>"
					   title="<?php esc_attr_e( 'Ir a mi cuenta', 'medmovil' ); ?>">
						<span class="cuenta-av"><?php echo esc_html( mb_substr( $mm_yo->display_name, 0, 1 ) ); ?></span>
					</a>
				<?php else : ?>
					<a class="cartbtn cuentabtn" href="<?php echo esc_url( medmovil_url_portal() ); ?>"
					   title="<?php esc_attr_e( 'Entrar a mi cuenta', 'medmovil' ); ?>">
						<?php medmovil_icono( 'i-user', 'ilo' ); ?>
					</a>
				<?php endif; ?>

				<?php medmovil_boton_carrito(); ?>
				<a class="btn btn-p btn-hd" href="<?php echo esc_url( medmovil_url_agenda() ); ?>">
					<?php medmovil_icono( 'i-cal' ); ?> <?php $cta = medmovil_opt( 'cta_texto', __( 'Solicitar hora', 'medmovil' ) ); echo esc_html( 'Agendar hora' === $cta ? __( 'Solicitar hora', 'medmovil' ) : $cta ); ?>
				</a>
			</div>

			<button class="burger" id="burger" aria-label="<?php esc_attr_e( 'Abrir menú', 'medmovil' ); ?>" aria-expanded="false">
				<?php medmovil_icono( 'i-menu' ); ?>
			</button>
		</div>
	</div>

	<div class="head-2">
		<div class="wrap">
			<nav class="nav" id="nav" aria-label="<?php esc_attr_e( 'Menú principal', 'medmovil' ); ?>">
				<div>
					<button class="nl" id="megaBtn" aria-expanded="false" aria-controls="mega">
						<?php medmovil_icono( 'i-menu', 'ico' ); ?>
						<?php esc_html_e( 'Prestaciones', 'medmovil' ); ?>
						<?php medmovil_icono( 'i-chev', 'ico chev' ); ?>
					</button>
					<div class="mega" id="mega">
						<?php
						$lineas = get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => true ) );
						$mostradas = 0;
						foreach ( $lineas as $linea ) :
							if ( $mostradas >= 3 ) {
								break;
							}
							$mostradas++;
							$items = get_posts( array(
								'post_type'      => 'mm_prestacion',
								'posts_per_page' => 8,
								'orderby'        => array( 'menu_order' => 'ASC' ),
								'tax_query'      => array( array( 'taxonomy' => 'mm_linea', 'field' => 'term_id', 'terms' => $linea->term_id ) ),
							) );
							$icono = array( 'il-eco', 'il-msk', 'il-dop' )[ $mostradas - 1 ];
							?>
							<div class="mega-col">
								<div class="mega-h" style="color:<?php echo esc_attr( medmovil_color_linea( $linea ) ); ?>">
									<?php medmovil_icono( $icono, 'ilo' ); ?>
									<a href="<?php echo esc_url( get_term_link( $linea ) ); ?>" style="color:inherit"><?php echo esc_html( $linea->name ); ?></a>
								</div>
								<ul>
									<?php foreach ( $items as $item ) : ?>
										<li><a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( $item->post_title ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>

						<div class="mega-foot">
							<p><?php esc_html_e( 'También radiografía digital portátil, electrocardiograma, Holter, presión arterial de 24 horas, espirometría, toma de muestras y enfermería a domicilio.', 'medmovil' ); ?></p>
							<a class="btn btn-p btn-sm" href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>">
								<?php esc_html_e( 'Ver catálogo completo', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
							</a>
						</div>
					</div>
				</div>

				<?php
				if ( has_nav_menu( 'principal' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'principal',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'walker'         => new Medmovil_Menu_Walker(),
						'depth'          => 1,
					) );
				}
				?>
			</nav>

			<p class="head-envio">
				<?php medmovil_icono( 'i-car' ); ?>
				<span><?php esc_html_e( 'Sin cobro de traslado en toda la Región Metropolitana', 'medmovil' ); ?></span>
			</p>
		</div>
	</div>
</header>

<main id="contenido">
