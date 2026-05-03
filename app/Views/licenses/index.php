<?php
$licenses = $licenses ?? [];
$searchQuery = $searchQuery ?? '';
$msg = session()->getFlashdata('message');
$err = session()->getFlashdata('errors');
?>

<?php if ($msg) : ?>
    <p class="muted" role="status"><?= esc($msg) ?></p>
<?php endif; ?>
<?php if (is_array($err) && $err !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($err as $e) : ?>
                <li><?= esc(is_array($e) ? json_encode($e) : (string) $e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<p class="page-intro"><?= esc(lang('App.licenses_intro')) ?></p>

<div class="toolbar">
    <div class="toolbar__left toolbar__grow">
        <?= form_open(site_url('licenses'), ['method' => 'get', 'class' => 'toolbar__grow', 'style' => 'display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;']) ?>
            <input class="input toolbar__search" type="search" name="q" value="<?= esc($searchQuery, 'attr') ?>" placeholder="<?= esc(lang('App.licenses_search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('App.licenses_search_aria'), 'attr') ?>">
            <button type="submit" class="btn btn--secondary btn--sm"><?= esc(lang('App.action_search')) ?></button>
            <?php if ($searchQuery !== '') : ?>
                <a class="btn btn--ghost btn--sm" href="<?= site_url('licenses') ?>"><?= esc(lang('App.action_clear')) ?></a>
            <?php endif; ?>
        <?= form_close() ?>
    </div>
    <div class="toolbar__right">
        <?php if (user_can('licenses.create')) : ?>
            <a class="btn btn--primary" href="<?= site_url('licenses/create') ?>"><?= esc(lang('App.licenses_create')) ?></a>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th><?= esc(lang('App.licenses_col_work')) ?></th>
            <th><?= esc(lang('App.licenses_col_licensee')) ?></th>
            <th><?= esc(lang('App.licenses_col_type')) ?></th>
            <th><?= esc(lang('App.licenses_col_territory')) ?></th>
            <th><?= esc(lang('App.licenses_col_end')) ?></th>
            <th><?= esc(lang('App.licenses_col_fee')) ?></th>
            <th><?= esc(lang('App.licenses_col_status')) ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($licenses === []) : ?>
            <tr>
                <td colspan="8" class="muted"><?= esc(lang('App.licenses_empty')) ?></td>
            </tr>
        <?php else : ?>
            <?php foreach ($licenses as $r) : ?>
                <?php
                $id = (int) ($r['id'] ?? 0);
                $eff = \App\Models\LicenseModel::effectiveStatus($r);
                $effLabel = localized_license_status((string) $eff);
                $fee = (string) ($r['fee_amount'] ?? '0');
                $cur = (string) ($r['currency'] ?? 'USD');
                ?>
                <tr>
                    <td><?= esc((string) ($r['work_title'] ?? '—')) ?></td>
                    <td><?= esc((string) ($r['licensee_name'] ?? '—')) ?></td>
                    <td><?= esc(\App\Models\LicenseModel::licenseTypeLabel((string) ($r['license_type'] ?? ''))) ?></td>
                    <td><?= esc((string) ($r['territory'] ?? '—')) ?></td>
                    <td><?= esc($r['end_date'] !== null && $r['end_date'] !== '' ? (string) $r['end_date'] : '—') ?></td>
                    <td><?= esc($cur . ' ' . number_format((float) $fee, 2)) ?></td>
                    <td>
                        <?php
                        $tone = $eff === \App\Models\LicenseModel::STATUS_ACTIVE ? 'success' : ($eff === \App\Models\LicenseModel::STATUS_EXPIRING_SOON ? 'warning' : ($eff === \App\Models\LicenseModel::STATUS_EXPIRED ? 'danger' : 'neutral'));
                        echo view('components/badges', ['label' => $effLabel, 'tone' => $tone]);
                        ?>
                    </td>
                    <td class="table-actions">
                        <a class="btn btn--ghost btn--sm" href="<?= site_url('licenses/' . $id) ?>">View</a>
                        <?php if (user_can('licenses.update')) : ?>
                            <a class="btn btn--ghost btn--sm" href="<?= site_url('licenses/' . $id . '/edit') ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>
