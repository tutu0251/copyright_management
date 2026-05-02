<?php
$licenseTone = static function (array $lic): string {
    $st = (string) ($lic['status'] ?? '');
    if (strcasecmp($st, 'Active') === 0) {
        $exp   = (string) ($lic['expires'] ?? '');
        $expTs = $exp !== '' ? strtotime($exp) : false;
        if ($expTs !== false && $expTs < strtotime('+90 days') && $expTs >= strtotime('today')) {
            return 'warning';
        }

        return 'success';
    }
    if (preg_match('/pending|expir/i', $st) === 1) {
        return 'warning';
    }
    if (preg_match('/expired|terminated|revoked|cancel|archiv|draft/i', $st) === 1) {
        return 'neutral';
    }

    return 'neutral';
};
?>

<p class="page-intro">License inventory with status badges. Actions remain disabled in this static mock.</p>

<div class="toolbar">
    <div class="toolbar__left">
        <button type="button" class="btn btn--primary" disabled>New license</button>
        <button type="button" class="btn btn--secondary" disabled>Bulk renew</button>
    </div>
    <div class="toolbar__right">
        <select class="select" aria-label="Status filter (mock)">
            <option>All statuses</option>
            <option>Active</option>
            <option>Expired</option>
        </select>
        <input class="input" type="search" placeholder="Search licensee…" style="min-width: 200px;" aria-label="Search licensee (mock)">
    </div>
</div>

<?= $this->include('components/table') ?>
<table class="data-table">
    <thead>
        <tr>
            <th class="sortable"><span class="th-sort">License ID <span class="sort-ico">↕</span></span></th>
            <th>Work</th>
            <th>Licensee</th>
            <th>Type</th>
            <th>Status</th>
            <th class="sortable"><span class="th-sort">Expires <span class="sort-ico">↕</span></span></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($licenses as $lic) : ?>
            <tr>
                <td><?= esc($lic['id']) ?></td>
                <td>
                    <a href="<?= site_url('mockup/work/' . $lic['work_id']) ?>"><?= esc($lic['work_title']) ?></a>
                </td>
                <td><?= esc($lic['licensee']) ?></td>
                <td><?= esc($lic['type']) ?></td>
                <td><?php echo view('components/badges', ['label' => (string) $lic['status'], 'tone' => $licenseTone($lic)]); ?></td>
                <td><?= esc($lic['expires']) ?></td>
                <td class="table-actions">
                    <a class="btn btn--ghost btn--sm" href="<?= site_url('mockup/license/' . $lic['id']) ?>">Open</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->include('components/table_end') ?>
