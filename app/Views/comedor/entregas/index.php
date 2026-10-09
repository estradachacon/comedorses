<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
:root {
    --sap-border: #d9d9d9;
    --sap-bg: #ffffff;
    --sap-text: #32363a;
    --sap-text-muted: #6a6d70;
}
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

.tab-horario {
    border: 1px solid var(--sap-border);
    background: var(--sap-bg);
    color: var(--sap-text-muted);
    border-radius: 4px;
    padding: 8px 16px;
    font-weight: 700;
    font-size: .85rem;
    cursor: pointer;
}
.tab-horario.active { background: #0a6ed1; border-color: #0a6ed1; color: #fff; }

.tab-modo {
    border: 1px solid var(--sap-border);
    background: var(--sap-bg);
    color: var(--sap-text-muted);
    border-radius: 4px;
    padding: 5px 12px;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
}
.tab-modo.active { background: var(--sap-text); border-color: var(--sap-text); color: #fff; }

.entrega-card {
    border: 1px solid var(--sap-border);
    border-radius: 4px;
    background: var(--sap-bg);
    padding: 10px 14px;
    margin-bottom: 6px;
    transition: border-color .15s, background-color .15s, box-shadow .15s;
}
.entrega-card.selected {
    border-color: #0a6ed1;
    background: #eef6fb;
    box-shadow: 0 0 0 1px #0a6ed1 inset;
}
.entrega-numero { font-size: .78rem; color: var(--sap-text-muted); font-family: 'Consolas', 'Courier New', monospace; }
.entrega-item-row { font-size: .82rem; color: var(--sap-text-muted); }

.chk-tuani {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    border: 2px solid #c6cbd1;
    cursor: pointer;
    accent-color: #0a6ed1;
    flex-shrink: 0;
    margin-top: 2px;
}
#filaSeleccion {
    background: #eef6fb;
    border: 1px solid #c9e2f5;
    border-radius: 6px;
    padding: 8px 12px;
}

@media (max-width: 480px) {
    .entrega-card.d-flex { flex-direction: column; gap: 8px; }
    .entrega-card .text-right.ml-2 {
        margin-left: 0 !important;
        text-align: left;
        width: 100%;
    }
    .entrega-card .text-right.ml-2 > div:last-child {
        justify-content: flex-start !important;
    }
    .entrega-card .btn { flex: 1; padding: .5rem; }
    #filaSeleccion { flex-direction: column; align-items: stretch !important; }
    #btnConfirmarMasivo { width: 100%; }
}
</style>

<div class="container-fluid px-3">

    <div class="d-flex justify-content-between mb-3 pt-1">
        <h5 class="mb-0 font-weight-bold">
            <i class="fa-solid fa-dolly mr-2 text-primary"></i><?= esc($title) ?>
        </h5>
    </div>

    <div class="d-flex flex-wrap mb-3" style="gap:8px;">
        <button type="button" class="tab-horario" data-servicio="desayuno">Desayuno</button>
        <button type="button" class="tab-horario" data-servicio="refrigerio">Refrigerio</button>
        <button type="button" class="tab-horario" data-servicio="almuerzo">Almuerzo</button>
    </div>

    <div class="d-flex flex-wrap justify-content-between mb-3" style="gap:8px;">
        <div style="gap:6px;" class="d-flex">
            <button type="button" class="tab-modo active" data-modo="cliente">Por cliente</button>
            <button type="button" class="tab-modo" data-modo="item">Por item</button>
        </div>
        <span class="text-muted small" id="totalLlamado"></span>
    </div>

    <div id="filaSeleccion" class="d-flex flex-wrap justify-content-between mb-3" style="display:none;gap:10px;">
        <label class="d-flex align-items-center mb-0" style="gap:8px;cursor:pointer;">
            <input type="checkbox" id="chkSeleccionarTodos" class="chk-tuani">
            <span class="small font-weight-bold" style="color:var(--sap-text);">Seleccionar todos</span>
        </label>
        <button type="button" class="btn btn-sm btn-primary" id="btnConfirmarMasivo" disabled>
            <i class="fa-solid fa-check-double mr-1"></i>Confirmar seleccionados (<span id="cantSeleccionados">0</span>)
        </button>
    </div>

    <div id="listaEntregas">
        <div class="text-center text-muted py-5">
            <i class="fa-solid fa-dolly fa-2x mb-2" style="opacity:.25;"></i>
            <p class="mb-0">Elige un horario para llamar los pedidos pendientes de entregar.</p>
        </div>
    </div>
</div>

<!-- Modal: confirmar entrega -->
<div class="modal fade" id="modalEntregar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-truck text-success mr-2"></i>Confirmar Entrega
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong id="entCliente"></strong>
                    <div class="text-muted small" id="entNumero"></div>
                    <div class="font-weight-bold text-primary mt-1" id="entTotal"></div>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold">Tipo de pago</label>
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" class="btn btn-outline-success tipo-ent-btn" data-tipo="contado">
                            <i class="fa-solid fa-money-bill-wave mr-1"></i>Contado
                        </button>
                        <button type="button" class="btn btn-outline-warning tipo-ent-btn" data-tipo="fiado">
                            <i class="fa-solid fa-clock mr-1"></i>Fiado
                        </button>
                    </div>
                </div>
                <div id="entContadoRow" class="form-group mb-2" style="display:none;">
                    <label class="small font-weight-bold text-muted">PAGÓ CON</label>
                    <div class="input-group input-group-sm mb-1">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input type="number" min="0" step="0.01" id="entMontoRecibido" class="form-control">
                    </div>
                    <div class="text-muted small mb-2" id="entCambioTexto"></div>
                    <div class="custom-control custom-checkbox" id="entVueltoPendienteRow" style="display:none;">
                        <input type="checkbox" class="custom-control-input" id="entVueltoPendienteCheck">
                        <label class="custom-control-label small" for="entVueltoPendienteCheck">
                            No tengo el vuelto ahora, se lo quedo debiendo
                        </label>
                    </div>
                </div>
                <!-- El pedido ya trae un comensal vinculado (fiado siempre requiere cuenta o alta
                     rápida desde antes): se muestra de solo lectura, sin volver a pedirlo. -->
                <div id="entClienteVinculado" class="form-group mb-0" style="display:none;">
                    <label class="small font-weight-bold text-muted">COMENSAL (fiado)</label>
                    <div class="d-flex align-items-center justify-content-between" style="background:#eef6fb;border:1px solid #bfe0f7;border-radius:8px;padding:8px 10px;">
                        <span><i class="fa-solid fa-circle-check text-success mr-1"></i><strong id="entClienteVinculadoNombre"></strong></span>
                        <span class="text-muted small" style="cursor:pointer;" id="btnCambiarClienteEnt">Cambiar</span>
                    </div>
                </div>
                <!-- Fallback: solo si, por alguna razón, el pedido no trae comensal asociado -->
                <div id="entClienteRow" style="display:none;position:relative;" class="form-group mb-0">
                    <label class="small font-weight-bold text-muted">COMENSAL REGISTRADO (para fiado)</label>
                    <input type="text" id="entClienteInput" class="form-control form-control-sm" placeholder="Buscar comensal...">
                    <div id="entClienteSug" class="list-group" style="position:absolute;z-index:999;width:100%;display:none;"></div>
                    <input type="hidden" id="entClienteId">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarEntrega">
                    <i class="fa-solid fa-check mr-1"></i>Confirmar Entrega
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfName = '<?= csrf_token() ?>';
const csrfHash = '<?= csrf_hash() ?>';
const servicioDefault = '<?= esc($defaultServicio) ?>';

let servicioActivo = null;
let modoVista = 'cliente';
let datosActuales = [];
let pedidoActivo = null;
let pollTimer = null;
let seleccionados = new Set();

function fetchDatos(servicio) {
    return $.get('/comedor/entregas/llamar', { servicio });
}

function cargar(servicio) {
    servicioActivo = servicio;
    seleccionados.clear();
    $('.tab-horario').removeClass('active');
    $('.tab-horario[data-servicio="' + servicio + '"]').addClass('active');
    $('#listaEntregas').html('<div class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin"></i></div>');
    fetchDatos(servicio).done(function (data) {
        datosActuales = data;
        render();
    });
    reiniciarPolling();
}

function actualizarBarraSeleccion() {
    const haySolicitudes = datosActuales.some(p => p.estado === 'solicitud');
    $('#filaSeleccion').toggle(modoVista === 'cliente' && haySolicitudes);
    $('#cantSeleccionados').text(seleccionados.size);
    $('#btnConfirmarMasivo').prop('disabled', seleccionados.size === 0);
    const totalSolicitudes = datosActuales.filter(p => p.estado === 'solicitud').length;
    $('#chkSeleccionarTodos').prop('checked', totalSolicitudes > 0 && seleccionados.size === totalSolicitudes);
}

// Revisa cada pocos segundos si hay pedidos nuevos o si alguien (otro cajero, otro dispositivo)
// ya entregó/confirmó/anuló alguno — e inyecta los cambios sin recargar toda la pantalla.
function reiniciarPolling() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(actualizarSilencioso, 8000);
}

