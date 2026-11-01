<?php
/* Shared by pickup_new.php and pickup_edit.php.
   Needs: $values (cleaned form values), $errors, $action, $submitLabel, $zones, $services */
$v = fn(string $k) => cust_h($values[$k] ?? '');
?>
<?php if (!empty($errors)): ?>
  <div class="cu-notice cu-notice-error"><strong>Please fix the following:</strong>
    <ul><?php foreach ($errors as $er): ?><li><?= cust_h($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form class="login-form cu-fform" id="pickupForm" method="post" action="<?= cust_h($action) ?>" novalidate>
  <input type="hidden" name="csrf_token" value="<?= cust_h(cust_csrf()) ?>">

  <!-- 1. Pickup (Pickup class) -->
  <fieldset class="cu-fsec">
    <legend><span class="cu-step-no">1</span> Pickup details</legend>
    <div class="cu-fgrid">
      <div class="form-row cu-span2">
        <label for="pickupAddress">Pickup address</label>
        <textarea id="pickupAddress" name="pickupAddress" rows="2" maxlength="255" placeholder="House / building, street, city" required><?= $v('pickupAddress') ?></textarea>
      </div>
      <div class="form-row">
        <label for="pickupDate">Pickup date</label>
        <input id="pickupDate" name="pickupDate" type="date" min="<?= date('Y-m-d') ?>" value="<?= $v('pickupDate') ?>" required>
      </div>
      <div class="form-row">
        <label id="windowLabel">Preferred time window</label>
        <div class="cu-windows" role="radiogroup" aria-labelledby="windowLabel">
          <?php foreach (PICKUP_WINDOWS as $i => $w): ?>
            <input type="radio" id="win<?= $i ?>" name="preferredTimeWindow" value="<?= cust_h($w) ?>" <?= ($values['preferredTimeWindow'] ?? '') === $w ? 'checked' : '' ?>>
            <label for="win<?= $i ?>"><?= cust_h($w) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </fieldset>

  <!-- 2. Parcel (Parcel class: recordParcelDetails) -->
  <fieldset class="cu-fsec">
    <legend><span class="cu-step-no">2</span> Parcel details</legend>
    <div class="cu-fgrid">
      <div class="form-row cu-span2">
        <label for="description">What are you sending?</label>
        <input id="description" name="description" type="text" maxlength="255" placeholder="e.g. Ceramic tableware set" value="<?= $v('description') ?>" required>
      </div>
      <div class="form-row">
        <label for="parcelCategory">Category</label>
        <select id="parcelCategory" name="parcelCategory" required>
          <option value="">Select a category</option>
          <?php foreach (PARCEL_CATEGORIES as $k => $label): ?>
            <option value="<?= cust_h($k) ?>" <?= ($values['parcelCategory'] ?? '') === $k ? 'selected' : '' ?>><?= cust_h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row">
        <label for="serviceType">Delivery service</label>
        <select id="serviceType" name="serviceType" required>
          <option value="">Select a service</option>
          <?php foreach ($services as $k => $label): ?>
            <option value="<?= cust_h($k) ?>" <?= ($values['serviceType'] ?? '') === $k ? 'selected' : '' ?>><?= cust_h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row">
        <label for="declaredWeight">Weight (kg)</label>
        <input id="declaredWeight" name="declaredWeight" type="number" step="0.01" min="0.01" max="1000" placeholder="e.g. 2.5" value="<?= $v('declaredWeight') ?>" required>
      </div>
      <div class="form-row">
        <label>Size in cm (optional)</label>
        <div class="cu-dims">
          <input name="dimL" type="number" step="0.1" min="0.1" placeholder="Length" aria-label="Length in cm" value="<?= $v('dimL') ?>">
          <span>×</span>
          <input name="dimW" type="number" step="0.1" min="0.1" placeholder="Width" aria-label="Width in cm" value="<?= $v('dimW') ?>">
          <span>×</span>
          <input name="dimH" type="number" step="0.1" min="0.1" placeholder="Height" aria-label="Height in cm" value="<?= $v('dimH') ?>">
        </div>
      </div>
      <label class="cu-check cu-span2" for="isFragile">
        <input id="isFragile" name="isFragile" type="checkbox" value="1" <?= !empty($values['isFragile']) ? 'checked' : '' ?>>
        <span><b>Fragile</b> &ndash; handle with care</span>
      </label>
    </div>
  </fieldset>

  <!-- 3. Receiver (Receiver class) -->
  <fieldset class="cu-fsec">
    <legend><span class="cu-step-no">3</span> Receiver details</legend>
    <div class="cu-fgrid">
      <div class="form-row">
        <label for="receiverName">Receiver name</label>
        <input id="receiverName" name="receiverName" type="text" maxlength="150" value="<?= $v('receiverName') ?>" required>
      </div>
      <div class="form-row">
        <label for="receiverPhone">Receiver phone</label>
        <input id="receiverPhone" name="receiverPhone" type="tel" maxlength="30" placeholder="+94 77 123 4567" value="<?= $v('receiverPhone') ?>" required>
      </div>
      <div class="form-row cu-span2">
        <label for="receiverAddress">Delivery address</label>
        <textarea id="receiverAddress" name="receiverAddress" rows="2" maxlength="255" placeholder="House / building, street, city" required><?= $v('receiverAddress') ?></textarea>
      </div>
      <div class="form-row">
        <label for="zoneCode">Delivery zone</label>
        <select id="zoneCode" name="zoneCode" required>
          <option value="">Select a zone</option>
          <?php foreach ($zones as $code => $label): ?>
            <option value="<?= cust_h($code) ?>" <?= ($values['zoneCode'] ?? '') === $code ? 'selected' : '' ?>><?= cust_h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </fieldset>

  <div class="cu-form-actions">
    <button class="btn btn-primary" type="submit"><?= cust_h($submitLabel) ?> <span aria-hidden="true">→</span></button>
    <a class="btn btn-outline" href="<?= cust_h($backUrl ?? 'pickups.php') ?>">Back</a>
  </div>
</form>
