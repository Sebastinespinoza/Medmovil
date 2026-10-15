/**
 * MedMóvil — interacciones del sitio público.
 * Sin librerías: todo lo que hay aquí es JavaScript del navegador.
 */

/**
 * Lee #mm-datos y rearma los campos que el servidor manda comprimidos.
 *
 * El servidor no repite la URL completa ni el nombre de la línea en los 39
 * exámenes y las 40 comunas: manda la base una vez y acá se vuelve a armar.
 * Eran 5 KB en cada página.
 */
window.mmDatos = function () {
	if (window.mmDatos.cache) { return window.mmDatos.cache; }

	var datos = { ex: [], co: [] };
	var json = document.getElementById('mm-datos');
	if (json) {
		try { datos = JSON.parse(json.textContent); } catch (e) { /* sin datos */ }
	}
	var lineas = datos.ln || [];
	(datos.ex || []).forEach(function (e) {
		e.u = (datos.bx || '') + e.s + '/';
		var l = lineas[e.li] || {};
		e.l = l.n || '';
		e.ls = l.s || '';
		e.c = l.c || '';
	});
	(datos.co || []).forEach(function (c) {
		c.u = (datos.bc || '') + c.s + '/';
	});

	window.mmDatos.cache = datos;
	return datos;
};

(function () {
	'use strict';

	var d = document;
	var $ = function (s, c) { return (c || d).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };

	/* ---- cabecera que se comprime al bajar ---- */
	var head = $('#head');
	if (head) {
		var onScroll = function () { head.classList.toggle('stuck', window.scrollY > 12); };
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* ---- mega menú de prestaciones ---- */
	var megaBtn = $('#megaBtn');
	var mega = $('#mega');
	function cerrarMega() {
		if (mega) {
			mega.classList.remove('open');
			megaBtn.setAttribute('aria-expanded', 'false');
		}
	}
	if (megaBtn && mega) {
		megaBtn.addEventListener('click', function (e) {
			e.stopPropagation();
			var abierto = mega.classList.toggle('open');
			megaBtn.setAttribute('aria-expanded', abierto ? 'true' : 'false');
		});
		d.addEventListener('click', function (e) {
			if (mega.classList.contains('open') && !mega.contains(e.target) && e.target !== megaBtn) {
				cerrarMega();
			}
		});
	}

	/* ---- menú móvil ---- */
	var burger = $('#burger');
	var nav = $('#nav');
	function cerrarNav() {
		if (nav) {
			nav.classList.remove('open');
			burger.setAttribute('aria-expanded', 'false');
		}
	}
	if (burger && nav) {
		burger.addEventListener('click', function () {
			var abierto = nav.classList.toggle('open');
			burger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
		});
	}

	d.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { cerrarMega(); cerrarNav(); }
	});

	/* ---- buscador rápido de la portada ---- */
	var qbook = $('.qbook[method="get"]');
	if (qbook && $('#qb-p', qbook)) {
		qbook.addEventListener('submit', function (e) {
			e.preventDefault();
			var slug = $('#qb-p', qbook).value;
			if (slug) {
				window.location.href = qbook.dataset.base
					? qbook.dataset.base + slug + '/'
					: '/examenes/' + slug + '/';
			}
		});
	}

	/* ---- mapa de cobertura ---- */
	(function () {
		var map = $('.rmmap');
		var salida = $('#mapout');
		if (!map || !salida) { return; }

		var datos = {};
		var json = $('#mm-comunas');
		if (json) {
			try { datos = JSON.parse(json.textContent); } catch (err) { datos = {}; }
		}

		var nombre = $('#mapoutN');
		var detalle = $('#mapoutT');
		var textos = {
			a: 'atendemos todos los días',
			b: 'varios días por semana',
			c: 'coordinamos a pedido'
		};

		function marcar(objetivo) {
			var forma = objetivo.closest ? objetivo.closest('.cm') : null;
			if (!forma) { return null; }
			var titulo = forma.querySelector('title');
			var comuna = titulo ? titulo.textContent : '';
			var info = datos[comuna];

			$$('.cm', map).forEach(function (x) { x.classList.remove('act'); });
			forma.classList.add('act');

			nombre.textContent = comuna;
			if (info) {
				detalle.textContent = (textos[info.zona] || textos.c) + ' · llegada estimada ' + info.min + ' min';
			} else {
				detalle.textContent = 'escríbenos y lo coordinamos';
			}
			salida.classList.add('on');
			return info;
		}

		map.addEventListener('mouseover', function (e) { marcar(e.target); });
		map.addEventListener('click', function (e) {
			var info = marcar(e.target);
			if (info && info.url) { window.location.href = info.url; }
		});
		map.addEventListener('mouseleave', function () {
			salida.classList.remove('on');
			$$('.cm', map).forEach(function (x) { x.classList.remove('act'); });
		});
	})();

	/* ---- revelado al hacer scroll, solo bajo el primer pantallazo ---- */
	(function () {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
		if (!('IntersectionObserver' in window)) { return; }

		var todos = $$('[data-rv]');
		var abajo = todos.filter(function (el) {
			return el.getBoundingClientRect().top > window.innerHeight * 0.92;
		});
		if (!abajo.length) { return; }

		d.body.classList.add('js-anim');
		todos.forEach(function (el) {
			if (abajo.indexOf(el) === -1) { el.classList.add('in'); }
		});

		var io = new IntersectionObserver(function (entradas) {
			entradas.forEach(function (x) {
				if (x.isIntersecting) {
					x.target.classList.add('in');
					io.unobserve(x.target);
				}
			});
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

		abajo.forEach(function (el) { io.observe(el); });
	})();
})();

/* ---- selector de zona del cuerpo ---- */
(function () {
	'use strict';
	var d = document;
	var fig = d.querySelector('.mmbody');
	var panel = d.getElementById('zonasPanel');
	if (!fig || !panel) { return; }

	var datos = {};
	var json = d.getElementById('mm-zonas');
	if (json) {
		try { datos = JSON.parse(json.textContent); } catch (e) { datos = {}; }
	}

	var vacio = d.getElementById('zonasVacio');
	var lista = d.getElementById('zonasLista');
	var items = d.getElementById('zonasItems');
	var nombre = d.getElementById('zonasNombre');
	var cerrar = d.getElementById('zonasCerrar');
	var seleccion = d.getElementById('zonasSeleccion');

	function pintar(grupo, etiqueta) {
		var examenes = datos[grupo];
		if (!examenes || !examenes.length) { nombre.textContent = etiqueta; items.textContent = 'Consulta disponibilidad de exámenes para esta zona.'; vacio.hidden = true; lista.hidden = false; return; }

		nombre.textContent = etiqueta;
		items.innerHTML = examenes.map(function (e) {
			var meta = [];
			if (e.l) { meta.push(e.l); }
			if (e.m) { meta.push(e.m + ' min'); }
			meta.push('A domicilio');
			return '<a class="zex" style="--ac:' + e.c + '" href="' + e.u + '">' +
				'<span class="zex-ic"><svg class="ilo"><use href="#' + e.i + '"/></svg></span>' +
				'<span><b>' + e.n + '</b><span class="zex-meta"><span>' +
				meta.join('</span><span>') + '</span></span></span>' +
				'<span class="zex-precio">' + e.p + '<small>todo incluido</small></span></a>';
		}).join('');

		vacio.hidden = true;
		lista.hidden = false;
	}

	function limpiar() {
		Array.prototype.forEach.call(fig.querySelectorAll('.mmz'), function (z) {
			z.classList.remove('act'); z.setAttribute('aria-pressed', 'false');
		});
		lista.hidden = true;
		vacio.hidden = false;
		if (seleccion) seleccion.textContent = seleccion.dataset.base || 'Vista espejo: posterior a la izquierda y frontal a la derecha. Selecciona una zona.';
		d.dispatchEvent(new CustomEvent('mm:zona-limpiar'));
	}

	function elegir(zona) {
		var grupo = zona.dataset.grupo;
		var t = zona.querySelector('title');
		var etiqueta = t ? t.textContent : '';
		Array.prototype.forEach.call(fig.querySelectorAll('.mmz'), function (z) {
			var activa = z.dataset.zona === zona.dataset.zona; z.classList.toggle('act', activa); z.setAttribute('aria-pressed', activa ? 'true' : 'false');
		});
		if (seleccion) seleccion.textContent = 'Zona seleccionada: ' + etiqueta + '. Vista espejo.';
		pintar(grupo, etiqueta);
	}

	fig.addEventListener('click', function (e) {
		var z = e.target.closest('.mmz');
		if (z) { elegir(z); }
	});

	// accesible con teclado
	Array.prototype.forEach.call(fig.querySelectorAll('.mmz'), function (z) {
		var t = z.querySelector('title');
		z.setAttribute('tabindex', '0');
		z.setAttribute('role', 'button'); z.setAttribute('aria-pressed', 'false');
		if (t) { z.setAttribute('aria-label', t.textContent); }
		z.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); elegir(z); }
		});
	});

	if (cerrar) { cerrar.addEventListener('click', limpiar); }

	// La figura 3D (assets/js/cuerpo-3d.js) usa el mismo panel de exámenes.
	window.medmovilZonas = {
		elegir: function (grupo, etiqueta) {
			if (seleccion) seleccion.textContent = 'Zona seleccionada: ' + etiqueta + '.';
			pintar(grupo, etiqueta);
		},
		limpiar: limpiar
	};
})();

