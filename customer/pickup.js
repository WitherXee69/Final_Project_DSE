/** HELMS - customer pages: mobile menu, form checks (the server re-validates), cancel dialog */
(function () {
  'use strict';

  // ---- Mobile menu (same behaviour as the home page)
  var burger = document.getElementById('burger'), menu = document.getElementById('menu');
  if (burger && menu) burger.addEventListener('click', function () { menu.classList.toggle('open'); });

  var form = document.getElementById('pickupForm');

  // ---- Form validation (mirrors pickup_validate())
  if (form) {
    form.addEventListener('submit', function (e) {
      var errs = [];
      var len = function (n) { return form.elements[n].value.trim().length; };
      var num = function (n) { return parseFloat(form.elements[n].value); };

      if (len('pickupAddress') < 10) errs.push('Pickup address must be at least 10 characters.');
      var d = form.elements.pickupDate.value;
      if (!d) errs.push('Please choose a pickup date.');
      else {
        var today = new Date(); today.setHours(0, 0, 0, 0);
        if (new Date(d + 'T00:00:00') < today) errs.push('Pickup date cannot be in the past.');
      }
      if (!form.querySelector('input[name="preferredTimeWindow"]:checked')) errs.push('Please choose a preferred time window.');

      if (len('description') < 3) errs.push('Please describe the parcel.');
      if (!form.elements.parcelCategory.value) errs.push('Please choose a parcel category.');
      if (!form.elements.serviceType.value) errs.push('Please choose a delivery service.');
      if (!(num('declaredWeight') > 0)) errs.push('Parcel weight must be a number above 0.');
      var dims = ['dimL', 'dimW', 'dimH'].filter(function (n) { return form.elements[n].value.trim() !== ''; });
      if (dims.length && (dims.length !== 3 || dims.some(function (n) { return !(num(n) > 0); })))
        errs.push('Enter all three dimensions as positive numbers, or leave all blank.');

      if (len('receiverName') < 2) errs.push('Please enter the receiver name.');
      if (!/^\+?[0-9][0-9 \-]{6,18}$/.test(form.elements.receiverPhone.value.trim())) errs.push('Enter a valid receiver phone number.');
      if (len('receiverAddress') < 10) errs.push('Receiver address must be at least 10 characters.');
      if (!form.elements.zoneCode.value) errs.push('Please choose the receiver\'s delivery zone.');

      if (errs.length) {
        e.preventDefault();
        var boxErr = document.getElementById('jsErrors');
        if (!boxErr) {
          boxErr = document.createElement('div');
          boxErr.id = 'jsErrors';
          boxErr.className = 'cu-notice cu-notice-error';
          form.parentNode.insertBefore(boxErr, form);
        }
        var ul = document.createElement('ul');
        errs.forEach(function (m) { var li = document.createElement('li'); li.textContent = m; ul.appendChild(li); });
        boxErr.innerHTML = '<strong>Please fix the following:</strong>';
        boxErr.appendChild(ul);
        if (boxErr.scrollIntoView) boxErr.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    });
  }

  // ---- Styled confirm dialog for any form carrying data-confirm
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