function actualizarSilencioso() {
    if (!servicioActivo || $('#modalEntregar').hasClass('show')) return; // no interrumpir una entrega en curso
    fetchDatos(servicioActivo).done(function (data) {
        if (modoVista !== 'cliente') {
            datosActuales = data;
            render();
            return;
        }
        aplicarDiff(data);
    });
}

function aplicarDiff(data) {
    const idsNuevos   = data.map(p => p.id);
    const idsActuales = datosActuales.map(p => p.id);

    // Quitar los que ya no están (los procesó alguien más, u otro dispositivo)
    datosActuales.forEach(p => {
        if (!idsNuevos.includes(p.id)) {
            seleccionados.delete(p.id);
            $('#entregaCard_' + p.id).fadeOut(250, function () {
                $(this).remove();
                if (!$('#listaEntregas .entrega-card').length) renderPorCliente();
            });
        }
    });

    // Agregar los pedidos nuevos, o refrescar los que cambiaron de estado
    data.forEach(p => {
        const anterior = datosActuales.find(x => x.id === p.id);
        if (!anterior) {
            $('#listaEntregas .text-center.text-muted').remove();
            const $card = $(cardHtml(p)).hide();
            $('#listaEntregas').append($card);
            $card.fadeIn(250);
        } else if (JSON.stringify(anterior) !== JSON.stringify(p)) {
            $('#entregaCard_' + p.id).replaceWith(cardHtml(p));
        }
    });

    datosActuales = data;
    $('#totalLlamado').text(data.length + ' pedido' + (data.length !== 1 ? 's' : ''));
    actualizarBarraSeleccion();
}

