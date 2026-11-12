(function () {
  var form = document.getElementById('contact-form');
  if (!form) return;

  var statusBox = document.getElementById('form-status');
  var submitButton = document.getElementById('sd');
  var buttonLabel = submitButton ? submitButton.querySelector('span') : null;

  // Forma xabarlari admin paneldan keladi (#site-data); bo'lmasa standart matnlar.
  var texts = {};
  try {
    var dataElement = document.getElementById('site-data');
    texts = (dataElement && JSON.parse(dataElement.textContent).form) || {};
  } catch (error) {
    texts = {};
  }

  function currentLanguage() {
    return document.documentElement.lang === 'ru' ||
      (document.documentElement.dataset.lang === 'ru') ? 'ru' : 'uz';
  }

  function message(key, uz, ru) {
    var lang = currentLanguage();
    if (texts[key] && texts[key][lang]) return texts[key][lang];
    return lang === 'ru' ? ru : uz;
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!form.reportValidity()) return;

    if (submitButton) submitButton.disabled = true;
    if (buttonLabel) buttonLabel.textContent = message('sending', 'Yuborilmoqda...', 'Отправка...');
    if (statusBox) statusBox.textContent = '';

    try {
      var body = new FormData(form);
      body.append('lang', currentLanguage());
      var response = await fetch('api/contact.php', {
        method: 'POST',
        body: body,
        headers: { 'Accept': 'application/json' }
      });
      var result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || message('error', 'Yuborishda xatolik yuz berdi.', 'Не удалось отправить заявку.'));
      }

      form.reset();
      if (statusBox) statusBox.textContent = message(
        'success',
        'Murojaatingiz qabul qilindi. Tez orada siz bilan bog‘lanamiz.',
        'Ваша заявка принята. Мы скоро свяжемся с вами.'
      );
    } catch (error) {
      if (statusBox) statusBox.textContent = (error && error.name !== 'SyntaxError' && error.message) || message(
        'network',
        'Server bilan bog‘lanib bo‘lmadi. Keyinroq urinib ko‘ring.',
        'Не удалось связаться с сервером. Попробуйте позже.'
      );
    } finally {
      if (submitButton) submitButton.disabled = false;
      if (buttonLabel) buttonLabel.textContent = message('submit', 'Ariza qoldirish', 'Оставить заявку');
    }
  });
})();
