<?php
$exportQuery = $exportQuery ?? '';
$csvUrl = site_url('reports/export/csv') . ($exportQuery !== '' ? '?' . $exportQuery : '');
$pdfUrl = site_url('reports/export/pdf') . ($exportQuery !== '' ? '?' . $exportQuery : '');
?>
<div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;align-items:center;">
    <span class="muted" style="font-size:0.85rem;">Export</span>
    <a class="btn btn--secondary btn--sm" href="<?= esc($csvUrl, 'attr') ?>">Download CSV</a>
    <a class="btn btn--secondary btn--sm" href="<?= esc($pdfUrl, 'attr') ?>">Download PDF</a>
</div>
