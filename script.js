(function () {
  var language = 'uz';
  var $ = function (selector, root) {
    return (root || document).querySelector(selector);
  };
  var $$ = function (selector, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(selector));
  };
  var activeServiceId = null;
  var activeDistrictId = null;
  var renderSelectedDistrict = null;
  var serviceCatalog = {
    billboard: {
      image: 'assets/billboard-catalog.png',
      title: { uz: 'Billboard 6×3 m', ru: 'Билборд 6×3 м' },
      description: { uz: 'Katalogdagi 18 m² billboard taklifi.', ru: 'Билборд площадью 18 м² по каталогу.' },
      prices: [
        { label: { uz: '1 oylik ijara', ru: 'Аренда за 1 месяц' }, value: '1 500 000 UZS' },
        { label: { uz: 'Bosma (18 m²)', ru: 'Печать (18 м²)' }, value: '550 000 UZS' },
        { label: { uz: 'Montaj xizmati', ru: 'Монтаж' }, value: '250 000 UZS' }
      ]
    },
    led: {
      image: 'assets/led-catalog.png',
      title: { uz: 'LED monitor', ru: 'LED-экран' },
      description: { uz: '1 oylik ijara narxi rolik davomiyligiga qarab belgilanadi.', ru: 'Цена аренды за 1 месяц зависит от длительности ролика.' },
      prices: [
        { label: { uz: '5 sekundlik rolik / oy', ru: 'Ролик 5 секунд / мес.' }, value: '1 500 000 UZS' },
        { label: { uz: '10 sekundlik rolik / oy', ru: 'Ролик 10 секунд / мес.' }, value: '2 000 000 UZS' },
        { label: { uz: '15 sekundlik rolik / oy', ru: 'Ролик 15 секунд / мес.' }, value: '2 500 000 UZS' },
        { label: { uz: '20 sekundlik rolik / oy', ru: 'Ролик 20 секунд / мес.' }, value: '3 000 000 UZS' }
      ]
    },
    cityboard: {
      image: 'assets/cityboard-catalog.png',
      title: { uz: 'Cityboard 3,5×2,5 m', ru: 'Ситиборд 3,5×2,5 м' },
      description: { uz: 'Lightbox formatidagi cityboard, 8,75 m².', ru: 'Ситиборд в формате lightbox, 8,75 м².' },
      prices: [
        { label: { uz: '1 oylik ijara', ru: 'Аренда за 1 месяц' }, value: '1 500 000 UZS' },
        { label: { uz: 'Bosma (8,75 m²)', ru: 'Печать (8,75 м²)' }, value: '1 350 000 UZS' },
        { label: { uz: 'Montaj xizmati', ru: 'Монтаж' }, value: '150 000 UZS' }
      ]
    },
    roof: {
      image: 'assets/roof-catalog.png',
      title: { uz: 'Tom va fasad brendlash', ru: 'Брендирование крыши и фасада' },
      description: { uz: 'Katalogdagi brendmauerlar: bino yon tomoni yoki tom qismiga joylashtiriladigan reklama.', ru: 'Брендмауэры по каталогу: реклама на фасаде или крыше здания.' },
      prices: [
        { label: { uz: '1 oylik ijara', ru: 'Аренда за 1 месяц' }, value: { uz: 'Kelishuv asosida', ru: 'По договорённости' } },
        { label: { uz: 'Bosma (1 m²)', ru: 'Печать (1 м²)' }, value: '35 000 UZS' },
        { label: { uz: 'Montaj (1 m²)', ru: 'Монтаж (1 м²)' }, value: '20 000 UZS' }
      ]
    },
    letters: {
      image: 'assets/letters-catalog.png',
      title: { uz: 'Katta hajmli harflar', ru: 'Крупноформатные буквы' },
      description: { uz: 'Katta yo‘l yoqasi yoki ko‘p qavatli bino tom qismiga o‘rnatiladigan hajmli harflar.', ru: 'Объёмные буквы у крупных дорог или на крышах многоэтажных зданий.' },
      prices: [
        { label: { uz: '1 oylik ijara', ru: 'Аренда за 1 месяц' }, value: { uz: 'Kelishuv asosida', ru: 'По договорённости' } },
        { label: { uz: 'Bosma narxi', ru: 'Стоимость изготовления' }, value: { uz: 'Kelishuv asosida', ru: 'По договорённости' } },
        { label: { uz: 'Montaj xizmati', ru: 'Монтаж' }, value: { uz: 'Kelishuv asosida', ru: 'По договорённости' } }
      ]
    },
    'bus-stops': {
      image: 'assets/bus-stops-catalog.png',
      title: { uz: 'Avtobus bekatlarini to‘liq brendlash', ru: 'Полное брендирование автобусных остановок' },
      description: { uz: 'Bekat konstruksiyasini to‘liq brendlash bo‘yicha katalog taklifi.', ru: 'Брендирование всей конструкции остановки по каталогу.' },
      prices: [
        { label: { uz: '1 oylik ijara', ru: 'Аренда за 1 месяц' }, value: '1 800 000 UZS' },
        { label: { uz: 'Bosma narxi', ru: 'Стоимость печати' }, value: { uz: 'Kelishuv asosida', ru: 'По договорённости' } },
        { label: { uz: 'Montaj xizmati', ru: 'Монтаж' }, value: { uz: 'Kelishuv asosida', ru: 'По договорённости' } }
      ]
    },
    'bus-branding': {
      image: 'assets/bus-branding-catalog.png',
      title: { uz: 'Avtobuslar uchun tashqi reklama', ru: 'Наружная реклама на автобусах' },
      description: { uz: 'Avtobus kuzoviga reklama joylashtirish bo‘yicha katalog taklifi.', ru: 'Размещение рекламы на автобусах по каталогу.' },
      prices: [
        { label: { uz: '1 oylik ijara', ru: 'Аренда за 1 месяц' }, value: '1 800 000 UZS' },
        { label: { uz: 'Bosma narxi', ru: 'Стоимость печати' }, value: '650 000 UZS' },
        { label: { uz: 'Montaj xizmati', ru: 'Монтаж' }, value: '1 000 000 UZS' }
      ]
    },
    underpass: {
      image: 'assets/underground-catalog.png',
      title: { uz: 'Yerosti yo‘li tashqi reklamasi 240×120 sm', ru: 'Реклама в подземном переходе 240×120 см' },
      description: { uz: 'Katalogdagi reklama joyi o‘lchami 240×120 sm, bosma maydoni 2,88 m².', ru: 'Размер рекламного места по каталогу — 240×120 см, площадь печати — 2,88 м².' },
      prices: [
        { label: { uz: '1 oylik ijara', ru: 'Аренда за 1 месяц' }, value: '700 000 UZS' },
        { label: { uz: 'Bosma (2,88 m²)', ru: 'Печать (2,88 м²)' }, value: '500 000 UZS' },
        { label: { uz: 'Qo‘shimcha', ru: 'Дополнительно' }, value: 'Sxemalar / Схемы' }
      ]
    }
  };

  // Admin paneldan boshqariladigan ma'lumotlar (render.php sahifaga #site-data sifatida joylaydi).
  // Ma'lumot bo'lmasa yuqoridagi standart qiymatlar ishlatiladi.
  var siteData = (function () {
    var element = document.getElementById('site-data');
    if (!element) return {};
    try {
      return JSON.parse(element.textContent) || {};
    } catch (error) {
      return {};
    }
  })();
  if (siteData.services && Object.keys(siteData.services).length) serviceCatalog = siteData.services;

  function initCursorBackground() {
    if (!window.matchMedia('(pointer: fine)').matches ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var canvas = document.createElement('canvas');
    canvas.id = 'cursor-bg';
    canvas.setAttribute('aria-hidden', 'true');
    document.body.appendChild(canvas);
    var context = canvas.getContext('2d');
    if (!context) {
      canvas.remove();
      return;
    }

    var width = 0;
    var height = 0;
    var pixelRatio = 1;
    var cursor = { x: 0, y: 0, targetX: 0, targetY: 0, initialized: false };
    var lastMove = 0;
    var animationFrame = 0;
    var particles = [];
    var logos = [
      { x: 0.15, y: 0.24, rotation: -0.08, scale: 1 },
      { x: 0.84, y: 0.32, rotation: 0.07, scale: 0.82 },
      { x: 0.17, y: 0.75, rotation: 0.06, scale: 0.76 },
      { x: 0.83, y: 0.82, rotation: -0.07, scale: 0.94 }
    ];
    var logoImages = {};
    ['dark', 'light'].forEach(function (theme) {
      logoImages[theme] = new Image();
      logoImages[theme].src = new URL('assets/logo-' + theme + '.png', document.baseURI).href;
      logoImages[theme].addEventListener('load', requestDraw);
    });

    for (var index = 0; index < 24; index += 1) {
      particles.push({
        angle: (Math.PI * 2 * index) / 24,
        radius: 48 + (index % 6) * 23,
        size: 1 + (index % 3) * 0.55,
        speed: 0.00015 + (index % 5) * 0.000035
      });
    }

    function resizeCanvas() {
      pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
      width = window.innerWidth;
      height = window.innerHeight;
      canvas.width = Math.round(width * pixelRatio);
      canvas.height = Math.round(height * pixelRatio);
      context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
    }

    function drawLogos(timestamp, energy) {
      var theme = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
      var image = logoImages[theme];
      if (!image.complete || !image.naturalWidth) return;

      logos.forEach(function (logo, index) {
        var float = Math.sin(timestamp * 0.00055 + index * 1.7);
        var logoWidth = Math.min(220, Math.max(92, width * 0.17)) * logo.scale;
        var logoHeight = logoWidth * image.naturalHeight / image.naturalWidth;
        var parallaxX = (cursor.targetX / width - 0.5) * -18;
        var parallaxY = (cursor.targetY / height - 0.5) * -14;

        context.save();
        context.translate(
          logo.x * width + parallaxX + float * 7,
          logo.y * height + parallaxY + float * 9
        );
        context.rotate(logo.rotation + float * 0.035);
        context.transform(1, float * 0.045, float * 0.11, 1, 0, 0);
        context.shadowColor = 'rgba(92,188,121,' + (0.32 * energy) + ')';
        context.shadowBlur = 20;

        for (var depth = 6; depth > 0; depth -= 1) {
          context.globalAlpha = 0.022 * energy;
          context.drawImage(
            image,
            -logoWidth / 2 + depth * 1.4,
            -logoHeight / 2 + depth * 1.4,
            logoWidth,
            logoHeight
          );
        }

        context.globalAlpha = 0.12 * energy;
        context.drawImage(image, -logoWidth / 2, -logoHeight / 2, logoWidth, logoHeight);
        context.restore();
      });
    }

    function draw(timestamp) {
      animationFrame = 0;
      if (!cursor.initialized) return;

      var idleTime = timestamp - lastMove;
      var energy = Math.max(0, 1 - Math.max(0, idleTime - 450) / 1050);
      cursor.x += (cursor.targetX - cursor.x) * 0.13;
      cursor.y += (cursor.targetY - cursor.y) * 0.13;
      context.clearRect(0, 0, width, height);

      if (energy > 0) {
        drawLogos(timestamp, energy);

        var glow = context.createRadialGradient(cursor.x, cursor.y, 0, cursor.x, cursor.y, 250);
        glow.addColorStop(0, 'rgba(92,188,121,' + (0.17 * energy) + ')');
        glow.addColorStop(0.42, 'rgba(92,188,121,' + (0.07 * energy) + ')');
        glow.addColorStop(1, 'rgba(92,188,121,0)');
        context.fillStyle = glow;
        context.fillRect(cursor.x - 250, cursor.y - 250, 500, 500);

        var points = particles.map(function (particle) {
          var angle = particle.angle + timestamp * particle.speed;
          var x = cursor.x + Math.cos(angle) * particle.radius;
          var y = cursor.y + Math.sin(angle) * particle.radius * 0.72;
          return { x: x, y: y, size: particle.size };
        });

        context.lineWidth = 0.7;
        for (var first = 0; first < points.length; first += 1) {
          for (var second = first + 1; second < points.length; second += 1) {
            var deltaX = points[first].x - points[second].x;
            var deltaY = points[first].y - points[second].y;
            var distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
            if (distance < 64) {
              context.strokeStyle = 'rgba(123,216,151,' + ((1 - distance / 64) * 0.2 * energy) + ')';
              context.beginPath();
              context.moveTo(points[first].x, points[first].y);
              context.lineTo(points[second].x, points[second].y);
              context.stroke();
            }
          }
        }

        points.forEach(function (point) {
          context.beginPath();
          context.arc(point.x, point.y, point.size, 0, Math.PI * 2);
          context.fillStyle = 'rgba(157,236,177,' + (0.58 * energy) + ')';
          context.fill();
        });
      }

      if (energy > 0) animationFrame = window.requestAnimationFrame(draw);
    }

    function requestDraw() {
      if (!animationFrame) animationFrame = window.requestAnimationFrame(draw);
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    window.addEventListener('pointermove', function (event) {
      if (event.pointerType === 'touch') return;
      if (!cursor.initialized) {
        cursor.x = event.clientX;
        cursor.y = event.clientY;
        cursor.initialized = true;
      }
      cursor.targetX = event.clientX;
      cursor.targetY = event.clientY;
      lastMove = performance.now();
      requestDraw();
    });
    window.addEventListener('pointerleave', function () {
      lastMove = performance.now() - 700;
      requestDraw();
    });
  }

  function readStorage(key, fallback) {
    try {
      return localStorage.getItem(key) || fallback;
    } catch (error) {
      return fallback;
    }
  }

  function writeStorage(key, value) {
    try {
      localStorage.setItem(key, value);
    } catch (error) {
      return false;
    }
    return true;
  }

  language = readStorage('al', 'uz');
  if (language !== 'uz' && language !== 'ru') language = 'uz';
  var savedTheme = readStorage('advertico-theme', 'dark');
  document.documentElement.dataset.theme = savedTheme === 'light' ? 'light' : 'dark';

  function applyLanguage() {
    $$('[data-uz][data-ru]').forEach(function (element) {
      element.textContent = element.getAttribute('data-' + language);
    });
    var languageButton = $('#lang');
    if (languageButton) {
      languageButton.textContent = language === 'uz' ? 'RU' : 'UZ';
      languageButton.setAttribute('data-language', language);
      languageButton.setAttribute('aria-label', language === 'uz' ? 'Switch to Russian' : 'Oʻzbekchaga oʻtish');
    }
    var themeButton = $('#th');
    if (themeButton) {
      var isDark = document.documentElement.dataset.theme === 'dark';
      themeButton.setAttribute('aria-label', isDark
        ? (language === 'uz' ? 'Kunduzgi rejimga oʻtish' : 'Переключить на дневную тему')
        : (language === 'uz' ? 'Tungi rejimga oʻtish' : 'Переключить на ночную тему'));
    }
    document.documentElement.lang = language;
    document.documentElement.classList.remove('i18n-pending');
    if (siteData.seo) {
      if (siteData.seo.title && siteData.seo.title[language]) document.title = siteData.seo.title[language];
      var metaDescription = $('meta[name="description"]');
      if (metaDescription && siteData.seo.description && siteData.seo.description[language]) {
        metaDescription.setAttribute('content', siteData.seo.description[language]);
      }
    }
    if (activeServiceId) renderServiceDetail(activeServiceId);
    if (renderSelectedDistrict) renderSelectedDistrict();
  }

  function initDistrictMap() {
    var map = $('#region-map');
    var title = $('#district-title');
    var description = $('#district-description');
    var index = $('#district-index');
    if (!map || !title || !description || !index) return;

    var districts = {
      arnasoy: { name: { uz: 'Arnasoy tumani', ru: 'Арнасайский район' }, number: '01' },
      baxmal: { name: { uz: 'Baxmal tumani', ru: 'Бахмальский район' }, number: '02' },
      dustlik: { name: { uz: 'Do‘stlik tumani', ru: 'Дустликский район' }, number: '03' },
      forish: { name: { uz: 'Forish tumani', ru: 'Форишский район' }, number: '04' },
      gallaorol: { name: { uz: 'G‘allaorol tumani', ru: 'Галляаральский район' }, number: '05' },
      'jizzakh-city': { name: { uz: 'Jizzax shahri', ru: 'город Джизак' }, number: '06' },
      mirzachul: { name: { uz: 'Mirzacho‘l tumani', ru: 'Мирзачульский район' }, number: '07' },
      paxtakor: { name: { uz: 'Paxtakor tumani', ru: 'Пахтакорский район' }, number: '08' },
      'sharof-rashidov': { name: { uz: 'Sharof Rashidov tumani', ru: 'Шараф-Рашидовский район' }, number: '09' },
      yangiobod: { name: { uz: 'Yangiobod tumani', ru: 'Янгиабадский район' }, number: '10' },
      zafarobod: { name: { uz: 'Zafarobod tumani', ru: 'Зафарабадский район' }, number: '11' },
      zarbdor: { name: { uz: 'Zarbdor tumani', ru: 'Зарбдорский район' }, number: '12' },
      zomin: { name: { uz: 'Zomin tumani', ru: 'Зааминский район' }, number: '13' }
    };
    if (siteData.districts && Object.keys(siteData.districts).length) districts = siteData.districts;
    var defaultDescription = siteData.districtDefault || {
      uz: 'Ushbu hudud bo‘yicha reklama formatlari va mavjud joylar haqida batafsil maʼlumot olish uchun biz bilan bog‘laning.',
      ru: 'Свяжитесь с нами, чтобы узнать о рекламных форматах и доступных местах в этом районе.'
    };
    function selectDistrict(districtId) {
      var district = districts[districtId];
      if (!district) return;
      activeDistrictId = districtId;
      title.textContent = district.name[language];
      index.textContent = district.number;
      description.textContent = (district.description && district.description[language]) || defaultDescription[language];
      $$('.map-marker', map).forEach(function (marker) {
        var active = marker.dataset.district === districtId;
        var markerDistrict = districts[marker.dataset.district];
        marker.classList.toggle('is-active', active);
        marker.setAttribute('aria-pressed', active ? 'true' : 'false');
        if (markerDistrict) marker.setAttribute('aria-label', markerDistrict.name[language]);
      });
      $$('.district-list [data-district]').forEach(function (button) {
        var active = button.dataset.district === districtId;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    }
    renderSelectedDistrict = function () {
      $$('.map-marker', map).forEach(function (marker) {
        var district = districts[marker.dataset.district];
        if (district) marker.setAttribute('aria-label', district.name[language]);
      });
      if (activeDistrictId) selectDistrict(activeDistrictId);
    };
    renderSelectedDistrict();

    map.addEventListener('click', function (event) {
      var marker = event.target.closest('.map-marker');
      if (marker) selectDistrict(marker.dataset.district);
    });
    map.addEventListener('keydown', function (event) {
      var marker = event.target.closest('.map-marker');
      if (marker && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();
        selectDistrict(marker.dataset.district);
      }
    });
    $$('.district-list [data-district]').forEach(function (button) {
      button.addEventListener('click', function () {
        selectDistrict(button.dataset.district);
      });
    });

    var initialDistrict = window.location.hash.slice(1);
    if (districts[initialDistrict]) selectDistrict(initialDistrict);
  }

  function renderServiceDetail(serviceId) {
    var service = serviceCatalog[serviceId];
    var title = $('#service-modal-title');
    var description = $('#service-modal-description');
    var prices = $('#service-modal-prices');
    var image = $('#service-catalog-image');
    var closeButton = $('.service-modal-close');
    if (!service || !title || !description || !prices || !image) return;

    title.textContent = service.title[language];
    description.textContent = service.description[language];
    prices.textContent = '';
    service.prices.forEach(function (price) {
      var row = document.createElement('div');
      var label = document.createElement('dt');
      var value = document.createElement('dd');
      label.textContent = price.label[language];
      value.textContent = typeof price.value === 'string' ? price.value : price.value[language];
      row.appendChild(label);
      row.appendChild(value);
      prices.appendChild(row);
    });

    image.src = service.image;
    image.alt = service.title[language];
    if (closeButton) closeButton.setAttribute('aria-label', language === 'uz' ? 'Yopish' : 'Закрыть');
  }

  function initServiceCatalog() {
    var modal = $('#service-modal');
    if (!modal) return;

    var closeButton = $('.service-modal-close', modal);
    var previousFocus = null;
    var previousOverflow = '';

    function closeService() {
      if (!modal.classList.contains('open')) return;
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = previousOverflow;
      activeServiceId = null;
      if (previousFocus) previousFocus.focus();
    }

    function openService(serviceId, trigger) {
      if (!serviceCatalog[serviceId]) return false;
      activeServiceId = serviceId;
      previousFocus = trigger || document.activeElement;
      previousOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      renderServiceDetail(serviceId);
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      if (closeButton) closeButton.focus();
      return true;
    }

    document.addEventListener('click', function (event) {
      var trigger = event.target.closest('[data-service]');
      if (trigger && openService(trigger.dataset.service, trigger)) {
        event.preventDefault();
        return;
      }
      if (event.target === modal || event.target.closest('.service-modal-close')) closeService();
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeService();
    });

    var initialService = window.location.hash.slice(1);
    if (serviceCatalog[initialService]) openService(initialService, null);
  }

  function hidePreloader() {
    var preloader = $('#pre');
    if (!preloader) return;
    preloader.classList.add('done');
    window.setTimeout(function () {
      preloader.style.display = 'none';
    }, 450);
  }

  function startPageAnimations() {
    if (!window.gsap) {
      window.setTimeout(hidePreloader, 300);
      return;
    }

    if (window.ScrollTrigger) gsap.registerPlugin(ScrollTrigger);
    if ($('.ln>span')) {
      gsap.from('.ln>span', {
        yPercent: 110,
        duration: 0.8,
        stagger: 0.1,
        ease: 'power4.out'
      });
    }

    if (window.ScrollTrigger) {
      $$('.rv').forEach(function (element) {
        gsap.from(element, {
          y: 30,
          opacity: 0,
          duration: 0.7,
          ease: 'power3.out',
          scrollTrigger: { trigger: element, start: 'top 90%' }
        });
      });
      $$('.ct').forEach(function (element) {
        var counter = { value: 0 };
        gsap.to(counter, {
          value: Number(element.dataset.v),
          duration: 1.5,
          ease: 'power2.out',
          scrollTrigger: { trigger: element, start: 'top 92%' },
          onUpdate: function () {
            element.textContent = Math.round(counter.value);
          }
        });
      });
      if ($('.blob')) {
        gsap.to('.blob', {
          y: 140,
          scrollTrigger: { trigger: '.hero', scrub: true }
        });
      }
      gsap.to('#wm', {
        y: -220,
        rotate: 8,
        ease: 'none',
        scrollTrigger: { trigger: document.body, start: 'top top', end: 'bottom bottom', scrub: 1 }
      });
      gsap.to('#pb', {
        scaleX: 1,
        ease: 'none',
        scrollTrigger: { trigger: document.body, start: 'top top', end: 'bottom bottom', scrub: true }
      });
    }

    var preloader = $('#pre');
    if (preloader && gsap.timeline) {
      var timeline = gsap.timeline({ onComplete: hidePreloader });
      timeline
        .from('#pm .m1', { x: -70, y: -45, opacity: 0, duration: 0.55, ease: 'power3.out' })
        .from('#pm .m2', { x: -70, y: 60, opacity: 0, duration: 0.55, ease: 'power3.out' }, 0)
        .to(preloader, {
          clipPath: 'polygon(0 0,100% 0,100% 0,0 0)',
          duration: 0.45,
          ease: 'power4.inOut'
        }, 0.8);
      window.setTimeout(hidePreloader, 2200);
    } else {
      hidePreloader();
    }

    $$('.mg').forEach(function (button) {
      button.addEventListener('mousemove', function (event) {
        var rect = button.getBoundingClientRect();
        gsap.to(button, {
          x: (event.clientX - rect.left - rect.width / 2) * 0.2,
          y: (event.clientY - rect.top - rect.height / 2) * 0.2,
          duration: 0.25
        });
      });
      button.addEventListener('mouseleave', function () {
        gsap.to(button, { x: 0, y: 0, duration: 0.4, ease: 'elastic.out(1,.4)' });
      });
    });

    var logo = $('.lg');
    if (logo) {
      logo.addEventListener('mouseenter', function () {
        gsap.fromTo('.lg img', { x: -4 }, { x: 0, duration: 0.5, ease: 'elastic.out(1,.4)' });
      });
    }
  }

  function updateActiveNavigation() {
    var currentPage = document.body.dataset.page || 'home';
    $$('.lk [data-r]').forEach(function (link) {
      var active = link.dataset.r === currentPage;
      link.classList.toggle('on', active);
      if (active) link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
    });
  }

  function initPageTransitions() {
    document.addEventListener('click', function (event) {
      var link = event.target.closest('a[href]');
      if (!link || event.defaultPrevented || event.button !== 0 ||
          event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ||
          link.target || link.hasAttribute('download')) return;

      var destination;
      try {
        destination = new URL(link.href, window.location.href);
      } catch (error) {
        return;
      }
      if (destination.origin !== window.location.origin ||
          !/\.html$/i.test(destination.pathname) ||
          destination.href === window.location.href) return;

      event.preventDefault();
      var preloader = $('#pre');
      if (preloader) {
        preloader.style.display = 'grid';
        preloader.classList.remove('done');
        preloader.classList.add('leaving');
      }
      document.body.classList.add('page-leaving');
      window.setTimeout(function () {
        window.location.href = destination.href;
      }, 240);
    });
  }

  function initMobileMenu() {
    var menu = $('#oc');
    var openButton = $('#bg');
    var closeButton = $('#x');
    if (openButton && menu) {
      openButton.setAttribute('aria-expanded', 'false');
      openButton.addEventListener('click', function () {
        menu.classList.add('open');
        openButton.setAttribute('aria-expanded', 'true');
      });
    }
    if (closeButton && menu) {
      closeButton.addEventListener('click', function () {
        menu.classList.remove('open');
        if (openButton) openButton.setAttribute('aria-expanded', 'false');
      });
    }
    if (menu) {
      menu.addEventListener('click', function (event) {
        if (event.target.closest('a')) {
          menu.classList.remove('open');
          if (openButton) openButton.setAttribute('aria-expanded', 'false');
        }
      });
    }
  }

  $$('.dd > button').forEach(function (button) {
    button.setAttribute('aria-expanded', 'false');
    button.addEventListener('click', function () {
      var dropdown = button.closest('.dd');
      var open = dropdown.classList.toggle('open');
      button.setAttribute('aria-expanded', String(open));
    });
  });

  document.addEventListener('click', function (event) {
    if (event.target.closest('.dd')) return;
    $$('.dd.open').forEach(function (dropdown) {
      dropdown.classList.remove('open');
      var button = $('button', dropdown);
      if (button) button.setAttribute('aria-expanded', 'false');
    });
  });

  function initPortfolio() {
    var buttons = $$('#ft button');
    var tiles = $$('#pg [data-category]');
    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        buttons.forEach(function (item) {
          var active = item === button;
          item.classList.toggle('on', active);
          item.setAttribute('aria-pressed', String(active));
        });
        tiles.forEach(function (tile) {
          tile.hidden = button.dataset.f !== 'all' && tile.dataset.category !== button.dataset.f;
        });
      });
      button.setAttribute('aria-pressed', String(button.classList.contains('on')));
    });

    var modal = $('#md');
    var projectTitle = $('#project-title');
    tiles.forEach(function (tile) {
      tile.addEventListener('click', function () {
        if (!modal || !projectTitle) return;
        var label = $('span[data-uz][data-ru]', tile);
        projectTitle.dataset.uz = label ? label.dataset.uz : tile.textContent.trim();
        projectTitle.dataset.ru = label ? label.dataset.ru : tile.textContent.trim();
        projectTitle.textContent = projectTitle.getAttribute('data-' + language);
        var projectDescription = $('#project-desc');
        if (projectDescription) {
          projectDescription.dataset.uz = tile.dataset.descUz || projectDescription.dataset.defaultUz || projectDescription.dataset.uz;
          projectDescription.dataset.ru = tile.dataset.descRu || tile.dataset.descUz || projectDescription.dataset.defaultRu || projectDescription.dataset.ru;
          projectDescription.textContent = projectDescription.getAttribute('data-' + language);
        }
        modal.classList.add('open');
      });
    });
    if (modal) {
      modal.addEventListener('click', function (event) {
        if (event.target === modal || event.target.closest('#mc')) {
          modal.classList.remove('open');
        }
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') modal.classList.remove('open');
      });
    }
  }

  function initTestimonials() {
    var testimonials = $$('#tv .testimonial');
    if (!testimonials.length) return;
    var index = 0;
    function show(indexToShow) {
      index = (indexToShow + testimonials.length) % testimonials.length;
      testimonials.forEach(function (testimonial, itemIndex) {
        testimonial.classList.toggle('active', itemIndex === index);
      });
    }
    var next = $('#nx');
    var previous = $('#pv');
    if (next) next.addEventListener('click', function () { show(index + 1); });
    if (previous) previous.addEventListener('click', function () { show(index - 1); });
  }

  var languageButton = $('#lang');
  if (languageButton) {
    languageButton.addEventListener('click', function () {
      language = language === 'uz' ? 'ru' : 'uz';
      writeStorage('al', language);
      applyLanguage();
    });
  }

  var themeButton = $('#th');
  if (themeButton) {
    function updateThemeButton() {
      var isDark = document.documentElement.dataset.theme === 'dark';
      themeButton.textContent = isDark ? '☀' : '☾';
      themeButton.setAttribute('aria-pressed', String(isDark));
      themeButton.setAttribute('aria-label', isDark
        ? (language === 'uz' ? 'Kunduzgi rejimga oʻtish' : 'Переключить на дневную тему')
        : (language === 'uz' ? 'Tungi rejimga oʻtish' : 'Переключить на ночную тему'));
    }
    themeButton.addEventListener('click', function () {
      var root = document.documentElement;
      root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
      writeStorage('advertico-theme', root.dataset.theme);
      updateThemeButton();
    });
    updateThemeButton();
  }

  var mockup = $('#mk');
  if (mockup) {
    document.addEventListener('mousemove', function (event) {
      var x = (event.clientX / window.innerWidth - 0.5) * 16;
      var y = (event.clientY / window.innerHeight - 0.5) * -12;
      mockup.style.transform = 'rotateY(' + x + 'deg) rotateX(' + y + 'deg)';
    });
  }

  /* ===================== Dizayn 2026: header, LED ekran, slayderlar ===================== */
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function initHeaderState() {
    var header = $('#site-header');
    if (!header) return;
    var update = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
    window.addEventListener('scroll', update, { passive: true });
    update();
  }

  function initLedScreen() {
    var canvas = $('[data-led]');
    if (!canvas) return;
    var context = canvas.getContext('2d');
    if (!context) return;
    var gap = 7;
    var levels = 14;
    var width = 0;
    var height = 0;
    var frame = 0;
    var visible = true;
    var lastDraw = 0;
    var palette = [];
    for (var level = 0; level < levels; level += 1) {
      var t = level / (levels - 1);
      palette.push('rgba(' + Math.round(20 + 164 * t * t) + ',' + Math.round(70 + 162 * t) + ',' + Math.round(55 + 66 * t) + ',' + (0.18 + 0.82 * t).toFixed(2) + ')');
    }

    function resize() {
      var ratio = Math.min(window.devicePixelRatio || 1, 1.5);
      width = canvas.offsetWidth;
      height = canvas.offsetHeight;
      canvas.width = Math.max(1, Math.round(width * ratio));
      canvas.height = Math.max(1, Math.round(height * ratio));
      context.setTransform(ratio, 0, 0, ratio, 0, 0);
    }

    function draw(time) {
      var cols = Math.ceil(width / gap);
      var rows = Math.ceil(height / gap);
      var buckets = [];
      for (var b = 0; b < levels; b += 1) buckets.push([]);
      context.fillStyle = '#020604';
      context.fillRect(0, 0, width, height);
      var sweep = (time * 0.00012) % 1.4 - 0.2;
      for (var i = 0; i < cols; i += 1) {
        for (var j = 0; j < rows; j += 1) {
          var value = Math.sin(i * 0.17 + time * 0.0011) + Math.sin(j * 0.23 - time * 0.0008) + Math.sin((i + j) * 0.085 + time * 0.0005);
          var n = (value + 3) / 6;
          n = Math.pow(n, 1.6);
          var band = 1 - Math.min(1, Math.abs(i / cols - sweep) * 7);
          if (band > 0) n = Math.min(1, n + band * 0.35);
          buckets[Math.min(levels - 1, Math.floor(n * levels))].push(i * gap + 1.5, j * gap + 1.5);
        }
      }
      for (var k = 0; k < levels; k += 1) {
        var points = buckets[k];
        if (!points.length) continue;
        context.fillStyle = palette[k];
        context.beginPath();
        for (var p = 0; p < points.length; p += 2) context.rect(points[p], points[p + 1], 3.2, 3.2);
        context.fill();
      }
      var vignette = context.createRadialGradient(width / 2, height / 2, 0, width / 2, height / 2, width * 0.55);
      vignette.addColorStop(0, 'rgba(2,6,4,.6)');
      vignette.addColorStop(0.55, 'rgba(2,6,4,.15)');
      vignette.addColorStop(1, 'rgba(2,6,4,0)');
      context.fillStyle = vignette;
      context.fillRect(0, 0, width, height);
    }

    function loop(time) {
      frame = 0;
      if (!visible || document.hidden) return;
      if (time - lastDraw > 33) {
        draw(time);
        lastDraw = time;
      }
      frame = window.requestAnimationFrame(loop);
    }

    function start() {
      if (reduceMotion) { draw(4000); return; }
      if (!frame) frame = window.requestAnimationFrame(loop);
    }

    resize();
    draw(4000);
    window.addEventListener('resize', function () { resize(); draw(lastDraw || 4000); });
    document.addEventListener('visibilitychange', start);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        visible = entries[0].isIntersecting;
        if (visible) start();
      }).observe(canvas.closest('[data-scene]') || canvas);
    }
    start();
  }

  function initTracks() {
    $$('[data-track]').forEach(function (track) {
      var nav = $('[data-track-nav="' + track.dataset.track + '"]');
      if (!nav) return;
      var previous = $('[data-prev]', nav);
      var next = $('[data-next]', nav);
      var step = function () {
        var item = track.firstElementChild;
        var gapSize = parseFloat(window.getComputedStyle(track).columnGap) || 16;
        return item ? item.getBoundingClientRect().width + gapSize : track.clientWidth * 0.8;
      };
      var update = function () {
        var max = track.scrollWidth - track.clientWidth - 2;
        nav.style.visibility = max <= 0 ? 'hidden' : '';
        previous.disabled = track.scrollLeft <= 2;
        next.disabled = track.scrollLeft >= max;
      };
      previous.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: reduceMotion ? 'auto' : 'smooth' }); });
      next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: reduceMotion ? 'auto' : 'smooth' }); });
      track.addEventListener('scroll', update, { passive: true });
      window.addEventListener('resize', update);
      update();
    });
  }

  function initSceneParallax() {
    var scene = $('[data-scene]');
    var hero = scene ? scene.closest('.x-hero') : null;
    if (!scene || !hero || reduceMotion || !window.matchMedia('(pointer: fine)').matches) return;
    var pending = false;
    var x = 0;
    var y = 0;
    hero.addEventListener('pointermove', function (event) {
      x = (event.clientX / window.innerWidth - 0.5) * 2;
      y = (event.clientY / window.innerHeight - 0.5) * 2;
      if (pending) return;
      pending = true;
      window.requestAnimationFrame(function () {
        scene.style.setProperty('--px', x.toFixed(3));
        scene.style.setProperty('--py', y.toFixed(3));
        pending = false;
      });
    });
    hero.addEventListener('pointerleave', function () {
      scene.style.setProperty('--px', '0');
      scene.style.setProperty('--py', '0');
    });
  }

  function initSectionIndex() {
    var links = $$('.x-index a');
    if (!links.length || !('IntersectionObserver' in window)) return;
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        links.forEach(function (link) { link.classList.toggle('on', link.getAttribute('href') === '#' + entry.target.id); });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    links.forEach(function (link) {
      var target = document.getElementById(link.getAttribute('href').slice(1));
      if (target) observer.observe(target);
    });
  }

  initHeaderState();
  initLedScreen();
  initTracks();
  initSceneParallax();
  initSectionIndex();

  initCursorBackground();
  applyLanguage();
  updateActiveNavigation();
  initServiceCatalog();
  initDistrictMap();
  initPageTransitions();
  initMobileMenu();
  initPortfolio();
  initTestimonials();
  startPageAnimations();
})();
