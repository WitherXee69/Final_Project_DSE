
/**
 * HELMS - Home page scripts
 */
(function () {
  'use strict';

  document.getElementById('burger').addEventListener('click', function () {
    document.getElementById('menu').classList.toggle('open');
  });

  document.querySelectorAll('#menu a').forEach(function (a) {
    a.addEventListener('click', function () {
      document.getElementById('menu').classList.remove('open');
    });
  });
})();
