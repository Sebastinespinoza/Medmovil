# MedMóvil 1.8.0 — Inicio comercial

Esta versión parte del tema original 1.6.1. Los portales demo quedan fuera de esta entrega.

## Cambios

- Corrige `Undefined array key "l"`: el panel utiliza el índice `li` para obtener el nombre de categoría desde `ln`, con respaldo cuando falta la categoría.
- Sustituye el inicio por una tienda de prestaciones: categorías laterales, banner, catálogo, búsqueda, orden por precio/nombre, fotografía, valor, reserva y carrito.
- Usa categorías y prestaciones publicadas de WordPress. No copia los precios de las capturas ni inventa productos.
- Iconos principales médicos/comerciales Lucide incorporados localmente; licencia incluida. Se respetan los iconos propios que hubieras configurado en MedMóvil > Iconos.
- Fotos de cada prestación: **Prestaciones > Editar > Imagen destacada**. Sin foto se muestra el icono de categoría.
- Foto del banner: **Páginas > página configurada como Inicio > Imagen destacada**. Si falta se muestra un bloque gráfico con icono.
- Se conserva `screenshot.png` en minúscula y singular. La captura es la anterior, no una imagen de la nueva portada.

## Instalar

1. Respalda el tema actual y la base de datos.
2. En **Apariencia > Temas > Añadir nuevo > Subir tema**, sube `Medmovil-1.8.0-TIENDA.zip` y reemplaza el tema existente. Conserva la carpeta `medmovil-beta-1.0` para actualizar ese tema.
3. Vacía la caché del sitio y del navegador. El número de versión también renueva las URLs de CSS y JavaScript.
4. Abre Inicio. Comprueba categorías, búsqueda, ordenar, detalle, agregar al carrito y reservar. Los enlaces conducen a los flujos existentes; esta entrega no incorpora pago en línea ni cambia permisos clínicos.
5. Cambia una imagen destacada y confirma que aparece en el inicio y en la ficha de la prestación.

Las páginas y precios editados se consultan desde WordPress; no se reemplazan por datos de prueba. Se mantiene el comportamiento de actualización existente del tema.

## Verificación

Sintaxis JavaScript y pruebas locales de filtros/orden. Revisión de integración con el carrito existente y del acceso seguro a la categoría. No se dispone aquí de PHP ni de WordPress en ejecución; falta confirmar el resultado visual y el funcionamiento completo en la instalación de ensayo antes de publicarlo.
