<?php /* Shared by pickup_new.php and pickup_edit.php. Needs: $values, $errors, $action, $submitLabel */ ?>
<?php if (!empty($errors)): ?>
  <div class="cu-alert cu-alert-error"><strong>Please fix the following:</strong>
    <ul><?php foreach ($errors as $er): ?><li><?= cust_h($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form class="cu-form" id="pickupForm" method="post" action="<?= cust_h($action) ?>" novalidate>
  <input type="hidden" name="csrf" value="<?= cust_h(cust_csrf()) ?>">

  <div class="cu-field cu-full">
    <label for="pickupAddress">Pickup address</label>
    <textarea id="pickupAddress" name="pickupAddress" rows="3" maxlength="255" placeholder="House / building, street, city" required><?= cust_h($values['pickupAddress'] ?? '') ?></textarea>
    <small>Where should our driver collect the parcel?</small>
  </div>

  <div class="cu-field">
    <label for="pickupDate">Pickup date</label>
    <input id="pickupDate" name="pickupDate" type="date" min="<?= date('Y-m-d') ?>" value="<?= cust_h($values['pickupDate'] ?? '') ?>" required>
    <small>Today or a future date.</small>
  </div>

  <div class="cu-field cu-full">
    <label id="windowLabel">Preferred time window</label>
    <div class="cu-windows" role="radiogroup" aria-labelledby="windowLabel">
      <?php foreach (PICKUP_WINDOWS as $i => $w): ?>
        <input type="radio" id="win<?= $i ?>" name="preferredTimeWindow" value="<?= cust_h($w) ?>" <?= ($values['preferredTimeWindow'] ?? '') === $w ? 'checked' : '' ?>>
        <label for="win<?= $i ?>"><?= cust_h($w) ?></label>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="cu-form-actions">
    <button class="cu-btn cu-btn-primary" type="submit"><?= cust_h($submitLabel) ?></button>
    <a class="cu-btn cu-btn-light" href="pickups.php">Cancel</a>
  </div>
</form>
