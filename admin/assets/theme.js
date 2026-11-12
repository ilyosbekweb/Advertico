// Sahifa chizilishidan oldin mavzuni qo'llaymiz (miltillash bo'lmasligi uchun). Sayt bilan bir xil kalit.
(function () {
  try {
    var theme = localStorage.getItem('advertico-theme');
    document.documentElement.dataset.theme = theme === 'light' ? 'light' : 'dark';
  } catch (error) {
    document.documentElement.dataset.theme = 'dark';
  }
})();
