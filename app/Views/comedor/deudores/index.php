<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
:root {
    --sap-border: #d9d9d9;
    --sap-bg: #ffffff;
    --sap-text: #32363a;
    --sap-text-muted: #6a6d70;
}
.deudor-card {
    border-radius: 4px;
    border: 1px solid var(--sap-border);
    background: var(--sap-bg);
    padding: 10px 14px;
    margin-bottom: 6px;
    transition: border-color .12s, background .12s;
}
.deudor-card:hover { border-color: #b7b7b7; background: #fafbfc; }
.deudor-nombre { font-weight: 600; font-size: .92rem; color: var(--sap-text); }
.deudor-telefono { font-size: .76rem; color: var(--sap-text-muted); }
.search-box { position: relative; }
.search-box .fa-magnifying-glass {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #aaa; font-size: .85rem;
}
.search-box input { padding-left: 30px; }
@media (max-width: 480px) {
    .deudor-card .deudor-acciones { width: 100%; margin-top: 8px; justify-content: flex-start !important; }
    .deudor-card .deudor-acciones .btn { flex: 1; }
}
</style>

<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-start mb-3 pt-1" style="flex-wrap:wrap;gap:8px;">
        <h5 class="mb-0 font-weight-bold">
            <i class="fa-solid fa-hand-holding-dollar mr-2 text-danger"></i><?= esc($title) ?>
        </h5>
        <?php
            $totalDeudores = count(array_filter($comensales, fn($c) => (float) $c['saldo_pendiente'] > 0));
            $totalVueltos  = count(array_filter($comensales, fn($c) => (float) ($c['vuelto_pendiente'] ?? 0) > 0));
        ?>
        <span class="badge badge-danger badge-pill px-3 py-2" style="font-size:.8rem;">
            <?= $totalDeudores ?> con deuda · <?= $totalVueltos ?> con vuelto · <?= count($comensales) ?> comensal<?= count($comensales) !== 1 ? 'es' : '' ?>
        </span>
    </div>

    <?php if (empty($comensales)): ?>
        <div class="alert alert-info">
            <i class="fa-solid fa-circle-info mr-2"></i>
            No hay comensales registrados todavía.
        </div>
    <?php else: ?>

    <div class="search-box mb-3">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="buscadorDeudores" class="form-control form-control-sm"
               placeholder="Buscar comensal por nombre o teléfono…">
    </div>

    <div id="listaDeudores">
        <?php foreach ($comensales as $d): ?>
        <?php
            $tieneDeuda  = (float) $d['saldo_pendiente'] > 0;
            $tieneVuelto = (float) ($d['vuelto_pendiente'] ?? 0) > 0;
            $puedeCompensar = $tieneDeuda && $tieneVuelto;
        ?>
        <div class="deudor-card d-flex justify-content-between align-items-center"
             style="flex-wrap:wrap;"
             data-buscar="<?= strtolower(esc($d['nombre'], 'attr') . ' ' . esc($d['telefono'] ?? '', 'attr')) ?>">
            <div style="min-width:0;flex:1;">
                <div class="deudor-nombre"><?= esc($d['nombre']) ?></div>
                <div class="deudor-telefono"><?= esc($d['telefono'] ?? '—') ?></div>
                <div class="mt-1" style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php if ($tieneDeuda): ?>
                        <span class="badge badge-danger badge-pill px-2 py-1" style="font-size:.8rem;">
                            Debe $<?= number_format($d['saldo_pendiente'], 2) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($tieneVuelto): ?>
                        <span class="badge badge-info badge-pill px-2 py-1" style="font-size:.8rem;">
                            Vuelto $<?= number_format($d['vuelto_pendiente'], 2) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!$tieneDeuda && !$tieneVuelto): ?>
                        <span class="text-success small"><i class="fa-solid fa-circle-check mr-1"></i>Al día</span>
                    <?php endif; ?>
                </div>
                <?php if ($puedeCompensar): ?>
                <div class="mt-1">
                    <button class="btn btn-sm btn-outline-primary btn-compensar"
                        data-id="<?= $d['id'] ?>"
                        data-nombre="<?= esc($d['nombre'], 'attr') ?>"
                        data-saldo="<?= $d['saldo_pendiente'] ?>"
                        data-vuelto="<?= $d['vuelto_pendiente'] ?>">
                        <i class="fa-solid fa-arrows-rotate mr-1"></i>Compensar deuda con vuelto
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php if (tienePermiso('registrar_pago_deudor_comedor')): ?>
            <div class="deudor-acciones ml-2" style="display:flex;gap:4px;justify-content:flex-end;flex-shrink:0;">
                <?php if ($tieneDeuda): ?>
                <button class="btn btn-sm btn-success btn-pagar"
                    data-id="<?= $d['id'] ?>"
                    data-nombre="<?= esc($d['nombre'], 'attr') ?>"
                    data-saldo="<?= $d['saldo_pendiente'] ?>">
                    <i class="fa-solid fa-money-bill-wave mr-1"></i>Pago
                </button>
                <button class="btn btn-sm btn-outline-secondary btn-ver-pedidos"
                    data-id="<?= $d['id'] ?>" data-nombre="<?= esc($d['nombre'], 'attr') ?>">
                    <i class="fa-solid fa-list"></i>
                </button>
                <?php endif; ?>
                <?php if ($tieneVuelto): ?>
                <button class="btn btn-sm btn-info btn-vuelto"
                    data-id="<?= $d['id'] ?>"
                    data-nombre="<?= esc($d['nombre'], 'attr') ?>"
                    data-vuelto="<?= $d['vuelto_pendiente'] ?>">
                    <i class="fa-solid fa-hand-holding-dollar mr-1"></i>Vuelto
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div id="sinResultadosDeudores" class="text-center text-muted py-4" style="display:none;">
            <i class="fa-solid fa-magnifying-glass mb-2"></i>
            <p class="mb-0 small">Sin resultados para tu búsqueda.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal: Registrar Pago -->
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-money-bill-wave text-success mr-2"></i>
                    Registrar Pago — <span id="modalNombreCliente"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2">
                    <strong>Saldo total:</strong> <span id="modalSaldoTotal"></span>
                </div>
                <div class="form-group">
                    <label>Monto a abonar <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input type="number" id="montoPago" class="form-control" step="0.01" min="0.01" placeholder="0.00">
                    </div>
                    <small class="text-muted">El pago se aplica comenzando por los pedidos más antiguos.</small>
                </div>
                <div class="form-group">
                    <label>Notas</label>
                    <input type="text" id="notasPago" class="form-control" placeholder="Abono, Pago completo...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarPago">
                    <i class="fa-solid fa-check mr-1"></i>Confirmar Pago
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Dar Vuelto -->
<div class="modal fade" id="modalVuelto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-hand-holding-dollar text-info mr-2"></i>
                    Dar Vuelto — <span id="modalNombreVuelto"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2">
                    <strong>Vuelto pendiente:</strong> <span id="modalVueltoTotal"></span>
                </div>
                <div class="form-group mb-0">
                    <label>Monto que le vas a dar <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input type="number" id="montoVuelto" class="form-control" step="0.01" min="0.01" placeholder="0.00">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnConfirmarVuelto">
                    <i class="fa-solid fa-check mr-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver pedidos pendientes -->
