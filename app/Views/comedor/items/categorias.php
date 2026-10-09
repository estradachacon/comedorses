<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
.cat-card {
    border-radius: 4px;
    border: 1px solid #d9d9d9;
    background: #fff;
    padding: 10px 14px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    transition: border-color .12s, background .12s;
}
.cat-card:hover { border-color: #b7b7b7; background: #fafbfc; }
.cat-card-nombre { font-weight: 600; font-size: .92rem; min-width: 0; word-break: break-word; }
.cat-card-acciones { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
</style>

<div class="container-fluid px-3 px-sm-4">
    <div class="d-flex flex-wrap mb-4" style="gap:10px;">
        <a href="/comedor/items" class="btn btn-outline-secondary btn-sm" style="flex-shrink:0;">
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
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12 col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Nueva Categoría</strong></div>
                <div class="card-body">
                    <form action="/comedor/categorias/crear" method="post">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fa-solid fa-plus mr-1"></i>Agregar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-8">
            <?php if (empty($categorias)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-4">Sin categorías.</div>
                </div>
            <?php else: ?>
            <div id="listaCategorias">
                <?php foreach ($categorias as $cat): ?>
                <div class="cat-card" id="catCard_<?= $cat['id'] ?>">
                    <span class="cat-card-nombre" id="catNombre_<?= $cat['id'] ?>"><?= esc($cat['nombre']) ?></span>
                    <div class="cat-card-acciones">
                        <?php if (tienePermiso('gestionar_items_comedor')): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-editar-cat"
                            data-id="<?= $cat['id'] ?>" data-nombre="<?= esc($cat['nombre'], 'attr') ?>"
                            style="padding:3px 10px;">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <div class="custom-control custom-switch d-inline-block">
                            <input type="checkbox" class="custom-control-input toggle-cat"
                                id="cat_<?= $cat['id'] ?>" data-id="<?= $cat['id'] ?>"
                                <?= $cat['activa'] ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="cat_<?= $cat['id'] ?>"></label>
                        </div>
                        <?php else: ?>
                        <span class="badge badge-<?= $cat['activa'] ? 'success' : 'secondary' ?>">
                            <?= $cat['activa'] ? 'Activa' : 'Inactiva' ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: editar categoría -->
<div class="modal fade" id="modalEditarCat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fa-solid fa-pen mr-2 text-primary"></i>Editar Categoría
                </h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-muted">NOMBRE</label>
                    <input type="text" id="editCatNombre" class="form-control">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarEdicionCat">
                    <i class="fa-solid fa-check mr-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfName = '<?= csrf_token() ?>';
const csrfHash = '<?= csrf_hash() ?>';
let catEditandoId = null;

$(document).on('change', '.toggle-cat', function () {
    const id  = $(this).data('id');
    const chk = this;
    $.post('/comedor/categorias/toggle/' + id, { [csrfName]: csrfHash })
        .done(function (r) { if (!r.ok) chk.checked = !chk.checked; })
        .fail(function ()  { chk.checked = !chk.checked; });
});

$(document).on('click', '.btn-editar-cat', function () {
    catEditandoId = $(this).data('id');
    $('#editCatNombre').val($(this).data('nombre'));
    $('#modalEditarCat').modal('show');
});

$('#btnGuardarEdicionCat').on('click', function () {
    const nombre = $('#editCatNombre').val().trim();
    if (!nombre) {
        Swal.fire('Dato inválido', 'El nombre es requerido.', 'warning');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i>Guardando...');
    $.post('/comedor/categorias/actualizar/' + catEditandoId, { [csrfName]: csrfHash, nombre })
        .done(function (r) {
            if (!r.ok) { Swal.fire('Error', r.msg, 'error'); return; }
            $('#catNombre_' + catEditandoId).text(r.nombre);
            $('.btn-editar-cat[data-id="' + catEditandoId + '"]').data('nombre', r.nombre);
            $('#modalEditarCat').modal('hide');
            Swal.fire({ icon: 'success', title: 'Categoría actualizada', timer: 900, showConfirmButton: false });
        })
        .fail(function () {
            Swal.fire('Error', 'No se pudo guardar la categoría.', 'error');
        })
        .always(function () {
            $('#btnGuardarEdicionCat').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Guardar');
        });
});
</script>

<?= $this->endSection() ?>
