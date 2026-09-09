<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="mb-4"><?= isset($pedido) ? 'Editar Pedido #' . $pedido['id_pedido'] : 'Registrar Nuevo Pedido' ?></h2>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger">
        <?= esc(session('error')) ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('pedidos/' . $accion) ?>" method="post">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">1. Seleccionar Mesa</div>
                <div class="card-body">
                    <select name="id_mesa" class="form-select" required>
                        <option value="">-- Seleccione mesa --</option>
                        <?php foreach ($mesas as $m): ?>
                            <option value="<?= $m['id_mesa'] ?>"
                                <?= (isset($pedido) && $pedido['id_mesa'] == $m['id_mesa']) ? 'selected' : '' ?>>
                                Mesa <?= esc($m['numero']) ?> (Cap: <?= $m['capacidad'] ?>) - <?= $m['estado'] ?>
                            </option>
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
                        <option value="1" <?= (isset($pedido) && $pedido['id_usuario'] == 1) ? 'selected' : '' ?>>Marco (Mozo)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header bg-white fw-bold">3. Seleccionar Productos</div>
        <div class="card-body">
            <div class="row g-3">
                <?php
                $productosSeleccionados = [];
                $cantidadesActuales = [];
                if (isset($detalles)) {
                    foreach ($detalles as $d) {
                        $productosSeleccionados[] = $d['id_producto'];
                        $cantidadesActuales[$d['id_producto']] = $d['cantidad'];
                    }
                }
                ?>
                <?php foreach ($productos as $prod): ?>
                <?php
                    $checked = in_array($prod['id_producto'], $productosSeleccionados);
                    $cantidad = $cantidadesActuales[$prod['id_producto']] ?? 1;
                ?>
                <div class="col-md-4">
                    <div class="form-check border rounded p-2 <?= $checked ? 'bg-light border-success' : '' ?>">
                        <input class="form-check-input" type="checkbox"
                               name="productos[]" value="<?= $prod['id_producto'] ?>"
                               id="prod<?= $prod['id_producto'] ?>"
                               <?= $checked ? 'checked' : '' ?>>
                        <label class="form-check-label w-100" for="prod<?= $prod['id_producto'] ?>">
                            <div class="d-flex justify-content-between">
                                <span><?= esc($prod['nombre']) ?></span>
                                <span class="text-success fw-bold">S/ <?= number_format($prod['precio'],2) ?></span>
                            </div>
                            <input type="number" name="cantidades[]"
                                   class="form-control form-control-sm mt-1"
                                   value="<?= $cantidad ?>" min="1" max="50">
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="mt-4 d-grid">
        <button type="submit" class="btn btn-sagras btn-lg">
            <?= isset($pedido) ? 'Guardar Cambios' : 'Enviar Pedido a Cocina' ?>
        </button>
    </div>
</form>

<?= $this->endSection() ?>
