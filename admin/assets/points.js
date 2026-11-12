/* Reklama nuqtalari: xaritadan koordinata tanlash, tumanni aniqlash, fotosuratlar. */
(function () {
  'use strict';

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var body = document.body;
  var csrf = body.dataset.csrf || '';
  var REGION_CENTER = [40.18, 67.85];

  function request(url, options) {
    options = options || {};
    options.credentials = 'same-origin';
    options.headers = Object.assign({ 'X-Requested-With': 'fetch', 'Accept': 'application/json', 'X-CSRF-Token': csrf }, options.headers || {});
    return fetch(url, options).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok || data.success === false) throw new Error(data.message || ('Server xatosi (' + r.status + ')'));
        return data;
      });
    });
  }

  function brandMarker() {
    return L.divIcon({
      className: 'admin-pin',
      html: '<svg viewBox="0 0 236 404" aria-hidden="true"><path d="M22 117L236 0v91L82 177z"/><path d="M0 270l236-134v268l-73-41V262l-86 52z"/></svg>',
      iconSize: [22, 38],
      iconAnchor: [11, 38]
    });
  }

  function addTiles(map, el) {
    var base = L.tileLayer(el.dataset.tileUrl, { maxZoom: 19, attribution: el.dataset.tileAttribution || '' });
    base.addTo(map);
    if (el.dataset.satelliteUrl) {
      var sat = L.tileLayer(el.dataset.satelliteUrl, { maxZoom: 19, attribution: el.dataset.satelliteAttribution || '' });
      L.control.layers({ 'Xarita': base, 'Sun’iy yo‘ldosh': sat }, null, { position: 'topright' }).addTo(map);
    }
  }

  /* Nuqta poligon ichidami (GeoJSON: [lng, lat]) */
  function inRing(x, y, ring) {
    var inside = false;
    for (var i = 0, j = ring.length - 1; i < ring.length; j = i++) {
      var xi = ring[i][0], yi = ring[i][1], xj = ring[j][0], yj = ring[j][1];
      if (((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) inside = !inside;
    }
    return inside;
  }
  function featureContains(feature, lat, lng) {
    var g = feature.geometry;
    var polys = g.type === 'Polygon' ? [g.coordinates] : g.coordinates;
    return polys.some(function (poly) {
      if (!inRing(lng, lat, poly[0])) return false;
      return !poly.slice(1).some(function (hole) { return inRing(lng, lat, hole); });
    });
  }

  /* ================================================================ Forma */
  var form = $('[data-points-form]');
  if (form && window.L) {
    var mapEl = $('[data-point-map]', form);
    var latInput = $('[data-lat]', form);
    var lngInput = $('[data-lng]', form);
    var districtSelect = $('[data-district-select]', form);
    var hint = $('[data-district-hint]', form);
    var map = L.map(mapEl, { scrollWheelZoom: true }).setView(REGION_CENTER, 8);
    addTiles(map, form);
    var geo = null;
    var layers = {};
    var marker = null;

    var parse = function (v) { v = String(v || '').replace(',', '.').trim(); return v === '' || isNaN(Number(v)) ? null : Number(v); };

    function optionFor(slug) {
      return $$('option', districtSelect).filter(function (o) { return o.dataset.slug === slug; })[0] || null;
    }

    function detect(lat, lng) {
      if (!geo) return null;
      var hit = geo.features.filter(function (f) { return featureContains(f, lat, lng); })[0];
      return hit ? hit.properties.slug : null;
    }

    function highlight(slug) {
      Object.keys(layers).forEach(function (key) {
        layers[key].setStyle(key === slug
          ? { color: '#00e879', weight: 2.5, fillColor: '#00e879', fillOpacity: 0.12 }
          : { color: '#5CBC79', weight: 1, fillColor: '#5CBC79', fillOpacity: 0.03 });
      });
    }

    function updateHint() {
      var lat = parse(latInput.value);
      var lng = parse(lngInput.value);
      var selected = districtSelect.selectedOptions[0];
      var selectedSlug = selected ? selected.dataset.slug : '';
      highlight(selectedSlug);
      if (lat === null || lng === null || !geo) { hint.textContent = ''; return; }
      var found = detect(lat, lng);
      if (!found) {
        hint.textContent = '⚠ Nuqta Jizzax viloyati tumanlari chegarasidan tashqarida.';
      } else if (selectedSlug && found !== selectedSlug) {
        var opt = optionFor(found);
        hint.textContent = '⚠ Nuqta xaritada “' + (opt ? opt.textContent : found) + '” hududiga tushmoqda.';
      } else {
        hint.textContent = '✓ Nuqta tanlangan tuman ichida.';
      }
    }

    function placeMarker(lat, lng, pan) {
      if (marker) {
        marker.setLatLng([lat, lng]);
      } else {
        marker = L.marker([lat, lng], { icon: brandMarker(), draggable: true, keyboard: true, title: 'Reklama joyi' }).addTo(map);
        marker.on('dragend', function () {
          var p = marker.getLatLng();
          setCoords(p.lat, p.lng);
        });
      }
      if (pan) map.setView([lat, lng], Math.max(map.getZoom(), 15));
    }

    function setCoords(lat, lng) {
      latInput.value = lat.toFixed(6);
      lngInput.value = lng.toFixed(6);
      placeMarker(lat, lng, false);
      var found = detect(lat, lng);
      if (found) {
        var opt = optionFor(found);
        if (opt && !opt.selected) {
          opt.selected = true;
          districtSelect.dispatchEvent(new Event('input', { bubbles: true }));
        }
      }
      updateHint();
    }

    map.on('click', function (e) { setCoords(e.latlng.lat, e.latlng.lng); });

    [latInput, lngInput].forEach(function (input) {
      input.addEventListener('change', function () {
        var lat = parse(latInput.value);
        var lng = parse(lngInput.value);
        if (lat !== null && lng !== null) {
          placeMarker(lat, lng, true);
          var found = detect(lat, lng);
          var opt = found ? optionFor(found) : null;
          if (opt && !districtSelect.value) opt.selected = true;
        }
        updateHint();
      });
    });

    districtSelect.addEventListener('change', function () {
      var opt = districtSelect.selectedOptions[0];
      var layer = opt && layers[opt.dataset.slug];
      if (layer && !marker) map.fitBounds(layer.getBounds(), { padding: [20, 20] });
      updateHint();
    });

    fetch(form.dataset.geojson).then(function (r) { return r.json(); }).then(function (data) {
      geo = data;
      L.geoJSON(data, {
        interactive: false,
        style: { color: '#5CBC79', weight: 1, fillOpacity: 0.03 },
        onEachFeature: function (feature, layer) { layers[feature.properties.slug] = layer; }
      }).addTo(map);
      var lat = parse(latInput.value);
      var lng = parse(lngInput.value);
      if (lat !== null && lng !== null) {
        placeMarker(lat, lng, true);
      } else {
        var opt = districtSelect.selectedOptions[0];
        var layer = opt && layers[opt.dataset.slug];
        map.fitBounds(layer ? layer.getBounds() : L.geoJSON(data).getBounds(), { padding: [20, 20] });
      }
      updateHint();
    }).catch(function () { hint.textContent = 'Tuman chegaralarini yuklab bo‘lmadi.'; });

    /* ---------- Fotosuratlar ---------- */
    var grid = $('[data-photo-grid]', form);
    var status = $('[data-photo-status]', form);
    var countEl = $('[data-photo-count]', form);

    function refreshPhotos() {
      var items = $$('.photo-item', grid);
      countEl.textContent = items.length + ' ta';
      var checked = $('input[name=cover]:checked', grid);
      if (!checked && items[0]) {
        $('input[name=cover]', items[0]).checked = true;
      }
      items.forEach(function (item) { item.classList.toggle('is-cover', $('input[name=cover]', item).checked); });
    }

    function addPhoto(item) {
      if ($('.photo-item[data-id="' + item.id + '"]', grid)) return;
      var el = document.createElement('div');
      el.className = 'photo-item';
      el.draggable = true;
      el.dataset.id = item.id;
      var img = document.createElement('img');
      img.src = item.url;
      img.alt = '';
      img.loading = 'lazy';
      el.appendChild(img);
      el.insertAdjacentHTML('beforeend',
        '<input type="hidden" name="photos[]" value="' + Number(item.id) + '">' +
        '<label class="photo-cover" title="Muqova qilish"><input type="radio" name="cover" value="' + Number(item.id) + '"><span>★</span></label>' +
        '<button type="button" class="photo-remove" data-photo-remove title="Olib tashlash" aria-label="Olib tashlash">✕</button>');
      grid.appendChild(el);
    }

    grid.addEventListener('click', function (e) {
      var remove = e.target.closest('[data-photo-remove]');
      if (remove) { remove.closest('.photo-item').remove(); refreshPhotos(); }
    });
    grid.addEventListener('change', refreshPhotos);

    var dragged = null;
    grid.addEventListener('dragstart', function (e) {
      dragged = e.target.closest('.photo-item');
      if (dragged) { dragged.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; }
    });
    grid.addEventListener('dragover', function (e) {
      if (!dragged) return;
      e.preventDefault();
      var target = e.target.closest('.photo-item');
      if (!target || target === dragged) return;
      var r = target.getBoundingClientRect();
      grid.insertBefore(dragged, (e.clientX > r.left + r.width / 2) ? target.nextSibling : target);
    });
    grid.addEventListener('dragend', function () {
      if (dragged) dragged.classList.remove('dragging');
      dragged = null;
      refreshPhotos();
    });

    $('[data-photo-upload]', form).addEventListener('change', function () {
      var input = this;
      if (!input.files.length) return;
      var data = new FormData();
      Array.prototype.forEach.call(input.files, function (f) { data.append('file[]', f); });
      data.append('_csrf', csrf);
      status.textContent = 'Yuklanmoqda… (' + input.files.length + ' ta)';
      request(body.dataset.mediaUpload, { method: 'POST', body: data }).then(function (result) {
        (result.items || []).forEach(addPhoto);
        refreshPhotos();
        status.textContent = (result.errors && result.errors.length) ? result.errors.join('; ') : 'Yuklandi. Saqlashni unutmang.';
      }).catch(function (err) { status.textContent = err.message; }).then(function () { input.value = ''; });
    });

    var modal = $('[data-photo-modal]');
    var modalGrid = $('[data-photo-modal-grid]');
    var picked = {};
    function closeModal() { modal.hidden = true; body.style.overflow = ''; }
    $('[data-photo-library]', form).addEventListener('click', function () {
      picked = {};
      modal.hidden = false;
      body.style.overflow = 'hidden';
      modalGrid.textContent = 'Yuklanmoqda…';
      request(body.dataset.mediaList).then(function (data) {
        modalGrid.textContent = '';
        (data.items || []).forEach(function (item) {
          var b = document.createElement('button');
          b.type = 'button';
          b.className = 'media-item';
          b.innerHTML = '<span class="media-thumb"><img alt="" loading="lazy"></span><span class="media-name"></span>';
          $('img', b).src = item.url;
          $('.media-name', b).textContent = item.name;
          b.addEventListener('click', function () {
            if (picked[item.id]) { delete picked[item.id]; b.classList.remove('selected'); } else { picked[item.id] = item; b.classList.add('selected'); }
          });
          modalGrid.appendChild(b);
        });
      }).catch(function (err) { modalGrid.textContent = err.message; });
    });
    $('[data-photo-modal-done]').addEventListener('click', function () {
      Object.keys(picked).forEach(function (k) { addPhoto(picked[k]); });
      refreshPhotos();
      closeModal();
    });
    $('[data-photo-modal-close]').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  }

  /* ================================================================ Oldindan ko'rish */
  var previewMap = $('[data-preview-map]');
  if (previewMap && window.L) {
    var lat = Number(previewMap.dataset.lat);
    var lng = Number(previewMap.dataset.lng);
    var pm = L.map(previewMap).setView([lat, lng], 16);
    addTiles(pm, previewMap);
    L.marker([lat, lng], { icon: brandMarker() }).addTo(pm);
  }
  $$('[data-preview-src]').forEach(function (button) {
    button.addEventListener('click', function () {
      $('[data-preview-main]').src = button.dataset.previewSrc;
      $$('[data-preview-src]').forEach(function (b) { b.classList.toggle('on', b === button); });
    });
  });
})();
