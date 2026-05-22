<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/comedor/items" class="btn btn-outline-secondary btn-sm mr-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h4 class="mb-0"><i class="fa-solid fa-utensils mr-2 text-warning"></i><?= esc($title) ?></h4>
    </div>

    <div class="card shadow-sm" style="max-width:600px;">
        <div class="card-body">
            <?php $action = $item ? '/comedor/items/actualizar/' . $item['id'] : '/comedor/items/crear'; ?>
            <form action="<?= $action ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control"
                        value="<?= esc($item['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">— Sin categoría —</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= (isset($item) && $item['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= esc($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Precio <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input type="number" name="precio" class="form-control" step="0.01" min="0"
                            value="<?= esc($item['precio'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"><?= esc($item['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="disponible" name="disponible" value="1"
                            <?= (!$item || $item['disponible']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="disponible">Disponible para venta</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save mr-1"></i>
                    <?= $item ? 'Actualizar' : 'Guardar' ?>
                </button>
                <a href="/comedor/items" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