function render() {
    if (modoVista === 'cliente') {
        renderPorCliente();
    } else {
        renderPorItem();
    }
}

// "2026-10-06 13:48:25" -> "06/10 13:48"
function formatFechaHora(fechaStr) {
    if (!fechaStr) return '';
    const [f, h] = fechaStr.split(' ');
    const partes = f.split('-');
    return partes[2] + '/' + partes[1] + ' ' + (h || '').slice(0, 5);
}

function infoPagoHtml(p) {
    if (p.tipo_pago === 'fiado') return '';
    const total = parseFloat(p.total);
    const monto = parseFloat(p.monto_recibido);
    return (monto && monto > total)
        ? `<div class="text-muted small">Paga con $${monto.toFixed(2)} · Cambio $${(monto - total).toFixed(2)}</div>`
        : `<div class="text-muted small">Pago exacto</div>`;
}

function cardHtml(p) {
    const itemsHtml = p.items.map(it =>
        `<div class="entrega-item-row">${parseFloat(it.cantidad)}× ${it.nombre}</div>`
    ).join('');
    const tipoTag = p.tipo_pago === 'fiado'
        ? '<span class="tag tag-warning">Fiado</span>'
        : '<span class="tag tag-secondary">Contado</span>';
    const estadoTag = p.estado === 'solicitud' ? '<span class="tag tag-info">Sin confirmar</span>' : '';
    const parcialTag = p.ya_entregado_parcial
        ? '<span class="tag tag-success" title="Ya se le entregó algo de este pedido en otro horario">Entrega parcial</span>'
        : '';
    const confirmarBtn = p.estado === 'solicitud'
        ? `<button type="button" class="btn btn-sm btn-outline-primary mt-1 btn-confirmar-ent" data-id="${p.id}">
                <i class="fa-solid fa-check mr-1"></i>Confirmar
           </button>`
        : '';
    const estaSeleccionado = p.estado === 'solicitud' && seleccionados.has(p.id);
    const checkboxHtml = p.estado === 'solicitud'
        ? `<input type="checkbox" class="chk-tuani chk-solicitud" data-id="${p.id}" ${estaSeleccionado ? 'checked' : ''}>`
        : '<span style="display:inline-block;width:22px;flex-shrink:0;"></span>';

    const subtotalLlamado = parseFloat(p.subtotal_llamado);
    const totalPedido      = parseFloat(p.total);
    const esParteDelTotal  = Math.abs(subtotalLlamado - totalPedido) > 0.009;

    return `
    <div class="entrega-card d-flex justify-content-between${estaSeleccionado ? ' selected' : ''}" id="entregaCard_${p.id}">
        <div class="d-flex" style="min-width:0;flex:1;gap:10px;">
            ${checkboxHtml}
            <div style="min-width:0;flex:1;">
                <div class="d-flex flex-wrap" style="gap:6px;">
                    <span class="font-weight-bold" style="font-size:.9rem;">${p.cliente_nombre}</span>
                    ${tipoTag} ${estadoTag} ${parcialTag}
                </div>
                <div class="entrega-numero">${p.numero_formateado} · pedido a las ${formatFechaHora(p.created_at)}</div>
                ${infoPagoHtml(p)}
                <div class="mt-1">${itemsHtml}</div>
            </div>
        </div>
        <div class="text-right ml-2 flex-shrink-0">
            <div class="font-weight-bold">$${subtotalLlamado.toFixed(2)}</div>
            ${esParteDelTotal ? `<div class="text-muted" style="font-size:.7rem;">de $${totalPedido.toFixed(2)} del pedido</div>` : ''}
            <div class="mt-1" style="display:flex;gap:4px;justify-content:flex-end;">
                ${confirmarBtn}
                <button type="button" class="btn btn-sm btn-success btn-entregar mt-1" data-id="${p.id}">
                    <i class="fa-solid fa-truck mr-1"></i>Entregar
                </button>
            </div>
        </div>
    </div>`;
}

