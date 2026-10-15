<?php
/** Presentación comercial; utiliza las prestaciones y precios existentes. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
require_once __DIR__ . '/lucide-icons.php';

/** Asociación estable: nunca depende del orden de get_terms(). */
function medmovil_icono_linea( $linea ) {
    $mapa = array(
        'cardiologia' => 'il-cardio',
        'doppler' => 'il-dop',
        'ecografias-generales' => 'il-eco',
        'ecografia-general' => 'il-eco',
        'enfermeria' => 'il-nurse',
        'funcion-pulmonar' => 'il-lung',
        'laboratorio' => 'il-lab',
        'musculoesqueletica' => 'il-msk',
        'musculoesqueletico' => 'il-msk',
        'radiologia' => 'il-rx',
        'radiografias' => 'il-rx',
    );
    if ( ! is_object( $linea ) ) { return 'il-nurse'; }
    foreach ( array( isset( $linea->slug ) ? $linea->slug : '', isset( $linea->name ) ? $linea->name : '' ) as $valor ) {
        $clave = sanitize_title( remove_accents( $valor ) );
        if ( isset( $mapa[ $clave ] ) ) { return $mapa[ $clave ]; }
    }
    return 'il-nurse';
}
add_action( 'wp_enqueue_scripts', function () {
    if ( is_front_page() ) {
        wp_enqueue_style( 'medmovil-tienda', MEDMOVIL_URI . '/assets/css/storefront.css', array( 'medmovil' ), MEDMOVIL_VERSION );
        wp_enqueue_script( 'medmovil-tienda', MEDMOVIL_URI . '/assets/js/storefront.js', array(), MEDMOVIL_VERSION, true );
    }
}, 30 );

/** Tarjeta comercial con enlaces nativos y botón del carrito existente. */
function medmovil_producto_tienda( $id ) {
    $linea = medmovil_linea_de( $id );
    $precio = (int) get_post_meta( $id, '_mm_precio', true );
    $antes = (int) get_post_meta( $id, '_mm_precio_antes', true );
    $icono = get_post_meta( $id, '_mm_icono', true ) ?: 'il-eco';
    $titulo = get_the_title( $id );
    $terminos = wp_get_post_terms( $id, 'mm_linea', array( 'fields' => 'slugs' ) );
    $categorias = is_wp_error( $terminos ) ? array() : $terminos;
    ?>
    <article class="shop-product" data-category="<?php echo esc_attr( implode( ' ', $categorias ) ); ?>" data-search="<?php echo esc_attr( $titulo . ' ' . get_post_meta( $id, '_mm_codigo', true ) ); ?>" data-price="<?php echo esc_attr( $precio ); ?>">
        <a class="shop-photo" href="<?php echo esc_url( get_permalink( $id ) ); ?>" aria-label="<?php echo esc_attr( 'Ver ' . $titulo ); ?>">
            <?php if ( has_post_thumbnail( $id ) ) : ?>
                <?php echo get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
            <?php else : ?>
                <span class="shop-photo-icon"><?php medmovil_icono( $icono, 'ilo' ); ?></span>
                <span class="shop-photo-label">Atención a domicilio</span>
            <?php endif; ?>
            <?php if ( $precio > 0 && $antes > $precio ) : ?><span class="shop-off">−<?php echo esc_html( round( ( 1 - $precio / $antes ) * 100 ) ); ?>%</span><?php endif; ?>
        </a>
        <?php medmovil_boton_subir_imagen( $id ); ?>
        <div class="shop-product-body">
            <span class="shop-category"><?php echo esc_html( $linea ? $linea->name : 'Atención a domicilio' ); ?></span>
            <h3><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( $titulo ); ?></a></h3>
            <div class="shop-price"><?php echo esc_html( $precio > 0 ? medmovil_precio( $precio ) : 'Consultar valor' ); ?><?php if ( $precio > 0 && $antes > $precio ) : ?><s><?php echo esc_html( medmovil_precio( $antes ) ); ?></s><?php endif; ?></div>
            <a class="shop-reserve" href="<?php echo esc_url( medmovil_url_reserva( $id ) ); ?>">Reservar hora <?php medmovil_icono( 'i-arr' ); ?></a>
            <div class="shop-secondary"><a href="<?php echo esc_url( get_permalink( $id ) ); ?>">Ver detalle</a><button type="button" class="mm-add" data-mm-add="<?php echo esc_attr( wp_json_encode( medmovil_datos_carrito( $id ) ) ); ?>" aria-label="<?php echo esc_attr( 'Agregar ' . $titulo . ' a mi reserva' ); ?>"><?php medmovil_icono( 'i-plus' ); ?> Agregar</button></div>
        </div>
    </article>
    <?php
}
