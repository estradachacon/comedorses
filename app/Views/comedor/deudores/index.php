<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between mb-4">
        <h4 class="mb-0">
            <i class="fa-solid fa-hand-holding-dollar mr-2 text-danger"></i><?= esc($title) ?>
        </h4>
        <?php $totalDeudores = count(array_filter($comensales, fn($c) => (float) $c['saldo_pendiente'] > 0)); ?>
        <span class="badge badge-danger badge-pill px-3 py-2" style="font-size:.85rem;">
            <?= $totalDeudores ?> con deuda · <?= count($comensales) ?> comensal<?= count($comensales) !== 1 ? 'es' : '' ?>
        </span>
    </div>

    <?php if (empty($comensales)): ?>
        <div class="alert alert-info">
            <i class="fa-solid fa-circle-info mr-2"></i>
            No hay comensales registrados todavía.
        </div>
    <?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table id="tablaDeudores" class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Comensal</th>
                        <th>Teléfono</th>
                        <th class="text-right">Saldo Pendiente</th>
                        <?php if (tienePermiso('registrar_pago_deudor_comedor')): ?><th class="text-center">Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comensales as $d): ?>
                    <?php $tieneDeuda = (float) $d['saldo_pendiente'] > 0; ?>
                    <tr>
                        <td class="font-weight-bold"><?= esc($d['nombre']) ?></td>
                        <td class="text-muted"><?= esc($d['telefono'] ?? '—') ?></td>
                        <td class="text-right">
                            <?php if ($tieneDeuda): ?>
                                <span class="badge badge-danger badge-pill px-2 py-1" style="font-size:.9rem;">
                                    $<?= number_format($d['saldo_pendiente'], 2) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-light text-muted px-2 py-1" style="font-size:.9rem;">
                                    $0.00
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php if (tienePermiso('registrar_pago_deudor_comedor')): ?>
                        <td class="text-center">
                            <?php if ($tieneDeuda): ?>
                            <button class="btn btn-sm btn-success btn-pagar"
                                data-id="<?= $d['id'] ?>"
                                data-nombre="<?= esc($d['nombre'], 'attr') ?>"
                                data-saldo="<?= $d['saldo_pendiente'] ?>">
                                <i class="fa-solid fa-money-bill-wave mr-1"></i>Registrar Pago
                            </button>
                            <button class="btn btn-sm btn-outline-secondary btn-ver-pedidos"
                                data-id="<?= $d['id'] ?>" data-nombre="<?= esc($d['nombre'], 'attr') ?>">
                                <i class="fa-solid fa-list"></i>
                            </button>
                            <?php else: ?>
                            <span class="text-success small"><i class="fa-solid fa-circle-check mr-1"></i>Al día</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

$('#tablaDeudores').DataTable({ pageLength: 25, order: [[2, 'desc']] });
</script>

<?= $this->endSection() ?>
