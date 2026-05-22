<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between  mb-3">
        <h4 class="mb-0">
            <i class="fa-solid fa-clipboard-list mr-2 text-success"></i><?= esc($title) ?>
        </h4>
        <div class="d-flex  gap-2">
            <!-- URL pública para QR -->
            <span class="text-muted small mr-2">
                <i class="fa-solid fa-qrcode mr-1"></i>
                <a href="<?= esc($urlPublico) ?>" target="_blank"><?= esc($urlPublico) ?></a>
            </span>
        </div>
    </div>

    <!-- Filtro de fecha + acciones rápidas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2 d-flex flex-wrap  gap-2">
            <form method="get" class="d-flex  mr-3">
                <label class="mr-2 mb-0 text-muted small font-weight-bold">FECHA</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="<?= esc($fecha) ?>" style="width:160px;">
                <button type="submit" class="btn btn-outline-secondary btn-sm ml-2">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </form>
            <button class="btn btn-sm btn-outline-success mr-1" id="btnAgregarTodos">
                <i class="fa-solid fa-check-double mr-1"></i>Agregar todos
            </button>
            <?php if (!empty($ultimoMenu)): ?>
            <button class="btn btn-sm btn-outline-primary mr-1" id="btnCopiarUltimo"
                    data-fecha-origen="<?= esc($ultimoMenu['fecha']) ?>"
                    data-total="<?= (int)$ultimoMenu['total'] ?>">
                <i class="fa-solid fa-copy mr-1"></i>Copiar del
                <?= date('d/m', strtotime($ultimoMenu['fecha'])) ?>
                <span class="badge badge-light ml-1"><?= (int)$ultimoMenu['total'] ?></span>
            </button>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-danger mr-1" id="btnLimpiar">
                <i class="fa-solid fa-trash mr-1"></i>Limpiar menú
            </button>
            <button class="btn btn-sm btn-success" id="btnWhatsapp">
                <i class="fa-brands fa-whatsapp mr-1"></i>WhatsApp
            </button>
            <span class="badge badge-success badge-pill ml-auto px-3 py-2" id="contadorMenu">
                <?= count($enMenu) ?> en menú
            </span>
        </div>
    </div>

    <?php if (empty($todos)): ?>
        <div class="alert alert-warning">
            No hay items disponibles en el catálogo.
            <a href="/comedor/items">Gestionar items</a>
        </div>
    <?php else: ?>
    <?php
        $porCategoria = [];
        foreach ($todos as $item) {
            $cat = $item['categoria_nombre'] ?? 'Sin categoría';
            $porCategoria[$cat][] = $item;
        }
    ?>
    <?php foreach ($porCategoria as $catNombre => $items): ?>
    <div class="mb-1">
        <div class="d-flex align-items-center mb-2 mt-1">
            <span class="text-muted font-weight-bold mr-2"
                  style="font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;">
                <?= esc($catNombre) ?>
            </span>
            <div class="flex-grow-1 border-bottom" style="border-color:#dee2e6!important;"></div>
        </div>
        <div class="row">
            <?php foreach ($items as $item): ?>
            <?php
                $activo = in_array($item['id'], $enMenu);
                $srv    = $servicios[$item['id']] ?? ['desayuno' => 0, 'refrigerio' => 0, 'almuerzo' => 0];
            ?>
            <div class="col-md-4 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm item-toggle-card <?= $activo ? 'border-success' : '' ?>"
                     data-id="<?= $item['id'] ?>" data-activo="<?= $activo ? 1 : 0 ?>"
                     style="cursor:pointer; transition: border .15s;">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <div class="custom-control custom-switch mb-0">
                                    <input type="checkbox" class="custom-control-input switch-item"
                                           id="sw_<?= $item['id'] ?>"
                                           <?= $activo ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="sw_<?= $item['id'] ?>"></label>
                                </div>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="font-weight-bold" style="font-size:.88rem;"><?= esc($item['nombre']) ?></div>
                            </div>
                            <div class="text-success font-weight-bold ml-2" style="font-size:.9rem; flex-shrink:0;">
                                $<?= number_format($item['precio'], 2) ?>
                            </div>
                        </div>
                        <div class="srv-btns" id="srv_<?= $item['id'] ?>"
                             style="<?= $activo ? '' : 'display:none;' ?> padding-left:42px; margin-top:5px;">
                            <button type="button"
                                    class="btn btn-srv btn-xs <?= $srv['desayuno'] ? 'btn-warning' : 'btn-outline-secondary' ?>"
                                    data-id="<?= $item['id'] ?>" data-srv="desayuno"
                                    data-val="<?= (int)$srv['desayuno'] ?>"
                                    title="Desayuno" style="font-size:.7rem;padding:1px 7px;">D</button>
                            <button type="button"
                                    class="btn btn-srv btn-xs <?= $srv['refrigerio'] ? 'btn-info' : 'btn-outline-secondary' ?>"
                                    data-id="<?= $item['id'] ?>" data-srv="refrigerio"
                                    data-val="<?= (int)$srv['refrigerio'] ?>"
                                    title="Refrigerio" style="font-size:.7rem;padding:1px 7px;">R</button>
                            <button type="button"
                                    class="btn btn-srv btn-xs <?= $srv['almuerzo'] ? 'btn-success' : 'btn-outline-secondary' ?>"
                                    data-id="<?= $item['id'] ?>" data-srv="almuerzo"
                                    data-val="<?= (int)$srv['almuerzo'] ?>"
                                    title="Almuerzo" style="font-size:.7rem;padding:1px 7px;">A</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal WhatsApp -->
