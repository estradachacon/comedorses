<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
$badgeMap   = ['pagado' => 'success', 'pendiente' => 'warning text-dark', 'anulado' => 'secondary', 'solicitud' => 'info'];
$badgeClass = $badgeMap[$pedido['estado']] ?? 'light';
?>

<style>
.ver-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e3e6ea;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    margin-bottom: 14px;
    overflow: hidden;
}
.ver-card-header {
    padding: 10px 16px;
    font-weight: 700;
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #555;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
}
.ver-card-body { padding: 14px 16px; }
.item-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 7px 0;
    border-bottom: 1px dashed #f0f0f0;
    font-size: .9rem;
}
.item-row:last-child { border-bottom: none; }
.item-name { font-weight: 500; }
.item-qty  { font-size: .78rem; color: #888; margin-top: 2px; }
.item-sub  { font-weight: 600; white-space: nowrap; margin-left: 12px; }
.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0 4px;
    font-weight: 700;
    font-size: 1.05rem;
    border-top: 2px solid #e3e6ea;
    margin-top: 4px;
}
.kv-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: .88rem;
}
.kv-row:last-child { border-bottom: none; }
.kv-label { color: #888; }
.kv-value { font-weight: 600; text-align: right; }
.pago-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: .85rem;
}
.pago-row:last-child { border-bottom: none; }

@media (max-width: 575px) {
    .ver-header-row { flex-wrap: wrap; }
    .ver-header-acciones {
        width: 100%;
        order: 99;
        display: flex;
        gap: 8px;
        margin-top: 4px;
    }
    .ver-header-acciones .btn { flex: 1; padding: .5rem; }
}
</style>

