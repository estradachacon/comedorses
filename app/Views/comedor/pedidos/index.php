<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
:root {
    --sap-border: #d9d9d9;
    --sap-bg: #ffffff;
    --sap-text: #32363a;
    --sap-text-muted: #6a6d70;
    --sap-accent: #0a6ed1;
}
.kpi-tile {
    background: var(--sap-bg);
    border: 1px solid var(--sap-border);
    border-radius: 4px;
    padding: 10px 12px;
    height: 100%;
}
.kpi-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--sap-text-muted);
    font-weight: 700;
    margin-bottom: 4px;
}
.kpi-value { font-size: 1.15rem; font-weight: 700; color: var(--sap-text); }

.pedido-card, .sol-card {
    border-radius: 4px;
    border: 1px solid var(--sap-border);
    background: var(--sap-bg);
    padding: 10px 14px;
    margin-bottom: 6px;
    transition: border-color .12s, background .12s;
}
.pedido-card:hover, .sol-card:hover { border-color: #b7b7b7; background: #fafbfc; }
.sol-card { border-left: 3px solid #c98a1f; }

.pedido-numero {
    font-weight: 600;
    font-size: .8rem;
    color: var(--sap-text-muted);
    font-family: 'Consolas', 'Courier New', monospace;
}
.pedido-numero:hover { color: var(--sap-accent); }
.pedido-cliente { font-weight: 600; font-size: .92rem; color: var(--sap-text); }
.pedido-cajero { font-size: .76rem; color: var(--sap-text-muted); }
.pedido-total { font-weight: 700; font-size: 1rem; color: var(--sap-text); }
.pedido-saldo { font-size: .78rem; font-weight: 600; color: #a6420f; }

.tag {
    display: inline-block;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 2px 7px;
    border-radius: 3px;
    line-height: 1.4;
}
.tag-success   { background: #eaf5ec; color: #237a3f; }
.tag-warning   { background: #fdf0dd; color: #9b5b0a; }
.tag-info      { background: #e6eef7; color: #1c5a96; }
.tag-secondary { background: #eceef0; color: #5b5f63; }

.search-box { position: relative; }
.search-box .fa-magnifying-glass {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #aaa; font-size: .85rem;
}
.search-box input { padding-left: 30px; }
</style>

<div class="container-fluid px-3">

    <!-- ── ENCABEZADO ─────────────────────────────────────────────────── -->
    <div class="d-flex justify-content-between mb-3 pt-1">
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
        <div class="d-flex justify-content-between mb-2">
            <span class="font-weight-bold" style="font-size:.9rem;color:var(--sap-text);">
                <i class="fa-solid fa-triangle-exclamation mr-1" style="color:#9b5b0a;"></i>
                Solicitudes pendientes
                <span class="tag tag-warning ml-1" id="badgeSolicitudes">0</span>
            </span>
            <a href="/comedor/solicitudes" class="text-muted small">Ver todas</a>
        </div>
        <div id="listaSolicitudes"></div>
    </div>
    <?php endif; ?>

    <!-- ── FILTRO FECHA ────────────────────────────────────────────────── -->
    <form method="get" class="mb-3">
        <div class="d-flex gap-2" style="gap:.5rem;">
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
            <div class="kpi-tile">
                <div class="kpi-label">Pedidos</div>
                <div class="kpi-value"><?= $resumen['cantidad_pedidos'] ?? 0 ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1 mb-2">
            <div class="kpi-tile">
                <div class="kpi-label">Vendido</div>
                <div class="kpi-value">$<?= number_format($resumen['ventas_total'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1 mb-2">
            <div class="kpi-tile">
                <div class="kpi-label">Cobrado</div>
                <div class="kpi-value">$<?= number_format($resumen['cobrado_total'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1 mb-2">
            <div class="kpi-tile">
                <div class="kpi-label">Fiado</div>
                <div class="kpi-value">$<?= number_format($resumen['pendiente_total'] ?? 0, 2) ?></div>
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
        $mapEstado = ['pagado' => 'tag-success', 'pendiente' => 'tag-warning', 'anulado' => 'tag-secondary'];
        ?>
        <?php foreach ($pedidos as $p): ?>
        <div class="pedido-card"
             data-buscar="<?= strtolower(esc($p['cliente_nombre'], 'attr') . ' ' . esc(formatearNumeroPedido($p['numero']), 'attr') . ' ' . esc($p['numero'], 'attr')) ?>">
            <div class="d-flex justify-content-between align-items-start">
                <!-- Izquierda -->
                <div style="min-width:0;flex:1;">
                    <div class="d-flex flex-wrap" style="gap:6px;">
                        <a href="/comedor/pedidos/ver/<?= $p['id'] ?>" class="pedido-numero"><?= esc(formatearNumeroPedido($p['numero'])) ?></a>
                        <span class="tag <?= $mapEstado[$p['estado']] ?? 'tag-secondary' ?>">
                            <?= ucfirst($p['estado']) ?>
                        </span>
                        <span class="tag <?= $p['tipo_pago'] === 'contado' ? 'tag-secondary' : 'tag-warning' ?>">
                            <?= ucfirst($p['tipo_pago']) ?>
                        </span>
                    </div>
                    <div class="pedido-cliente mt-1"><?= esc($p['cliente_nombre']) ?></div>
                    <?php
                        $deudaReasignada = !empty($p['comensal_actual_nombre'])
                            && mb_strtolower(trim($p['comensal_actual_nombre'])) !== mb_strtolower(trim($p['cliente_nombre']));
                    ?>
                    <?php if ($deudaReasignada): ?>
                    <div class="text-info" style="font-size:.74rem;font-weight:600;"
                         title="El pedido se tomó a nombre de un comensal, pero la deuda/cuenta quedó atribuida a otro">
                        <i class="fa-solid fa-right-left mr-1"></i>Atribuido a <?= esc($p['comensal_actual_nombre']) ?>
                    </div>
                    <?php endif; ?>
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
function renderSolicitudes(lista) {
    if (!lista.length) { $('#panelSolicitudes').hide(); return; }
    $('#panelSolicitudes').show();
    $('#badgeSolicitudes').text(lista.length);
    let html = '';
    lista.forEach(p => {
        const itemsHtml = (p.items || []).map(it =>
            `<div class="text-muted" style="font-size:.78rem;">
                <i class="fa-solid fa-circle-dot mr-1" style="font-size:.4rem;vertical-align:middle;"></i>${it}
            </div>`
        ).join('');
        const tipoTag = p.tipo_pago === 'fiado'
            ? '<span class="tag tag-warning">Fiado</span>'
            : '<span class="tag tag-secondary">Contado</span>';

        html += `
        <div class="sol-card d-flex justify-content-between">
            <div style="min-width:0;flex:1;">
                <div class="d-flex" style="gap:6px;">
                    <span class="font-weight-bold" style="font-size:.9rem;">${p.cliente_nombre}</span>
                    ${tipoTag}
                </div>
                <div class="pedido-numero" style="font-size:.78rem;">${formatearNumeroPedido(p.numero)}</div>
                <div class="mt-1">${itemsHtml}</div>
                ${p.notas ? `<div class="text-muted" style="font-size:.78rem;"><i class="fa-solid fa-note-sticky mr-1"></i>${p.notas}</div>` : ''}
            </div>
            <div class="text-right ml-2 flex-shrink-0">
                <div class="font-weight-bold" style="color:var(--sap-text);">$${parseFloat(p.total).toFixed(2)}</div>
                <div class="mt-1" style="display:flex;gap:4px;">
                    <a href="/comedor/pedidos/ver/${p.id}" class="btn btn-sm btn-outline-secondary" style="padding:2px 8px;">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <button class="btn btn-sm btn-success btn-confirmar-sol" style="padding:2px 10px;"
                        data-id="${p.id}" data-nombre="${p.cliente_nombre}">
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

// Confirmar: solo acepta la solicitud y la pasa al control del comedor (NO la entrega ni
// resuelve el pago — eso se decide hasta /comedor/entregas). Por eso no se vuelve a preguntar
// tipo de pago ni comensal aquí: ya se definieron cuando el cliente hizo el pedido.
$(document).on('click', '.btn-confirmar-sol', function () {
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
                    Swal.fire({ icon: 'success', title: '¡Confirmado!', timer: 1000, showConfirmButton: false })
                        .then(() => location.reload());
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
<?php endif; ?>
</script>
<?= $this->endSection() ?>
