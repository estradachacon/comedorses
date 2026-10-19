<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
:root {
    --sap-border: #d9d9d9;
    --sap-bg: #ffffff;
    --sap-text: #32363a;
    --sap-text-muted: #6a6d70;
}
.item-cat-card {
    border-radius: 4px;
    border: 1px solid var(--sap-border);
    background: var(--sap-bg);
    padding: 10px 14px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: border-color .12s, background .12s;
}
.item-cat-card:hover { border-color: #b7b7b7; background: #fafbfc; }
.item-cat-thumb {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid #e3e6ea;
    background: #f8f9fa;
}
.item-cat-emoji {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    flex-shrink: 0;
    background: #f8f9fa;
    border: 1px solid #e3e6ea;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}
.item-cat-nombre { font-weight: 600; font-size: .92rem; color: var(--sap-text); }
.item-cat-desc { font-size: .76rem; color: var(--sap-text-muted); }
.item-cat-cat { font-size: .76rem; color: var(--sap-text-muted); }
.item-cat-precio { font-weight: 700; font-size: .92rem; color: var(--sap-text); white-space: nowrap; }
.item-cat-acciones { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
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
@media (max-width: 480px) {
    .item-cat-card { flex-wrap: wrap; }
    .item-cat-info { width: 100%; order: 1; }
    .item-cat-acciones { width: 100%; order: 2; justify-content: space-between !important; margin-top: 4px; }
}
</style>

<div class="container-fluid px-3">
    <div class="d-flex flex-wrap justify-content-between mb-3 pt-1" style="gap:10px;">
        <h5 class="mb-0 font-weight-bold">
            <i class="fa-solid fa-utensils mr-2 text-warning"></i><?= esc($title) ?>
            <span class="badge badge-light text-muted ml-1" style="font-size:.75rem;"><?= count($items) ?></span>
        </h5>
        <?php if (tienePermiso('gestionar_items_comedor')): ?>
        <div class="d-flex" style="gap:8px;">
            <a href="/comedor/categorias" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-tags mr-1"></i>Categorías
            </a>
            <a href="/comedor/items/nuevo" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus mr-1"></i>Nuevo Item
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
    <div class="search-box mb-3">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="buscadorItems" class="form-control form-control-sm"
               placeholder="Buscar por nombre o categoría…">
    </div>
    <?php endif; ?>

    <div id="listaItems">
        <?php if (empty($items)): ?>
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-utensils fa-2x mb-2" style="opacity:.25;"></i>
                <p class="mb-0">No hay items registrados.</p>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
            <?php $fotoUrl = !empty($item['foto']) ? base_url('upload/comedor_items/' . $item['foto']) : null; ?>
            <div class="item-cat-card"
                 data-buscar="<?= strtolower(esc($item['nombre'], 'attr') . ' ' . esc($item['categoria_nombre'] ?? '', 'attr')) ?>">
                <?php if ($fotoUrl): ?>
                <img src="<?= esc($fotoUrl) ?>" class="item-cat-thumb" alt="">
                <?php else: ?>
                <div class="item-cat-emoji">🍽️</div>
                <?php endif; ?>

                <div class="item-cat-info" style="min-width:0;flex:1;">
                    <div class="item-cat-nombre"><?= esc($item['nombre']) ?></div>
                    <?php if ($item['descripcion']): ?>
                        <div class="item-cat-desc"><?= esc($item['descripcion']) ?></div>
                    <?php endif; ?>
                    <div class="item-cat-cat"><?= esc($item['categoria_nombre'] ?? '—') ?></div>
                </div>

                <div class="item-cat-acciones">
                    <div class="item-cat-precio">$<?= number_format($item['precio'], 2) ?></div>
                    <?php if (tienePermiso('gestionar_items_comedor')): ?>
                    <div class="custom-control custom-switch d-inline-block mb-0">
                        <input type="checkbox" class="custom-control-input toggle-disponible"
                            id="disp_<?= $item['id'] ?>" data-id="<?= $item['id'] ?>"
                            <?= $item['disponible'] ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="disp_<?= $item['id'] ?>"></label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-editar-item-cat" style="padding:3px 10px;"
                        data-id="<?= $item['id'] ?>"
                        data-nombre="<?= esc($item['nombre'], 'attr') ?>"
                        data-descripcion="<?= esc($item['descripcion'] ?? '', 'attr') ?>"
                        data-precio="<?= $item['precio'] ?>"
                        data-categoria-id="<?= $item['categoria_id'] ?? '' ?>"
                        data-foto-url="<?= $fotoUrl ? esc($fotoUrl, 'attr') : '' ?>">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <?php else: ?>
                    <span class="badge badge-<?= $item['disponible'] ? 'success' : 'secondary' ?>">
                        <?= $item['disponible'] ? 'Sí' : 'No' ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div id="sinResultadosItems" class="text-center text-muted py-4" style="display:none;">
                <i class="fa-solid fa-magnifying-glass mb-2"></i>
                <p class="mb-0 small">Sin resultados para tu búsqueda.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="paginacion-box" id="paginacionItems" style="display:none;">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPaginaAnteriorItems">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <span class="text-muted small" id="paginaInfoItems"></span>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPaginaSiguienteItems">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Modal: edición rápida de item -->
<div class="modal fade" id="modalEditarItemCat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fa-solid fa-pen mr-2 text-primary"></i>Editar Item
                </h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="previewFotoItemCat" src="<?= base_url('upload/no-image.png') ?>" alt=""
                         style="width:100%;max-height:160px;object-fit:contain;border-radius:8px;border:1px solid #e3e6ea;background:#f8f9fa;">
                    <div class="d-flex mt-2" style="gap:8px;">
                        <label class="btn btn-sm btn-outline-primary flex-fill mb-0">
                            <i class="fa-solid fa-camera mr-1"></i>Tomar foto
                            <input type="file" id="inputFotoCatCapturar" accept="image/*" capture="environment" style="display:none;">
                        </label>
                        <label class="btn btn-sm btn-outline-secondary flex-fill mb-0">
                            <i class="fa-solid fa-image mr-1"></i>Galería
                            <input type="file" id="inputFotoCatGaleria" accept="image/*" style="display:none;">
                        </label>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.72rem;">JPG, PNG o WEBP · máx. 3MB (opcional)</div>
                    <input type="file" id="inputFotoCatReal" style="display:none;">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold text-muted">NOMBRE</label>
                    <input type="text" id="editItemNombre" class="form-control">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold text-muted">CATEGORÍA</label>
                    <select id="editItemCategoriaId" class="form-control">
                        <option value="">Sin categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= esc($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold text-muted">PRECIO</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input type="number" id="editItemPrecio" class="form-control" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-muted">DESCRIPCIÓN</label>
                    <textarea id="editItemDescripcion" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarEdicionItemCat">
                    <i class="fa-solid fa-check mr-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('change', '.toggle-disponible', function () {
    const id  = $(this).data('id');
    const chk = this;
    $.post('/comedor/items/toggle/' + id, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
        .done(function (r) { if (!r.ok) chk.checked = !chk.checked; })
        .fail(function ()  { chk.checked = !chk.checked; });
});

// ── Búsqueda + paginado (en vivo, sin recargar) ─────────────────────────
const PAGINA_TAMANO = 10;
let paginaActualItems = 1;

function aplicarFiltroYPaginarItems() {
    const q = $('#buscadorItems').val().toLowerCase().trim();
    const todas = $('.item-cat-card');
    const coincidentes = todas.filter(function () {
        return !q || $(this).data('buscar').includes(q);
    });

    todas.hide();
    $('#sinResultadosItems').toggle(coincidentes.length === 0 && q.length > 0);

    const totalPaginas = Math.max(1, Math.ceil(coincidentes.length / PAGINA_TAMANO));
    if (paginaActualItems > totalPaginas) paginaActualItems = totalPaginas;

    const inicio = (paginaActualItems - 1) * PAGINA_TAMANO;
    coincidentes.slice(inicio, inicio + PAGINA_TAMANO).show();

    if (totalPaginas <= 1) {
        $('#paginacionItems').hide();
    } else {
        $('#paginacionItems').show();
        $('#paginaInfoItems').text('Página ' + paginaActualItems + ' de ' + totalPaginas + ' · ' + coincidentes.length + ' items');
        $('#btnPaginaAnteriorItems').prop('disabled', paginaActualItems <= 1);
        $('#btnPaginaSiguienteItems').prop('disabled', paginaActualItems >= totalPaginas);
    }
}

$('#buscadorItems').on('input', function () {
    paginaActualItems = 1;
    aplicarFiltroYPaginarItems();
});

$('#btnPaginaAnteriorItems').on('click', function () {
    paginaActualItems--;
    aplicarFiltroYPaginarItems();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

$('#btnPaginaSiguienteItems').on('click', function () {
    paginaActualItems++;
    aplicarFiltroYPaginarItems();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

aplicarFiltroYPaginarItems();

// ── Edición rápida de item (modal) ──────────────────────────────────────
let itemEditandoId = null;

$(document).on('click', '.btn-editar-item-cat', function () {
    const btn = $(this);
    itemEditandoId = btn.data('id');

    $('#editItemNombre').val(btn.data('nombre'));
    $('#editItemDescripcion').val(btn.data('descripcion'));
    $('#editItemPrecio').val(btn.data('precio'));
    $('#editItemCategoriaId').val(btn.data('categoria-id') || '');
    $('#previewFotoItemCat').attr('src', btn.data('foto-url') || '<?= base_url('upload/no-image.png') ?>');
    $('#inputFotoCatCapturar, #inputFotoCatGaleria, #inputFotoCatReal').val('');

    $('#modalEditarItemCat').modal('show');
});

// Las dos opciones visibles (cámara / galería) alimentan el mismo input real,
// usando DataTransfer porque no se puede asignar un FileList directamente por JS.
function manejarArchivoFotoCat(inputOrigen) {
    const file = inputOrigen.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('inputFotoCatReal').files = dt.files;

    const reader = new FileReader();
    reader.onload = e => $('#previewFotoItemCat').attr('src', e.target.result);
    reader.readAsDataURL(file);
}
$('#inputFotoCatCapturar, #inputFotoCatGaleria').on('change', function () {
    manejarArchivoFotoCat(this);
});

$('#btnGuardarEdicionItemCat').on('click', function () {
    const nombre = $('#editItemNombre').val().trim();
    const precio = $('#editItemPrecio').val();
    if (!nombre || precio === '' || parseFloat(precio) < 0) {
        Swal.fire('Datos inválidos', 'Nombre y precio son requeridos.', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    formData.append('nombre', nombre);
    formData.append('categoria_id', $('#editItemCategoriaId').val());
    formData.append('precio', precio);
    formData.append('descripcion', $('#editItemDescripcion').val());
    const foto = document.getElementById('inputFotoCatReal').files[0];
    if (foto) formData.append('foto', foto);

    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i>Guardando...');

    $.ajax({
        url: '/comedor/items/actualizar-rapido/' + itemEditandoId,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
    }).done(function (r) {
        if (!r.ok) { Swal.fire('Error', r.msg, 'error'); return; }
        $('#modalEditarItemCat').modal('hide');
        Swal.fire({ icon: 'success', title: 'Item actualizado', timer: 900, showConfirmButton: false })
            .then(() => location.reload());
    }).fail(function () {
        Swal.fire('Error', 'No se pudo guardar el item.', 'error');
    }).always(function () {
        $('#btnGuardarEdicionItemCat').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Guardar');
    });
});
</script>

<?= $this->endSection() ?>
