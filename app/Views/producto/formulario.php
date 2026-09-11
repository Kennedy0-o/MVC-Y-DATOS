<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Registrar Nuevo Producto</h2>

<div class="card">
    <div class="card-body p-4">
        <form action="<?= base_url('productos/registrar') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Nombre del producto</label>
                <input type="text" name="nombre" class="form-control" value="<?= old('nombre') ?>" maxlength="100" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Precio (S/)</label>
                <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= old('precio') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Categoría</label>
                <select name="id_categoria" class="form-select" required>
                    <option value="">-- Selecciona una categoría --</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>" <?= old('id_categoria') == $c['id_categoria'] ? 'selected' : '' ?>>
                            <?= esc($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4 form-check">
                <input type="hidden" name="disponible" value="0">
                <input type="checkbox" name="disponible" value="1" class="form-check-input" id="disponibleCheck" checked>
                <label class="form-check-label" for="disponibleCheck">Disponible para la venta</label>
            </div>

            <button type="submit" class="btn btn-sagras">Guardar producto</button>
            <a href="<?= base_url('productos') ?>" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?= $this->endSection() ?>