<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">📝 Registrar Nuevo Pedido</h2>

<?php if (session()->has('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session('errors') as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="/pedidos/registrar" method="post" id="formPedido">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">1. Seleccionar Mesa</div>
                <div class="card-body">
                    <select name="id_mesa" class="form-select" required>
                        <option value="">-- Seleccione mesa libre --</option>
                        <?php foreach ($mesas as $m): ?>
                            <option value="<?= $m['id_mesa'] ?>">Mesa <?= esc($m['numero']) ?> (Cap: <?= $m['capacidad'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">2. Mozo Responsable</div>
                <div class="card-body">
                    <select name="id_usuario" class="form-select" required>
                        <option value="1">Marco (Mozo)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header bg-white fw-bold">3. Seleccionar Productos</div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($productos as $prod): ?>
                <div class="col-md-4">
                    <div class="form-check border rounded p-2">
                        <input class="form-check-input" type="checkbox" name="productos[]" value="<?= $prod['id_producto'] ?>" id="prod<?= $prod['id_producto'] ?>">
                        <label class="form-check-label w-100" for="prod<?= $prod['id_producto'] ?>">
                            <div class="d-flex justify-content-between">
                                <span><?= esc($prod['nombre']) ?></span>
                                <span class="text-success fw-bold">S/ <?= number_format($prod['precio'],2) ?></span>
                            </div>
                            <input type="number" name="cantidades[]" class="form-control form-control-sm mt-1" value="1" min="1" max="20">
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="mt-4 d-grid">
        <button type="submit" class="btn btn-sagras btn-lg">🚀 Enviar Pedido a Cocina</button>
    </div>
</form>

<?= $this->endSection() ?>