<div class="modal fade" id="modalPedidos" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-list-check mr-2"></i>
                    Pedidos Pendientes — <span id="modalPedidosNombre"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" id="modalPedidosBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let clienteActivo = null;

$('.btn-pagar').on('click', function () {
    clienteActivo = {
        id:    $(this).data('id'),
        nombre: $(this).data('nombre'),
        saldo: parseFloat($(this).data('saldo')),
    };
    $('#modalNombreCliente').text(clienteActivo.nombre);
    $('#modalSaldoTotal').text('$' + clienteActivo.saldo.toFixed(2));
    $('#montoPago').val('').attr('max', clienteActivo.saldo);
    $('#notasPago').val('');
    $('#modalPago').modal('show');
});

$('#btnConfirmarPago').on('click', function () {
    const monto = parseFloat($('#montoPago').val());
    if (!monto || monto <= 0) {
        Swal.fire('Monto inválido', 'Ingresa un monto mayor a cero.', 'warning');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

    $.post('/comedor/deudores/pagar', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        cliente_id: clienteActivo.id,
        monto:      monto,
        notas:      $('#notasPago').val(),
    }).done(function (res) {
        if (res.ok) {
            $('#modalPago').modal('hide');
            Swal.fire('¡Pago registrado!', '', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }).always(function () {
        $('#btnConfirmarPago').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Confirmar Pago');
    });
});

let clienteVueltoActivo = null;

$('.btn-vuelto').on('click', function () {
    clienteVueltoActivo = {
        id:     $(this).data('id'),
        nombre: $(this).data('nombre'),
        vuelto: parseFloat($(this).data('vuelto')),
    };
    $('#modalNombreVuelto').text(clienteVueltoActivo.nombre);
    $('#modalVueltoTotal').text('$' + clienteVueltoActivo.vuelto.toFixed(2));
    $('#montoVuelto').val('').attr('max', clienteVueltoActivo.vuelto);
    $('#modalVuelto').modal('show');
});

$('#btnConfirmarVuelto').on('click', function () {
    const monto = parseFloat($('#montoVuelto').val());
    if (!monto || monto <= 0) {
        Swal.fire('Monto inválido', 'Ingresa un monto mayor a cero.', 'warning');
        return;
    }
    if (monto > clienteVueltoActivo.vuelto) {
        Swal.fire('Monto inválido', 'No puede ser mayor al vuelto pendiente.', 'warning');
        return;
    }
    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

    $.post('/comedor/deudores/vuelto', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        cliente_id: clienteVueltoActivo.id,
        monto:      monto,
    }).done(function (res) {
        if (res.ok) {
            $('#modalVuelto').modal('hide');
            Swal.fire('¡Vuelto entregado!', '', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }).always(function () {
        $('#btnConfirmarVuelto').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Confirmar');
    });
});

// Compensar: cuando un comensal debe y a la vez se le debe vuelto, se netean ambos en un
// solo paso en vez de cobrarle por un lado y darle cambio por otro.
$('.btn-compensar').on('click', function () {
    const btn     = $(this);
    const nombre  = btn.data('nombre');
    const saldo   = parseFloat(btn.data('saldo'));
    const vuelto  = parseFloat(btn.data('vuelto'));
    const monto   = Math.min(saldo, vuelto);

    Swal.fire({
        title: '¿Compensar deuda con el vuelto?',
        html: `A <strong>${nombre}</strong> se le aplicarán <strong>$${monto.toFixed(2)}</strong> del vuelto que se le debe
               directo a su deuda pendiente.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, compensar',
        cancelButtonText: 'Cancelar',
    }).then(r => {
        if (!r.isConfirmed) return;
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
        $.post('/comedor/deudores/compensar', {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
            cliente_id: btn.data('id'),
        }).done(res => {
            if (res.ok) {
                Swal.fire('¡Compensado!', '', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.msg, 'error');
                btn.prop('disabled', false).html('<i class="fa-solid fa-arrows-rotate mr-1"></i>Compensar deuda con vuelto');
            }
        }).fail(() => {
            Swal.fire('Error', 'No se pudo compensar.', 'error');
            btn.prop('disabled', false).html('<i class="fa-solid fa-arrows-rotate mr-1"></i>Compensar deuda con vuelto');
        });
    });
});

$('.btn-ver-pedidos').on('click', function () {
    const id     = $(this).data('id');
    const nombre = $(this).data('nombre');
    $('#modalPedidosNombre').text(nombre);
    $('#modalPedidosBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    $('#modalPedidos').modal('show');

    $.get('/comedor/deudores/pendientes/' + id).done(function (pedidos) {
        if (!pedidos.length) {
            $('#modalPedidosBody').html('<p class="text-center text-muted py-3">Sin pedidos pendientes.</p>');
            return;
        }
        let html = '<table class="table table-sm mb-0"><thead class="thead-light"><tr>' +
            '<th>N°</th><th>Fecha</th><th class="text-right">Total</th><th class="text-right">Saldo</th></tr></thead><tbody>';
        pedidos.forEach(p => {
            html += `<tr>
                <td><a href="/comedor/pedidos/ver/${p.id}" target="_blank">${p.numero}</a></td>
                <td>${p.fecha}</td>
                <td class="text-right">$${parseFloat(p.total).toFixed(2)}</td>
                <td class="text-right text-danger">$${parseFloat(p.saldo).toFixed(2)}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        $('#modalPedidosBody').html(html);
    });
});

$('#buscadorDeudores').on('input', function () {
    const q = $(this).val().toLowerCase().trim();
    let visible = 0;
    $('.deudor-card').each(function () {
        const match = !q || $(this).data('buscar').includes(q);
        $(this).toggle(match);
        if (match) visible++;
    });
    $('#sinResultadosDeudores').toggle(visible === 0 && q.length > 0);
});
</script>

<?= $this->endSection() ?>
