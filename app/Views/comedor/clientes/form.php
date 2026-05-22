<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/comedor/clientes" class="btn btn-outline-secondary btn-sm mr-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h4 class="mb-0"><i class="fa-solid fa-user-plus mr-2 text-info"></i><?= esc($title) ?></h4>
    </div>

    <div class="card shadow-sm" style="max-width:560px;">
        <div class="card-body">
            <?php $action = $cliente ? '/comedor/clientes/actualizar/' . $cliente['id'] : '/comedor/clientes/crear'; ?>
            <form action="<?= $action ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control"
                        value="<?= esc($cliente['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Identificación</label>
                        <input type="text" name="identificacion" class="form-control"
                            value="<?= esc($cliente['identificacion'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                            value="<?= esc($cliente['telefono'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Notas</label>
                    <textarea name="notas" class="form-control" rows="2"><?= esc($cliente['notas'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save mr-1"></i>
                    <?= $cliente ? 'Actualizar' : 'Guardar' ?>
                </button>
                <a href="/comedor/clientes" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
