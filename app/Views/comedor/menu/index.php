<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa-solid fa-clipboard-list mr-2 text-success"></i><?= esc($title) ?>
        </h4>
        <div class="d-flex align-items-center gap-2">
            <!-- URL pública para QR -->
            <span class="text-muted small mr-2">
                <i class="fa-solid fa-qrcode mr-1"></i>
                <a href="<?= esc($urlPublico) ?>" target="_blank"><?= esc($urlPublico) ?></a>
            </span>
        </div>
    </div>

    <!-- Filtro de fecha + acciones rápidas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
            <form method="get" class="d-flex align-items-center mr-3">
                <label class="mr-2 mb-0 text-muted small font-weight-bold">FECHA</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="<?= esc($fecha) ?>" style="width:160px;">
                <button type="submit" class="btn btn-outline-secondary btn-sm ml-2">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </form>
            <button class="btn btn-sm btn-outline-success" id="btnAgregarTodos">
                <i class="fa-solid fa-check-double mr-1"></i>Agregar todos
            </button>
            <button class="btn btn-sm btn-outline-danger" id="btnLimpiar">
                <i class="fa-solid fa-trash mr-1"></i>Limpiar menú
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
    <div class="row">
        <?php foreach ($todos as $item): ?>
        <?php $activo = in_array($item['id'], $enMenu); ?>
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm item-toggle-card <?= $activo ? 'border-success' : '' ?>"
                 data-id="<?= $item['id'] ?>" data-activo="<?= $activo ? 1 : 0 ?>"
                 style="cursor:pointer; transition: border .15s;">
                <div class="card-body py-2 px-3 d-flex align-items-center">
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
                        <div class="text-muted" style="font-size:.75rem;"><?= esc($item['categoria_nombre'] ?? '—') ?></div>
                    </div>
                    <div class="text-success font-weight-bold ml-2" style="font-size:.9rem; flex-shrink:0;">
                        $<?= number_format($item['precio'], 2) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
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
    const card  = $(this).closest('.item-toggle-card');
    const id    = parseInt(card.data('id'));
    const chk   = this;

    $.post('/comedor/menu/toggle', { [csrfName]: csrfHash, item_id: id, fecha })
        .done(function (r) {
            if (!r.ok) { chk.checked = !chk.checked; return; }
            if (r.en_menu) {
                card.addClass('border-success');
                if (!enMenu.includes(id)) enMenu.push(id);
            } else {
                card.removeClass('border-success');
                enMenu = enMenu.filter(x => x !== id);
            }
            actualizarContador();
        })
        .fail(function () { chk.checked = !chk.checked; });
});

// Click en la card también activa el switch
$('.item-toggle-card').on('click', function (e) {
    if ($(e.target).is('input, label')) return;
    $(this).find('.switch-item').trigger('click');
});

// Agregar todos
$('#btnAgregarTodos').on('click', function () {
    $.post('/comedor/menu/agregar-todos', { [csrfName]: csrfHash, fecha })
        .done(function (r) {
            if (r.ok) location.reload();
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
</script>

<?= $this->endSection() ?>