/* ============================================================
   Carrito de reserva, cotizador de la portada, filtros del
   catálogo y asistente. Sin librerías.
   ============================================================ */
(function () {
	'use strict';

	var d = document;
	var $ = function (s, c) { return (c || d).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };

	var CLAVE = 'mm_reserva_v1';
	var CLAVE_COMUNA = 'mm_comuna_v1';
	function seguro(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
	function iconoSeguro(t) { return /^[a-z0-9-]+$/.test(t || '') ? t : 'il-eco'; }

	function pesos(n) {
		n = parseInt(n, 10) || 0;
		if (!n) { return 'Consultar'; }
		return '$' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
	}

	function guardar(clave, valor) {
		try { localStorage.setItem(clave, JSON.stringify(valor)); } catch (e) { /* modo privado */ }
	}
	function leer(clave, porDefecto) {
		try {
			var v = localStorage.getItem(clave);
			return v ? JSON.parse(v) : porDefecto;
		} catch (e) { return porDefecto; }
	}

	var reserva = leer(CLAVE, []) || [];
	if (!Array.isArray(reserva)) { reserva = []; }
	if (d.getElementById('reservaCompletada')) { reserva = []; guardar(CLAVE, reserva); }

	/* ---------- avisos ---------- */
	var toastT = null;
	function avisar(texto) {
		var t = $('#toast');
		if (!t) { return; }
		t.textContent = texto;
		t.hidden = false;
		clearTimeout(toastT);
		toastT = setTimeout(function () { t.hidden = true; }, 2600);
	}

	/* ---------- carrito ---------- */
	var cart = $('#cart');
	var cartBg = $('#cartBg');
	var cartBtn = $('#cartBtn');

	function pintarContador() {
		var n = $('#cartN');
		if (!n) { return; }
		n.textContent = reserva.length;
		n.hidden = reserva.length === 0;
	}

	function pintarCarrito() {
		if (!cart) { return; }
		var lista = $('#cartList');
		var vacio = $('#cartEmpty');
		var pie = $('#cartFoot');
		var com = $('#cartCom');

		pintarContador();

		if (!reserva.length) {
			lista.hidden = true;
			com.hidden = true;
			pie.hidden = true;
			vacio.hidden = false;
			return;
		}

		vacio.hidden = true;
		lista.hidden = false;
		com.hidden = false;
		pie.hidden = false;

		lista.innerHTML = reserva.map(function (e, i) {
			return '<li class="cart-it">' +
				'<span class="cart-it-ic"><svg class="ilo" aria-hidden="true"><use href="#' + iconoSeguro(e.i) + '"/></svg></span>' +
				'<span><b>' + seguro(e.n) + '</b><span>' + (e.m ? seguro(e.m) + ' min · ' : '') + seguro(e.l || 'A domicilio') + '</span></span>' +
				'<span class="cart-it-r"><span class="cart-it-p">' + pesos(e.p) + '</span>' +
				'<button type="button" class="cart-it-x" data-i="' + i + '">Quitar</button></span></li>';
		}).join('');

		var total = reserva.reduce(function (s, e) { return s + (parseInt(e.p, 10) || 0); }, 0);
		$('#cartTotal').textContent = pesos(total);

		var sel = $('#cartComuna');
		var guardada = leer(CLAVE_COMUNA, '');
		if (sel && guardada && sel.value !== guardada) { sel.value = guardada; }
		pintarLlegada();
	}

	function pintarLlegada() {
		var sel = $('#cartComuna');
		var hint = $('#cartHint');
		if (!sel || !hint) { return; }
		var op = sel.options[sel.selectedIndex];
		var min = op ? op.getAttribute('data-min') : '';
		if (sel.value && min) {
			hint.textContent = 'Llegamos a ' + sel.value + ' en unos ' + min + ' minutos desde que confirmamos la hora.';
		} else {
			hint.textContent = 'Con la comuna sabemos qué profesionales cubren tu sector y a qué hora podemos llegar.';
		}
	}

	function abrirCarrito() {
		if (!cart) { return; }
		pintarCarrito();
		cart.hidden = false;
		cartBg.hidden = false;
		d.body.style.overflow = 'hidden';
	}
	function cerrarCarrito() {
		if (!cart) { return; }
		cart.hidden = true;
		cartBg.hidden = true;
		d.body.style.overflow = '';
	}

	function agregar(item, silencioso) {
		if (!item || !item.id) { return false; }
		var existe = reserva.some(function (e) { return e.id === item.id; });
		if (existe) {
			if (!silencioso) { avisar(item.n + ' ya está en tu reserva'); }
			return false;
		}
		reserva.push(item);
		guardar(CLAVE, reserva);
		pintarCarrito();
		if (cartBtn) {
			cartBtn.classList.remove('pop');
			void cartBtn.offsetWidth;
			cartBtn.classList.add('pop');
		}
		if (!silencioso) { avisar('Agregado: ' + item.n); }
		return true;
	}

	d.addEventListener('click', function (e) {
		var b = e.target.closest ? e.target.closest('.mm-add') : null;
		if (b && b.dataset.mmAdd) {
			e.preventDefault();
			var datos;
			try { datos = JSON.parse(b.dataset.mmAdd); } catch (err) { return; }
			if (agregar(datos)) {
				b.classList.add('ok');
				var etiqueta = b.querySelector('span');
				var antes = etiqueta ? etiqueta.textContent : '';
				if (etiqueta) { etiqueta.textContent = 'Agregado'; }
				setTimeout(function () {
					b.classList.remove('ok');
					if (etiqueta) { etiqueta.textContent = antes; }
				}, 1600);
			}
		}

		var q = e.target.closest ? e.target.closest('.cart-it-x') : null;
		if (q) {
			reserva.splice(parseInt(q.dataset.i, 10), 1);
			guardar(CLAVE, reserva);
			pintarCarrito();
		}
	});

	if (cartBtn) { cartBtn.addEventListener('click', abrirCarrito); }
	if ($('#cartX')) { $('#cartX').addEventListener('click', cerrarCarrito); }
	if (cartBg) { cartBg.addEventListener('click', cerrarCarrito); }
	if ($('#cartComuna')) {
		$('#cartComuna').addEventListener('change', function () {
			guardar(CLAVE_COMUNA, this.value);
			pintarLlegada();
		});
	}
	if ($('#cartClear')) {
		$('#cartClear').addEventListener('click', function () {
			reserva = [];
			guardar(CLAVE, reserva);
			pintarCarrito();
		});
	}
	d.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { cerrarCarrito(); }
	});

	if ($('#cartWa')) {
		$('#cartWa').addEventListener('click', function () {
			if (!reserva.length) { return; }
			var comuna = ($('#cartComuna') && $('#cartComuna').value) || '';
			var total = reserva.reduce(function (s, e) { return s + (parseInt(e.p, 10) || 0); }, 0);
			var txt = 'Hola, quiero agendar a domicilio:\n';
			reserva.forEach(function (e) { txt += '• ' + e.n + ' — ' + pesos(e.p) + '\n'; });
			txt += 'Total de referencia: ' + pesos(total) + '\n';
			if (comuna) { txt += 'Comuna: ' + comuna + '\n'; }
			txt += '¿Qué horas tienen disponibles?';
			window.open('https://wa.me/' + cart.dataset.fono + '?text=' + encodeURIComponent(txt), '_blank', 'noopener');
		});
	}

	/* El formulario lee las selecciones locales, pero el servidor valida los IDs y precios. */
	var formulario = $('#mmReservaForm');
	if (formulario) {
		var entradas = $$('input[name="examenes[]"]', formulario);
		var directo = parseInt(formulario.dataset.examen || '0', 10);
		if (!directo && !formulario.querySelector('input[name="examenes[]"]:checked')) {
			var elegidos = reserva.map(function (e) { return parseInt(e.id, 10); });
			entradas.forEach(function (e) { e.checked = elegidos.indexOf(parseInt(e.value, 10)) >= 0; });
		}
		function totalReserva() {
			var monto = entradas.reduce(function (s, e) { return s + (e.checked ? parseInt(e.dataset.precio, 10) || 0 : 0); }, 0);
			$('#reservaTotal').textContent = pesos(monto);
		}
		entradas.forEach(function (e) { e.addEventListener('change', totalReserva); });
		formulario.addEventListener('submit', function (e) {
			if (!entradas.some(function (x) { return x.checked; })) { e.preventDefault(); avisar('Selecciona al menos un examen'); if (entradas[0]) { entradas[0].focus(); } }
		});
		totalReserva();
	}

	if ($('#cartImp')) {
		$('#cartImp').addEventListener('click', function () {
			if (!reserva.length || !window.mmImprimirPresupuesto) { return; }
			var comuna = ($('#cartComuna') && $('#cartComuna').value) || '';
			window.mmImprimirPresupuesto(reserva, comuna);
		});
	}

	pintarContador();

	/* ---------- datos del catálogo ---------- */
	var datos = window.mmDatos();
	var porSlug = {};
	datos.ex.forEach(function (e) { porSlug[e.s] = e; });
	var comunaPorSlug = {};
	datos.co.forEach(function (c) { comunaPorSlug[c.s] = c; });

	/* ---------- cotizador de la portada ---------- */
	(function () {
		var cot = $('#cot');
		if (!cot || !datos.ex.length) { return; }

		var selEx = $('#cotEx');
		var selCo = $('#cotCo');
		var pasos = $$('.cot-pasos i', cot);
		var textos = { a: 'todos los días', b: 'varios días por semana', c: 'a pedido' };

		function pintar() {
			var e = porSlug[selEx.value] || datos.ex[0];
			if (!e) { return; }

			$('#cotUse').setAttribute('href', '#' + e.i);
			$('#cotNom').textContent = e.n;
			$('#cotSub').textContent = e.l;
			$('#cotP').textContent = e.p ? pesos(e.p) : 'Consultar';
			$('#cotDur').innerHTML = e.m ? '<b>' + e.m + ' min</b> de examen' : 'Duración a confirmar';
			$('#cotPrep').textContent = e.ae || 'Sin ayuno';
			$('#cotVer').href = e.u;

			var c = comunaPorSlug[selCo.value];
			if (c) {
				$('#cotLleg').innerHTML = 'Llegamos a <b>' + c.n + '</b> en ~' + c.t + ' min · ' + (textos[c.z] || textos.c);
			} else {
				$('#cotLleg').textContent = 'Elige tu comuna y te decimos en cuánto llegamos';
			}

			var n = 1 + (selEx.value ? 1 : 0) + (selCo.value ? 1 : 0);
			pasos.forEach(function (p, i) { p.classList.toggle('on', i < n); });

			$('#cotAdd').dataset.mmAdd = JSON.stringify({
				id: e.id, n: e.n, p: e.p, m: e.m, u: e.u, i: e.i, c: e.c, l: e.l
			});
		}

		selEx.addEventListener('change', pintar);
		selCo.addEventListener('change', function () {
			guardar(CLAVE_COMUNA, comunaPorSlug[this.value] ? comunaPorSlug[this.value].n : '');
			pintar();
		});

		var comunaGuardada = leer(CLAVE_COMUNA, '');
		if (comunaGuardada) {
			datos.co.forEach(function (c) { if (c.n === comunaGuardada) { selCo.value = c.s; } });
		}
		pintar();
	})();

	/* ---------- filtros del catálogo ---------- */
	(function () {
		var grilla = $('#grilla');
		if (!grilla || !$('#filtros')) { return; }

		var tarjetas = $$('.prod', grilla);
		var orden = tarjetas.slice();
		var q = $('#flQ');
		var o = $('#flO');
		var cuenta = $('#flCuenta');
		var vacio = $('#flVacio');
		var linea = '';

		function normalizar(t) {
			return t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
		}

		function aplicar() {
			var texto = normalizar(q.value.trim());
			var visibles = 0;

			tarjetas.forEach(function (c) {
				var okLinea = !linea || c.dataset.linea === linea;
				var okTexto = !texto || c.dataset.buscar.indexOf(texto) !== -1;
				var ver = okLinea && okTexto;
				c.hidden = !ver;
				if (ver) { visibles++; }
			});

			var lista = orden.slice();
			var modo = o.value;
			if (modo === 'pa') { lista.sort(function (a, b) { return (+a.dataset.precio || 9e9) - (+b.dataset.precio || 9e9); }); }
			if (modo === 'pd') { lista.sort(function (a, b) { return (+b.dataset.precio || 0) - (+a.dataset.precio || 0); }); }
			if (modo === 'md') { lista.sort(function (a, b) { return (+a.dataset.min || 999) - (+b.dataset.min || 999); }); }
			if (modo === 'az') { lista.sort(function (a, b) { return a.dataset.buscar.localeCompare(b.dataset.buscar); }); }
			lista.forEach(function (c) { grilla.appendChild(c); });

			cuenta.innerHTML = visibles === tarjetas.length
				? '<b>' + tarjetas.length + '</b> exámenes disponibles a domicilio'
				: '<b>' + visibles + '</b> de ' + tarjetas.length + ' exámenes';
			vacio.hidden = visibles > 0;
			grilla.hidden = visibles === 0;
			$('#flX').hidden = q.value === '';
		}

		q.addEventListener('input', aplicar);
		o.addEventListener('change', aplicar);
		$('#flX').addEventListener('click', function () { q.value = ''; aplicar(); q.focus(); });
		$$('.chip', $('#filtros')).forEach(function (chip) {
			chip.addEventListener('click', function () {
				$$('.chip').forEach(function (c) { c.classList.remove('on'); });
				chip.classList.add('on');
				linea = chip.dataset.linea;
				aplicar();
			});
		});

		aplicar();
	})();

	/* ---------- asistente ---------- */
	(function () {
		var w = $('#chatw');
		var b = $('#chatBtn');
		if (!w || !b) { return; }

		var msgs = $('#chatMsgs');
		var chips = $('#chatChips');
		var form = $('#chatF');
		var input = $('#chatIn');
		var conIA = w.dataset.ia === '1';
		var wa = w.dataset.wa;
		var historial = [];
		var esperando = false;
		var arrancado = false;

		var guiadas = [
			{
				q: '¿Cuánto cuesta?',
				a: 'Cada examen tiene su precio publicado en su página, e incluye la visita, el examen y el informe. No cobramos el traslado.',
				chips: ['Ver el catálogo', 'Quiero agendar']
			},
			{
				q: '¿Atienden en mi comuna?',
				a: 'Cubrimos toda la Región Metropolitana. En el mapa de cobertura puedes ver el tiempo de llegada estimado a tu comuna.',
				chips: ['Ver cobertura', 'Quiero agendar']
			},
			{
				q: '¿Cuándo llega el informe?',
				a: 'En menos de 24 horas, en PDF, firmado por médico radiólogo. Las imágenes se envían al médico informante el mismo día del examen.',
				chips: ['Quiero agendar']
			},
			{
				q: '¿Necesito orden médica?',
				a: 'Para la mayoría de los exámenes sí, porque es lo que indica qué hay que estudiar. Si no la tienes, escríbenos y te orientamos.',
				chips: ['Quiero agendar']
			},
			{ q: 'Quiero agendar', a: '', wa: true }
		];

		function burbuja(texto, clase) {
			var el = d.createElement('div');
			el.className = 'msg ' + clase;
			el.textContent = texto;
			msgs.appendChild(el);
			msgs.scrollTop = msgs.scrollHeight;
			return el;
		}

		function tarjetaExamen(slug) {
			var e = porSlug[slug];
			if (!e) { return; }
			var el = d.createElement('div');
			el.className = 'chat-card';
			el.style.setProperty('--ac', e.c);
			el.innerHTML = '<b>' + e.n + '</b>' +
				'<span class="cc-p">' + (e.p ? pesos(e.p) : 'Consultar') + '</span>' +
				'<span class="cc-m">' + (e.m ? e.m + ' min · ' : '') + e.l + ' · a domicilio</span>';

			var btn = d.createElement('button');
			btn.type = 'button';
			btn.className = 'btn btn-p mm-add';
			btn.dataset.mmAdd = JSON.stringify({ id: e.id, n: e.n, p: e.p, m: e.m, u: e.u, i: e.i, c: e.c, l: e.l });
			btn.innerHTML = '<span>Agregar a mi reserva</span>';
			el.appendChild(btn);

			var a = d.createElement('a');
			a.className = 'btn btn-o';
			a.href = e.u;
			a.textContent = 'Ver la página del examen';
			el.appendChild(a);

			msgs.appendChild(el);
			msgs.scrollTop = msgs.scrollHeight;
		}

		function botonWa() {
			var el = d.createElement('div');
			el.className = 'chat-card';
			el.innerHTML = '<b>Confirmemos por WhatsApp</b><span class="cc-m">Te respondemos dentro del horario de atención.</span>';
			var a = d.createElement('a');
			a.className = 'btn btn-g';
			a.href = wa;
			a.target = '_blank';
			a.rel = 'noopener';
			a.textContent = 'Escribir por WhatsApp';
			el.appendChild(a);
			msgs.appendChild(el);
			msgs.scrollTop = msgs.scrollHeight;
		}

		function pintarChips(lista) {
			chips.innerHTML = '';
			lista.forEach(function (t) {
				var c = d.createElement('button');
				c.type = 'button';
				c.textContent = t;
				c.addEventListener('click', function () { responder(t); });
				chips.appendChild(c);
			});
		}

		function acciones(texto) {
			var encontrado = [];
			var limpio = texto.replace(/\[\[([A-ZÁÉÍÓÚ]+)(?::([^\]]+))?\]\]/g, function (m, tipo, valor) {
				encontrado.push({ tipo: tipo, valor: valor || '' });
				return '';
			}).trim();
			return { texto: limpio, acciones: encontrado };
		}

		function ejecutar(lista) {
			lista.forEach(function (a) {
				if (a.tipo === 'EXAMEN') { tarjetaExamen(a.valor.trim()); }
				if (a.tipo === 'AGREGAR') {
					var e = porSlug[a.valor.trim()];
					if (e) { agregar({ id: e.id, n: e.n, p: e.p, m: e.m, u: e.u, i: e.i, c: e.c, l: e.l }); }
					tarjetaExamen(a.valor.trim());
				}
				if (a.tipo === 'COMUNA') {
					var c = comunaPorSlug[a.valor.trim()];
					if (c) { guardar(CLAVE_COMUNA, c.n); }
				}
				if (a.tipo === 'WHATSAPP') { botonWa(); }
			});
		}

		function responder(texto) {
			if (esperando) { return; }
			burbuja(texto, 'yo');
			historial.push({ rol: 'yo', texto: texto });
			chips.innerHTML = '';

			if (!conIA) {
				var m = guiadas.filter(function (g) { return g.q === texto; })[0];
				setTimeout(function () {
					if (m && m.wa) {
						burbuja('Perfecto. Sigamos por WhatsApp y coordinamos el día y la hora.', 'bot');
						botonWa();
					} else if (m) {
						burbuja(m.a, 'bot');
						pintarChips(m.chips || []);
					} else {
						burbuja('Esa la responde mejor una persona del equipo. ¿Seguimos por WhatsApp?', 'bot');
						botonWa();
					}
				}, 420);
				return;
			}

			esperando = true;
			var esc = d.createElement('div');
			esc.className = 'msg bot esc';
			esc.innerHTML = '<i></i><i></i><i></i>';
			msgs.appendChild(esc);
			msgs.scrollTop = msgs.scrollHeight;

			fetch(w.dataset.url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ mensajes: historial })
			}).then(function (r) { return r.json(); }).then(function (r) {
				esc.remove();
				esperando = false;
				var salida = acciones(r.texto || '');
				if (salida.texto) {
					burbuja(salida.texto, 'bot');
					historial.push({ rol: 'bot', texto: salida.texto });
				}
				ejecutar(salida.acciones);
			}).catch(function () {
				esc.remove();
				esperando = false;
				burbuja('Se me cortó la conexión. ¿Seguimos por WhatsApp?', 'bot');
				botonWa();
			});
		}

		function arrancar() {
			if (arrancado) { return; }
			arrancado = true;
			burbuja(w.dataset.saludo || 'Hola, soy el asistente de MedMóvil. Dime qué examen necesitas y en qué comuna vives.', 'bot');
			pintarChips(guiadas.map(function (g) { return g.q; }));
		}

		function abrir() {
			w.hidden = false;
			b.setAttribute('aria-expanded', 'true');
			b.hidden = true;
			arrancar();
			setTimeout(function () { input.focus(); }, 120);
		}
		function cerrar() {
			w.hidden = true;
			b.hidden = false;
			b.setAttribute('aria-expanded', 'false');
		}

		// El botón se encoge solo para no tapar el contenido de la página.
		var dock = b.closest('.dock');
		if (dock) {
			setTimeout(function () { dock.classList.add('mini'); }, 6000);
			window.addEventListener('scroll', function () {
				if (window.scrollY > 400) { dock.classList.add('mini'); }
			}, { passive: true });
		}

		b.addEventListener('click', abrir);
		$('#chatX').addEventListener('click', cerrar);
		d.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !w.hidden) { cerrar(); }
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var t = input.value.trim();
			if (!t) { return; }
			input.value = '';
			responder(t);
		});

		if (!conIA) {
			var links = { 'Ver el catálogo': w.dataset.catalogo || '', 'Ver cobertura': w.dataset.cobertura || '' };
			chips.addEventListener('click', function (e) {
				var t = e.target.textContent;
				if (links[t]) { window.location.href = links[t]; }
			}, true);
		}
	})();
})();

