<?php
// TODO: Wire search to global catalog API; notifications to real-time feed.
$flashError = session()->getFlashdata('error');
$locale     = service('request')->getLocale();
?>
<?php if ($flashError) : ?>
    <div class="app-flash app-flash--error" role="alert" style="margin:0 1.25rem;padding:0.65rem 1rem;border-radius:8px;background:rgba(196,68,68,0.15);border:1px solid var(--cm-danger, #c44);font-size:0.9rem;">
        <?= esc((string) $flashError) ?>
    </div>
<?php endif; ?>
<header class="app-topbar">
    <div class="app-topbar__search">
        <span class="app-topbar__search-icon" aria-hidden="true">⌕</span>
        <input type="search" class="app-topbar__search-input" placeholder="<?= esc(lang('App.topbar_search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('App.topbar_search_aria'), 'attr') ?>" autocomplete="off">
        <kbd class="app-topbar__kbd">/</kbd>
    </div>
    <div class="app-topbar__actions">
        <div class="app-topbar__lang" style="display:flex;align-items:center;gap:0.35rem;">
            <label for="app-lang" class="muted" style="font-size:0.75rem;white-space:nowrap;"><?= esc(lang('App.topbar_language')) ?></label>
            <select id="app-lang" class="input-like" style="padding:0.35rem 0.5rem;border-radius:8px;border:1px solid var(--border, #334155);background:var(--surface, #0f172a);color:inherit;font-size:0.85rem;" aria-label="<?= esc(lang('App.topbar_language'), 'attr') ?>"
                    onchange="if(this.value) window.location.href=this.value;">
                <option value="<?= esc(current_lang_url('en'), 'attr') ?>" <?= $locale === 'en' ? 'selected' : '' ?>><?= esc(lang('App.lang_english')) ?></option>
                <option value="<?= esc(current_lang_url('ja'), 'attr') ?>" <?= $locale === 'ja' ? 'selected' : '' ?>><?= esc(lang('App.lang_japanese')) ?></option>
            </select>
        </div>
        <button type="button" class="ui-icon-btn ui-icon-btn--ghost" id="btn-notifications" title="<?= esc(lang('App.topbar_notifications'), 'attr') ?>" aria-label="<?= esc(lang('App.topbar_notifications'), 'attr') ?>">
            <span class="ui-bell" aria-hidden="true"></span>
            <span class="ui-dot" aria-hidden="true"></span>
        </button>
        <button type="button" class="ui-theme-toggle" id="theme-toggle" title="<?= esc(lang('App.topbar_theme_toggle'), 'attr') ?>" aria-label="<?= esc(lang('App.topbar_theme_toggle'), 'attr') ?>">
            <span class="ui-theme-toggle__icon ui-theme-toggle__icon--sun" aria-hidden="true"></span>
            <span class="ui-theme-toggle__icon ui-theme-toggle__icon--moon" aria-hidden="true"></span>
        </button>
        <?php $currentUser ??= ['name' => 'Guest', 'role' => '—']; ?>
        <div class="app-topbar__user">
            <span class="app-user__avatar app-user__avatar--sm" aria-hidden="true"><?= esc(strtoupper(substr((string) $currentUser['name'], 0, 1))) ?></span>
            <span class="app-topbar__user-name"><?= esc($currentUser['name']) ?></span>
        </div>
    </div>
</header>
