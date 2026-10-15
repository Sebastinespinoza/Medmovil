/* Filtros locales sobre prestaciones publicadas; sin copiar precios ni datos. */
(() => {
  'use strict';
  const grid = document.querySelector('.shop-grid');
  if (!grid) return;
  const cards = [...grid.querySelectorAll('.shop-product')];
  const search = document.querySelector('#shop-search');
  const sort = document.querySelector('#shop-sort');
  const previous = document.querySelector('#shop-prev');
  const next = document.querySelector('#shop-next');
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
  function arrows() {
    previous.disabled = grid.scrollLeft <= 2;
    next.disabled = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 2;
  }
  previous.addEventListener('click', () => grid.scrollBy({left: -grid.clientWidth, behavior: motion}));
  next.addEventListener('click', () => grid.scrollBy({left: grid.clientWidth, behavior: motion}));
  grid.addEventListener('scroll', arrows, {passive:true});
  window.addEventListener('resize', arrows);
  document.querySelector('.shop-arrows').hidden = false;
  let category = 'all';
  const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
  function update() {
    let count = 0;
    const query = normalize(search.value.trim());
    const ordered = [...cards];
    if (sort.value === 'asc' || sort.value === 'desc') {
      ordered.sort((a, b) => {
        const x = Number(a.dataset.price), y = Number(b.dataset.price);
        if (!x || !y) return !x && !y ? 0 : !x ? 1 : -1;
        return sort.value === 'asc' ? x - y : y - x;
      });
    } else if (sort.value === 'name') ordered.sort((a,b) => a.dataset.search.localeCompare(b.dataset.search, 'es'));
    ordered.forEach(card => {
      card.hidden = !(category === 'all' || card.dataset.category.split(' ').includes(category)) || !normalize(card.dataset.search).includes(query);
      if (!card.hidden) count++;
      grid.appendChild(card);
    });
    document.querySelector('#shop-count').textContent = count + (count === 1 ? ' prestación' : ' prestaciones');
    document.querySelector('#shop-empty').hidden = count !== 0;
    grid.scrollLeft = 0;
    arrows();
  }
  document.querySelector('.shop-toolbar').hidden = false;
  document.querySelectorAll('[data-filter]').forEach(link => link.addEventListener('click', event => {
    event.preventDefault(); category = link.dataset.filter;
    document.querySelectorAll('[data-filter]').forEach(item => {
      item.classList.toggle('active', item === link);
      if (item === link) item.setAttribute('aria-current', 'true'); else item.removeAttribute('aria-current');
    });
    update();
  }));
  search.addEventListener('input', update); sort.addEventListener('change', update);
  arrows();
})();
