<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Catalogo de Productos</h2>

<div class="row g-3">
    <?php foreach ($productos as $p): ?>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title"><?= esc($p['nombre']) ?></h5>
                <h6 class="card-subtitle mb-2 text-muted"><?= esc($p['categoria_nombre']) ?></h6>
                <p class="card-text text-success fw-bold fs-5">S/ <?= number_format($p['precio'], 2) ?></p>
                <span class="badge bg-<?= $p['disponible'] ? 'success' : 'secondary' ?>">
                    <?= $p['disponible'] ? 'Disponible' : 'No disponible' ?>
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
