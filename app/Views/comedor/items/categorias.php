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
.search-box { position: relative; }
.search-box .fa-magnifying-glass {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #aaa; font-size: .85rem;
}
.search-box input { padding-left: 30px; }
.paginacion-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 0 4px;
}
.paginacion-box .btn { min-width: 44px; }
</style>

<div class="container-fluid px-3">
    <div class="d-flex flex-wrap justify-content-between mb-3 pt-1" style="gap:10px;">
        <div class="d-flex align-items-center" style="gap:10px;">
            <a href="/comedor/items" class="btn btn-outline-secondary btn-sm" style="flex-shrink:0;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h5 class="mb-0 font-weight-bold">
                <i class="fa-solid fa-tags mr-2 text-secondary"></i><?= esc($title) ?>
                <span class="badge badge-light text-muted ml-1" style="font-size:.75rem;"><?= count($categorias) ?></span>
            </h5>
        </div>
        <?php if (tienePermiso('gestionar_items_comedor')): ?>
        <button type="button" class="btn btn-primary btn-sm" id="btnNuevaCategoria">
            <i class="fa-solid fa-plus mr-1"></i>Nueva Categoría
        </button>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($categorias)): ?>
    <div class="search-box mb-3">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="buscadorCategorias" class="form-control form-control-sm" placeholder="Buscar categoría…">
    </div>
    <?php endif; ?>

    <div id="listaCategorias">
        <?php if (empty($categorias)): ?>
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-tags fa-2x mb-2" style="opacity:.25;"></i>
                <p class="mb-0">Sin categorías todavía.</p>
            </div>
        <?php else: ?>
            <?php foreach ($categorias as $cat): ?>
            <div class="cat-card" id="catCard_<?= $cat['id'] ?>" data-buscar="<?= strtolower(esc($cat['nombre'], 'attr')) ?>">
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

            <div id="sinResultadosCat" class="text-center text-muted py-4" style="display:none;">
                <i class="fa-solid fa-magnifying-glass mb-2"></i>
                <p class="mb-0 small">Sin resultados para tu búsqueda.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="paginacion-box" id="paginacionCat" style="display:none;">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPaginaAnteriorCat">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <span class="text-muted small" id="paginaInfoCat"></span>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPaginaSiguienteCat">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Modal: nueva categoría -->
<div class="modal fade" id="modalNuevaCat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fa-solid fa-plus mr-2 text-primary"></i>Nueva Categoría
                </h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-muted">NOMBRE</label>
                    <input type="text" id="nuevaCatNombre" class="form-control" placeholder="Ej. Bebidas">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarNuevaCat">
                    <i class="fa-solid fa-check mr-1"></i>Guardar
                </button>
            </div>
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

function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

// Misma estructura que el parcial del servidor, para poder agregar una categoría nueva sin
// tener que recargar toda la página.
function construirTarjetaCategoria(id, nombre) {
    return `<div class="cat-card" id="catCard_${id}" data-buscar="${escapeHtml(nombre.toLowerCase())}">
        <span class="cat-card-nombre" id="catNombre_${id}">${escapeHtml(nombre)}</span>
        <div class="cat-card-acciones">
            <button type="button" class="btn btn-sm btn-outline-primary btn-editar-cat"
                data-id="${id}" data-nombre="${escapeHtml(nombre)}" style="padding:3px 10px;">
                <i class="fa-solid fa-pen"></i>
            </button>
            <div class="custom-control custom-switch d-inline-block">
                <input type="checkbox" class="custom-control-input toggle-cat" id="cat_${id}" data-id="${id}" checked>
                <label class="custom-control-label" for="cat_${id}"></label>
            </div>
        </div>
    </div>`;
}

$(document).on('change', '.toggle-cat', function () {
    const id  = $(this).data('id');
    const chk = this;
    $.post('/comedor/categorias/toggle/' + id, { [csrfName]: csrfHash })
        .done(function (r) { if (!r.ok) chk.checked = !chk.checked; })
        .fail(function ()  { chk.checked = !chk.checked; });
});

// ── Búsqueda + paginado (en vivo, sin recargar) ─────────────────────────
const PAGINA_TAMANO_CAT = 10;
let paginaActualCat = 1;

function aplicarFiltroYPaginarCat() {
    const q = ($('#buscadorCategorias').val() || '').toLowerCase().trim();
    const todas = $('.cat-card');
    const coincidentes = todas.filter(function () {
        return !q || $(this).data('buscar').includes(q);
    });

    todas.hide();
    $('#sinResultadosCat').toggle(coincidentes.length === 0 && q.length > 0);

    const totalPaginas = Math.max(1, Math.ceil(coincidentes.length / PAGINA_TAMANO_CAT));
    if (paginaActualCat > totalPaginas) paginaActualCat = totalPaginas;

    const inicio = (paginaActualCat - 1) * PAGINA_TAMANO_CAT;
    coincidentes.slice(inicio, inicio + PAGINA_TAMANO_CAT).show();

    if (totalPaginas <= 1) {
        $('#paginacionCat').hide();
    } else {
        $('#paginacionCat').show();
        $('#paginaInfoCat').text('Página ' + paginaActualCat + ' de ' + totalPaginas + ' · ' + coincidentes.length + ' categorías');
        $('#btnPaginaAnteriorCat').prop('disabled', paginaActualCat <= 1);
        $('#btnPaginaSiguienteCat').prop('disabled', paginaActualCat >= totalPaginas);
    }
}

$('#buscadorCategorias').on('input', function () {
    paginaActualCat = 1;
    aplicarFiltroYPaginarCat();
});

$('#btnPaginaAnteriorCat').on('click', function () {
    paginaActualCat--;
    aplicarFiltroYPaginarCat();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

$('#btnPaginaSiguienteCat').on('click', function () {
    paginaActualCat++;
    aplicarFiltroYPaginarCat();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

aplicarFiltroYPaginarCat();

// ── Nueva categoría (modal) ──────────────────────────────────────────────
$('#btnNuevaCategoria').on('click', function () {
    $('#nuevaCatNombre').val('');
    $('#modalNuevaCat').modal('show');
});

$('#btnGuardarNuevaCat').on('click', function () {
    const nombre = $('#nuevaCatNombre').val().trim();
    if (!nombre) {
        Swal.fire('Dato inválido', 'El nombre es requerido.', 'warning');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i>Guardando...');
    $.post('/comedor/categorias/crear', { [csrfName]: csrfHash, nombre })
        .done(function (r) {
            if (!r.ok) { Swal.fire('Error', r.msg, 'error'); return; }

            $('#listaCategorias .text-center.text-muted').remove(); // "Sin categorías todavía", si estaba
            $('#listaCategorias').append(construirTarjetaCategoria(r.id, r.nombre));
            paginaActualCat = Math.ceil($('.cat-card').length / PAGINA_TAMANO_CAT); // ir a la última página para verla
            aplicarFiltroYPaginarCat();

            $('#modalNuevaCat').modal('hide');
            Swal.fire({ icon: 'success', title: 'Categoría creada', timer: 900, showConfirmButton: false });
        })
        .fail(function () {
            Swal.fire('Error', 'No se pudo crear la categoría.', 'error');
        })
        .always(function () {
            $('#btnGuardarNuevaCat').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Guardar');
        });
});

// ── Editar categoría (modal) ─────────────────────────────────────────────
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
            $('#catCard_' + catEditandoId).data('buscar', r.nombre.toLowerCase());
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
