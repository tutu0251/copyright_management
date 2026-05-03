<?php
$exportQuery = $exportQuery ?? '';
$csvUrl = site_url('reports/export/csv') . ($exportQuery !== '' ? '?' . $exportQuery : '');
$pdfUrl = site_url('reports/export/pdf') . ($exportQuery !== '' ? '?' . $exportQuery : '');
?>
<div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;align-items:center;">
    <span class="muted" style="font-size:0.85rem;"><?= esc(lang('App.reports_export_label')) ?></span>
    <a class="btn btn--secondary btn--sm" href="<?= esc($csvUrl, 'attr') ?>"><?= esc(lang('App.reports_export_csv')) ?></a>
    <a class="btn btn--secondary btn--sm" href="<?= esc($pdfUrl, 'attr') ?>"><?= esc(lang('App.reports_export_pdf')) ?></a>
</div>
