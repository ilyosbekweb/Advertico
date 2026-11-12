/*
 * ADVERTICO — reklama joylari xaritasi (Leaflet).
 * Viloyat → tuman → marker → galereya va tafsilotlar → ariza.
 * Ma'lumotlar: api/locations.php (faqat nashr qilingan joylar), chegaralar: assets/geo/jizzakh-districts.geojson.
 * Koordinatalar hech qachon o'zgartirilmaydi; yaqin nuqtalar klasterlanadi.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-ad-map]');
  var configEl = document.getElementById('adm-config');
  if (!root || !configEl || !window.L) return;

  var cfg;
  try { cfg = JSON.parse(configEl.textContent); } catch (error) { return; }

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var STATUSES = ['available', 'reserved', 'occupied', 'maintenance'];

  function lang() { return document.documentElement.lang === 'ru' ? 'ru' : 'uz'; }
  function tr(pair) { return pair ? (pair[lang()] || pair.uz || '') : ''; }
  function t(key) { return tr(cfg.i18n[key]); }
  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function num(value) { return String(Math.round(value * 100) / 100).replace('.', ','); }

  var districts = {};
  cfg.districts.forEach(function (d) { districts[d.slug] = d; });

  var state = { district: null, selected: null, type: '', availability: '', query: '', locations: [], types: {}, photo: 0 };

  /* ------------------------------------------------------------------ Elementlar */
  var mapEl = $('[data-adm-map]', root);
  var detailEl = $('[data-adm-detail]', root);
  var listEl = $('[data-adm-list]', root);
  var districtListEl = $('.adm-districts', root);
  var titleEl = $('[data-adm-title]', root);
  var descEl = $('[data-adm-desc]', root);
  var hintEl = $('[data-adm-hint]', root);
  var backBtn = $('[data-adm-back]', root);
  var crumbSep = $('[data-adm-crumb-sep]', root);
  var crumbDistrict = $('[data-adm-crumb-district]', root);
  var searchInput = $('[data-adm-search]', root);
  var typeSelect = $('[data-adm-type]', root);
  var availabilitySelect = $('[data-adm-availability]', root);
  var resultsEl = document.createElement('div');
  resultsEl.className = 'adm-results';
  listEl.appendChild(resultsEl);

  /* ------------------------------------------------------------------ Xarita */
  var map = L.map(mapEl, {
    zoomControl: false,
    minZoom: 7,
    maxZoom: 19,
    zoomSnap: 0.25,
    zoomAnimation: !reduceMotion,
    fadeAnimation: !reduceMotion,
    markerZoomAnimation: !reduceMotion,
    wheelPxPerZoomLevel: 110
  }).setView([40.3, 67.9], 8);
  map.attributionControl.setPrefix(false);
  L.control.zoom({ position: 'bottomright' }).addTo(map);

  var attribution = [cfg.tiles.attribution, cfg.boundaryAttribution].filter(Boolean).join(' · ');
  var baseLayer = L.tileLayer(cfg.tiles.url, { maxZoom: 19, attribution: attribution }).addTo(map);
  if (cfg.satellite && cfg.satellite.url) {
    var satelliteLayer = L.tileLayer(cfg.satellite.url, { maxZoom: 19, attribution: [cfg.satellite.attribution, cfg.boundaryAttribution].filter(Boolean).join(' · ') });
    L.control.layers({ '<span data-uz="Xarita" data-ru="Карта">Xarita</span>': baseLayer, '<span data-uz="Sun’iy yo‘ldosh" data-ru="Спутник">Sun’iy yo‘ldosh</span>': satelliteLayer }, null, { position: 'bottomleft' }).addTo(map);
    map.on('baselayerchange', function (e) { mapEl.classList.toggle('is-satellite', e.layer === satelliteLayer); });
  }

  var areaLayers = {};
  var areaRings = {};
  var labelMarkers = {};
  var regionBounds = null;
  var districtMask = null;
  var WORLD = [[-85, -180], [-85, 180], [85, 180], [85, -180]];
  // Viloyat ko'rinishi: yuqorida breadcrumb, pastda yo'riqnoma uchun joy.
  var REGION_PADDING = { paddingTopLeft: [24, 70], paddingBottomRight: [24, 66] };

  /* Tanlangan tumandan tashqarini to'liq yopish — faqat shu tuman ko'rinadi. */
  function setDistrictMask(slug) {
    if (districtMask) { map.removeLayer(districtMask); districtMask = null; }
    if (!slug || !areaRings[slug]) return;
    districtMask = L.polygon([WORLD].concat(areaRings[slug]), { className: 'adm-mask', interactive: false, weight: 0 }).addTo(map);
    districtMask.bringToBack();
  }

  /* Viloyat rejimida xarita foni yashirin; tuman ochilganda faqat tuman ichida ko'rinadi. */
  function showTiles(visible) { mapEl.classList.toggle('show-tiles', visible); }

  /* Harakat tugagach bajariladigan amal (harakat bo'lmasa ham kafolatlangan). */
  function afterMove(callback) {
    var done = false;
    var run = function () { if (!done) { done = true; callback(); } };
    map.once('moveend', run);
    setTimeout(run, reduceMotion ? 50 : 1300);
  }
  var markers = {};
  var clusters = L.markerClusterGroup({
    showCoverageOnHover: false,
    spiderfyOnMaxZoom: true,
    maxClusterRadius: 46,
    animate: !reduceMotion,
    iconCreateFunction: function (cluster) {
      return L.divIcon({ className: 'adm-cluster', html: '<span><b>' + cluster.getChildCount() + '</b></span>', iconSize: [48, 48] });
    }
  }).addTo(map);

  function moveTo(bounds, options) {
    options = Object.assign({ padding: [36, 36] }, options || {});
    if (reduceMotion) map.fitBounds(bounds, Object.assign(options, { animate: false }));
    else map.flyToBounds(bounds, Object.assign(options, { duration: 0.75 }));
  }

  /* ------------------------------------------------------------------ Marker (3D Advertico belgisi) */
  function markerHtml(loc) {
    var mark = '<svg viewBox="0 0 236 404" aria-hidden="true" focusable="false"><use href="#adm-mark"/></svg>';
    return '<span class="adm-pin" data-st="' + esc(loc.availability) + '">' +
      '<span class="adm-pin-ring"></span><span class="adm-pin-shadow"></span>' +
      '<span class="adm-pin-body">' +
        '<span class="adm-pin-layer l3">' + mark + '</span>' +
        '<span class="adm-pin-layer l2">' + mark + '</span>' +
        '<span class="adm-pin-layer l1">' + mark + '</span>' +
        '<span class="adm-pin-face">' + mark + '</span>' +
      '</span>' +
      '<span class="adm-pin-dot"></span>' +
    '</span>';
  }

  function createMarker(loc) {
    var marker = L.marker([loc.lat, loc.lng], {
      icon: L.divIcon({ className: 'adm-marker', html: markerHtml(loc), iconSize: [44, 64], iconAnchor: [22, 60] }),
      keyboard: true,
      riseOnHover: true,
      title: tr(loc.name)
    });
    marker.on('add', function () {
      var el = marker.getElement();
      if (!el) return;
      el.setAttribute('role', 'button');
      el.setAttribute('aria-label', tr(loc.name) + ' — ' + t('st_' + loc.availability));
      el.classList.toggle('is-selected', state.selected === loc.id);
    });
    marker.on('click', function () { selectLocation(loc.id, { fromMap: true }); });
    return marker;
  }

  function markSelected() {
    Object.keys(markers).forEach(function (id) {
      var el = markers[id].getElement();
      if (el) el.classList.toggle('is-selected', Number(id) === state.selected);
    });
  }

  /* ------------------------------------------------------------------ Filtrlash */
  function matches(loc) {
    if (state.type && loc.type !== state.type) return false;
    if (state.availability && loc.availability !== state.availability) return false;
    if (state.query) {
      var hay = [loc.name.uz, loc.name.ru, loc.address.uz, loc.address.ru].join(' ').toLowerCase();
      if (hay.indexOf(state.query) === -1) return false;
    }
    return true;
  }
  function filtersActive() { return !!(state.type || state.availability || state.query); }
  function inDistrict(slug) { return state.locations.filter(function (l) { return l.district === slug; }); }
  function visibleLocations() {
    return state.locations.filter(function (l) { return (!state.district || l.district === state.district) && matches(l); });
  }
  function findLocation(id) {
    for (var i = 0; i < state.locations.length; i += 1) if (state.locations[i].id === id) return state.locations[i];
    return null;
  }

  function renderMarkers() {
    clusters.clearLayers();
    markers = {};
    if (!state.district) return;
    var layers = visibleLocations().map(function (loc) {
      var marker = createMarker(loc);
      markers[loc.id] = marker;
      return marker;
    });
    clusters.addLayers(layers);
  }

  /* ------------------------------------------------------------------ Yon panel */
  function thumbFor(loc) {
    return loc.photos.length ? loc.photos[0].thumb : '';
  }

  function itemHtml(loc, withDistrict) {
    var thumb = thumbFor(loc);
    var type = state.types[loc.type] ? tr(state.types[loc.type]) : '';
    var meta = [withDistrict && districts[loc.district] ? tr(districts[loc.district].short) : '', type].filter(Boolean).join(' · ');
    return '<button type="button" class="adm-item' + (state.selected === loc.id ? ' is-active' : '') + '" data-loc="' + loc.id + '">' +
      '<span class="adm-item-thumb">' + (thumb ? '<img src="' + esc(thumb) + '" alt="" loading="lazy">' : '<svg viewBox="0 0 236 404" aria-hidden="true"><use href="#adm-mark"/></svg>') + '</span>' +
      '<span class="adm-item-copy"><strong>' + esc(tr(loc.name)) + '</strong><small>' + esc(meta) + '</small></span>' +
      '<span class="adm-status st-' + esc(loc.availability) + '">' + esc(t('st_' + loc.availability)) + '</span>' +
    '</button>';
  }

  function renderSide() {
    var district = state.district ? districts[state.district] : null;
    titleEl.textContent = district ? tr(district.name) : t('region');
    if (district) {
      descEl.textContent = tr(district.description) || t('districtDefault');
    } else {
      descEl.textContent = '';
      descEl.appendChild(document.createTextNode(t('hint')));
    }

    // Tuman tugmalaridagi sonlar
    $$('[data-adm-count]', root).forEach(function (badge) {
      var count = inDistrict(badge.dataset.admCount).length;
      badge.textContent = count ? String(count) : '↗';
      badge.classList.toggle('has-count', count > 0);
    });
    $$('.adm-districts [data-district]', root).forEach(function (button) {
      var active = button.dataset.district === state.district;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    var showDistricts = !state.district && !filtersActive();
    districtListEl.hidden = !showDistricts;
    resultsEl.hidden = showDistricts;
    if (showDistricts) return;

    var items = visibleLocations();
    var countLine = '<p class="adm-count"><b>' + items.length + '</b> ' + esc(t('count')) + '</p>';
    if (!items.length) {
      var emptyText = state.district && !inDistrict(state.district).length ? t('empty') : t('noResults');
      resultsEl.innerHTML = countLine + '<div class="adm-empty"><svg viewBox="0 0 236 404" aria-hidden="true"><use href="#adm-mark"/></svg><p>' + esc(emptyText) + '</p></div>';
      return;
    }
    resultsEl.innerHTML = countLine + items.map(function (loc) { return itemHtml(loc, !state.district); }).join('');
  }

  resultsEl.addEventListener('click', function (event) {
    var item = event.target.closest('[data-loc]');
    if (item) selectLocation(Number(item.dataset.loc), { fromList: true });
  });
  $$('.adm-districts [data-district]', root).forEach(function (button) {
    button.addEventListener('click', function () { openDistrict(button.dataset.district); });
  });

  /* ------------------------------------------------------------------ Rejimlar */
  function updateChrome() {
    var district = state.district ? districts[state.district] : null;
    root.classList.toggle('is-district', !!district);
    backBtn.hidden = !district;
    crumbSep.hidden = !district;
    crumbDistrict.hidden = !district;
    crumbDistrict.textContent = district ? tr(district.name) : '';
    hintEl.hidden = !!district;
    Object.keys(areaLayers).forEach(function (slug) {
      var el = areaLayers[slug].getElement();
      if (el) el.classList.toggle('is-active', slug === state.district);
    });
    Object.keys(labelMarkers).forEach(function (slug) {
      var el = labelMarkers[slug].getElement();
      if (el) el.classList.toggle('is-hidden', !!state.district);
    });
  }

  function setHash() {
    var hash = state.district ? '#' + state.district + (state.selected ? '/' + state.selected : '') : '';
    var url = window.location.pathname + window.location.search + hash;
    try { window.history.replaceState(null, '', url); } catch (error) { /* ixtiyoriy */ }
  }

  function openDistrict(slug, options) {
    options = options || {};
    if (!districts[slug] || !areaLayers[slug]) return;
    var changed = state.district !== slug;
    state.district = slug;
    if (changed && !options.keepSelection) closeDetail(true);
    updateChrome();
    renderSide();
    setHash();
    if (!changed && districtMask) { renderMarkers(); return; }

    // 1) tanlangan tuman yorishadi, qolganlari so'nadi  2) kamera tumanga uchadi
    // 3) tashqarisi yopiladi va faqat tuman ichida xarita foni paydo bo'ladi  4) markerlar
    clusters.clearLayers();
    markers = {};
    setDistrictMask(null);
    showTiles(false);
    var el = areaLayers[slug].getElement();
    if (el) el.classList.add('is-selecting');
    var reveal = function () {
      if (state.district !== slug) return;
      if (el) el.classList.remove('is-selecting');
      setDistrictMask(slug);
      showTiles(true);
      renderMarkers();
    };
    if (options.skipMove) { reveal(); return; }
    afterMove(reveal);
    moveTo(areaLayers[slug].getBounds(), { padding: [14, 14] });
  }

  function openRegion() {
    state.district = null;
    closeDetail(true);
    showTiles(false);
    setDistrictMask(null);
    clusters.clearLayers();
    markers = {};
    updateChrome();
    renderSide();
    if (regionBounds) moveTo(regionBounds, Object.assign({ padding: [0, 0] }, REGION_PADDING));
    setHash();
  }

  backBtn.addEventListener('click', openRegion);
  $('[data-adm-to-region]', root).addEventListener('click', openRegion);

  /* ------------------------------------------------------------------ Joy paneli va galereya */
  function selectLocation(id, options) {
    options = options || {};
    var loc = findLocation(id);
    if (!loc) return;
    if (state.district !== loc.district) openDistrict(loc.district, { keepSelection: true, skipMove: true });
    state.selected = id;
    state.photo = 0;
    renderDetail(loc);
    markSelected();
    renderSide();
    setHash();
    var marker = markers[id];
    var focusMarker = function () {
      var target = L.latLng(loc.lat, loc.lng);
      var zoom = Math.max(map.getZoom(), 15);
      if (reduceMotion) map.setView(target, zoom, { animate: false });
      else map.flyTo(target, zoom, { duration: 0.6 });
      map.once('moveend', function () {
        var size = map.getSize();
        var mobile = window.matchMedia('(max-width: 760px)').matches;
        var offset = mobile ? [0, Math.min(size.y * 0.28, 220)] : [Math.min(detailEl.offsetWidth / 2, size.x / 4), 0];
        map.panBy(offset, { animate: !reduceMotion });
        markSelected();
      });
    };
    if (marker && clusters.hasLayer(marker)) clusters.zoomToShowLayer(marker, focusMarker);
    else focusMarker();
  }

  function closeDetail(silent) {
    state.selected = null;
    detailEl.hidden = true;
    detailEl.innerHTML = '';
    root.classList.remove('has-detail');
    markSelected();
    if (!silent) { renderSide(); setHash(); }
  }

  function dimensions(loc) {
    if (loc.width && loc.height) return num(loc.width) + ' × ' + num(loc.height) + ' m';
    if (loc.width || loc.height) return num(loc.width || loc.height) + ' m';
    return '';
  }

  function renderDetail(loc) {
    var type = state.types[loc.type] ? tr(state.types[loc.type]) : '';
    var district = districts[loc.district] ? tr(districts[loc.district].name) : '';
    var rows = [
      [t('type'), type],
      [t('district'), district],
      [t('dimensions'), dimensions(loc)],
      [t('sides'), loc.sides ? String(loc.sides) : ''],
      [t('lighting'), loc.lighting ? t('yes') : t('no')]
    ].filter(function (row) { return row[1]; });

    var gallery;
    if (loc.photos.length) {
      gallery = '<div class="adm-gallery">' +
        '<div class="adm-stage" tabindex="0" aria-roledescription="carousel" aria-label="' + esc(tr(loc.name)) + '">' +
          '<img class="adm-stage-img" alt="" decoding="async">' +
          (loc.photos.length > 1 ? '<button type="button" class="adm-nav prev" data-photo-prev aria-label="‹"><svg class="x-ico" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button><button type="button" class="adm-nav next" data-photo-next aria-label="›"><svg class="x-ico" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>' : '') +
          '<button type="button" class="adm-full" data-photo-full aria-label="Fullscreen"><svg class="x-ico" viewBox="0 0 24 24"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg></button>' +
          '<span class="adm-counter" data-photo-counter></span>' +
        '</div>' +
        (loc.photos.length > 1 ? '<div class="adm-thumbs">' + loc.photos.map(function (p, i) {
          return '<button type="button" data-photo-thumb="' + i + '" aria-label="' + (i + 1) + '"><img src="' + esc(p.thumb) + '" alt="" loading="lazy"></button>';
        }).join('') + '</div>' : '') +
      '</div>';
    } else {
      gallery = '<div class="adm-gallery-empty"><svg viewBox="0 0 236 404" aria-hidden="true"><use href="#adm-mark"/></svg><p>' + esc(t('noPhotos')) + '</p></div>';
    }

    detailEl.innerHTML =
      '<button type="button" class="adm-detail-close" data-detail-close aria-label="' + esc(t('close')) + '"><svg class="x-ico" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button>' +
      gallery +
      '<div class="adm-detail-body">' +
        '<span class="adm-status st-' + esc(loc.availability) + '">' + esc(t('st_' + loc.availability)) + '</span>' +
        '<h3>' + esc(tr(loc.name)) + '</h3>' +
        (tr(loc.address) ? '<p class="adm-address">' + esc(tr(loc.address)) + '</p>' : '') +
        (rows.length ? '<dl class="adm-facts">' + rows.map(function (row) { return '<div><dt>' + esc(row[0]) + '</dt><dd>' + esc(row[1]) + '</dd></div>'; }).join('') + '</dl>' : '') +
        (tr(loc.description) ? '<p class="adm-description">' + esc(tr(loc.description)) + '</p>' : '') +
        '<a class="x-btn x-btn-primary adm-cta" href="contact.html?location=' + loc.id + '">' + esc(t('cta')) + '<svg class="x-ico" viewBox="0 0 24 24"><path d="M7 17 17 7M8 7h9v9"/></svg></a>' +
      '</div>';
    detailEl.hidden = false;
    root.classList.add('has-detail');
    if (loc.photos.length) showPhoto(loc, state.photo);
    bindGallery(loc);
  }

  function showPhoto(loc, index) {
    var count = loc.photos.length;
    state.photo = (index + count) % count;
    var photo = loc.photos[state.photo];
    var img = $('.adm-stage-img', detailEl);
    if (!img) return;
    img.classList.add('is-changing');
    var next = new Image();
    next.onload = next.onerror = function () {
      img.src = photo.large;
      img.alt = tr(photo.alt) || tr(loc.name);
      img.classList.remove('is-changing');
    };
    next.src = photo.large;
    // Qo'shni rasmni oldindan yuklash
    if (count > 1) { var pre = new Image(); pre.src = loc.photos[(state.photo + 1) % count].large; }
    var counter = $('[data-photo-counter]', detailEl);
    if (counter) counter.textContent = (state.photo + 1) + ' / ' + count;
    $$('[data-photo-thumb]', detailEl).forEach(function (thumb) {
      var active = Number(thumb.dataset.photoThumb) === state.photo;
      thumb.classList.toggle('on', active);
      if (active && thumb.scrollIntoView) thumb.scrollIntoView({ block: 'nearest', inline: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }

  function bindSwipe(element, onPrev, onNext) {
    var startX = null;
    var startY = null;
    element.addEventListener('pointerdown', function (e) { startX = e.clientX; startY = e.clientY; });
    element.addEventListener('pointerup', function (e) {
      if (startX === null) return;
      var dx = e.clientX - startX;
      var dy = e.clientY - startY;
      startX = null;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) { if (dx > 0) onPrev(); else onNext(); }
    });
    element.addEventListener('pointercancel', function () { startX = null; });
  }

  function bindGallery(loc) {
    $('[data-detail-close]', detailEl).addEventListener('click', function () { closeDetail(false); });
    var stage = $('.adm-stage', detailEl);
    if (!stage) return;
    var prev = function () { showPhoto(loc, state.photo - 1); };
    var next = function () { showPhoto(loc, state.photo + 1); };
    var prevBtn = $('[data-photo-prev]', detailEl);
    var nextBtn = $('[data-photo-next]', detailEl);
    if (prevBtn) prevBtn.addEventListener('click', prev);
    if (nextBtn) nextBtn.addEventListener('click', next);
    $('[data-photo-full]', detailEl).addEventListener('click', function () { openLightbox(loc, state.photo); });
    $$('[data-photo-thumb]', detailEl).forEach(function (thumb) {
      thumb.addEventListener('click', function () { showPhoto(loc, Number(thumb.dataset.photoThumb)); });
    });
    stage.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') { e.preventDefault(); prev(); }
      if (e.key === 'ArrowRight') { e.preventDefault(); next(); }
      if (e.key === 'Enter') openLightbox(loc, state.photo);
    });
    stage.addEventListener('dblclick', function () { openLightbox(loc, state.photo); });
    if (loc.photos.length > 1) bindSwipe(stage, prev, next);
  }

  /* ------------------------------------------------------------------ To'liq ekran */
  var lightbox = document.createElement('div');
  lightbox.className = 'adm-lightbox';
  lightbox.hidden = true;
  lightbox.setAttribute('role', 'dialog');
  lightbox.setAttribute('aria-modal', 'true');
  lightbox.innerHTML =
    '<button type="button" class="adm-lb-close" data-lb-close><svg class="x-ico" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button>' +
    '<button type="button" class="adm-nav prev" data-lb-prev aria-label="‹"><svg class="x-ico" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>' +
    '<figure><img alt="" decoding="async"><figcaption></figcaption></figure>' +
    '<button type="button" class="adm-nav next" data-lb-next aria-label="›"><svg class="x-ico" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>';
  document.body.appendChild(lightbox);
  var lb = { loc: null, index: 0, returnFocus: null };

  function showLightbox(index) {
    var photos = lb.loc.photos;
    lb.index = (index + photos.length) % photos.length;
    var photo = photos[lb.index];
    var img = $('img', lightbox);
    img.classList.add('is-changing');
    img.onload = function () { img.classList.remove('is-changing'); };
    img.src = photo.full;
    img.alt = tr(photo.alt) || tr(lb.loc.name);
    $('figcaption', lightbox).textContent = tr(lb.loc.name) + ' — ' + (lb.index + 1) + ' / ' + photos.length;
    var multiple = photos.length > 1;
    $('[data-lb-prev]', lightbox).hidden = !multiple;
    $('[data-lb-next]', lightbox).hidden = !multiple;
  }
  function openLightbox(loc, index) {
    lb.loc = loc;
    lb.returnFocus = document.activeElement;
    lightbox.hidden = false;
    lightbox.setAttribute('aria-label', tr(loc.name));
    $('[data-lb-close]', lightbox).setAttribute('aria-label', t('close'));
    document.body.style.overflow = 'hidden';
    showLightbox(index);
    $('[data-lb-close]', lightbox).focus();
  }
  function closeLightbox() {
    if (lightbox.hidden) return;
    lightbox.hidden = true;
    document.body.style.overflow = '';
    if (lb.loc) showPhoto(lb.loc, lb.index);
    if (lb.returnFocus && lb.returnFocus.focus) lb.returnFocus.focus();
  }
  $('[data-lb-close]', lightbox).addEventListener('click', closeLightbox);
  $('[data-lb-prev]', lightbox).addEventListener('click', function () { showLightbox(lb.index - 1); });
  $('[data-lb-next]', lightbox).addEventListener('click', function () { showLightbox(lb.index + 1); });
  lightbox.addEventListener('click', function (e) { if (e.target === lightbox || e.target.tagName === 'FIGURE') closeLightbox(); });
  bindSwipe(lightbox, function () { showLightbox(lb.index - 1); }, function () { showLightbox(lb.index + 1); });
  document.addEventListener('keydown', function (e) {
    if (!lightbox.hidden) {
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') showLightbox(lb.index - 1);
      if (e.key === 'ArrowRight') showLightbox(lb.index + 1);
      if (e.key === 'Tab') { e.preventDefault(); $('[data-lb-close]', lightbox).focus(); }
      return;
    }
    if (e.key === 'Escape' && state.selected) closeDetail(false);
  });

  /* ------------------------------------------------------------------ Filtrlar */
  var searchTimer = null;
  searchInput.addEventListener('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function () {
      state.query = searchInput.value.trim().toLowerCase();
      renderMarkers();
      renderSide();
    }, 160);
  });
  typeSelect.addEventListener('change', function () { state.type = typeSelect.value; renderMarkers(); renderSide(); });
  availabilitySelect.addEventListener('change', function () { state.availability = availabilitySelect.value; renderMarkers(); renderSide(); });

  function fillTypeOptions() {
    var used = {};
    state.locations.forEach(function (l) { used[l.type] = true; });
    var current = typeSelect.value;
    $$('option[data-dynamic]', typeSelect).forEach(function (o) { o.remove(); });
    Object.keys(state.types).forEach(function (slug) {
      if (!used[slug]) return;
      var option = document.createElement('option');
      option.value = slug;
      option.dataset.dynamic = '1';
      option.setAttribute('data-uz', state.types[slug].uz);
      option.setAttribute('data-ru', state.types[slug].ru);
      option.textContent = tr(state.types[slug]);
      typeSelect.appendChild(option);
    });
    typeSelect.value = current;
  }

  /* ------------------------------------------------------------------ Tilni almashtirish */
  function refreshLanguage() {
    searchInput.placeholder = searchInput.dataset['placeholder' + (lang() === 'ru' ? 'Ru' : 'Uz')] || '';
    Object.keys(labelMarkers).forEach(function (slug) {
      var el = labelMarkers[slug].getElement();
      if (el) $('.adm-label-name', el).textContent = tr(districts[slug].short);
    });
    updateChrome();
    renderSide();
    if (state.selected) {
      var keep = state.photo;
      var loc = findLocation(state.selected);
      if (loc) { state.photo = keep; renderDetail(loc); }
    }
    Object.keys(markers).forEach(function (id) {
      var el = markers[id].getElement();
      var loc = findLocation(Number(id));
      if (el && loc) el.setAttribute('aria-label', tr(loc.name) + ' — ' + t('st_' + loc.availability));
    });
  }
  new MutationObserver(refreshLanguage).observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

  /* ------------------------------------------------------------------ Yuklash */
  function buildRegion(geojson) {
    var region = L.geoJSON(geojson, {
      style: function (feature) { return { className: 'adm-area adm-area-' + feature.properties.slug, weight: 1.4 }; },
      onEachFeature: function (feature, layer) {
        var slug = feature.properties.slug;
        if (!districts[slug]) return;
        areaLayers[slug] = layer;
        layer.on('click', function () { if (state.district !== slug) openDistrict(slug); });
        layer.on('mouseover', function () { if (!state.district) layer.getElement().classList.add('is-hover'); });
        layer.on('mouseout', function () { layer.getElement().classList.remove('is-hover'); });
        var geometry = feature.geometry;
        areaRings[slug] = (geometry.type === 'Polygon' ? [geometry.coordinates] : geometry.coordinates).map(function (poly) {
          return poly[0].map(function (p) { return [p[1], p[0]]; });
        });
      }
    });
    region.addTo(map);
    regionBounds = region.getBounds();
    map.setMaxBounds(regionBounds.pad(0.35));
    map.fitBounds(regionBounds, Object.assign({ animate: false }, REGION_PADDING));
    // Viloyatdan uzoqlashib bo'lmaydi: eng kichik zoom — butun viloyat ko'rinadigan daraja.
    map.setMinZoom(Math.max(6, map.getZoom() - 0.5));

    // Chegaralar chizilib paydo bo'ladi (navbatma-navbat).
    Object.keys(areaLayers).forEach(function (slug, index) {
      var el = areaLayers[slug].getElement();
      if (!el || reduceMotion) return;
      el.classList.add('is-drawing');
      el.style.animationDelay = (index * 70) + 'ms';
      el.addEventListener('animationend', function done(e) {
        if (e.animationName !== 'adm-draw') return;
        el.classList.remove('is-drawing');
        el.style.animationDelay = '';
        el.removeEventListener('animationend', done);
      });
    });

    Object.keys(areaLayers).forEach(function (slug) {
      var label = L.marker(areaLayers[slug].getCenter(), {
        interactive: false,
        keyboard: false,
        icon: L.divIcon({ className: 'adm-label', html: '<span class="adm-label-in"><span class="adm-label-name">' + esc(tr(districts[slug].short)) + '</span><b class="adm-label-count" hidden></b></span>', iconSize: null })
      }).addTo(map);
      labelMarkers[slug] = label;
    });
  }

  function updateLabelCounts() {
    Object.keys(labelMarkers).forEach(function (slug) {
      var el = labelMarkers[slug].getElement();
      if (!el) return;
      var count = inDistrict(slug).length;
      var badge = $('.adm-label-count', el);
      badge.textContent = count ? String(count) : '';
      badge.hidden = !count;
    });
  }

  function applyHash() {
    var parts = window.location.hash.slice(1).split('/');
    var slug = decodeURIComponent(parts[0] || '');
    var id = Number(parts[1] || 0);
    if (id && findLocation(id)) { selectLocation(id); return; }
    if (districts[slug]) openDistrict(slug);
  }

  var loading = $('[data-adm-loading]', root);
  Promise.all([
    fetch(cfg.geojson).then(function (r) { if (!r.ok) throw new Error('geojson'); return r.json(); }),
    fetch(cfg.api, { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.ok ? r.json() : { locations: [], types: [] }; }).catch(function () { return { locations: [], types: [] }; })
  ]).then(function (results) {
    var data = results[1] || {};
    (data.types || []).forEach(function (type) { state.types[type.slug] = type.name; });
    state.locations = (data.locations || []).filter(function (l) {
      return districts[l.district] && isFinite(l.lat) && isFinite(l.lng) && STATUSES.indexOf(l.availability) !== -1;
    });
    buildRegion(results[0]);
    fillTypeOptions();
    updateLabelCounts();
    updateChrome();
    renderSide();
    applyHash();
    refreshLanguage();
  }).catch(function () {
    hintEl.textContent = 'Xaritani yuklab bo‘lmadi. Sahifani yangilang.';
  }).then(function () {
    if (loading) loading.hidden = true;
  });

  window.addEventListener('hashchange', applyHash);
})();
