(function () {
  var form = document.getElementById('contact-form');
  if (!form) return;

  var statusBox = document.getElementById('form-status');
  var submitButton = document.getElementById('sd');
  var buttonLabel = submitButton ? submitButton.querySelector('span') : null;

  function currentLanguage() {
    return document.documentElement.lang === 'ru' ||
      (document.documentElement.dataset.lang === 'ru') ? 'ru' : 'uz';
  }

  function message(uz, ru) {
    return currentLanguage() === 'ru' ? ru : uz;
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!form.reportValidity()) return;

    if (submitButton) submitButton.disabled = true;
    if (buttonLabel) buttonLabel.textContent = message('Yuborilmoqda...', 'Отправка...');
    if (statusBox) statusBox.textContent = '';

    try {
      var response = await fetch('api/contact.php', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json' }
      });
      var result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || message('Yuborishda xatolik yuz berdi.', 'Не удалось отправить заявку.'));
      }

      form.reset();
      if (statusBox) statusBox.textContent = message(
        'Murojaatingiz qabul qilindi. Tez orada siz bilan bog‘lanamiz.',
        'Ваша заявка принята. Мы скоро свяжемся с вами.'
      );
    } catch (error) {
      if (statusBox) statusBox.textContent = error.message || message(
        'Server bilan bog‘lanib bo‘lmadi. Keyinroq urinib ko‘ring.',
        'Не удалось связаться с сервером. Попробуйте позже.'
      );
    } finally {
      if (submitButton) submitButton.disabled = false;
      if (buttonLabel) buttonLabel.textContent = message('Ariza qoldirish', 'Оставить заявку');
    }
  });
})();
