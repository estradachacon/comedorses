<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-2 px-sm-4">
    <div class="d-flex flex-wrap justify-content-between mb-3" style="gap:8px;">
        <h4 class="mb-0" style="font-size:1.25rem;">
            <i class="fa-solid fa-paper-plane mr-2 text-warning"></i><?= esc($title) ?>
        </h4>
        <span class="badge badge-warning text-dark px-3 py-2" id="badgeTotal">
            <?= count($solicitudes) ?> pendiente<?= count($solicitudes) !== 1 ? 's' : '' ?>
        </span>
    </div>

    <div id="solicitudesWrapper">
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
                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-2" style="gap:6px;">
                        <div style="min-width:0;">
                            <div class="font-weight-bold" style="word-break:break-word;"><?= esc($s['cliente_nombre']) ?></div>
                            <div class="text-muted small">
                                <?= esc(formatearNumeroPedido($s['numero'])) ?> ·
                                <?= date('d/m H:i', strtotime($s['created_at'])) ?>
                            </div>
                        </div>
                        <span class="badge badge-warning text-dark flex-shrink-0">Solicitud</span>
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

                    <div class="d-flex" style="gap:6px;">
                        <a href="/comedor/pedidos/ver/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="fa-solid fa-eye mr-1"></i>Ver
                        </a>
                        <button class="btn btn-sm btn-success flex-fill btn-confirmar"
                            data-id="<?= $s['id'] ?>"
                            data-nombre="<?= esc($s['cliente_nombre'], 'attr') ?>">
                            <i class="fa-solid fa-check mr-1"></i>Confirmar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-anular-sol flex-shrink-0" data-id="<?= $s['id'] ?>">
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
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Formatea "P202600009" -> "Pedido 000009-2026" (mismo valor, solo presentación)
function formatearNumeroPedido(numero) {
    if (!numero) return '';
    const m = /^P(\d{4})(\d+)$/.exec(numero);
    if (!m) return numero;
    return 'Pedido ' + m[2].padStart(6, '0') + '-' + m[1];
}

// Formatea "2026-10-08 14:32:10" (datetime de MySQL) -> "08/10 14:32"
function formatearFechaHora(fecha) {
    const m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(fecha || '');
    if (!m) return fecha || '';
    return `${m[3]}/${m[2]} ${m[4]}:${m[5]}`;
}

function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

// Reconstruye toda la lista a partir del JSON vivo, para que las solicitudes nuevas
// aparezcan sin que el cajero tenga que refrescar la página manualmente.
function renderListaSolicitudes(lista) {
    $('#badgeTotal').text(lista.length + ' pendiente' + (lista.length !== 1 ? 's' : ''));

    if (!lista.length) {
        $('#solicitudesWrapper').html(`
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                    <p class="mb-0">No hay solicitudes pendientes.</p>
                </div>
            </div>
        `);
        return;
    }

    let html = '<div class="row" id="listaSolicitudes">';
    lista.forEach(s => {
        const itemsHtml = (s.items || []).map(it =>
            `<li class="text-muted"><i class="fa-solid fa-circle-dot mr-1" style="font-size:.5rem;vertical-align:middle;"></i>${escapeHtml(it)}</li>`
        ).join('');
        const pagoHtml = s.tipo_pago === 'fiado'
            ? '<span class="badge badge-info"><i class="fa-solid fa-clock mr-1"></i>Fiado</span>'
            : (`<span class="badge badge-success"><i class="fa-solid fa-money-bill-wave mr-1"></i>Contado</span>` +
               (s.monto_recibido && parseFloat(s.monto_recibido) > parseFloat(s.total)
                   ? `<span class="text-muted small ml-1">Paga con $${parseFloat(s.monto_recibido).toFixed(2)} · Cambio $${(parseFloat(s.monto_recibido) - parseFloat(s.total)).toFixed(2)}</span>`
                   : '<span class="text-muted small ml-1">Pago exacto</span>'));

        html += `
        <div class="col-md-6 col-lg-4 mb-3" id="card_${s.id}">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body py-3 px-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-2" style="gap:6px;">
                        <div style="min-width:0;">
                            <div class="font-weight-bold" style="word-break:break-word;">${escapeHtml(s.cliente_nombre)}</div>
                            <div class="text-muted small">${formatearNumeroPedido(s.numero)} · ${formatearFechaHora(s.created_at)}</div>
                        </div>
                        <span class="badge badge-warning text-dark flex-shrink-0">Solicitud</span>
                    </div>
                    <div class="mb-2">${pagoHtml}</div>
                    ${itemsHtml ? `<ul class="list-unstyled mb-2" style="font-size:.82rem;">${itemsHtml}</ul>` : ''}
                    ${s.notas ? `<div class="text-muted small mb-2"><i class="fa-solid fa-note-sticky mr-1"></i>${escapeHtml(s.notas)}</div>` : ''}
                    <div class="h5 text-primary font-weight-bold mb-3">$${parseFloat(s.total).toFixed(2)}</div>
                    <div class="d-flex" style="gap:6px;">
                        <a href="/comedor/pedidos/ver/${s.id}" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="fa-solid fa-eye mr-1"></i>Ver
                        </a>
                        <button class="btn btn-sm btn-success flex-fill btn-confirmar"
                            data-id="${s.id}" data-nombre="${escapeHtml(s.cliente_nombre)}">
                            <i class="fa-solid fa-check mr-1"></i>Confirmar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-anular-sol flex-shrink-0" data-id="${s.id}">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    });
    html += '</div>';
    $('#solicitudesWrapper').html(html);
}

function cargarSolicitudesLista() {
    $.get('/comedor/pedidos/solicitudes').done(renderListaSolicitudes);
}
setInterval(cargarSolicitudesLista, 15000);

// Confirmar: solo acepta la solicitud y la pasa al control del comedor (NO la entrega ni
// resuelve el pago — eso se decide hasta /comedor/entregas). Por eso no se vuelve a preguntar
// tipo de pago ni comensal aquí: ya se definieron cuando el cliente hizo el pedido.
$(document).on('click', '.btn-confirmar', function () {
    const id     = $(this).data('id');
    const nombre = $(this).data('nombre');
    const btn    = $(this);

    Swal.fire({
        title: '¿Confirmar esta solicitud?',
        text: `Se acepta el pedido de ${nombre} y pasa al control del comedor. El pago se resuelve hasta la entrega.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
    }).then(r => {
        if (!r.isConfirmed) return;
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

        $.post('/comedor/pedidos/confirmar/' + id, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
            .done(res => {
                if (res.ok) {
                    $('#card_' + id).fadeOut(300, function () {
                        $(this).remove();
                        cargarSolicitudesLista();
                    });
                    Swal.fire({ icon: 'success', title: '¡Confirmado!', timer: 1000, showConfirmButton: false });
                } else {
                    Swal.fire('Error', res.msg, 'error');
                    btn.prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Confirmar');
                }
            })
            .fail(() => {
                Swal.fire('Error', 'No se pudo confirmar.', 'error');
                btn.prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Confirmar');
            });
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
                        cargarSolicitudesLista();
                    });
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            });
    });
});
</script>
<?= $this->endSection() ?>
