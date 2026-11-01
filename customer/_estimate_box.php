<?php
/* Live cost estimate panel (Customer.viewCostEstimate). Needs $rules from estimate_rules(). pickup.js fills it in. */
$jsRules = array_map(fn($r) => [
    's' => $r['serviceType'], 'z' => $r['zoneCode'], 'min' => (float)$r['minWeight'], 'max' => (float)$r['maxWeight'],
    'base' => (float)$r['baseFee'], 'perKg' => (float)$r['additionalPerKgRate'], 'frag' => (float)$r['fragileSurcharge'],
], $rules);
?>
<aside class="cu-card cu-estimate" id="estBox" data-rules="<?= cust_h(json_encode($jsRules)) ?>">
  <div class="cu-card-head"><h2>Cost estimate</h2></div>
  <div id="estEmpty" class="cu-muted">Choose a delivery service and enter the parcel weight to see your estimated delivery cost.</div>
  <div id="estNone" class="cu-muted" hidden>We have no standard price for this service and weight. You can still submit the request and our team will confirm the cost.</div>
  <dl id="estLines" class="cu-estlines" hidden>
    <div><dt>Base charge</dt><dd id="estBase">–</dd></div>
    <div><dt>Surcharges</dt><dd id="estSur">–</dd></div>
    <div class="cu-esttotal"><dt>Estimated total</dt><dd id="estTotal">–</dd></div>
  </dl>
  <p class="cu-muted cu-estnote">Estimate only. The final charge is confirmed after the warehouse weighs your parcel.</p>
</aside>
