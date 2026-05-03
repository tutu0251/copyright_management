<?php
/** @var array<string, mixed> $work */
$errors = $errors ?? [];
$types = ['Image', 'Audio', 'Video', 'Text', 'Software', 'Design', 'Course'];
$statuses = [
    'draft'            => 'Draft',
    'registered'       => 'Registered',
    'pending_review'   => 'Pending review',
    'under_audit'      => 'Under audit',
];
$wid = (int) ($work['id'] ?? 0);
$files = $files ?? [];
?>

<?php if ($errors !== []) : ?>
    <div class="card" style="margin-bottom: 1rem; border-color: var(--cm-danger, #c44);">
        <h2 class="card__title">Please fix the following</h2>
        <ul class="muted" style="margin:0;padding-left:1.25rem;">
            <?php foreach ($errors as $err) : ?>
                <li><?= esc(is_array($err) ? json_encode($err) : (string) $err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<p class="page-intro">Update catalog metadata or attach additional evidence. Existing files remain on record until removed in a future release.</p>

<div class="toolbar">
    <div class="toolbar__left">
        <a class="btn btn--secondary btn--sm" href="<?= site_url('works/' . $wid) ?>">← Back to asset</a>
    </div>
    <div class="toolbar__right"></div>
</div>

<div class="card" style="max-width: 880px;">
    <h2 class="card__title">Work metadata</h2>
    <?= form_open_multipart(site_url('works/' . $wid . '/update'), ['class' => 'stack']) ?>
        <div class="form-grid">
            <div class="field">
                <label for="title">Title <span aria-hidden="true">*</span></label>
                <input class="input" id="title" name="title" type="text" required value="<?= esc(old('title', (string) ($work['title'] ?? '')), 'attr') ?>">
            </div>
            <div class="field">
                <label for="work_type">Work type <span aria-hidden="true">*</span></label>
                <select class="select" id="work_type" name="work_type" required>
                    <?php foreach ($types as $t) : ?>
                        <option value="<?= esc($t, 'attr') ?>" <?= old('work_type', (string) ($work['work_type'] ?? 'Text')) === $t ? 'selected' : '' ?>><?= esc($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="owner">Rights owner</label>
                <input class="input" id="owner" name="owner" type="text" value="<?= esc(old('owner', (string) ($work['owner'] ?? '')), 'attr') ?>">
            </div>
            <div class="field">
                <label for="registered_at">Registration date</label>
                <?php
                $reg = old('registered_at', (string) ($work['registered_at'] ?? ''));
                $reg = $reg !== '' && $reg !== '0000-00-00' ? substr($reg, 0, 10) : '';
                ?>
                <input class="input" id="registered_at" name="registered_at" type="date" value="<?= esc($reg, 'attr') ?>">
            </div>
            <div class="field">
                <label for="copyright_status">Copyright status <span aria-hidden="true">*</span></label>
                <select class="select" id="copyright_status" name="copyright_status" required>
                    <?php foreach ($statuses as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('copyright_status', (string) ($work['copyright_status'] ?? 'draft')) === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="risk_level">Risk level <span aria-hidden="true">*</span></label>
                <select class="select" id="risk_level" name="risk_level" required>
                    <?php foreach (['Low', 'Medium', 'High'] as $r) : ?>
                        <option value="<?= esc($r, 'attr') ?>" <?= old('risk_level', (string) ($work['risk_level'] ?? 'Low')) === $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="field">
            <label for="description">Description</label>
            <textarea class="textarea" id="description" name="description" rows="4"><?= esc(old('description', (string) ($work['description'] ?? ''))) ?></textarea>
        </div>
        <div class="field">
            <label for="creator">Creators / authors</label>
            <input class="input" id="creator" name="creator" type="text" value="<?= esc(old('creator', (string) ($work['creator'] ?? '')), 'attr') ?>" placeholder="Comma-separated names">
        </div>
        <div class="field">
            <label for="evidence_files">Add evidence files</label>
            <input id="evidence_files" name="evidence_files[]" type="file" multiple accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.mp3,.wav,.mp4,.txt,.csv,.doc,.docx,.zip">
            <p class="muted" style="margin:0.35rem 0 0;">Additional files only — same limits as registration (10&nbsp;MiB total per save).</p>
        </div>
        <div class="toolbar" style="margin: 0; padding-top: 0.5rem;">
            <div class="toolbar__left"></div>
            <div class="toolbar__right">
                <a class="btn btn--secondary" href="<?= site_url('works/' . $wid) ?>">Cancel</a>
                <button type="submit" class="btn btn--primary">Save changes</button>
            </div>
        </div>
    <?= form_close() ?>
</div>

<?php if ($files !== []) : ?>
<div class="card" style="max-width: 880px; margin-top: 1rem;">
    <h2 class="card__title">Files on record</h2>
    <?= $this->include('components/table') ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Original name</th>
                <th>Stored name</th>
                <th>Size</th>
                <th>MIME</th>
                <th>SHA-256</th>
                <th>Uploaded</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($files as $f) : ?>
                <tr>
                    <td><?= esc((string) ($f['original_filename'] ?? '')) ?></td>
                    <td><code><?= esc((string) ($f['stored_filename'] ?? '')) ?></code></td>
                    <td><?= esc(number_format((int) ($f['size_bytes'] ?? 0))) ?> B</td>
                    <td><?= esc((string) ($f['mime_type'] ?? '—')) ?></td>
                    <td><code style="font-size:0.75rem;"><?= esc(substr((string) ($f['sha256'] ?? ''), 0, 16)) ?>…</code></td>
                    <td><?= esc((string) ($f['created_at'] ?? '—')) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->include('components/table_end') ?>
</div>
<?php endif; ?>

<div class="card" style="max-width: 880px; margin-top: 1rem;">
    <h2 class="card__title">Archive work</h2>
    <p class="muted">Soft-deletes this catalog row. Linked licenses remain in the database; adjust them separately if required.</p>
    <?= form_open(site_url('works/' . $wid . '/delete'), ['onsubmit' => "return confirm('Archive this work?');"]) ?>
        <button type="submit" class="btn btn--secondary">Archive work</button>
    <?= form_close() ?>
</div>
