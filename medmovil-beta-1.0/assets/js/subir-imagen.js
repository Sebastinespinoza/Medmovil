/* ============================================================
   «Cambiar imagen» de una prestación desde la misma página.
   Solo se carga para administradores (ver inc/subir-imagen.php).
   ============================================================ */
(function () {
	'use strict';

	var cfg = window.mmSubirImagen;
	if (!cfg) { return; }

	var d = document;
	var TIPOS = ['image/jpeg', 'image/png', 'image/webp'];

	var entrada = d.createElement('input');
	entrada.type = 'file';
	entrada.accept = TIPOS.join(',');
	entrada.hidden = true;
	d.body.appendChild(entrada);

	var boton = null;

	d.addEventListener('click', function (e) {
		var b = e.target.closest('[data-mm-subir]');
		if (!b) { return; }
		// El botón vive dentro de tarjetas que son enlaces: que no navegue.
		e.preventDefault();
		e.stopPropagation();
		if (b.disabled) { return; }
		boton = b;
		entrada.value = '';
		entrada.click();
	}, true);

	function estado(b, texto, clase) {
		var s = b.querySelector('span');
		if (!b.dataset.original) { b.dataset.original = s.textContent; }
		s.textContent = texto;
		b.classList.remove('ok', 'mal');
		if (clase) { b.classList.add(clase); }
	}

	function aviso(b, texto) {
		estado(b, texto, 'mal');
		b.disabled = false;
		setTimeout(function () { estado(b, b.dataset.original); }, 5000);
	}

	entrada.addEventListener('change', function () {
		var b = boton;
		var f = entrada.files && entrada.files[0];
		if (!b || !f) { return; }

		if (TIPOS.indexOf(f.type) === -1) { aviso(b, cfg.textos.tipo); return; }
		if (cfg.maxMb && f.size > cfg.maxMb * 1048576) { aviso(b, cfg.textos.grande.replace('%s', cfg.maxMb)); return; }

		var datos = new FormData();
		datos.append('action', 'medmovil_subir_imagen');
		datos.append('nonce', cfg.nonce);
		datos.append('post_id', b.dataset.mmSubir);
		datos.append('imagen', f);

		b.disabled = true;
		estado(b, cfg.textos.subiendo);

		fetch(cfg.url, { method: 'POST', body: datos, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (r) {
				if (!r || !r.success) { throw new Error(r && r.data && r.data.mensaje ? r.data.mensaje : cfg.textos.error); }
				estado(b, cfg.textos.listo, 'ok');
				// Recarga para que la foto aparezca en todas las tarjetas y en su tamaño correcto.
				setTimeout(function () { window.location.reload(); }, 600);
			})
			.catch(function (err) { aviso(b, err && err.message ? err.message : cfg.textos.error); });
	});
})();
