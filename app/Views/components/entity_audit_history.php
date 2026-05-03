<?php
/** @var list<array<string, mixed>> $auditHistory */
$auditHistory = $auditHistory ?? [];
$sectionTitle = $sectionTitle ?? 'Activity / history';
$moreUrl = $moreUrl ?? null;
?>

<div class="card" style="margin-top: 1rem;">
    <h2 class="card__title"><?= esc($sectionTitle) ?></h2>
    <p class="muted" style="margin-top: 0;">System audit trail for this record (authentication, changes, and status updates).</p>
    <?php if ($auditHistory === []) : ?>
        <p class="muted" style="margin: 0;">No audit entries for this record yet.</p>
    <?php else : ?>
        <div class="table-wrap table-wrap--flush" style="margin-top: 0.75rem;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditHistory as $log) : ?>
                        <?php
                        $actor = (string) ($log['actor_name'] ?? '') !== ''
                            ? (string) $log['actor_name']
                            : ((string) ($log['actor_email'] ?? '') !== '' ? (string) $log['actor_email'] : '—');
                        $action = (string) ($log['action_type'] ?? '');
                        $oldV = $log['old_values'] ?? null;
                        $newV = $log['new_values'] ?? null;
                        $oldS = is_string($oldV) ? $oldV : (is_array($oldV) ? json_encode($oldV, JSON_UNESCAPED_UNICODE) : '');
                        $newS = is_string($newV) ? $newV : (is_array($newV) ? json_encode($newV, JSON_UNESCAPED_UNICODE) : '');
                        $parts = [];
                        if ($oldS !== '' && $oldS !== '[]' && $oldS !== 'null') {
                            $t = strlen($oldS) > 140 ? substr($oldS, 0, 140) . '…' : $oldS;
                            $parts[] = 'Before: ' . $t;
                        }
                        if ($newS !== '' && $newS !== '[]' && $newS !== 'null') {
                            $t = strlen($newS) > 140 ? substr($newS, 0, 140) . '…' : $newS;
                            $parts[] = 'After: ' . $t;
                        }
                        $detailText = $parts !== [] ? implode(' · ', $parts) : '—';
                        ?>
                        <tr>
                            <td><?= esc((string) ($log['created_at'] ?? '')) ?></td>
                            <td><?= esc($actor) ?></td>
                            <td><code><?= esc($action) ?></code></td>
                            <td class="muted" style="font-size: 0.85rem; max-width: 28rem;"><?= esc($detailText) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php if ($moreUrl !== null && $moreUrl !== '') : ?>
        <div style="margin-top: 0.75rem;">
            <a class="btn btn--ghost btn--sm" href="<?= esc($moreUrl, 'attr') ?>">Open full activity log</a>
        </div>
    <?php endif; ?>
</div>
