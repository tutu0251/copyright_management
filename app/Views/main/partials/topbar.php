<?php
$initial = strtoupper(substr($currentUser['name'] ?? '?', 0, 1));
?>
<header class="app-topbar">
    <div class="app-topbar__search">
        <span class="app-topbar__search-icon" aria-hidden="true">⌕</span>
        <input type="search" class="app-topbar__search-input" placeholder="Search (coming soon)" aria-label="Search" autocomplete="off" disabled>
    </div>
    <div class="app-topbar__actions">
        <button type="button" class="ui-theme-toggle" id="theme-toggle" title="Toggle theme" aria-label="Toggle light and dark theme">
            <span class="ui-theme-toggle__icon ui-theme-toggle__icon--sun" aria-hidden="true"></span>
            <span class="ui-theme-toggle__icon ui-theme-toggle__icon--moon" aria-hidden="true"></span>
        </button>
        <div class="app-topbar__user">
            <span class="app-user__avatar app-user__avatar--sm" aria-hidden="true"><?= esc($initial) ?></span>
            <span class="app-topbar__user-name"><?= esc($currentUser['name'] ?? '') ?></span>
        </div>
    </div>
</header>
