/* Interacciones del workspace. Sin almacenar datos clínicos en el navegador. */
(function () {
 'use strict';
 var root = document.querySelector('.pn-pulse');
 if (!root) return;
 var side = root.querySelector('#pnSide'), burger = root.querySelector('#pnBurger');
 var pin = root.querySelector('#mpPin'), bg = root.querySelector('#pnBg');
 var mobile = window.matchMedia('(max-width:780px)');
 var account = root.querySelector('.mp-account'), oldOverflow = '';
 var main = root.querySelector('.pn-main');
 if (!mobile.matches && root.classList.contains('is-pinned')) {pin.setAttribute('aria-expanded','true');pin.setAttribute('aria-label','Contraer menú');pin.title='Contraer menú';}
 function setMobile(open) {
  if (open && !side.classList.contains('open')) oldOverflow = document.body.style.overflow;
  side.classList.toggle('open', open); bg.hidden = !open;
  burger.setAttribute('aria-expanded', String(open));
  pin.setAttribute('aria-expanded', String(open || root.classList.contains('is-pinned')));
  pin.setAttribute('aria-label', open ? 'Cerrar menú' : 'Expandir menú');
  document.body.style.overflow = open ? 'hidden' : oldOverflow;
  main.inert = open;
  if (open) pin.focus();
  else if (mobile.matches) burger.focus();
 }
 burger.addEventListener('click', function () { setMobile(true); });
 bg.addEventListener('click', function () { setMobile(false); });
 pin.addEventListener('click', function () {
  if (mobile.matches) { setMobile(false); return; }
  var pinned = root.classList.toggle('is-pinned');
  pin.setAttribute('aria-expanded', String(pinned));
  pin.setAttribute('aria-label', pinned ? 'Contraer menú' : 'Expandir menú');
  pin.title = pinned ? 'Contraer menú' : 'Expandir menú';
 });
 mobile.addEventListener('change', function () { setMobile(false); });
 document.addEventListener('keydown', function (event) {
  if (event.key === 'Escape') {
   if (side.classList.contains('open')) setMobile(false);
   if (account && account.open) { account.open = false; account.querySelector('summary').focus(); }
  }
  if (event.key === 'Tab' && mobile.matches && side.classList.contains('open')) {
   var links = Array.from(side.querySelectorAll('a[href],button'));
   var first = links[0], last = links[links.length - 1];
   if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
   else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  }
 });
 document.addEventListener('click', function (event) { if (account && !account.contains(event.target)) account.open = false; });
 root.querySelector('#mpRefresh').addEventListener('click', function () {
  // Una recarga GET evita reenviar formularios si hay errores de validación.
  window.location.assign(window.location.href);
 });
 var active = root.querySelector('.mp-tabs [aria-current]');
 if (active) active.parentNode.scrollLeft = Math.max(0, active.offsetLeft - active.parentNode.offsetLeft - 20);

 var planning = root.querySelector('#mpAvailability');
 if (planning) {
  var tabs = Array.from(root.querySelectorAll('[data-availability-tab]'));
  var panels = Array.from(planning.querySelectorAll('[data-availability-panel]'));
  function showPanel(id) {
   if (!panels.some(function(p){return p.dataset.availabilityPanel === id;})) id = 'semana';
   panels.forEach(function(p){p.hidden = p.dataset.availabilityPanel !== id;});
   tabs.forEach(function(a){if(a.dataset.availabilityTab === id)a.setAttribute('aria-current','location');else a.removeAttribute('aria-current');});
  }
  tabs.forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();var id=a.dataset.availabilityTab;showPanel(id);history.replaceState(null,'','#mp-'+id);});});
  showPanel(location.hash.replace('#mp-',''));
  window.addEventListener('hashchange',function(){showPanel(location.hash.replace('#mp-',''));});
  planning.addEventListener('invalid',function(e){var panel=e.target.closest('[data-availability-panel]');if(panel)showPanel(panel.dataset.availabilityPanel);},true);
  var grid=planning.querySelector('.mp-week-grid'), day=planning.querySelector('#mpCalendarDay');
  // Sin JavaScript la semana completa sigue disponible con desplazamiento.
  grid.classList.add('is-day-view'); day.parentElement.classList.add('is-ready');
  day.addEventListener('change',function(){grid.dataset.mobileDay=day.value;});
  function updatePlanning() {
   var count=planning.querySelectorAll('.grilla input:checked').length;
   planning.querySelector('.mp-selection-count').textContent=count+' horas de inicio disponibles';
   var duration=Number(planning.querySelector('#mpDuration').value), travel=Number(planning.querySelector('#mpTravel').value);
   planning.querySelector('.mp-time-total').textContent=(duration+travel)+' min de referencia por visita: '+duration+' de atención + '+travel+' de traslado.';
   var coverage=Array.from(planning.querySelectorAll('[name="coverage[]"]:checked')).map(function(c){return c.value;});
   planning.querySelectorAll('[data-zone-select]').forEach(function(select){
    Array.from(select.options).forEach(function(option){option.disabled=option.value!=='0'&&!coverage.includes(option.value);});
    if(select.selectedOptions[0] && select.selectedOptions[0].disabled) select.value='0';
   });
  }
  function dirty(){planning.querySelector('#mpSaveState').textContent='Cambios sin guardar';updatePlanning();}
  planning.addEventListener('change',dirty);planning.addEventListener('input',dirty);
  ['grillaHabil','grillaLimpiar'].forEach(function(id){planning.querySelector('#'+id).addEventListener('click',dirty);});
  var exceptions=planning.querySelector('#mpExceptions'), nextException=exceptions.children.length;
  function updateExceptions(){planning.querySelector('#mpNoExceptions').hidden=exceptions.children.length>0;planning.querySelector('#mpAddException').disabled=exceptions.children.length>=50;}
  planning.querySelector('#mpAddException').addEventListener('click',function(){
   if(exceptions.children.length>=50)return;
   var clone=planning.querySelector('#mpExceptionTemplate').content.cloneNode(true);
   clone.querySelectorAll('input').forEach(function(input){input.name=input.name.replace('[0]','['+nextException+']');});nextException++;
   exceptions.append(clone);updateExceptions();dirty();exceptions.lastElementChild.querySelector('input').focus();
  });
  exceptions.addEventListener('click',function(e){var button=e.target.closest('[data-remove-exception]');if(button){button.closest('.mp-exception').remove();updateExceptions();dirty();planning.querySelector('#mpAddException').focus();}});
  updatePlanning();updateExceptions();
 }

 // Filtra únicamente los registros ya autorizados y renderizados por MedMóvil.
 var section = root.dataset.section;
 if (['horas', 'agenda', 'pacientes', 'informes', 'pagos', 'ingresos'].includes(section)) {
  var content = root.querySelector('.pn-body');
  var items = Array.from(content.querySelectorAll('.ptabla tbody tr,.vcard,.icard,.ucard'));
  if (items.length) {
   var bar = document.createElement('div'); bar.className = 'mp-filter';
   var label = document.createElement('label'); label.htmlFor = 'mpSearch';
   var sr = document.createElement('span'); sr.className = 'sr'; sr.textContent = 'Buscar en esta vista';
   var input = document.createElement('input'); input.type = 'search'; input.id = 'mpSearch'; input.placeholder = 'Buscar en esta vista…'; input.autocomplete = 'off';
   var clear = document.createElement('button'); clear.type = 'button'; clear.className = 'pbtn'; clear.textContent = 'Limpiar';
   var output = document.createElement('output'); output.setAttribute('aria-live', 'polite');
   var empty = document.createElement('p'); empty.className = 'mp-no-results'; empty.textContent = 'No hay resultados para esta búsqueda.'; empty.hidden = true;
   label.append(sr, input); bar.append(label, clear, output); content.prepend(bar); bar.after(empty);
   function normalize(text) { return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase(); }
   var texts = items.map(function (item) { return normalize(item.textContent); });
   function filter() {
    var query = normalize(input.value.trim()), count = 0;
    items.forEach(function (item, i) { item.hidden = query !== '' && !texts[i].includes(query); if (!item.hidden) count++; });
    output.textContent = count + ' de ' + items.length + ' registros'; empty.hidden = count !== 0;
    content.querySelectorAll('.pn-dia').forEach(function (day) {
     var list = day.nextElementSibling;
     day.hidden = !!(list && list.classList.contains('hlist') && Array.from(list.children).every(function (child) { return child.hidden; }));
    });
   }
   input.addEventListener('input', filter); clear.addEventListener('click', function () { input.value = ''; filter(); input.focus(); }); filter();
  }
 }

 // Mismo endpoint protegido; el documento se abre dentro del espacio de trabajo.
 var dialog = root.querySelector('#mpDocument'), frame = dialog.querySelector('iframe');
 var external = root.querySelector('#mpDocumentOpen'), opener;
 root.addEventListener('click', function (event) {
  var link = event.target.closest('a[href]');
  if (!link || dialog.contains(link) || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0 || !dialog.showModal) return;
  var url = new URL(link.href, window.location.href);
  if (url.origin !== window.location.origin || url.searchParams.get('action') !== 'medmovil_archivo') return;
  event.preventDefault(); opener = link;
  external.href = url.href; frame.src = url.href; dialog.showModal();
  root.querySelector('#mpDocumentClose').focus();
 });
 root.querySelector('#mpDocumentClose').addEventListener('click', function () { dialog.close(); });
 dialog.addEventListener('close', function () { frame.removeAttribute('src'); external.removeAttribute('href'); if (opener) opener.focus(); });
})();