/* ============================================================
   Cotizador y arancel
   ============================================================ */
(function () {
	'use strict';

	var d = document;
	var $ = function (s, c) { return (c || d).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };

	function pesos(n) {
		n = parseInt(n, 10) || 0;
		return '$' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
	}
	function normalizar(t) {
		return t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
	}

	/* ---------- cotizador ---------- */
	(function () {
		var lista = $('#czLista');
		if (!lista) { return; }

		var chks = $$('.cz-chk', lista);
		var items = $$('.cz-it', lista);
		var grupos = $$('.cz-grupo', lista);
		var q = $('#czQ');
		var sel = $('#czSel');
		var vacioSel = $('#czVacioSel');
		var co = $('#czCo');

		function elegidos() {
			return chks.filter(function (c) { return c.checked; });
		}

		function pintar() {
			var marcados = elegidos();
			var total = marcados.reduce(function (s, c) { return s + (parseInt(c.dataset.p, 10) || 0); }, 0);

			$('#czN').textContent = marcados.length;
			$('#czT').textContent = pesos(total);
			$('#czTotal').textContent = pesos(total);

			sel.innerHTML = marcados.map(function (c) {
				return '<li><b>' + c.dataset.n + '</b>' +
					'<span>' + (c.dataset.p > 0 ? pesos(c.dataset.p) : 'Consultar') +
					'<button type="button" data-q="' + c.id + '" aria-label="Quitar ' + c.dataset.n + '">&times;</button></span></li>';
			}).join('');

			vacioSel.hidden = marcados.length > 0;
			$('#czWa').disabled = marcados.length === 0;
			$('#czImp').disabled = marcados.length === 0;
			$('#czClear').hidden = marcados.length === 0;
		}

		function filtrar() {
			var texto = normalizar(q.value.trim());
			var visibles = 0;

			items.forEach(function (it) {
				var ver = !texto || it.dataset.buscar.indexOf(texto) !== -1;
				it.hidden = !ver;
				if (ver) { visibles++; }
			});
			grupos.forEach(function (g) {
				g.hidden = $$('.cz-it:not([hidden])', g).length === 0;
			});

			$('#czCuenta').innerHTML = texto
				? '<b>' + visibles + '</b> de ' + items.length + ' exámenes'
				: '<b>' + items.length + '</b> exámenes disponibles a domicilio';
			$('#czVacio').hidden = visibles > 0;
			$('#czX').hidden = q.value === '';
		}

		chks.forEach(function (c) { c.addEventListener('change', pintar); });
		sel.addEventListener('click', function (e) {
			var b = e.target.closest('button');
			if (!b) { return; }
			var c = d.getElementById(b.dataset.q);
			if (c) { c.checked = false; pintar(); }
		});
		$('#czClear').addEventListener('click', function () {
			chks.forEach(function (c) { c.checked = false; });
			pintar();
		});
		q.addEventListener('input', filtrar);
		$('#czX').addEventListener('click', function () { q.value = ''; filtrar(); q.focus(); });

		// La comuna se comparte con el resto del sitio.
		try {
			var guardada = JSON.parse(localStorage.getItem('mm_comuna_v1') || '""');
			if (guardada) { co.value = guardada; }
		} catch (e) { /* modo privado */ }
		co.addEventListener('change', function () {
			try { localStorage.setItem('mm_comuna_v1', JSON.stringify(co.value)); } catch (e) { /* nada */ }
		});

		$('#czWa').addEventListener('click', function () {
			var marcados = elegidos();
			if (!marcados.length) { return; }
			var total = marcados.reduce(function (s, c) { return s + (parseInt(c.dataset.p, 10) || 0); }, 0);
			var txt = 'Hola, quiero cotizar estos exámenes a domicilio:\n';
			marcados.forEach(function (c) { txt += '• ' + c.dataset.n + ' — ' + pesos(c.dataset.p) + '\n'; });
			txt += 'Total de referencia: ' + pesos(total) + '\n';
			if (co.value) { txt += 'Comuna: ' + co.value + '\n'; }
			txt += '¿Qué horas tienen disponibles?';

			var cart = $('#cart');
			var fono = cart ? cart.dataset.fono : '';
			window.open('https://wa.me/' + fono + '?text=' + encodeURIComponent(txt), '_blank', 'noopener');
		});

		$('#czImp').addEventListener('click', function () {
			var marcados = elegidos();
			if (!marcados.length) { return; }
			window.mmImprimirPresupuesto(marcados.map(function (c) {
				return { n: c.dataset.n, m: c.dataset.m, p: c.dataset.p };
			}), co.value);
		});

		pintar();
		filtrar();
	})();

	/* ---------- hoja del presupuesto ----------
	   Vive en el pie del sitio, fuera de <main>, porque el CSS de impresión
	   oculta a todos los hijos directos de <body>. La llenan el cotizador y el
	   cajón del carrito. */
	window.mmImprimirPresupuesto = function (items, comuna) {
		var hoja = $('#czPrint');
		if (!hoja || !items || !items.length) { return; }

		var total = items.reduce(function (s, e) { return s + (parseInt(e.p, 10) || 0); }, 0);

		$('#czPrCuerpo').innerHTML = items.map(function (e) {
			return '<tr><td>' + e.n + '</td><td>' + (e.m ? e.m + ' min' : '—') +
				'</td><td class="der">' + (parseInt(e.p, 10) > 0 ? pesos(e.p) : 'Consultar') + '</td></tr>';
		}).join('');

		$('#czPrTotal').textContent = pesos(total);
		$('#czPrFecha').textContent = new Date().toLocaleDateString('es-CL', {
			day: 'numeric', month: 'long', year: 'numeric'
		});
		$('#czPrComuna').textContent = comuna ? 'Comuna de atención: ' + comuna : '';

		hoja.hidden = false;
		window.print();
		setTimeout(function () { hoja.hidden = true; }, 500);
	};

	/* ---------- arancel ---------- */
	(function () {
		var tabla = $('#arTabla');
		if (!tabla) { return; }

		var filas = $$('tbody tr', tabla);
		var q = $('#arQ');
		var l = $('#arL');

		function aplicar() {
			var texto = normalizar(q.value.trim());
			var linea = l.value;
			var visibles = 0;

			filas.forEach(function (f) {
				var ver = (!linea || f.dataset.linea === linea) &&
					(!texto || f.dataset.buscar.indexOf(texto) !== -1);
				f.hidden = !ver;
				if (ver) { visibles++; }
			});

			$('#arCuenta').innerHTML = (visibles === filas.length)
				? '<b>' + filas.length + '</b> prestaciones'
				: '<b>' + visibles + '</b> de ' + filas.length + ' prestaciones';
			$('#arVacio').hidden = visibles > 0;
			tabla.parentNode.hidden = visibles === 0;
			$('#arX').hidden = q.value === '';
		}

		q.addEventListener('input', aplicar);
		l.addEventListener('change', aplicar);
		$('#arX').addEventListener('click', function () { q.value = ''; aplicar(); q.focus(); });
		aplicar();
	})();
})();

