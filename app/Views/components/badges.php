<?php
/**
 * Status badge — pass $label (string) and $tone: success | warning | danger | neutral
 * TODO: Map domain statuses to tones in a helper when wiring the backend.
 */
$tone = $tone ?? 'neutral';
$tone = in_array($tone, ['success', 'warning', 'danger', 'neutral'], true) ? $tone : 'neutral';
$label = $label ?? '';
?>
<span class="ui-badge ui-badge--<?= esc($tone, 'attr') ?>"><?= esc($label) ?></span>
