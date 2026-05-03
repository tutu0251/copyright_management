<?php
/**
 * KPI / stat card — expects: $kpi_label, $kpi_value, $kpi_hint, optional $kpi_key for icon class
 */
$kpi_key   = $kpi_key ?? 'default';
$kpi_href  = $kpi_href ?? null;
?>
<div class="ui-kpi" data-kpi="<?= esc($kpi_key, 'attr') ?>">
    <div class="ui-kpi__top">
        <span class="ui-kpi__label"><?= esc($kpi_label) ?></span>
    </div>
    <div class="ui-kpi__value">
        <?php if (! empty($kpi_href)) : ?>
            <a href="<?= esc($kpi_href, 'url') ?>" style="color: inherit; text-decoration: none;"><?= esc($kpi_value) ?></a>
        <?php else : ?>
            <?= esc($kpi_value) ?>
        <?php endif; ?>
    </div>
    <p class="ui-kpi__hint muted"><?= esc($kpi_hint) ?></p>
</div>
