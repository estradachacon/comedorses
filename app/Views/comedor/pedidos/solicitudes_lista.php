<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">
            <i class="fa-solid fa-paper-plane mr-2 text-warning"></i><?= esc($title) ?>
        </h4>
        <span class="badge badge-warning text-dark px-3 py-2" id="badgeTotal">
            <?= count($solicitudes) ?> pendiente<?= count($solicitudes) !== 1 ? 's' : '' ?>
        </span>
    </div>

    <?php if (empty($solicitudes)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                <p class="mb-0">No hay solicitudes pendientes.</p>
            </div>
        </div>
    <?php else: ?>
    <div class="row" id="listaSolicitudes">
        <?php foreach ($solicitudes as $s): ?>
        <div class="col-md-6 col-lg-4 mb-3" id="card_<?= $s['id'] ?>">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="font-weight-bold"><?= esc($s['cliente_nombre']) ?></div>
                            <div class="text-muted small">
                                <?= esc($s['numero']) ?> ·
                                <?= date('d/m H:i', strtotime($s['created_at'])) ?>
                            </div>
                        </div>
                        <span class="badge badge-warning text-dark">Solicitud</span>
                    </div>

                    <div class="mb-2">
                        <?php if ($s['tipo_pago'] === 'fiado'): ?>
                            <span class="badge badge-info"><i class="fa-solid fa-clock mr-1"></i>Fiado</span>
                        <?php else: ?>
                            <span class="badge badge-success"><i class="fa-solid fa-money-bill-wave mr-1"></i>Contado</span>
                            <?php if (!empty($s['monto_recibido']) && (float) $s['monto_recibido'] > (float) $s['total']): ?>
                                <span class="text-muted small ml-1">
                                    Paga con $<?= number_format($s['monto_recibido'], 2) ?>
                                    · Cambio $<?= number_format($s['monto_recibido'] - $s['total'], 2) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small ml-1">Pago exacto</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($s['items'])): ?>
                    <ul class="list-unstyled mb-2" style="font-size:.82rem;">
                        <?php foreach ($s['items'] as $it): ?>
                        <li class="text-muted"><i class="fa-solid fa-circle-dot mr-1" style="font-size:.5rem;vertical-align:middle;"></i><?= esc($it) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <?php if ($s['notas']): ?>
                    <div class="text-muted small mb-2"><i class="fa-solid fa-note-sticky mr-1"></i><?= esc($s['notas']) ?></div>
                    <?php endif; ?>

                    <div class="h5 text-primary font-weight-bold mb-3">$<?= number_format($s['total'], 2) ?></div>

                    <div class="d-flex gap-2">
                        <a href="/comedor/pedidos/ver/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="fa-solid fa-eye mr-1"></i>Ver
                        </a>
                        <button class="btn btn-sm btn-success flex-fill btn-confirmar"
                            data-id="<?= $s['id'] ?>"
                            data-nombre="<?= esc($s['cliente_nombre'], 'attr') ?>"
                            data-total="<?= $s['total'] ?>"
                            data-numero="<?= esc($s['numero'], 'attr') ?>"
                            data-tipo-pago="<?= esc($s['tipo_pago'], 'attr') ?>"
                            data-cliente-id="<?= $s['cliente_id'] ?? '' ?>">
                            <i class="fa-solid fa-check mr-1"></i>Confirmar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-anular-sol" data-id="<?= $s['id'] ?>">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal confirmar -->
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
                        <button type="button" class="btn btn-outline-success tipo-btn active" data-tipo="contado">
                            <i class="fa-solid fa-money-bill-wave mr-1"></i>Contado
                        </button>
                        <button type="button" class="btn btn-outline-warning tipo-btn" data-tipo="fiado">
                            <i class="fa-solid fa-clock mr-1"></i>Fiado
                        </button>
                    </div>
                </div>
                <div id="rowClienteFiado" style="display:none;" class="form-group mb-0">
                    <label class="small font-weight-bold text-muted">COMENSAL REGISTRADO (para fiado)</label>
                    <input type="text" id="inputClienteFiado" class="form-control form-control-sm" placeholder="Buscar comensal...">
                    <div id="sugClienteFiado" class="list-group" style="position:absolute;z-index:999;width:90%;display:none;"></div>
                    <input type="hidden" id="clienteFiadoId">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnOkConfirmar">
                    <i class="fa-solid fa-check mr-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let solicitudActiva = null;

// Abrir modal confirmar
$(document).on('click', '.btn-confirmar', function () {
    solicitudActiva = {
        id:        $(this).data('id'),
        nombre:    $(this).data('nombre'),
        total:     parseFloat($(this).data('total')),
        numero:    $(this).data('numero'),
        tipoPago:  $(this).data('tipo-pago'),
        clienteId: $(this).data('cliente-id'),
    };
    $('#confCliente').text(solicitudActiva.nombre);
    $('#confDetalle').text('Pedido ' + solicitudActiva.numero);
    $('#confTotal').text('Total: $' + solicitudActiva.total.toFixed(2));

    // Precargar lo que el cliente ya eligió al pedir (el cajero puede corregirlo)
    const esFiado = solicitudActiva.tipoPago === 'fiado';
    $('.tipo-btn').removeClass('active');
    $('.tipo-btn[data-tipo="' + (esFiado ? 'fiado' : 'contado') + '"]').addClass('active');

    if (esFiado && solicitudActiva.clienteId) {
        $('#rowClienteFiado').show();
        $('#inputClienteFiado').val(solicitudActiva.nombre);
        $('#clienteFiadoId').val(solicitudActiva.clienteId);
    } else {
        $('#rowClienteFiado').toggle(esFiado);
        $('#inputClienteFiado').val('');
        $('#clienteFiadoId').val('');
    }
    $('#modalConfirmar').modal('show');
});

// Toggle tipo pago
$('.tipo-btn').on('click', function () {
    $('.tipo-btn').removeClass('active');
    $(this).addClass('active');
    if ($(this).data('tipo') === 'fiado') {
        $('#rowClienteFiado').show();
    } else {
        $('#rowClienteFiado').hide();
        $('#clienteFiadoId').val('');
        $('#inputClienteFiado').val('');
    }
});

// Buscar comensal para fiado
let searchTimer;
$('#inputClienteFiado').on('input', function () {
    $('#clienteFiadoId').val('');
    clearTimeout(searchTimer);
    const q = $(this).val().trim();
    if (q.length < 2) { $('#sugClienteFiado').hide(); return; }
    searchTimer = setTimeout(() => {
        $.get('/comedor/clientes/buscar', { q }).done(data => {
            if (!data.length) { $('#sugClienteFiado').hide(); return; }
            let html = '';
            data.forEach(c => {
                html += `<a href="#" class="list-group-item list-group-item-action py-1 px-2 sug-cli"
                    data-id="${c.id}" data-nombre="${c.nombre}" style="font-size:.85rem;">${c.nombre}</a>`;
            });
            $('#sugClienteFiado').html(html).show();
        });
    }, 250);
});

$(document).on('click', '.sug-cli', function (e) {
    e.preventDefault();
    $('#inputClienteFiado').val($(this).data('nombre'));
    $('#clienteFiadoId').val($(this).data('id'));
    $('#sugClienteFiado').hide();
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('#inputClienteFiado, #sugClienteFiado').length) $('#sugClienteFiado').hide();
});

