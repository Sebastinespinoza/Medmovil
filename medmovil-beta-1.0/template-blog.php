<?php
/**
 * Template Name: Blog
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pagina = max( 1, (int) get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : (int) get_query_var( 'page' ) );
$tema   = isset( $_GET['tema'] ) ? sanitize_title( wp_unslash( $_GET['tema'] ) ) : '';

$args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 9,
	'paged'          => $pagina,
);
if ( $tema ) {
	$args['category_name'] = $tema;
}

$entradas = new WP_Query( $args );
$termino  = $tema ? get_category_by_slug( $tema ) : null;
?>
<div class="svc-top">
	<div class="wrap" style="padding-block:34px 40px">
		<?php medmovil_migas( array( array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ), array( get_the_title(), '' ) ) ); ?>
		<p class="eyebrow" style="margin-top:20px"><?php esc_html_e( 'Blog', 'medmovil' ); ?></p>
		<h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:14px">
			<?php echo esc_html( $termino ? $termino->name : __( 'Guías escritas por tecnólogos médicos', 'medmovil' ) ); ?>
		</h1>
		<div class="prose" style="margin-top:18px"><?php while ( have_posts() ) { the_post(); the_content(); } ?></div>
	</div>
</div>

<?php if ( $entradas->have_posts() ) : ?>

	<div class="filtros" id="filtros" style="padding-block:12px">
		<?php medmovil_chips_blog( $tema ); ?>
	</div>

	<section class="band" style="padding-block:36px 60px">
		<div class="wrap">
			<p class="fl-cuenta">
				<b><?php echo esc_html( number_format_i18n( $entradas->found_posts ) ); ?></b>
				<?php echo esc_html( _n( 'artículo publicado', 'artículos publicados', $entradas->found_posts, 'medmovil' ) ); ?>
			</p>

			<div class="bgrid">
				<?php
				while ( $entradas->have_posts() ) {
					$entradas->the_post();
					medmovil_tarjeta_entrada( get_post() );
				}
				wp_reset_postdata();
				?>
			</div>

			<?php
			$enlaces = paginate_links( array(
				'total'     => $entradas->max_num_pages,
				'current'   => $pagina,
				'type'      => 'array',
				'prev_text' => __( 'Anterior', 'medmovil' ),
				'next_text' => __( 'Siguiente', 'medmovil' ),
			) );
			if ( $enlaces ) :
				?>
				<nav class="bpag" aria-label="<?php esc_attr_e( 'Páginas del blog', 'medmovil' ); ?>">
					<?php foreach ( $enlaces as $enlace ) { echo wp_kses_post( $enlace ); } ?>
				</nav>
			<?php endif; ?>
		</div>
	</section>

<?php else : ?>

	<section class="band" style="padding-block:60px">
		<div class="wrap">
			<div class="fl-vacio">
				<?php medmovil_icono( 'il-doc', 'ilo' ); ?>
				<b><?php esc_html_e( 'Todavía no hay artículos publicados', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Mientras tanto, en cada examen del catálogo está explicado qué es, cuándo lo piden y cómo prepararse.', 'medmovil' ); ?></p>
				<a class="btn btn-p" href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>">
					<?php esc_html_e( 'Ver el catálogo', 'medmovil' ); ?> <?php medmovil_icono( 'i-arr' ); ?>
				</a>
			</div>
		</div>
	</section>

<?php endif; ?>

<?php get_template_part( 'partials/convenios' ); ?>
<?php get_footer(); ?>
