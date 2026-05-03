<?php
$errors = $errors ?? [];
$types = [
    'individual'    => 'Individual',
    'company'       => 'Company',
    'organization'  => 'Organization',
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

<p class="page-intro">Create a licensee you can attach to one or more licenses.</p>

<div class="card" style="max-width: 880px;">
    <h2 class="card__title">Licensee details</h2>
    <?= form_open(site_url('licensees'), ['class' => 'stack']) ?>
        <div class="form-grid">
            <div class="field">
                <label for="name">Name <span aria-hidden="true">*</span></label>
                <input class="input" id="name" name="name" type="text" required value="<?= esc(old('name', ''), 'attr') ?>" placeholder="Legal name">
            </div>
            <div class="field">
                <label for="licensee_type">Type <span aria-hidden="true">*</span></label>
                <select class="select" id="licensee_type" name="licensee_type" required>
                    <?php foreach ($types as $val => $label) : ?>
                        <option value="<?= esc($val, 'attr') ?>" <?= old('licensee_type', 'individual') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="<?= esc(old('email', ''), 'attr') ?>" placeholder="contact@example.com">
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input class="input" id="phone" name="phone" type="text" value="<?= esc(old('phone', ''), 'attr') ?>">
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label for="address">Address</label>
                <textarea class="textarea" id="address" name="address" rows="2" placeholder="Street, city, region"><?= esc(old('address', '')) ?></textarea>
            </div>
            <div class="field">
                <label for="country">Country</label>
                <input class="input" id="country" name="country" type="text" value="<?= esc(old('country', ''), 'attr') ?>">
            </div>
        </div>
        <div class="field">
            <label for="notes">Notes</label>
            <textarea class="textarea" id="notes" name="notes" rows="3" placeholder="Internal notes"><?= esc(old('notes', '')) ?></textarea>
        </div>
        <div class="toolbar" style="margin: 0; padding-top: 0.5rem;">
            <div class="toolbar__left"></div>
            <div class="toolbar__right">
                <a class="btn btn--secondary" href="<?= site_url('licensees') ?>">Cancel</a>
                <button type="submit" class="btn btn--primary">Save licensee</button>
            </div>
        </div>
    <?= form_close() ?>
</div>