function renderPorCliente() {
    $('#totalLlamado').text(datosActuales.length + ' pedido' + (datosActuales.length !== 1 ? 's' : ''));
    if (!datosActuales.length) {
        $('#listaEntregas').html('<div class="text-center text-muted py-5">No hay pedidos pendientes de entregar para este horario.</div>');
        actualizarBarraSeleccion();
        return;
    }
    $('#listaEntregas').html(datosActuales.map(cardHtml).join(''));
    actualizarBarraSeleccion();
}

function renderPorItem() {
    $('#filaSeleccion').hide();
    const totales = {};
    datosActuales.forEach(p => {
        p.items.forEach(it => {
            totales[it.nombre] = (totales[it.nombre] || 0) + parseFloat(it.cantidad);
        });
    });
    const nombres = Object.keys(totales).sort();
    $('#totalLlamado').text(nombres.length + ' item' + (nombres.length !== 1 ? 's' : ''));
    if (!nombres.length) {
        $('#listaEntregas').html('<div class="text-center text-muted py-5">No hay items para este horario.</div>');
        return;
    }
    let html = '<div class="entrega-card">';
    nombres.forEach(n => {
        html += `<div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;font-size:.9rem;">
            <span>${n}</span><span class="font-weight-bold">${totales[n]}</span>
        </div>`;
    });
    html += '</div>';
    $('#listaEntregas').html(html);
}

$('.tab-horario').on('click', function () {
    cargar($(this).data('servicio'));
});

$('.tab-modo').on('click', function () {
    $('.tab-modo').removeClass('active');
    $(this).addClass('active');
    modoVista = $(this).data('modo');
    render();
});

