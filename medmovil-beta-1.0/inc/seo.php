<?php
/**
 * SEO: títulos, descripciones, Open Graph, schema.org y sitemap.
 *
 * Todo esto funciona sin plugin. Si más adelante instalas Rank Math o Yoast,
 * el tema detecta que están activos y se hace a un lado para no duplicar etiquetas.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ¿Hay un plugin de SEO haciéndose cargo?
 *
 * @return bool
 */
function medmovil_hay_plugin_seo() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Título en Google.
 *
 * @param array $partes Partes del título.
 * @return array
 */
function medmovil_title_parts( $partes ) {
	if ( medmovil_hay_plugin_seo() || ! is_singular() ) {
		return $partes;
	}
	$propio = get_post_meta( get_queried_object_id(), '_mm_seo_title', true );
	if ( $propio ) {
		$partes['title'] = $propio;
		unset( $partes['tagline'], $partes['site'] );
	}
	return $partes;
}
add_filter( 'document_title_parts', 'medmovil_title_parts' );

/**
 * Descripción, Open Graph y canónica.
 */
function medmovil_head() {
	if ( medmovil_hay_plugin_seo() ) {
		return;
	}

	$desc  = '';
	$titulo = wp_get_document_title();
	$imagen = MEDMOVIL_URI . '/assets/img/logo.png';

	if ( is_singular() ) {
		$id   = get_queried_object_id();
		$desc = get_post_meta( $id, '_mm_seo_desc', true );

		if ( ! $desc && 'mm_prestacion' === get_post_type( $id ) ) {
			$precio = get_post_meta( $id, '_mm_precio', true );
			$desc   = sprintf(
				/* translators: 1: nombre del examen, 2: región, 3: precio */
				__( '%1$s a domicilio en %2$s. Un profesional llega a tu casa con equipo portátil e informe validado por médico radiólogo en 24 horas. Valor particular %3$s.', 'medmovil' ),
				get_the_title( $id ),
				medmovil_opt( 'region', 'la Región Metropolitana' ),
				medmovil_precio( $precio )
			);
		}
		if ( ! $desc ) {
			$desc = get_the_excerpt( $id );
		}
		if ( has_post_thumbnail( $id ) ) {
			$imagen = get_the_post_thumbnail_url( $id, 'full' );
		}
	} elseif ( is_home() || is_front_page() ) {
		$desc = get_bloginfo( 'description' );
	}

	$desc = wp_trim_words( wp_strip_all_tags( (string) $desc ), 32, '…' );

	echo "\n<!-- MedMóvil SEO -->\n";
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $titulo ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $imagen ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", 'es_CL' );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );

	if ( is_singular() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( get_permalink() ) );
	}

	medmovil_schema();

	$ga = medmovil_opt( 'ga4', '' );
	if ( $ga ) {
		printf( '<script async src="https://www.googletagmanager.com/gtag/js?id=%s"></script>' . "\n", esc_attr( $ga ) );
		printf(
			'<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","%s");</script>' . "\n",
			esc_js( $ga )
		);
	}
}
add_action( 'wp_head', 'medmovil_head', 1 );

/**
 * Marcado schema.org. Es lo que hace que Google muestre precio y preguntas
 * directamente en los resultados de búsqueda.
 */
function medmovil_schema() {
	$negocio = array(
		'@type'       => 'MedicalBusiness',
		'@id'         => home_url( '/#negocio' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'logo'        => MEDMOVIL_URI . '/assets/img/logo.png',
		'image'       => MEDMOVIL_URI . '/assets/img/logo.png',
		'telephone'   => medmovil_opt( 'telefono', '' ),
		'email'       => medmovil_opt( 'email', '' ),
		'priceRange'  => '$$',
		'address'     => array(
			'@type'           => 'PostalAddress',
			'addressRegion'   => medmovil_opt( 'region', 'Región Metropolitana' ),
			'addressCountry'  => 'CL',
		),
		'areaServed'  => array(
			'@type' => 'AdministrativeArea',
			'name'  => medmovil_opt( 'region', 'Región Metropolitana' ),
		),
		'availableService' => array(
			'@type' => 'MedicalProcedure',
			'name'  => __( 'Exámenes de imagenología, cardiología y laboratorio a domicilio', 'medmovil' ),
		),
	);

	$grafo = array( $negocio );

	if ( is_singular( 'mm_prestacion' ) ) {
		$id     = get_queried_object_id();
		$precio = (int) get_post_meta( $id, '_mm_precio', true );

		$grafo[] = array(
			'@type'       => 'MedicalTest',
			'name'        => get_the_title( $id ),
			'description' => wp_strip_all_tags( get_post_meta( $id, '_mm_resumen', true ) ),
			'url'         => get_permalink( $id ),
			'usedToDiagnose' => array_map(
				static function ( $c ) {
					return array( '@type' => 'MedicalCondition', 'name' => $c[1] );
				},
				medmovil_lineas( get_post_meta( $id, '_mm_condiciones', true ), 3 )
			),
		);

		if ( $precio ) {
			$grafo[] = array(
				'@type'       => 'Offer',
				'name'        => get_the_title( $id ),
				'url'         => get_permalink( $id ),
				'price'       => $precio,
				'priceCurrency' => 'CLP',
				'availability'  => 'https://schema.org/InStock',
				'areaServed'    => medmovil_opt( 'region', 'Región Metropolitana' ),
				'seller'        => array( '@id' => home_url( '/#negocio' ) ),
			);
		}

		$faq = medmovil_lineas( get_post_meta( $id, '_mm_faq', true ), 2 );
		if ( $faq ) {
			$grafo[] = array(
				'@type'      => 'FAQPage',
				'mainEntity' => array_map(
					static function ( $par ) {
						return array(
							'@type'          => 'Question',
							'name'           => $par[0],
							'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $par[1] ),
						);
					},
					$faq
				),
			);
		}
	}

	if ( is_singular( 'mm_comuna' ) ) {
		$id = get_queried_object_id();
		$grafo[] = array(
			'@type'      => 'LocalBusiness',
			'name'       => sprintf( '%s — %s', get_bloginfo( 'name' ), get_the_title( $id ) ),
			'url'        => get_permalink( $id ),
			'telephone'  => medmovil_opt( 'telefono', '' ),
			'areaServed' => array( '@type' => 'City', 'name' => get_the_title( $id ) ),
			'parentOrganization' => array( '@id' => home_url( '/#negocio' ) ),
		);
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array( '@context' => 'https://schema.org', '@graph' => $grafo ),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}

/**
 * Incluye los tipos propios en el sitemap nativo de WordPress.
 *
 * @param array $tipos Tipos incluidos.
 * @return array
 */
function medmovil_sitemap_tipos( $tipos ) {
	foreach ( array( 'mm_prestacion', 'mm_comuna', 'mm_pack' ) as $tipo ) {
		$objeto = get_post_type_object( $tipo );
		if ( $objeto ) {
			$tipos[ $tipo ] = $objeto;
		}
	}
	return $tipos;
}
add_filter( 'wp_sitemaps_post_types', 'medmovil_sitemap_tipos' );
