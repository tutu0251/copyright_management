<?= $this->extend('layouts/main_shell') ?>
<?= $this->section('content') ?>

<section class="stack" style="gap: 1.5rem;">
    <p class="muted">Live record counts from the database.</p>

    <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem;">
        <?php
        $labels = [
            'works'              => 'Works',
            'work_files'         => 'Work files',
            'owners'             => 'Owners',
            'licenses'           => 'Licenses',
            'licensees'          => 'Licensees',
            'usage_reports'      => 'Usage reports',
            'infringement_cases' => 'Infringement cases',
            'audit_logs'         => 'Audit logs',
        ];
        foreach ($labels as $key => $label) :
            $n = (int) ($counts[$key] ?? 0);
            ?>
            <article class="card" style="padding: 1rem;">
                <div class="muted" style="font-size: 0.8rem;"><?= esc($label) ?></div>
                <div style="font-size: 1.75rem; font-weight: 600; margin-top: 0.25rem;"><?= esc((string) $n) ?></div>
            </article>
        <?php endforeach ?>
    </div>
</section>

<?= $this->endSection() ?>
