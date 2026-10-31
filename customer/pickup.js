
(function () {
  'use strict';

  var form = document.getElementById('pickupForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      var errs = [];
      if (form.pickupAddress.value.trim().length < 10) errs.push('Pickup address must be at least 10 characters.');
      var d = form.pickupDate.value;
      if (!d) errs.push('Please choose a pickup date.');
      else {
        var today = new Date(); today.setHours(0, 0, 0, 0);
        if (new Date(d + 'T00:00:00') < today) errs.push('Pickup date cannot be in the past.');
      }
      if (!form.querySelector('input[name="preferredTimeWindow"]:checked')) errs.push('Please choose a preferred time window.');
      if (errs.length) {
        e.preventDefault();
        var box = document.getElementById('jsErrors');
        if (!box) {
          box = document.createElement('div');
          box.id = 'jsErrors';
          box.className = 'cu-alert cu-alert-error';
          form.parentNode.insertBefore(box, form);
        }
        box.innerHTML = '<strong>Please fix the following:</strong><ul>' +
          errs.map(function (m) { return '<li>' + m + '</li>'; }).join('') + '</ul>';
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  // Styled confirm dialog for any form with data-confirm
  var dlg = document.getElementById('cuConfirm');
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!dlg || typeof dlg.showModal !== 'function') {
        if (!confirm(f.dataset.confirm)) e.preventDefault();
        return;
      }
      e.preventDefault();
      document.getElementById('cuConfirmText').textContent = f.dataset.confirm;
      dlg.returnValue = '';
      dlg.showModal();
      dlg.addEventListener('close', function once() {
        dlg.removeEventListener('close', once);
        if (dlg.returnValue === 'ok') f.submit();
      });
    });
  });
})();
