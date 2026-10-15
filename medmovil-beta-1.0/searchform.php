<?php
/**
 * Formulario de búsqueda.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="qbook" style="grid-template-columns:1fr auto;max-width:520px" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="field">
		<label for="s"><?php esc_html_e( 'Buscar un examen', 'medmovil' ); ?></label>
		<input type="search" id="s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Ej. ecografía abdominal', 'medmovil' ); ?>">
	</div>
	<button class="btn btn-p" type="submit"><?php esc_html_e( 'Buscar', 'medmovil' ); ?></button>
</form>
