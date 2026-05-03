<?php
$errors = $errors ?? [];
$types = ['Image', 'Audio', 'Video', 'Text', 'Software', 'Design', 'Course'];
$statuses = [
    'draft'            => 'Draft',
    'registered'       => 'Registered',
    'pending_review'   => 'Pending review',
    'under_audit'      => 'Under audit',
];
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

<p class="page-intro">Create a catalog record with metadata and optional evidence uploads. Files are stored under <code>writable/uploads/works/</code> and fingerprinted with SHA-256.</p>

<div class="card" style="max-width: 880px;">
    <h2 class="card__title">Work metadata</h2>
    <?= form_open_multipart(site_url('works'), ['class' => 'stack']) ?>
        <div class="form-grid">
            <div class="field">
                <label for="title">Title <span aria-hidden="true">*</span></label>
                <input class="input" id="title" name="title" type="text" required value="<?= esc(old('title', ''), 'attr') ?>" placeholder="e.g. Meridian Annual Report 2026">
            </div>
            <div class="field">
                <label for="work_type">Work type <span aria-hidden="true">*</span></label>
                <select class="select" id="work_type" name="work_type" required>
                    <?php foreach ($types as $t) : ?>
                        <option value="<?= esc($t, 'attr') ?>" <?= old('work_type', 'Text') === $t ? 'selected' : '' ?>><?= esc($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="owner">Rights owner</label>
                <input class="input" id="owner" name="owner" type="text" value="<?= esc(old('owner', ''), 'attr') ?>" placeholder="Legal entity name">
            </div>
            <div class="field">
                <label for="registered_at">Registration date</label>
                <input class="input" id="registered_at" name="registered_at" type="date" value="<?= esc(old('registered_at', date('Y-m-d')), 'attr') ?>">
            </div>
            <div class="field">
                <label for="copyright_status">Copyright status <span aria-hidden="true">*</span></label>
                <select class="select" id="copyright_status" name="copyright_status" required>
                    <?php foreach ($statuses as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('copyright_status', 'draft') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="risk_level">Risk level <span aria-hidden="true">*</span></label>
                <select class="select" id="risk_level" name="risk_level" required>
                    <?php foreach (['Low', 'Medium', 'High'] as $r) : ?>
                        <option value="<?= esc($r, 'attr') ?>" <?= old('risk_level', 'Low') === $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="field">
            <label for="description">Description</label>
            <textarea class="textarea" id="description" name="description" rows="4" placeholder="Short synopsis or catalog notes…"><?= esc(old('description', '')) ?></textarea>
        </div>
        <div class="field">
            <label for="creator">Creators / authors</label>
            <input class="input" id="creator" name="creator" type="text" value="<?= esc(old('creator', ''), 'attr') ?>" placeholder="Comma-separated names">
        </div>
        <div class="field">
            <label for="evidence_files">Evidence files</label>
            <input id="evidence_files" name="evidence_files[]" type="file" multiple accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.mp3,.wav,.mp4,.txt,.csv,.doc,.docx,.zip">
            <p class="muted" style="margin:0.35rem 0 0;">Up to 10&nbsp;MiB total per save. Allowed types include documents, images, audio, and video. Each file is hashed (SHA-256) after upload.</p>
        </div>
        <div class="toolbar" style="margin: 0; padding-top: 0.5rem;">
            <div class="toolbar__left"></div>
            <div class="toolbar__right">
                <a class="btn btn--secondary" href="<?= site_url('works') ?>">Cancel</a>
                <button type="submit" class="btn btn--primary">Save work</button>
            </div>
        </div>
    <?= form_close() ?>
</div>
