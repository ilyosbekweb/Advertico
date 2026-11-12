(function () {
  'use strict';

  var $ = function (selector, root) { return (root || document).querySelector(selector); };
  var $$ = function (selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); };
  var body = document.body;
  var csrf = body.dataset.csrf || '';

  function request(url, options) {
    options = options || {};
    options.headers = Object.assign({ 'X-Requested-With': 'fetch', 'Accept': 'application/json', 'X-CSRF-Token': csrf }, options.headers || {});
    options.credentials = 'same-origin';
    return fetch(url, options).then(function (response) {
      return response.json().catch(function () { return {}; }).then(function (data) {
        if (!response.ok || data.success === false) {
          throw new Error(data.message || ('Server xatosi (' + response.status + ')'));
        }
        return data;
      });
    });
  }

  /* ---------- Mavzu (sayt bilan bir xil kalit) ---------- */
  $$('[data-theme-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      var root = document.documentElement;
      root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
      try { localStorage.setItem('advertico-theme', root.dataset.theme); } catch (error) { /* ixtiyoriy */ }
    });
  });

  /* ---------- Mobil menyu ---------- */
  $$('[data-sidebar-open]').forEach(function (b) { b.addEventListener('click', function () { body.classList.add('nav-open'); }); });
  $$('[data-sidebar-close]').forEach(function (b) { b.addEventListener('click', function () { body.classList.remove('nav-open'); }); });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') body.classList.remove('nav-open');
  });

  /* ---------- Tasdiqlash ---------- */
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (form.dataset && form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
      event.preventDefault();
    }
  }, true);

  /* ---------- Bosiladigan qatorlar ---------- */
  $$('tr[data-href]').forEach(function (row) {
    row.addEventListener('click', function (event) {
      if (event.target.closest('a, button, form, input')) return;
      window.location.href = row.dataset.href;
    });
  });

  /* ---------- Drag & drop tartiblash ---------- */
  $$('table[data-sortable]').forEach(function (table) {
    var tbody = $('tbody', table);
    var dragged = null;
    tbody.addEventListener('dragstart', function (event) {
      var grip = event.target.closest('.grip');
      if (!grip) { event.preventDefault(); return; }
      dragged = grip.closest('tr');
      dragged.classList.add('dragging');
      event.dataTransfer.effectAllowed = 'move';
      try { event.dataTransfer.setDragImage(dragged, 20, 20); } catch (error) { /* ixtiyoriy */ }
    });
    tbody.addEventListener('dragover', function (event) {
      if (!dragged) return;
      event.preventDefault();
      var target = event.target.closest('tr');
      if (!target || target === dragged) return;
      var rect = target.getBoundingClientRect();
      var after = event.clientY > rect.top + rect.height / 2;
      tbody.insertBefore(dragged, after ? target.nextSibling : target);
    });
    tbody.addEventListener('dragend', function () {
      if (!dragged) return;
      dragged.classList.remove('dragging');
      dragged = null;
      var data = new FormData();
      $$('tr[data-id]', tbody).forEach(function (row) { data.append('ids[]', row.dataset.id); });
      data.append('_csrf', csrf);
      request(table.dataset.sortable, { method: 'POST', body: data })
        .then(function () { toast('Tartib saqlandi'); })
        .catch(function (error) { toast(error.message, true); });
    });
  });

  /* ---------- Kichik bildirishnoma ---------- */
  var toastTimer = null;
  function toast(message, isError) {
    var box = $('#toast');
    if (!box) {
      box = document.createElement('div');
      box.id = 'toast';
      box.setAttribute('role', 'status');
      box.style.cssText = 'position:fixed;bottom:22px;left:50%;transform:translateX(-50%);z-index:90;padding:11px 18px;border-radius:99px;font-weight:600;font-size:.88rem;box-shadow:0 12px 30px rgba(0,0,0,.3);transition:opacity .2s';
      body.appendChild(box);
    }
    box.textContent = message;
    box.style.background = isError ? 'var(--danger)' : 'var(--ac)';
    box.style.color = isError ? '#fff' : 'var(--ac-ink)';
    box.style.opacity = '1';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { box.style.opacity = '0'; }, 2600);
  }

  /* ---------- Media tanlash oynasi ---------- */
  var modal = $('#media-modal');
  var activeField = null;

  function renderPicker(items) {
    var grid = $('[data-modal-grid]', modal);
    grid.textContent = '';
    if (!items.length) {
      grid.innerHTML = '<div class="empty" style="grid-column:1/-1">Hali rasm yo‘q. Yuqoridagi tugma orqali yuklang.</div>';
      return;
    }
    var current = activeField ? $('input[type=hidden]', activeField).value : '';
    items.forEach(function (item) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'media-item' + (String(item.id) === current ? ' selected' : '');
      var thumb = document.createElement('span');
      thumb.className = 'media-thumb';
      var img = document.createElement('img');
      img.src = item.url;
      img.alt = '';
      img.loading = 'lazy';
      thumb.appendChild(img);
      var name = document.createElement('span');
      name.className = 'media-name';
      name.textContent = item.name;
      var meta = document.createElement('span');
      meta.className = 'media-meta';
      meta.textContent = item.width + '×' + item.height;
      button.appendChild(thumb);
      button.appendChild(name);
      button.appendChild(meta);
      button.addEventListener('click', function () { choose(item); });
      grid.appendChild(button);
    });
  }

  function loadPicker() {
    $('[data-modal-status]', modal).textContent = 'Yuklanmoqda…';
    return request(body.dataset.mediaList).then(function (data) {
      $('[data-modal-status]', modal).textContent = '';
      renderPicker(data.items || []);
    }).catch(function (error) {
      $('[data-modal-status]', modal).textContent = error.message;
    });
  }

  function choose(item) {
    if (!activeField) return;
    $('input[type=hidden]', activeField).value = item.id;
    var preview = $('.media-field-preview', activeField);
    preview.textContent = '';
    var img = document.createElement('img');
    img.src = item.url;
    img.alt = '';
    preview.appendChild(img);
    $('[data-media-clear]', activeField).hidden = false;
    markDirty();
    closeModal();
  }

  function openModal(field) {
    activeField = field;
    modal.hidden = false;
    body.style.overflow = 'hidden';
    loadPicker();
    $('[data-modal-close]', modal).focus();
  }

  function closeModal() {
    modal.hidden = true;
    body.style.overflow = '';
    var pick = activeField ? $('[data-media-pick]', activeField) : null;
    activeField = null;
    if (pick) pick.focus();
  }

  function uploadFiles(files, statusElement) {
    var data = new FormData();
    Array.prototype.forEach.call(files, function (file) { data.append('file[]', file); });
    data.append('_csrf', csrf);
    if (statusElement) statusElement.textContent = 'Yuklanmoqda… (' + files.length + ' ta fayl)';
    return request(body.dataset.mediaUpload, { method: 'POST', body: data }).then(function (result) {
      if (statusElement) {
        statusElement.textContent = result.errors && result.errors.length
          ? 'Ba’zi fayllar yuklanmadi:\n' + result.errors.join('\n')
          : (result.items.length + ' ta rasm yuklandi.');
      }
      return result;
    }).catch(function (error) {
      if (statusElement) statusElement.textContent = error.message;
      throw error;
    });
  }

  if (modal) {
    $$('[data-media-field]').forEach(function (field) {
      $('[data-media-pick]', field).addEventListener('click', function () { openModal(field); });
      $('[data-media-clear]', field).addEventListener('click', function () {
        $('input[type=hidden]', field).value = '';
        $('.media-field-preview', field).innerHTML = '<span>Rasm tanlanmagan</span>';
        this.hidden = true;
        markDirty();
      });
    });
    $('[data-modal-close]', modal).addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !modal.hidden) closeModal(); });
    $('[data-modal-upload]', modal).addEventListener('change', function () {
      var input = this;
      if (!input.files.length) return;
      uploadFiles(input.files, $('[data-modal-status]', modal)).then(function (result) {
        input.value = '';
        if (result.items && result.items[0]) choose(Object.assign({}, result.items[0]));
      }).catch(function () { input.value = ''; });
    });
  }

  /* ---------- Media sahifasi: drag & drop yuklash ---------- */
  var dropzone = $('[data-dropzone]');
  if (dropzone) {
    var dzInput = $('[data-dropzone-input]', dropzone);
    var dzStatus = $('[data-dropzone-status]', dropzone);
    var send = function (files) {
      if (!files.length) return;
      uploadFiles(files, dzStatus).then(function () {
        setTimeout(function () { window.location.reload(); }, 600);
      }).catch(function () {});
    };
    dzInput.addEventListener('change', function () { send(dzInput.files); });
    ['dragenter', 'dragover'].forEach(function (type) {
      dropzone.addEventListener(type, function (event) { event.preventDefault(); dropzone.classList.add('over'); });
    });
    ['dragleave', 'drop'].forEach(function (type) {
      dropzone.addEventListener(type, function (event) { event.preventDefault(); dropzone.classList.remove('over'); });
    });
    dropzone.addEventListener('drop', function (event) { send(event.dataTransfer.files); });
  }

  /* ---------- Narxlar repeater ---------- */
  $$('[data-repeater]').forEach(function (repeater) {
    var rows = $('.repeater-rows', repeater);
    var template = $('template', repeater);
    var counter = rows.children.length + 1000;
    $('[data-repeater-add]', repeater).addEventListener('click', function () {
      var html = template.innerHTML.replace(/__i__/g, String(counter++));
      rows.insertAdjacentHTML('beforeend', html);
      var first = rows.lastElementChild.querySelector('input');
      if (first) first.focus();
      markDirty();
    });
    repeater.addEventListener('click', function (event) {
      var remove = event.target.closest('[data-repeater-remove]');
      if (remove) { remove.closest('.repeater-row').remove(); markDirty(); }
    });
  });

  /* ---------- Rang tanlash ---------- */
  $$('[data-color-sync]').forEach(function (picker) {
    var text = picker.nextElementSibling;
    picker.addEventListener('input', function () { text.value = picker.value; });
    text.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(text.value)) picker.value = text.value; });
  });

  /* ---------- Belgilar hisoblagichi ---------- */
  $$('[data-counter]').forEach(function (input) {
    var limit = Number(input.dataset.counter);
    var badge = document.createElement('span');
    badge.className = 'char-counter';
    input.parentNode.appendChild(badge);
    var update = function () {
      var length = input.value.length;
      badge.textContent = length + ' / ' + limit;
      badge.classList.toggle('over', length > limit);
    };
    input.addEventListener('input', update);
    update();
  });

  /* ---------- SEO ko'rinishi ---------- */
  var preview = $('[data-seo-preview]');
  if (preview) {
    var titleInput = $('[data-field="title"][data-lang="uz"]');
    var descInput = $('[data-field="description"][data-lang="uz"]');
    var renderPreview = function () {
      var title = titleInput ? titleInput.value : '';
      var desc = descInput ? descInput.value : '';
      $('[data-preview-title]', preview).textContent = title.length > 60 ? title.slice(0, 59) + '…' : (title || 'Sarlavha');
      $('[data-preview-desc]', preview).textContent = desc.length > 160 ? desc.slice(0, 159) + '…' : (desc || 'Tavsif kiritilmagan.');
    };
    [titleInput, descInput].forEach(function (el) { if (el) el.addEventListener('input', renderPreview); });
    renderPreview();
  }

  /* ---------- Textarea avtomatik balandlik ---------- */
  $$('textarea[data-autosize]').forEach(function (area) {
    var fit = function () { area.style.height = 'auto'; area.style.height = (area.scrollHeight + 2) + 'px'; };
    area.addEventListener('input', fit);
    fit();
  });

  /* ---------- Saqlanmagan o'zgarishlar ogohlantirishi ---------- */
  var dirty = false;
  function markDirty() {
    dirty = true;
    var note = $('[data-dirty-note]');
    if (note) note.hidden = false;
  }
  $$('form.form').forEach(function (form) {
    if (form.method.toLowerCase() !== 'post' || form.closest('.auth-card')) return;
    form.addEventListener('input', markDirty);
    form.addEventListener('submit', function () { dirty = false; });
  });
  window.addEventListener('beforeunload', function (event) {
    if (!dirty) return;
    event.preventDefault();
    event.returnValue = '';
  });
})();
