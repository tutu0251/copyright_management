<?php
/**
 * Enterprise table shell — wrap <table class="data-table">…</table> inside this include pair,
 * or pass $table_slot from a Parser cell. Here we only open the visual frame.
 * Close with: <?= $this->include('components/table_end') ?>  OR use inline closing tags below.
 *
 * Usage in a view:
 *   <?= $this->include('components/table') ?>
 *   <table class="data-table">...</table>
 *   </div></div>
 *
 * For simplicity, this file outputs only the opening wrapper (see comment in consuming views).
 */
?>
<div class="ui-table-card">
    <div class="ui-table-scroll">
