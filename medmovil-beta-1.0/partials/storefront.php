<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$categorias = get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => true ) );
if ( is_wp_error( $categorias ) ) { $categorias = array(); }
$productos = get_posts( array( 'post_type' => 'mm_prestacion', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
$catalogo = get_post_type_archive_link( 'mm_prestacion' );
?>
<section class="shop-home wrap" aria-label="Prestaciones a domicilio">
    <div class="shop-heading"><div><span class="shop-eyebrow">MEDMÓVIL · SALUD A DOMICILIO</span><h1>Elige tu examen. Nosotros vamos.</h1></div><a class="shop-viewall" href="<?php echo esc_url( $catalogo ); ?>">Ver todo el catálogo <?php medmovil_icono( 'i-arr' ); ?></a></div>
    <div class="shop-layout">
            <div class="shop-toolbar" hidden><label><span class="screen-reader-text">Buscar prestación</span><input id="shop-search" type="search" placeholder="Busca tu examen…"></label><label><span class="screen-reader-text">Ordenar prestaciones</span><select id="shop-sort"><option value="default">Recomendados</option><option value="asc">Menor precio</option><option value="desc">Mayor precio</option><option value="name">Nombre A–Z</option></select></label></div>
        <aside class="shop-sidebar" aria-label="Categorías de prestaciones">
            <span class="shop-eyebrow">ELIGE UN ÁREA</span><h2>¿Qué necesitas?</h2>
            <div class="shop-categories">
                <a href="<?php echo esc_url( $catalogo ); ?>" class="shop-filter active" data-filter="all" aria-current="true">Todas las prestaciones <?php medmovil_icono( 'i-arr' ); ?></a>
                <?php foreach ( $categorias as $categoria ) : $url = get_term_link( $categoria ); if ( is_wp_error( $url ) ) { continue; } ?>
                    <a class="shop-filter" href="<?php echo esc_url( $url ); ?>" data-filter="<?php echo esc_attr( $categoria->slug ); ?>"><?php echo esc_html( $categoria->name ); ?><?php medmovil_icono( 'i-chev' ); ?></a>
                <?php endforeach; ?>
            </div>
        </aside>
        <div class="shop-main" id="shop-catalog">
            <div class="shop-grid" id="shop-track" tabindex="0" aria-label="Carrusel de prestaciones; desplázate para ver más"><?php foreach ( $productos as $producto ) { medmovil_producto_tienda( $producto->ID ); } ?></div>
            <p id="shop-empty" <?php echo $productos ? 'hidden' : ''; ?>>No hay prestaciones que coincidan. Prueba otra búsqueda.</p>
            <div class="shop-carousel-footer"><span id="shop-count" role="status" aria-live="polite"><?php echo esc_html( count( $productos ) ); ?> prestaciones</span><div class="shop-arrows" hidden><button type="button" id="shop-prev" aria-label="Prestaciones anteriores" aria-controls="shop-track"><?php medmovil_icono( 'i-arr' ); ?></button><button type="button" id="shop-next" aria-label="Siguientes prestaciones" aria-controls="shop-track"><?php medmovil_icono( 'i-arr' ); ?></button></div></div>
            <noscript><p>Desliza las prestaciones o visita el catálogo completo.</p></noscript>
        </div>
    </div>
</section>
