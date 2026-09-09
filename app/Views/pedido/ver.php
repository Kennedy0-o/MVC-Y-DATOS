<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Detalle del Pedido #<?= esc($pedido['id_pedido']) ?></h2>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Informacion General</div>
            <div class="card-body">
                <p><strong>Mesa:</strong> <?= esc($pedido['mesa_numero']) ?></p>
                <p><strong>Mozo:</strong> <?= esc($pedido['mozo']) ?></p>
                <p><strong>Fecha:</strong> <?= esc($pedido['fecha']) ?></p>
                <p><strong>Estado:</strong>
                    <span class="badge bg-<?=
                        $pedido['estado']=='pendiente'?'warning':
                        ($pedido['estado']=='entregado'?'success':
                        ($pedido['estado']=='cancelado'?'secondary':'info')) ?>">
                        <?= ucfirst(esc($pedido['estado'])) ?>
                    </span>
                </p>
                <hr>
                <h5 class="text-end">Total: <span class="text-success">S/ <?= number_format($pedido['total'], 2) ?></span></h5>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Productos Solicitados</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>Producto</th><th>Cant.</th><th>P. Unit.</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $d): ?>
                        <tr>
                            <td><?= esc($d['producto_nombre']) ?></td>
                            <td><?= $d['cantidad'] ?></td>
                            <td>S/ <?= number_format($d['precio_unitario'], 2) ?></td>
                            <td class="fw-bold">S/ <?= number_format($d['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <a href="<?= base_url('pedidos') ?>" class="btn btn-outline-secondary">&larr; Volver al listado</a>
    <?php if (!in_array($pedido['estado'], ['entregado', 'cancelado'])): ?>
        <a href="<?= base_url('pedidos/editar/' . $pedido['id_pedido']) ?>" class="btn btn-warning">Editar Pedido</a>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
