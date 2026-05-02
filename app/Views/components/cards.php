<?php
/**
 * KPI / stat card — expects: $kpi_label, $kpi_value, $kpi_hint, optional $kpi_key for icon class
 */
$kpi_key = $kpi_key ?? 'default';
?>
<div class="ui-kpi" data-kpi="<?= esc($kpi_key, 'attr') ?>">
    <div class="ui-kpi__top">
        <span class="ui-kpi__label"><?= esc($kpi_label) ?></span>
    </div>
    <div class="ui-kpi__value"><?= esc($kpi_value) ?></div>
    <p class="ui-kpi__hint muted"><?= esc($kpi_hint) ?></p>
</div>
