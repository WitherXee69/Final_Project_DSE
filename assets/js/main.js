/**
 * HELMS - Home page scripts
 */
(function () {
  'use strict';

  // Mobile menu
  document.getElementById('burger').addEventListener('click', function () {
    document.getElementById('menu').classList.toggle('open');
  });
  document.querySelectorAll('#menu a').forEach(function (a) {
    a.addEventListener('click', function () { document.getElementById('menu').classList.remove('open'); });
  });
  // Tidy tracking number input
  document.getElementById('trackForm').addEventListener('submit', function () {
    var el = document.getElementById('trackId');
    el.value = el.value.trim().toUpperCase();
  });
})();