<div class="container-fluid px-3 pb-4">

    <!-- ── ENCABEZADO ─────────────────────────────────────────────────── -->
    <div class="d-flex mb-3 pt-1 ver-header-row" style="gap:10px;">
        <a href="/comedor/pedidos" class="btn btn-outline-secondary btn-sm" style="flex-shrink:0;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div style="flex:1;min-width:0;">
            <div class="d-flex flex-wrap" style="gap:6px;">
                <span class="font-weight-bold" style="font-size:1rem;"><?= esc(formatearNumeroPedido($pedido['numero'])) ?></span>
                <span class="badge badge-<?= $badgeClass ?>" style="font-size:.78rem;">
                    <?= ucfirst($pedido['estado']) ?>
                </span>
                <span class="badge badge-<?= $pedido['tipo_pago'] === 'contado' ? 'success' : 'warning text-dark' ?>" style="font-size:.78rem;">
                    <?= ucfirst($pedido['tipo_pago']) ?>
                </span>
            </div>
            <div class="text-muted" style="font-size:.78rem;">
                <?= date('d/m/Y', strtotime($pedido['fecha'])) ?>
                <?php if (!empty($pedido['created_at'])): ?>
                · <?= date('H:i', strtotime($pedido['created_at'])) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="ver-header-acciones" style="flex-shrink:0;">
        <?php if (tienePermiso('confirmar_solicitud_comedor') && $pedido['estado'] === 'solicitud' && !$pedido['anulado']): ?>
        <button class="btn btn-sm btn-success" id="btnConfirmarPedido">
            <i class="fa-solid fa-check mr-1"></i><span class="d-none d-sm-inline">Confirmar</span>
        </button>
        <?php endif; ?>
        <?php if (tienePermiso('anular_pedido_comedor') && !$pedido['anulado']): ?>
        <button class="btn btn-sm btn-outline-danger" id="btnAnularPedido">
            <i class="fa-solid fa-ban mr-1"></i><span class="d-none d-sm-inline">Anular</span>
        </button>
        <?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($pedido['anulado']): ?>
    <div class="alert alert-secondary py-2 mb-3" style="border-radius:10px;">
        <i class="fa-solid fa-ban mr-1"></i>
        Este pedido fue anulado el <?= date('d/m/Y H:i', strtotime($pedido['fecha_anulacion'])) ?>.
    </div>
    <?php endif; ?>

    <?php if ($comensalActual): ?>
    <div class="alert alert-info py-2 mb-3" style="border-radius:10px;">
        <i class="fa-solid fa-right-left mr-1"></i>
        El pedido se tomó a nombre de <strong><?= esc($pedido['cliente_nombre']) ?></strong>, pero la
        cuenta/deuda quedó atribuida a <strong><?= esc($comensalActual['nombre']) ?></strong>.
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- ── COL IZQUIERDA: Items ──────────────────────────────────── -->
        <div class="col-12 col-md-7">

            <!-- Items del pedido -->
            <div class="ver-card">
                <div class="ver-card-header">
                    <i class="fa-solid fa-utensils mr-1"></i> Detalle del pedido
                </div>
                <div class="ver-card-body">
                    <?php foreach ($detalles as $d): ?>
                    <div class="item-row">
                        <div style="flex:1;min-width:0;">
                            <div class="item-name">
                                <?= esc($d['item_nombre']) ?>
                                <?php if (!empty($d['servicio'])): ?>
                                <span class="badge badge-light text-muted" style="font-size:.68rem;font-weight:600;"><?= etiquetaServicioComedor($d['servicio']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="item-qty"><?= (int)$d['cantidad'] ?> × $<?= number_format($d['precio_unitario'], 2) ?></div>
                        </div>
                        <div class="item-sub">$<?= number_format($d['subtotal'], 2) ?></div>
                    </div>
                    <?php endforeach; ?>

                    <div class="total-row">
                        <span>Total</span>
                        <span>$<?= number_format($pedido['total'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Notas -->
            <?php if (!empty($pedido['notas'])): ?>
            <div class="ver-card">
                <div class="ver-card-header"><i class="fa-solid fa-note-sticky mr-1"></i> Comentario del comensal</div>
                <div class="ver-card-body text-muted" style="font-size:.9rem;">
                    <?= esc($pedido['notas']) ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ── COL DERECHA: Info + Pagos ────────────────────────────── -->
        <div class="col-12 col-md-5">

            <!-- Información general -->
            <div class="ver-card">
                <div class="ver-card-header"><i class="fa-solid fa-circle-info mr-1"></i> Información</div>
                <div class="ver-card-body">
                    <div class="kv-row">
                        <span class="kv-label">Cliente</span>
                        <span class="kv-value"><?= esc($pedido['cliente_nombre']) ?></span>
                    </div>
                    <?php if ($comensalActual): ?>
                    <div class="kv-row">
                        <span class="kv-label">Deuda atribuida a</span>
                        <span class="kv-value text-info"><?= esc($comensalActual['nombre']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="kv-row">
                        <span class="kv-label">Total</span>
                        <span class="kv-value">$<?= number_format($pedido['total'], 2) ?></span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Pagado</span>
                        <span class="kv-value text-success">$<?= number_format($pedido['monto_pagado'], 2) ?></span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Saldo</span>
                        <span class="kv-value <?= $pedido['saldo'] > 0 ? 'text-danger' : 'text-success' ?>">
                            $<?= number_format($pedido['saldo'], 2) ?>
                        </span>
                    </div>
                    <?php if ($pedido['tipo_pago'] === 'contado' && !empty($pedido['monto_recibido']) && (float) $pedido['monto_recibido'] > (float) $pedido['total']): ?>
                    <div class="kv-row">
                        <span class="kv-label">Pagó con</span>
                        <span class="kv-value">$<?= number_format($pedido['monto_recibido'], 2) ?></span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Vuelto a entregar</span>
                        <span class="kv-value text-info">
                            $<?= number_format($pedido['monto_recibido'] - $pedido['total'], 2) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historial de pagos -->
            <?php if (!empty($pagos)): ?>
            <div class="ver-card">
                <div class="ver-card-header"><i class="fa-solid fa-money-bill-wave mr-1"></i> Pagos</div>
                <div class="ver-card-body">
                    <?php foreach ($pagos as $pg): ?>
                    <div class="pago-row">
                        <div>
                            <div class="font-weight-bold text-success">$<?= number_format($pg['monto'], 2) ?></div>
                            <?php if (!empty($pg['notas'])): ?>
                            <div class="text-muted" style="font-size:.78rem;"><?= esc($pg['notas']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size:.78rem;">
                            <?= date('d/m H:i', strtotime($pg['created_at'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php if (tienePermiso('confirmar_solicitud_comedor') && $pedido['estado'] === 'solicitud' && !$pedido['anulado']): ?>
<!-- Modal: confirmar solicitud -->
<div class="modal fade" id="modalConfirmarVer" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-check-circle text-success mr-2"></i>Confirmar Solicitud
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="font-weight-bold">Tipo de pago</label>
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" class="btn btn-outline-success tipo-conf-ver-btn<?= $pedido['tipo_pago'] === 'contado' ? ' active' : '' ?>" data-tipo="contado">
                            <i class="fa-solid fa-money-bill-wave mr-1"></i>Contado
                        </button>
                        <button type="button" class="btn btn-outline-warning tipo-conf-ver-btn<?= $pedido['tipo_pago'] === 'fiado' ? ' active' : '' ?>" data-tipo="fiado">
                            <i class="fa-solid fa-clock mr-1"></i>Fiado
                        </button>
                    </div>
                </div>
                <div id="confVerClienteRow" style="<?= $pedido['tipo_pago'] === 'fiado' ? '' : 'display:none;' ?>" class="form-group mb-0">
                    <label class="small font-weight-bold text-muted">COMENSAL REGISTRADO (para fiado)</label>
                    <input type="text" id="confVerClienteInput" class="form-control form-control-sm" placeholder="Buscar comensal..." value="<?= $pedido['cliente_id'] ? esc($pedido['cliente_nombre'], 'attr') : '' ?>">
                    <div id="confVerClienteSug" class="list-group" style="position:absolute;z-index:999;width:90%;display:none;"></div>
                    <input type="hidden" id="confVerClienteId" value="<?= $pedido['cliente_id'] ?? '' ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarVerOk">
                    <i class="fa-solid fa-check mr-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
<?php if (tienePermiso('confirmar_solicitud_comedor') && $pedido['estado'] === 'solicitud' && !$pedido['anulado']): ?>
$('#btnConfirmarPedido').on('click', function () {
    $('#modalConfirmarVer').modal('show');
});

$('.tipo-conf-ver-btn').on('click', function () {
    $('.tipo-conf-ver-btn').removeClass('active');
    $(this).addClass('active');
    const esFiado = $(this).data('tipo') === 'fiado';
    $('#confVerClienteRow').toggle(esFiado);
    if (!esFiado) { $('#confVerClienteInput,#confVerClienteId').val(''); }
});

let searchTimerVer;
$('#confVerClienteInput').on('input', function () {
    $('#confVerClienteId').val('');
    clearTimeout(searchTimerVer);
    const q = $(this).val().trim();
    if (q.length < 2) { $('#confVerClienteSug').hide(); return; }
    searchTimerVer = setTimeout(() => {
        $.get('/comedor/clientes/buscar', { q }).done(data => {
            if (!data.length) { $('#confVerClienteSug').hide(); return; }
            $('#confVerClienteSug').html(
                data.map(c => `<a href="#" class="list-group-item list-group-item-action py-1 px-2 conf-ver-cli-sug"
                    data-id="${c.id}" data-nombre="${c.nombre}" style="font-size:.85rem;">${c.nombre}</a>`).join('')
            ).show();
        });
    }, 250);
});

$(document).on('click', '.conf-ver-cli-sug', function (e) {
    e.preventDefault();
    $('#confVerClienteInput').val($(this).data('nombre'));
    $('#confVerClienteId').val($(this).data('id'));
    $('#confVerClienteSug').hide();
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('#confVerClienteInput,#confVerClienteSug').length) $('#confVerClienteSug').hide();
});

$('#btnConfirmarVerOk').on('click', function () {
    const tipoPago  = $('.tipo-conf-ver-btn.active').data('tipo');
    const clienteId = $('#confVerClienteId').val();
    if (tipoPago === 'fiado' && !clienteId) {
        Swal.fire('Comensal requerido', 'Selecciona un comensal registrado para fiado.', 'warning');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
    $.post('/comedor/pedidos/confirmar/<?= $pedido['id'] ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        tipo_pago: tipoPago, cliente_id: clienteId,
    }).done(res => {
        if (res.ok) {
            location.reload();
        } else {
            Swal.fire('Error', res.msg, 'error');
            $('#btnConfirmarVerOk').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Confirmar');
        }
    });
});
<?php endif; ?>

$('#btnAnularPedido').on('click', function () {
    Swal.fire({
        title: '¿Anular este pedido?',
        text: 'Si era fiado con saldo pendiente, se revertirá la deuda del cliente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post('/comedor/pedidos/anular/<?= $pedido['id'] ?>', {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }).done(res => {
            if (res.ok) location.reload();
            else Swal.fire('Error', res.msg, 'error');
        });
    });
});
</script>
<?= $this->endSection() ?>