// ── Entregar ──────────────────────────────────────────────────────────
$(document).on('click', '.btn-entregar', function () {
    const id = $(this).data('id');
    pedidoActivo = datosActuales.find(p => p.id == id);
    if (!pedidoActivo) return;

    // Los pedidos tomados en el POS (origen "cajero") ya resolvieron el pago completo desde que
    // se crearon: aquí solo se confirma la entrega física, sin volver a cobrar ni preguntar tipo
    // de pago, comensal ni vuelto.
    if (pedidoActivo.origen === 'cajero') {
        const itemsTxt = pedidoActivo.items.map(it => `${parseFloat(it.cantidad)}× ${it.nombre}`).join(', ');
        Swal.fire({
            title: '¿Entregar estos items?',
            text: itemsTxt,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, entregar',
            cancelButtonText: 'Cancelar',
        }).then(r => {
            if (!r.isConfirmed) return;
            $.post('/comedor/entregas/marcar/' + id, { [csrfName]: csrfHash, servicio: servicioActivo })
                .done(res => {
                    if (res.ok) {
                        datosActuales = datosActuales.filter(p => p.id !== id);
                        render();
                        Swal.fire({ icon: 'success', title: '¡Entregado!', timer: 1000, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', res.msg, 'error');
                    }
                })
                .fail(() => Swal.fire('Error', 'No se pudo marcar la entrega.', 'error'));
        });
        return;
    }

    const montoEvento = parseFloat(pedidoActivo.subtotal_llamado);
    const esParteDelTotal = Math.abs(montoEvento - parseFloat(pedidoActivo.total)) > 0.009;

    $('#entCliente').text(pedidoActivo.cliente_nombre);
    $('#entNumero').text(pedidoActivo.numero_formateado);
    $('#entTotal').text(
        'A cobrar ahora: $' + montoEvento.toFixed(2) +
        (esParteDelTotal ? ' (de $' + parseFloat(pedidoActivo.total).toFixed(2) + ' del pedido)' : '')
    );

    const esFiado = pedidoActivo.tipo_pago === 'fiado';
    $('.tipo-ent-btn').removeClass('active');
    $('.tipo-ent-btn[data-tipo="' + (esFiado ? 'fiado' : 'contado') + '"]').addClass('active');

    mostrarClienteFiadoEnt(esFiado);
    $('#entContadoRow').toggle(!esFiado);
    $('#entMontoRecibido').val(montoEvento);
    actualizarCambioEntrega();

    $('#modalEntregar').modal('show');
});

$('.tipo-ent-btn').on('click', function () {
    $('.tipo-ent-btn').removeClass('active');
    $(this).addClass('active');
    const esFiado = $(this).data('tipo') === 'fiado';
    mostrarClienteFiadoEnt(esFiado);
    $('#entContadoRow').toggle(!esFiado);
});

// El comensal del pedido ya viene vinculado (fiado siempre se hace con cuenta o alta rápida
// desde antes), así que se muestra de solo lectura. Solo se ofrece buscar/cambiar si, por
// alguna razón, el pedido no trae comensal asociado, o si el cajero toca "Cambiar".
function mostrarClienteFiadoEnt(esFiado) {
    if (!esFiado) {
        $('#entClienteVinculado, #entClienteRow').hide();
        $('#entClienteInput, #entClienteId').val('');
        return;
    }
    if (pedidoActivo && pedidoActivo.cliente_id) {
        $('#entClienteVinculadoNombre').text(pedidoActivo.cliente_nombre);
        $('#entClienteId').val(pedidoActivo.cliente_id);
        $('#entClienteVinculado').show();
        $('#entClienteRow').hide();
    } else {
        $('#entClienteVinculado').hide();
        $('#entClienteRow').show();
        $('#entClienteInput, #entClienteId').val('');
    }
}

$('#btnCambiarClienteEnt').on('click', function () {
    $('#entClienteVinculado').hide();
    $('#entClienteRow').show();
    $('#entClienteInput').val('').focus();
    $('#entClienteId').val('');
});

// Calcula el cambio en vivo y muestra el checkbox de "vuelto pendiente" solo si hay cambio.
// El cambio se calcula sobre lo que se está cobrando AHORA (este llamado), no sobre el total
// del pedido completo si todavía hay otros horarios pendientes de entregar.
function actualizarCambioEntrega() {
    const montoEvento = parseFloat(pedidoActivo?.subtotal_llamado) || 0;
    const monto = parseFloat($('#entMontoRecibido').val());
    const cambio = (monto || 0) - montoEvento;

    if (!monto || cambio <= 0) {
        $('#entCambioTexto').text(monto ? 'Pago exacto.' : '');
        $('#entVueltoPendienteRow').hide();
        $('#entVueltoPendienteCheck').prop('checked', false);
    } else {
        $('#entCambioTexto').text('Cambio a entregar: $' + cambio.toFixed(2));
        $('#entVueltoPendienteRow').show();
    }
}

$('#entMontoRecibido').on('input', actualizarCambioEntrega);

// ── Selección múltiple para confirmar varios de un solo golpe ───────────
$(document).on('change', '.chk-solicitud', function () {
    const id = $(this).data('id');
    if (this.checked) seleccionados.add(id); else seleccionados.delete(id);
    $(this).closest('.entrega-card').toggleClass('selected', this.checked);
    actualizarBarraSeleccion();
});

$('#chkSeleccionarTodos').on('change', function () {
    const marcar = this.checked;
    $('.chk-solicitud').prop('checked', marcar).each(function () {
        const id = $(this).data('id');
        if (marcar) seleccionados.add(id); else seleccionados.delete(id);
        $(this).closest('.entrega-card').toggleClass('selected', marcar);
    });
    actualizarBarraSeleccion();
});

$('#btnConfirmarMasivo').on('click', function () {
    const ids = Array.from(seleccionados);
    if (!ids.length) return;
    const btn = $(this);

    Swal.fire({
        title: `¿Confirmar ${ids.length} pedido${ids.length !== 1 ? 's' : ''}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
    }).then(r => {
        if (!r.isConfirmed) return;
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

        const peticiones = ids.map(id =>
            $.post('/comedor/pedidos/confirmar/' + id, { [csrfName]: csrfHash })
        );
        Promise.allSettled(peticiones).then(resultados => {
            const exitosos = resultados.filter(r => r.status === 'fulfilled' && r.value && r.value.ok).length;
            seleccionados.clear();
            Swal.fire({
                icon: exitosos === ids.length ? 'success' : 'warning',
                title: `${exitosos} de ${ids.length} confirmados`,
                timer: 1500,
                showConfirmButton: false,
            }).then(() => cargar(servicioActivo));
        });
    });
});

// ── Confirmar (paso 1, sin marcar entregado) ────────────────────────────
$(document).on('click', '.btn-confirmar-ent', function () {
    const id  = $(this).data('id');
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
    $.post('/comedor/pedidos/confirmar/' + id, { [csrfName]: csrfHash })
        .done(res => {
            if (res.ok) {
                Swal.fire({ icon: 'success', title: '¡Confirmado!', timer: 800, showConfirmButton: false })
                    .then(() => cargar(servicioActivo));
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

let searchTimerEnt;
$('#entClienteInput').on('input', function () {
    $('#entClienteId').val('');
    clearTimeout(searchTimerEnt);
    const q = $(this).val().trim();
    if (q.length < 2) { $('#entClienteSug').hide(); return; }
    searchTimerEnt = setTimeout(() => {
        $.get('/comedor/clientes/buscar', { q }).done(data => {
            if (!data.length) { $('#entClienteSug').hide(); return; }
            $('#entClienteSug').html(
                data.map(c => `<a href="#" class="list-group-item list-group-item-action py-1 px-2 ent-cli-sug"
                    data-id="${c.id}" data-nombre="${c.nombre}" style="font-size:.85rem;">${c.nombre}</a>`).join('')
            ).show();
        });
    }, 250);
});

$(document).on('click', '.ent-cli-sug', function (e) {
    e.preventDefault();
    $('#entClienteInput').val($(this).data('nombre'));
    $('#entClienteId').val($(this).data('id'));
    $('#entClienteSug').hide();
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('#entClienteInput,#entClienteSug').length) $('#entClienteSug').hide();
});

$('#btnConfirmarEntrega').on('click', function () {
    const tipoPago  = $('.tipo-ent-btn.active').data('tipo');
    const clienteId = $('#entClienteId').val();
    if (tipoPago === 'fiado' && !clienteId) {
        Swal.fire('Comensal requerido', 'Selecciona un comensal registrado para fiado.', 'warning');
        return;
    }

    let montoRecibido = '';
    let vueltoPendiente = 0;
    if (tipoPago === 'contado') {
        const monto = parseFloat($('#entMontoRecibido').val());
        if (!monto || monto < parseFloat(pedidoActivo.subtotal_llamado)) {
            Swal.fire('Monto inválido', 'Ingresa con cuánto pagó (debe ser mayor o igual a lo que se le está entregando ahora).', 'warning');
            return;
        }
        montoRecibido = monto;
        vueltoPendiente = $('#entVueltoPendienteCheck').is(':checked') ? 1 : 0;
    }

    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
    $.post('/comedor/entregas/marcar/' + pedidoActivo.id, {
        [csrfName]: csrfHash,
        servicio: servicioActivo,
        tipo_pago: tipoPago, cliente_id: clienteId,
        monto_recibido: montoRecibido, vuelto_pendiente: vueltoPendiente,
    }).done(res => {
        if (res.ok) {
            $('#modalEntregar').modal('hide');
            datosActuales = datosActuales.filter(p => p.id !== pedidoActivo.id);
            render();
            Swal.fire({ icon: 'success', title: '¡Entregado!', timer: 1000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }).fail(() => {
        Swal.fire('Error', 'No se pudo marcar la entrega.', 'error');
    }).always(() => {
        $('#btnConfirmarEntrega').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Confirmar Entrega');
    });
});

cargar(servicioDefault);
</script>
<?= $this->endSection() ?>