<div class="modal fade" id="modalWhatsapp" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fa-brands fa-whatsapp mr-2 text-success"></i>Texto para WhatsApp
                </h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pb-2">
                <textarea id="txtWhatsapp" class="form-control" rows="12"
                          style="font-size:.85rem;font-family:monospace;resize:vertical;"></textarea>
                <small class="text-muted">Puedes editar el texto antes de copiar.</small>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success btn-sm" id="btnCopiarTexto">
                    <i class="fa-solid fa-copy mr-1"></i>Copiar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const fecha = '<?= $fecha ?>';
const csrfName = '<?= csrf_token() ?>';
const csrfHash = '<?= csrf_hash() ?>';
let enMenu = <?= json_encode($enMenu) ?>;

function actualizarContador() {
    $('#contadorMenu').text(enMenu.length + ' en menú');
}

// Toggle individual
$(document).on('change', '.switch-item', function () {
    const card = $(this).closest('.item-toggle-card');
    const id   = parseInt(card.data('id'));
    const chk  = this;

    $.post('/comedor/menu/toggle', { [csrfName]: csrfHash, item_id: id, fecha })
        .done(function (r) {
            if (!r.ok) { chk.checked = !chk.checked; return; }
            if (r.en_menu) {
                card.addClass('border-success');
                if (!enMenu.includes(id)) enMenu.push(id);
                $('#srv_' + id).show();
            } else {
                card.removeClass('border-success');
                enMenu = enMenu.filter(x => x !== id);
                $('#srv_' + id).hide();
                // Resetear botones visualmente (el row fue borrado en BD)
                $('#srv_' + id + ' .btn-srv')
                    .removeClass('btn-warning btn-info btn-success')
                    .addClass('btn-outline-secondary')
                    .data('val', 0);
            }
            actualizarContador();
        })
        .fail(function () { chk.checked = !chk.checked; });
});

// Click en la card también activa el switch (excluir botones D/R/A)
$('.item-toggle-card').on('click', function (e) {
    if ($(e.target).is('input, label, .btn-srv')) return;
    $(this).find('.switch-item').trigger('click');
});

// Toggle D / R / A
const srvColors = { desayuno: 'btn-warning', refrigerio: 'btn-info', almuerzo: 'btn-success' };

$(document).on('click', '.btn-srv', function (e) {
    e.stopPropagation();
    const btn     = $(this);
    const itemId  = btn.data('id');
    const servicio = btn.data('srv');
    const nuevoVal = btn.data('val') ? 0 : 1;

    $.post('/comedor/menu/servicio', { [csrfName]: csrfHash, item_id: itemId, fecha, servicio, valor: nuevoVal })
        .done(function (r) {
            if (!r.ok) return;
            btn.data('val', nuevoVal);
            if (nuevoVal) {
                btn.removeClass('btn-outline-secondary').addClass(srvColors[servicio]);
            } else {
                btn.removeClass(srvColors[servicio]).addClass('btn-outline-secondary');
            }
        });
});

// Agregar todos
$('#btnAgregarTodos').on('click', function () {
    $.post('/comedor/menu/agregar-todos', { [csrfName]: csrfHash, fecha })
        .done(function (r) { if (r.ok) location.reload(); });
});

// Copiar último menú
$('#btnCopiarUltimo').on('click', function () {
    const desde = $(this).data('fecha-origen');
    const total = $(this).data('total');
    Swal.fire({
        title: 'Copiar menú del ' + desde,
        text: 'Se agregarán los ' + total + ' item(s) de ese día al menú de hoy.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, copiar',
        confirmButtonColor: '#0d6efd',
        cancelButtonText: 'Cancelar',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post('/comedor/menu/copiar-ultimo', { [csrfName]: csrfHash, fecha })
            .done(res => { if (res.ok) location.reload(); });
    });
});

// Limpiar
$('#btnLimpiar').on('click', function () {
    Swal.fire({
        title: '¿Limpiar el menú?',
        text: 'Se quitarán todos los items del menú de esta fecha.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, limpiar',
        confirmButtonColor: '#dc3545',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post('/comedor/menu/limpiar', { [csrfName]: csrfHash, fecha })
            .done(res => { if (res.ok) location.reload(); });
    });
});

// WhatsApp
$('#btnWhatsapp').on('click', function () {
    $.get('/comedor/menu/whatsapp', { fecha })
        .done(function (r) {
            if (!r.ok) return;
            $('#txtWhatsapp').val(r.texto);
            $('#modalWhatsapp').modal('show');
        });
});

$('#btnCopiarTexto').on('click', function () {
    const txt = $('#txtWhatsapp')[0];
    txt.select();
    document.execCommand('copy');
    $(this).html('<i class="fa-solid fa-check mr-1"></i>Copiado!');
    setTimeout(() => $(this).html('<i class="fa-solid fa-copy mr-1"></i>Copiar'), 1500);
});
</script>

<?= $this->endSection() ?>