/* ============================================================
   Buscador de la cabecera y carrusel de categorías
   ============================================================ */
(function () {
	'use strict';

	var d = document;
	var $ = function (s, c) { return (c || d).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };

	function pesos(n) {
		n = parseInt(n, 10) || 0;
		if (!n) { return 'Consultar'; }
		return '$' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
	}
	function normalizar(t) {
		return String(t).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
	}
	function escapar(t) {
		return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	}
	function resaltar(texto, consulta) {
		if (!consulta) { return escapar(texto); }
		var i = normalizar(texto).indexOf(consulta);
		if (i === -1) { return escapar(texto); }
		return escapar(texto.slice(0, i)) + '<mark>' + escapar(texto.slice(i, i + consulta.length)) +
			'</mark>' + escapar(texto.slice(i + consulta.length));
	}

	/* ---------- buscador con sugerencias ---------- */
	(function () {
		var form = $('#hsearch');
		var input = $('#hq');
		var caja = $('#hsug');
		if (!form || !input || !caja) { return; }

		var datos = window.mmDatos();
		if (!datos.ex.length) { return; }

		// Índice de búsqueda: nombre, línea, código y resumen de cada examen.
		var indice = datos.ex.map(function (e) {
			return {
				e: e,
				t: normalizar(e.n + ' ' + e.l + ' ' + (e.cod || '') + ' ' + (e.s || '').replace(/-/g, ' '))
			};
		});

		var catalogo = form.getAttribute('action') || '/examenes/';
		var sel = -1;
		var visibles = [];

		function cerrar() {
			caja.hidden = true;
			input.setAttribute('aria-expanded', 'false');
			sel = -1;
		}

		function pintar(consulta) {
			var q = normalizar(consulta.trim());
			if (q.length < 2) { cerrar(); return; }

			visibles = indice.filter(function (x) { return x.t.indexOf(q) !== -1; })
				.sort(function (a, b) { return a.t.indexOf(q) - b.t.indexOf(q); })
				.slice(0, 7)
				.map(function (x) { return x.e; });

			if (!visibles.length) {
				caja.innerHTML = '<p class="hsug-nada">No encontramos ese examen. ' +
					'<a href="' + catalogo + '">Mira el catálogo completo</a> o escríbenos.</p>';
				caja.hidden = false;
				input.setAttribute('aria-expanded', 'true');
				return;
			}

			caja.innerHTML = '<p class="hsug-h">Exámenes</p>' + visibles.map(function (e, i) {
				return '<a class="hsug-i" role="option" href="' + e.u + '" style="--ac:' + e.c + '" data-i="' + i + '">' +
					'<span class="hsug-ic"><svg class="ilo" aria-hidden="true"><use href="#' + e.i + '"/></svg></span>' +
					'<span><span class="hsug-n">' + resaltar(e.n, q) + '</span>' +
					'<span class="hsug-m">' + escapar(e.l) + (e.m ? ' · ' + e.m + ' min' : '') +
					' · ' + escapar(e.ae || '') + '</span></span>' +
					'<span class="hsug-p">' + pesos(e.p) + '</span></a>';
			}).join('') +
			'<a class="hsug-todo" href="' + catalogo + '">Ver los ' + datos.ex.length + ' exámenes del catálogo</a>';

			caja.hidden = false;
			input.setAttribute('aria-expanded', 'true');
			sel = -1;
		}

		function marcar() {
			var items = $$('.hsug-i', caja);
			items.forEach(function (x, i) { x.classList.toggle('sel', i === sel); });
			if (items[sel]) { items[sel].scrollIntoView({ block: 'nearest' }); }
		}

		input.addEventListener('input', function () { pintar(input.value); });
		input.addEventListener('focus', function () { if (input.value.trim().length > 1) { pintar(input.value); } });

		input.addEventListener('keydown', function (e) {
			var items = $$('.hsug-i', caja);
			if (e.key === 'ArrowDown' && items.length) {
				e.preventDefault(); sel = (sel + 1) % items.length; marcar();
			} else if (e.key === 'ArrowUp' && items.length) {
				e.preventDefault(); sel = (sel - 1 + items.length) % items.length; marcar();
			} else if (e.key === 'Escape') {
				cerrar();
			} else if (e.key === 'Enter' && sel >= 0 && items[sel]) {
				e.preventDefault(); window.location.href = items[sel].href;
			}
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			// Sin sugerencia elegida, mandamos la búsqueda al catálogo.
			var q = input.value.trim();
			if (visibles.length === 1 && q.length > 2) {
				window.location.href = visibles[0].u;
				return;
			}
			window.location.href = catalogo + (q ? '?q=' + encodeURIComponent(q) : '');
		});

		d.addEventListener('click', function (e) {
			if (!form.contains(e.target)) { cerrar(); }
		});
	})();

	/* ---------- el catálogo recibe la búsqueda de la cabecera ---------- */
	(function () {
		var campo = $('#flQ');
		if (!campo) { return; }
		var q = new URLSearchParams(window.location.search).get('q');
		if (!q) { return; }
		campo.value = q;
		campo.dispatchEvent(new Event('input'));
		var grilla = $('#grilla');
		if (grilla) { grilla.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
	})();

	/* ---------- carrusel de categorías ---------- */
	(function () {
		var rail = $('#rail');
		var nav = $('#railNav');
		if (!rail || !nav) { return; }

		var prev = $('.prev', nav);
		var next = $('.next', nav);

		function paso() {
			var i = $('.rail-i', rail);
			return i ? (i.offsetWidth + 14) * 2 : 320;
		}
		function estado() {
			prev.disabled = rail.scrollLeft < 8;
			next.disabled = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 8;
		}

		prev.addEventListener('click', function () { rail.scrollLeft -= paso(); });
		next.addEventListener('click', function () { rail.scrollLeft += paso(); });
		rail.addEventListener('scroll', estado, { passive: true });
		window.addEventListener('resize', estado);
		estado();
	})();
})();
