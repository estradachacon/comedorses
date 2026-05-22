<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/comedor/items" class="btn btn-outline-secondary btn-sm mr-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h4 class="mb-0"><i class="fa-solid fa-tags mr-2 text-secondary"></i><?= esc($title) ?></h4>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Nueva Categoría</strong></div>
                <div class="card-body">
                    <form action="/comedor/categorias/crear" method="post">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus mr-1"></i>Agregar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Categoría</th><th class="text-center">Activa</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td><?= esc($cat['nombre']) ?></td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch d-inline-block">
                                        <input type="checkbox" class="custom-control-input toggle-cat"
                                            id="cat_<?= $cat['id'] ?>" data-id="<?= $cat['id'] ?>"
                                            <?= $cat['activa'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="cat_<?= $cat['id'] ?>"></label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($categorias)): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">Sin categorías.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('change', '.toggle-cat', function () {
    const id  = $(this).data('id');
    const chk = this;
    $.post('/comedor/categorias/toggle/' + id, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
        .done(function (r) { if (!r.ok) chk.checked = !chk.checked; })
        .fail(function ()  { chk.checked = !chk.checked; });
});
</script>

<?= $this->endSection() ?>
