<?php
$f = $filters ?? [];
helper('url');
$preset = (string) ($f['preset'] ?? '30');
$df = (string) ($f['date_from'] ?? '');
$dt = (string) ($f['date_to'] ?? '');
$workTypes = $workTypes ?? [];
$licenseStatuses = $licenseStatuses ?? \App\Models\LicenseModel::LICENSE_STATUSES;
$caseStatuses = $caseStatuses ?? \App\Models\InfringementCaseModel::ALL_STATUSES;
$wt = (string) ($f['work_type'] ?? '');
$ls = (string) ($f['license_status'] ?? '');
$cs = (string) ($f['case_status'] ?? '');
?>
<form class="dashboard-filters" method="get" action="<?= esc(current_url(), 'attr') ?>" style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:flex-end;margin-bottom:1.25rem;padding:1rem;border:1px solid var(--border, #334155);border-radius:10px;background:var(--surface-2, #1e293b);">
    <div>
        <label for="rep-preset" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;"><?= esc(lang('App.date_range')) ?></label>
        <select id="rep-preset" name="preset" class="input-like" style="min-width:11rem;padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
            <option value="7" <?= $preset === '7' ? 'selected' : '' ?>><?= esc(lang('App.date_range_7')) ?></option>
            <option value="30" <?= $preset === '30' ? 'selected' : '' ?>><?= esc(lang('App.date_range_30')) ?></option>
            <option value="90" <?= $preset === '90' ? 'selected' : '' ?>><?= esc(lang('App.date_range_90')) ?></option>
            <option value="custom" <?= $preset === 'custom' ? 'selected' : '' ?>><?= esc(lang('App.date_range_custom')) ?></option>
        </select>
    </div>
    <div>
        <label for="rep-from" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;"><?= esc(lang('App.date_from')) ?></label>
        <input id="rep-from" type="date" name="date_from" value="<?= esc($df, 'attr') ?>" class="input-like" style="padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
    </div>
    <div>
        <label for="rep-to" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;"><?= esc(lang('App.date_to')) ?></label>
        <input id="rep-to" type="date" name="date_to" value="<?= esc($dt, 'attr') ?>" class="input-like" style="padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
    </div>
    <?php if ($workTypes !== []) : ?>
        <div>
            <label for="rep-wt" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;"><?= esc(lang('App.work_type')) ?></label>
            <select id="rep-wt" name="work_type" class="input-like" style="min-width:12rem;padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
                <option value=""><?= esc(lang('App.all_types')) ?></option>
                <?php foreach ($workTypes as $t) : ?>
                    <option value="<?= esc($t, 'attr') ?>" <?= $wt === $t ? 'selected' : '' ?>><?= esc($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <div>
        <label for="rep-ls" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;"><?= esc(lang('App.license_status')) ?></label>
        <select id="rep-ls" name="license_status" class="input-like" style="min-width:11rem;padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
            <option value=""><?= esc(lang('App.filter_all')) ?></option>
            <?php foreach ($licenseStatuses as $st) : ?>
                <option value="<?= esc($st, 'attr') ?>" <?= $ls === $st ? 'selected' : '' ?>><?= esc(localized_license_status($st)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="rep-cs" class="muted" style="display:block;font-size:0.8rem;margin-bottom:0.25rem;"><?= esc(lang('App.case_status')) ?></label>
        <select id="rep-cs" name="case_status" class="input-like" style="min-width:12rem;padding:0.45rem 0.6rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;">
            <option value=""><?= esc(lang('App.filter_all')) ?></option>
            <?php foreach ($caseStatuses as $st) : ?>
                <option value="<?= esc($st, 'attr') ?>" <?= $cs === $st ? 'selected' : '' ?>><?= esc(localized_case_status($st)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn--secondary btn--sm"><?= esc(lang('App.action_apply_filters')) ?></button>
</form>
<p class="muted" style="font-size:0.85rem;margin:-0.75rem 0 1rem;"><?= esc(lang('App.filters_hint_reports')) ?></p>
