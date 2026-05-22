<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
.pedido-card {
    border-radius: 10px;
    border: 1px solid #e3e6ea;
    background: #fff;
    padding: 12px 14px;
    margin-bottom: 8px;
    transition: box-shadow .15s;
    cursor: default;
}
.pedido-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,.08); }
.pedido-numero { font-weight: 700; font-size: .85rem; color: #4e73df; }
.pedido-cliente { font-weight: 600; font-size: .95rem; }
.pedido-cajero { font-size: .78rem; color: #888; }
.pedido-total { font-weight: 700; font-size: 1.05rem; }
.pedido-saldo { font-size: .8rem; font-weight: 600; color: #e74a3b; }
.search-box { position: relative; }
.search-box .fa-magnifying-glass {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #aaa; font-size: .85rem;
}
.search-box input { padding-left: 30px; }
.sol-card {
    border-radius: 10px;
    border: 2px solid #f6c23e;
    background: #fffdf0;
    padding: 12px 14px;
    margin-bottom: 8px;
}
</style>

<div class="container-fluid px-3">

    <!-- ── ENCABEZADO ─────────────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-3 pt-1">
        <h5 class="mb-0 font-weight-bold">
            <i class="fa-solid fa-receipt mr-2 text-primary"></i><?= esc($title) ?>
        </h5>
        <?php if (tienePermiso('tomar_pedido_comedor')): ?>
        <a href="/comedor/pedidos/nuevo" class="btn btn-success btn-sm">
            <i class="fa-solid fa-plus mr-1"></i><span class="d-none d-sm-inline">Nuevo </span>Pedido
        </a>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- ── SOLICITUDES PENDIENTES ──────────────────────────────────────── -->
    <?php if (tienePermiso('confirmar_solicitud_comedor')): ?>
    <div id="panelSolicitudes" class="mb-3" style="display:none;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="font-weight-bold text-warning" style="font-size:.9rem;">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                Solicitudes pendientes
                <span class="badge badge-warning text-dark ml-1" id="badgeSolicitudes">0</span>
            </span>
            <a href="/comedor/solicitudes" class="text-muted small">Ver todas</a>
        </div>
        <div id="listaSolicitudes"></div>
    </div>
    <?php endif; ?>

    <!-- ── FILTRO FECHA ────────────────────────────────────────────────── -->
    <form method="get" class="mb-3">
        <div class="d-flex align-items-center gap-2" style="gap:.5rem;">
            <input type="date" name="fecha" class="form-control form-control-sm" value="<?= esc($fecha) ?>" style="max-width:160px;">
            <button class="btn btn-outline-secondary btn-sm" type="submit">
                <i class="fa-solid fa-filter"></i>
            </button>
            <?php if ($fecha !== date('Y-m-d')): ?>
            <a href="/comedor/pedidos" class="btn btn-outline-primary btn-sm">Hoy</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ── RESUMEN ─────────────────────────────────────────────────────── -->
    <?php if (!empty($resumen)): ?>
    <div class="row mb-3 mx-n1">
        <div class="col-6 col-md-3 px-1 mb-2">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #4e73df !important; border-radius:8px;">
                <div class="card-body py-2 px-3">
                    <div style="font-size:.7rem;" class="text-primary text-uppercase font-weight-bold mb-1">Pedidos</div>
                    <div class="h5 mb-0"><?= $resumen['cantidad_pedidos'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1 mb-2">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #1cc88a !important; border-radius:8px;">
                <div class="card-body py-2 px-3">
                    <div style="font-size:.7rem;" class="text-success text-uppercase font-weight-bold mb-1">Vendido</div>
                    <div class="h5 mb-0">$<?= number_format($resumen['ventas_total'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1 mb-2">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #36b9cc !important; border-radius:8px;">
                <div class="card-body py-2 px-3">
                    <div style="font-size:.7rem;" class="text-info text-uppercase font-weight-bold mb-1">Cobrado</div>
                    <div class="h5 mb-0">$<?= number_format($resumen['cobrado_total'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1 mb-2">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f6c23e !important; border-radius:8px;">
                <div class="card-body py-2 px-3">
                    <div style="font-size:.7rem;" class="text-warning text-uppercase font-weight-bold mb-1">Fiado</div>
                    <div class="h5 mb-0">$<?= number_format($resumen['pendiente_total'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── BUSCADOR ────────────────────────────────────────────────────── -->
    <?php if (!empty($pedidos)): ?>
    <div class="search-box mb-3">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="buscadorPedidos" class="form-control form-control-sm"
               placeholder="Buscar por cliente o número…">
    </div>
    <?php endif; ?>

    <!-- ── LISTA DE PEDIDOS ────────────────────────────────────────────── -->
    <div id="listaPedidos">
    <?php if (empty($pedidos)): ?>
        <div class="text-center text-muted py-5">
            <i class="fa-solid fa-receipt fa-2x mb-2" style="opacity:.25;"></i>
            <p class="mb-0">No hay pedidos para esta fecha.</p>
        </div>
    <?php else: ?>
        <?php
        $mapEstado = ['pagado'=>'success','pendiente'=>'warning text-dark','anulado'=>'secondary'];
        ?>
        <?php foreach ($pedidos as $p): ?>
        <div class="pedido-card"
             data-buscar="<?= strtolower(esc($p['cliente_nombre'], 'attr') . ' ' . esc($p['numero'], 'attr')) ?>">
            <div class="d-flex justify-content-between align-items-start">
                <!-- Izquierda -->
                <div style="min-width:0;flex:1;">
                    <div class="d-flex align-items-center flex-wrap" style="gap:6px;">
                        <a href="/comedor/pedidos/ver/<?= $p['id'] ?>" class="pedido-numero"><?= esc($p['numero']) ?></a>
                        <span class="badge badge-<?= $mapEstado[$p['estado']] ?? 'light' ?>" style="font-size:.72rem;">
                            <?= ucfirst($p['estado']) ?>
                        </span>
                        <span class="badge badge-<?= $p['tipo_pago'] === 'contado' ? 'success' : 'warning text-dark' ?>" style="font-size:.72rem;">
                            <?= ucfirst($p['tipo_pago']) ?>
                        </span>
                    </div>
                    <div class="pedido-cliente mt-1"><?= esc($p['cliente_nombre']) ?></div>
                    <?php if (!empty($p['cajero_nombre'])): ?>
                    <div class="pedido-cajero"><i class="fa-solid fa-user-tie mr-1"></i><?= esc($p['cajero_nombre']) ?></div>
                    <?php endif; ?>
                </div>
                <!-- Derecha -->
                <div class="text-right ml-3 flex-shrink-0">
                    <div class="pedido-total">$<?= number_format($p['total'], 2) ?></div>
                    <?php if ($p['saldo'] > 0): ?>
                    <div class="pedido-saldo">Debe $<?= number_format($p['saldo'], 2) ?></div>
                    <?php endif; ?>
                    <div class="mt-1" style="display:flex;gap:4px;justify-content:flex-end;">
                        <a href="/comedor/pedidos/ver/<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" style="padding:2px 8px;">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <?php if (tienePermiso('anular_pedido_comedor') && !$p['anulado']): ?>
                        <button class="btn btn-sm btn-outline-danger btn-anular" data-id="<?= $p['id'] ?>" style="padding:2px 8px;">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div id="sinResultados" class="text-center text-muted py-4" style="display:none;">
            <i class="fa-solid fa-magnifying-glass mb-2"></i>
            <p class="mb-0 small">Sin resultados para tu búsqueda.</p>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Modal: confirmar solicitud -->
<div class="modal fade" id="modalConfirmar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-check-circle text-success mr-2"></i>Confirmar Solicitud
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong id="confCliente"></strong>
                    <div class="text-muted small" id="confDetalle"></div>
                    <div class="font-weight-bold text-primary mt-1" id="confTotal"></div>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold">Tipo de pago</label>
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" class="btn btn-outline-success tipo-conf-btn active" data-tipo="contado">
                            <i class="fa-solid fa-money-bill-wave mr-1"></i>Contado
                        </button>
                        <button type="button" class="btn btn-outline-warning tipo-conf-btn" data-tipo="fiado">
                            <i class="fa-solid fa-clock mr-1"></i>Fiado
                        </button>
                    </div>
                </div>
                <div id="confClienteRow" style="display:none;" class="form-group mb-0">
                    <label class="small font-weight-bold text-muted">COMENSAL REGISTRADO (para fiado)</label>
                    <input type="text" id="confClienteInput" class="form-control form-control-sm" placeholder="Buscar comensal...">
                    <div id="confClienteSug" class="list-group" style="position:absolute;z-index:999;width:90%;display:none;"></div>
                    <input type="hidden" id="confClienteId">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarOk">
                    <i class="fa-solid fa-check mr-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// ── Búsqueda en vivo ──────────────────────────────────────────────────
$('#buscadorPedidos').on('input', function () {
    const q = $(this).val().toLowerCase().trim();
    let visible = 0;
    $('.pedido-card').each(function () {
        const match = !q || $(this).data('buscar').includes(q);
        $(this).toggle(match);
        if (match) visible++;
    });
    $('#sinResultados').toggle(visible === 0 && q.length > 0);
});

// ── Anular ────────────────────────────────────────────────────────────
$(document).on('click', '.btn-anular', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: '¿Anular pedido?', icon: 'warning',
        showCancelButton: true, confirmButtonText: 'Sí, anular',
        confirmButtonColor: '#dc3545',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post('/comedor/pedidos/anular/' + id, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
            .done(res => {
                if (res.ok) location.reload();
                else Swal.fire('Error', res.msg, 'error');
            });
    });
});

<?php if (tienePermiso('confirmar_solicitud_comedor')): ?>
// ── Solicitudes ───────────────────────────────────────────────────────
let solicitudActiva = null;

function renderSolicitudes(lista) {
    if (!lista.length) { $('#panelSolicitudes').hide(); return; }
    $('#panelSolicitudes').show();
    $('#badgeSolicitudes').text(lista.length);
    let html = '';
    lista.forEach(p => {
        html += `
        <div class="sol-card d-flex justify-content-between align-items-center">
            <div style="min-width:0;flex:1;">
                <div class="font-weight-bold" style="font-size:.9rem;">${p.cliente_nombre}</div>
                <div class="text-muted" style="font-size:.78rem;">${p.numero}</div>
            </div>
            <div class="text-right ml-2 flex-shrink-0">
                <div class="font-weight-bold text-primary">$${parseFloat(p.total).toFixed(2)}</div>
                <div class="mt-1" style="display:flex;gap:4px;">
                    <a href="/comedor/pedidos/ver/${p.id}" class="btn btn-sm btn-outline-secondary" style="padding:2px 8px;">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <button class="btn btn-sm btn-success btn-confirmar-sol" style="padding:2px 10px;"
                        data-id="${p.id}" data-nombre="${p.cliente_nombre}"
                        data-total="${p.total}" data-numero="${p.numero}">
                        <i class="fa-solid fa-check mr-1"></i>Confirmar
                    </button>
                </div>
            </div>
        </div>`;
    });
    $('#listaSolicitudes').html(html);
}

function cargarSolicitudes() {
    $.get('/comedor/pedidos/solicitudes').done(renderSolicitudes);
}

cargarSolicitudes();
setInterval(cargarSolicitudes, 15000);

$(document).on('click', '.btn-confirmar-sol', function () {
    solicitudActiva = {
        id:     $(this).data('id'),
        nombre: $(this).data('nombre'),
        total:  parseFloat($(this).data('total')),
        numero: $(this).data('numero'),
    };
    $('#confCliente').text(solicitudActiva.nombre);
    $('#confDetalle').text('Pedido ' + solicitudActiva.numero);
    $('#confTotal').text('Total: $' + solicitudActiva.total.toFixed(2));
    $('.tipo-conf-btn[data-tipo="contado"]').addClass('active');
    $('.tipo-conf-btn[data-tipo="fiado"]').removeClass('active');
    $('#confClienteRow').hide();
    $('#confClienteInput,#confClienteId').val('');
    $('#modalConfirmar').modal('show');
});

$('.tipo-conf-btn').on('click', function () {
    $('.tipo-conf-btn').removeClass('active');
    $(this).addClass('active');
    const esFiado = $(this).data('tipo') === 'fiado';
    $('#confClienteRow').toggle(esFiado);
    if (!esFiado) $('#confClienteInput,#confClienteId').val('');
});

let searchTimer;
$('#confClienteInput').on('input', function () {
    $('#confClienteId').val('');
    clearTimeout(searchTimer);
    const q = $(this).val().trim();
    if (q.length < 2) { $('#confClienteSug').hide(); return; }
    searchTimer = setTimeout(() => {
        $.get('/comedor/clientes/buscar', { q }).done(data => {
            if (!data.length) { $('#confClienteSug').hide(); return; }
            $('#confClienteSug').html(
                data.map(c => `<a href="#" class="list-group-item list-group-item-action py-1 px-2 conf-cli-sug"
                    data-id="${c.id}" data-nombre="${c.nombre}" style="font-size:.85rem;">${c.nombre}</a>`).join('')
            ).show();
        });
    }, 250);
});

$(document).on('click', '.conf-cli-sug', function (e) {
    e.preventDefault();
    $('#confClienteInput').val($(this).data('nombre'));
    $('#confClienteId').val($(this).data('id'));
    $('#confClienteSug').hide();
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('#confClienteInput,#confClienteSug').length) $('#confClienteSug').hide();
});

$('#btnConfirmarOk').on('click', function () {
    const tipoPago  = $('.tipo-conf-btn.active').data('tipo');
    const clienteId = $('#confClienteId').val();
    if (tipoPago === 'fiado' && !clienteId) {
        Swal.fire('Comensal requerido', 'Selecciona un comensal registrado para fiado.', 'warning');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
    $.post('/comedor/pedidos/confirmar/' + solicitudActiva.id, {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        tipo_pago: tipoPago, cliente_id: clienteId,
    }).done(res => {
        if (res.ok) {
            $('#modalConfirmar').modal('hide');
            Swal.fire({ icon: 'success', title: '¡Confirmado!', timer: 1200, showConfirmButton: false })
                .then(() => { cargarSolicitudes(); location.reload(); });
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }).always(() => {
        $('#btnConfirmarOk').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Confirmar');
    });
});
<?php endif; ?>
</script>
<?= $this->endSection() ?>
