<?php
// TODO: Wire search to global catalog API; notifications to real-time feed.
?>
<header class="app-topbar">
    <div class="app-topbar__search">
        <span class="app-topbar__search-icon" aria-hidden="true">⌕</span>
        <input type="search" class="app-topbar__search-input" placeholder="Search works, licenses, cases…" aria-label="Global search (mock)" autocomplete="off">
        <kbd class="app-topbar__kbd">/</kbd>
    </div>
    <div class="app-topbar__actions">
        <button type="button" class="ui-icon-btn ui-icon-btn--ghost" id="btn-notifications" title="Notifications (mock)" aria-label="Notifications">
            <span class="ui-bell" aria-hidden="true"></span>
            <span class="ui-dot" aria-hidden="true"></span>
        </button>
        <button type="button" class="ui-theme-toggle" id="theme-toggle" title="Toggle theme" aria-label="Toggle light and dark theme">
            <span class="ui-theme-toggle__icon ui-theme-toggle__icon--sun" aria-hidden="true"></span>
            <span class="ui-theme-toggle__icon ui-theme-toggle__icon--moon" aria-hidden="true"></span>
        </button>
        <div class="app-topbar__user">
            <span class="app-user__avatar app-user__avatar--sm" aria-hidden="true"><?= esc(strtoupper(substr($mockUser['name'], 0, 1))) ?></span>
            <span class="app-topbar__user-name"><?= esc($mockUser['name']) ?></span>
        </div>
    </div>
</header>
