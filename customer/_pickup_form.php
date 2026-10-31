<?php /* Shared by pickup_new.php and pickup_edit.php. Needs: $values, $errors, $action, $submitLabel */ ?>
<?php if (!empty($errors)): ?>
  <div class="cu-notice cu-notice-error"><strong>Please fix the following:</strong>
    <ul><?php foreach ($errors as $er): ?><li><?= cust_h($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form class="login-form" id="pickupForm" method="post" action="<?= cust_h($action) ?>" novalidate>
  <input type="hidden" name="csrf" value="<?= cust_h(cust_csrf()) ?>">

  <div class="form-row">
    <label for="pickupAddress">Pickup address</label>
    <textarea id="pickupAddress" name="pickupAddress" rows="3" maxlength="255" placeholder="House / building, street, city" required><?= cust_h($values['pickupAddress'] ?? '') ?></textarea>
  </div>

  <div class="form-row">
    <label for="pickupDate">Pickup date</label>
    <input id="pickupDate" name="pickupDate" type="date" min="<?= date('Y-m-d') ?>" value="<?= cust_h($values['pickupDate'] ?? '') ?>" required>
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

  <button class="btn btn-primary login-submit" type="submit"><?= cust_h($submitLabel) ?> <span aria-hidden="true">→</span></button>
</form>