// Confirmar
$('#btnOkConfirmar').on('click', function () {
    const tipoPago   = $('.tipo-btn.active').data('tipo');
    const clienteId  = $('#clienteFiadoId').val();

    if (tipoPago === 'fiado' && !clienteId) {
        Swal.fire('Comensal requerido', 'Para fiado debes seleccionar un comensal registrado.', 'warning');
        return;
    }

    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

    $.post('/comedor/pedidos/confirmar/' + solicitudActiva.id, {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        tipo_pago:  tipoPago,
        cliente_id: clienteId,
    }).done(res => {
        if (res.ok) {
            $('#modalConfirmar').modal('hide');
            $('#card_' + solicitudActiva.id).fadeOut(300, function () {
                $(this).remove();
                actualizarBadge();
            });
            Swal.fire({ icon: 'success', title: '¡Confirmado!', timer: 1200, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }).always(() => {
        $('#btnOkConfirmar').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Confirmar');
    });
});

// Anular solicitud
$(document).on('click', '.btn-anular-sol', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: '¿Anular solicitud?', icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        confirmButtonColor: '#dc3545',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post('/comedor/pedidos/anular/' + id, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
            .done(res => {
                if (res.ok) {
                    $('#card_' + id).fadeOut(300, function () {
                        $(this).remove();
                        actualizarBadge();
                    });
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            });
    });
});

function actualizarBadge() {
    const restantes = $('#listaSolicitudes .col-md-6').length;
    $('#badgeTotal').text(restantes + ' pendiente' + (restantes !== 1 ? 's' : ''));
    if (restantes === 0) {
        $('#listaSolicitudes').html('');
        $('.card.shadow-sm').first().replaceWith(`
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                    <p class="mb-0">No hay solicitudes pendientes.</p>
                </div>
            </div>
        `);
    }
}
</script>
<?= $this->endSection() ?>
