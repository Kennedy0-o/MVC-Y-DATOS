<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Consulta de Pedidos</h2>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th># Pedido</th>
                    <th>Mesa</th>
                    <th>Mozo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Total (S/)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><?= esc($p['id_pedido']) ?></td>
                    <td>Mesa <?= esc($p['mesa_numero']) ?></td>
                    <td><?= esc($p['mozo']) ?></td>
                    <td><?= esc($p['fecha']) ?></td>
                    <td>
                        <span class="badge bg-<?=
                            $p['estado']=='pendiente'?'warning':
                            ($p['estado']=='entregado'?'success':
                            ($p['estado']=='cancelado'?'secondary':'info')) ?>">
                            <?= ucfirst(esc($p['estado'])) ?>
                        </span>
                    </td>
                    <td class="fw-bold">S/ <?= number_format($p['total'], 2) ?></td>
                    <td>
                        <a href="<?= base_url('pedidos/ver/' . $p['id_pedido']) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                        <?php if (!in_array($p['estado'], ['entregado', 'cancelado'])): ?>
                            <a href="<?= base_url('pedidos/editar/' . $p['id_pedido']) ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                            <a href="<?= base_url('pedidos/cancelar/' . $p['id_pedido']) ?>" 
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('¿Está seguro de cancelar este pedido?');">Cancelar</a>
                    <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pedidos)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No hay pedidos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
