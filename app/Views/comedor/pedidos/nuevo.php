<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show mx-4 mt-2">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php endif; ?>

<style>
.pos-layout {
    display: flex;
    gap: 1rem;
    height: calc(100vh - 130px);
    padding: 0 1rem 1rem;
}
.pos-menu {
    flex: 1;
    overflow-y: auto;
    min-width: 0;
}
.pos-cart {
    width: 340px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
}
/* En celular/tablet angosta: apilar en vez de lado a lado (si no, el carrito de ancho
   fijo empuja el menú fuera de la pantalla y no se ve ningún item). */
@media (max-width: 900px) {
    .pos-layout { flex-direction: column; height: auto; padding: 0 .75rem .75rem; }
    .pos-menu { overflow-y: visible; }
    .pos-cart { width: 100%; max-height: none; }
    .pos-cart .cart-body { max-height: 260px; }
}
.item-card {
    cursor: pointer;
    transition: transform .12s, box-shadow .12s;
    border: 2px solid transparent;
    border-radius: 10px;
}
.pos-item-img {
    width: 100%;
    height: 64px;
    object-fit: cover;
    border-radius: 8px;
}
.item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    border-color: #4e73df;
}
.cat-heading {
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 4px;
    margin-bottom: 10px;
}
.cart-body {
    flex: 1;
    overflow-y: auto;
    padding: .5rem .75rem;
}
.cart-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 0;
    border-bottom: 1px solid #f0f0f0;
}
.cart-item-name { flex: 1; font-size: .85rem; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cart-qty-btn { border: none; background: #f0f0f0; border-radius: 4px; width: 30px; height: 30px; font-size: .9rem; cursor: pointer; flex-shrink: 0; }
.cart-qty-btn:hover { background: #dee2e6; }
.cart-item-qty { width: 28px; text-align: center; font-weight: 700; font-size: .9rem; flex-shrink: 0; }
.cart-item-sub { font-size: .85rem; font-weight: 600; min-width: 52px; text-align: right; flex-shrink: 0; }
.cliente-chip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #eef6fb;
    border: 1px solid #bfe0f7;
    border-radius: 8px;
    padding: 6px 10px;
    margin-bottom: 4px;
}
.cliente-chip-quitar { color: #dc3545; cursor: pointer; padding: 4px; }
.cliente-crear-rapido {
    border: 1px dashed #4e73df;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: .82rem;
    color: #4e73df;
    cursor: pointer;
    background: #f5f8ff;
}
.cliente-crear-rapido:hover { background: #e9f0ff; }
.cart-item-del { color: #dc3545; cursor: pointer; font-size: .85rem; }
.cart-total-row { font-size: 1.1rem; font-weight: 700; }
.cart-item-horario {
    font-size: .68rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 8px;
    background: var(--hbg, #eef2f5);
    color: var(--hcolor, #495057);
    flex-shrink: 0;
}

.horario-swal-grid { display: flex; flex-direction: column; gap: 10px; margin-top: 6px; }
.horario-swal-btn {
    display: flex;
    align-items: center;
    gap: 14px;
    border: 2px solid transparent;
    border-radius: 14px;
    padding: 13px 16px;
    background: var(--hbg, #f4f6f9);
    cursor: pointer;
    font-weight: 700;
    font-size: .95rem;
    color: var(--hcolor, #333);
    transition: transform .12s ease, border-color .12s ease;
    text-align: left;
}
.horario-swal-btn:hover, .horario-swal-btn:active { border-color: var(--hcolor, #333); transform: translateY(-1px) scale(.99); }
.horario-swal-btn i {
    font-size: 1.4rem;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: var(--hcolor, #333);
    flex-shrink: 0;
}
.horario-swal-btn .horario-swal-sub { font-size: .72rem; font-weight: 500; color: #888; margin-top: 1px; }
</style>

<div class="pos-layout">

    <!-- ── MENÚ ─────────────────────────────────────────────────── -->
    <div class="pos-menu">
        <div class="d-flex justify-content-between mb-3">
            <a href="/comedor/pedidos" class="btn btn-outline-secondary btn-sm mr-3">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h5 class="mb-0"><i class="fa-solid fa-bowl-food mr-2 text-warning"></i>Nuevo Pedido</h5>
        </div>

        <?php if (empty($porCategoria)): ?>
            <div class="alert alert-warning">
                No hay items disponibles. <a href="/comedor/items">Gestionar items</a>
            </div>
        <?php endif; ?>

        <?php foreach ($porCategoria as $categoria => $items): ?>
        <div class="cat-heading"><?= esc($categoria) ?></div>
        <div class="row mb-4">
            <?php foreach ($items as $item): ?>
            <div class="col-6 col-md-4 col-lg-3 mb-3">
                <div class="card item-card text-center p-2 add-item"
                    data-id="<?= $item['id'] ?>"
                    data-nombre="<?= esc($item['nombre'], 'attr') ?>"
                    data-precio="<?= $item['precio'] ?>"
                    data-requiere-horario="<?= $item['requiere_horario'] ? 1 : 0 ?>"
                    data-servicios-asignados="<?= esc(implode(',', $item['servicios_asignados']), 'attr') ?>">
                    <div class="card-body p-1">
                        <?php if (!empty($item['foto'])): ?>
                        <img src="<?= esc(base_url('upload/comedor_items/' . $item['foto'])) ?>" class="pos-item-img mb-2" alt="">
                        <?php else: ?>
                        <i class="fa-solid fa-bowl-food fa-2x text-warning mb-2"></i>
                        <?php endif; ?>
                        <div class="font-weight-bold" style="font-size:.85rem;"><?= esc($item['nombre']) ?></div>
                        <div class="text-success font-weight-bold mt-1">$<?= number_format($item['precio'], 2) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── CARRITO ────────────────────────────────────────────────── -->
    <div class="pos-cart card shadow">
        <div class="card-header bg-dark text-white py-2">
            <strong><i class="fa-solid fa-cart-shopping mr-2"></i>Pedido</strong>
        </div>

        <!-- Cliente -->
        <div class="px-3 pt-3 pb-1" style="position:relative;">
            <label class="small font-weight-bold text-muted">COMENSAL</label>

            <!-- Chip: se muestra cuando ya hay un comensal real vinculado (sin necesidad de que inicie sesión) -->
            <div class="cliente-chip" id="clienteChip" style="display:none;">
                <span><i class="fa-solid fa-circle-check text-success mr-1"></i><strong id="clienteChipNombre"></strong></span>
                <span class="cliente-chip-quitar" id="btnQuitarCliente" title="Quitar"><i class="fa-solid fa-xmark"></i></span>
            </div>

            <div id="clienteBuscarWrap">
                <div class="input-group input-group-sm mb-1">
                    <input type="text" id="clienteNombreInput" class="form-control"
                        placeholder="Nombre o DUI del comensal..." autocomplete="off">
                </div>
                <div id="clienteSugerencias" class="list-group" style="position:absolute;z-index:999;width:calc(100% - 2rem);display:none;"></div>
            </div>
            <input type="hidden" id="clienteIdHidden" value="">

            <!-- Alta rápida: aparece cuando no encuentra coincidencias -->
            <div class="cliente-crear-rapido mt-1" id="btnCrearComensalRapido" style="display:none;">
                <i class="fa-solid fa-user-plus mr-1"></i>Crear comensal nuevo
            </div>
            <div id="clienteCrearForm" style="display:none;" class="mt-2">
                <input type="text" id="crearComensalDui" class="form-control form-control-sm mb-1" placeholder="DUI (opcional)">
                <input type="text" id="crearComensalTelefono" class="form-control form-control-sm mb-1" placeholder="Teléfono (opcional)">
                <div class="d-flex" style="gap:6px;">
                    <button type="button" class="btn btn-sm btn-primary flex-fill" id="btnGuardarComensalRapido">
                        <i class="fa-solid fa-check mr-1"></i>Guardar comensal
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCancelarComensalRapido">Cancelar</button>
                </div>
            </div>
        </div>

        <!-- Items del carrito -->
        <div class="cart-body" id="cartBody">
            <p class="text-muted text-center mt-3 small" id="cartEmpty">
                <i class="fa-solid fa-circle-info mr-1"></i>Selecciona items del menú
            </p>
        </div>

        <!-- Total -->
        <div class="border-top px-3 py-2">
            <div class="d-flex justify-content-between cart-total-row">
                <span>TOTAL</span>
                <span id="cartTotal">$0.00</span>
            </div>
        </div>

        <!-- Tipo de pago + guardar -->
        <div class="px-3 pb-3 pt-2">
            <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                <button type="button" class="btn btn-outline-success tipo-pago-btn active" data-tipo="contado">
                    <i class="fa-solid fa-money-bill-wave mr-1"></i>Contado
                </button>
                <button type="button" class="btn btn-outline-warning tipo-pago-btn" data-tipo="fiado">
                    <i class="fa-solid fa-clock mr-1"></i>Fiado
                </button>
            </div>

            <!-- Vuelto: solo aplica a contado -->
            <div id="contadoExtra" class="mb-2">
                <div class="btn-group btn-group-sm w-100 mb-1" role="group">
                    <button type="button" class="btn btn-outline-secondary pago-exacto-btn active" data-exacto="1">Pago exacto</button>
                    <button type="button" class="btn btn-outline-secondary pago-exacto-btn" data-exacto="0">Necesito cambio</button>
                </div>
                <div id="pagaConWrap" style="display:none;">
                    <div class="input-group input-group-sm mb-1">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input type="number" min="0" step="0.01" id="inputPagaCon" class="form-control" placeholder="Paga con $">
                    </div>
                    <div class="text-muted small mb-1" id="cambioTexto"></div>
                    <div class="custom-control custom-checkbox mb-1" id="vueltoPendienteRow" style="display:none;">
                        <input type="checkbox" class="custom-control-input" id="chkVueltoPendiente">
                        <label class="custom-control-label small" for="chkVueltoPendiente">
                            No tengo el vuelto ahora, se lo quedo debiendo
                        </label>
                    </div>
                </div>
            </div>

            <input type="text" class="form-control form-control-sm mb-2" id="notasInput" placeholder="Notas (opcional)">
            <button class="btn btn-primary btn-block" id="btnGuardar">
                <i class="fa-solid fa-check mr-1"></i>Registrar Pedido
            </button>
            <button class="btn btn-outline-success btn-block mt-2" id="btnGuardarEntregado" title="Para cuando el comensal se lo lleva de una vez: registra el pedido ya como entregado">
                <i class="fa-solid fa-truck-fast mr-1"></i>Registrar y marcar Entregado
            </button>
        </div>
    </div>
</div>

<!-- Formulario oculto para envío -->
<form id="pedidoForm" action="/comedor/pedidos/guardar" method="post" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="items_json" id="itemsJsonInput">
    <input type="hidden" name="cliente_id" id="formClienteId">
    <input type="hidden" name="cliente_nombre" id="formClienteNombre">
    <input type="hidden" name="tipo_pago" id="formTipoPago" value="contado">
    <input type="hidden" name="notas" id="formNotas">
    <input type="hidden" name="monto_recibido" id="formMontoRecibido">
    <input type="hidden" name="vuelto_pendiente" id="formVueltoPendiente" value="0">
    <input type="hidden" name="entregar_ahora" id="formEntregarAhora" value="0">
</form>

<script>
const cart = {};

function formatMoney(v) {
    return '$' + parseFloat(v).toFixed(2);
}

const HORARIO_META = {
    desayuno:   { icon: 'fa-mug-hot',     label: 'Desayuno',   sub: 'Por la mañana',  bg: '#fff4e0', color: '#c97a0a' },
    refrigerio: { icon: 'fa-cookie-bite', label: 'Refrigerio', sub: 'A media mañana', bg: '#e9f1fb', color: '#1c5a96' },
    almuerzo:   { icon: 'fa-utensils',    label: 'Almuerzo',   sub: 'Al mediodía',    bg: '#eaf7ec', color: '#237a3f' },
};

// Selector de horario con tarjetas grandes para que el cajero elija de un vistazo para
// cuándo es el item (cuando tiene 2 o 3 horarios asignados hoy en el menú).
function elegirHorarioComedor(asignados) {
    return new Promise(resolve => {
        const html = '<div class="horario-swal-grid">' + asignados.map(s => {
            const m = HORARIO_META[s] || { icon: 'fa-clock', label: s, sub: '', bg: '#f4f6f9', color: '#333' };
            return `<button type="button" class="horario-swal-btn" data-value="${s}" style="--hbg:${m.bg};--hcolor:${m.color};">
                <i class="fa-solid ${m.icon}"></i>
                <span><span class="d-block">${m.label}</span>${m.sub ? `<span class="horario-swal-sub">${m.sub}</span>` : ''}</span>
            </button>`;
        }).join('') + '</div>';

        let elegido = null;
        Swal.fire({
            title: '¿Para cuándo es?',
            html,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                document.querySelectorAll('.horario-swal-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        elegido = btn.dataset.value;
                        Swal.close();
                    });
                });
            },
        }).then(() => resolve(elegido));
    });
}

function renderCart() {
    const keys = Object.keys(cart);
    if (keys.length === 0) {
        $('#cartBody').html('<p class="text-muted text-center mt-3 small"><i class="fa-solid fa-circle-info mr-1"></i>Selecciona items del menú</p>');
        $('#cartTotal').text('$0.00');
        return;
    }
    let html = '';
    let total = 0;
    keys.forEach(id => {
        const it = cart[id];
        const sub = it.precio * it.cantidad;
        total += sub;
        const m = it.servicio ? HORARIO_META[it.servicio] : null;
        const horarioTag = m ? `<span class="cart-item-horario" style="--hbg:${m.bg};--hcolor:${m.color};">${m.label}</span>` : '';
        html += `<div class="cart-item" data-id="${id}">
            <button class="cart-qty-btn btn-minus" data-id="${id}">-</button>
            <span class="cart-item-qty">${it.cantidad}</span>
            <button class="cart-qty-btn btn-plus" data-id="${id}">+</button>
            <span class="cart-item-name">${it.nombre}</span>
            ${horarioTag}
            <span class="cart-item-sub">${formatMoney(sub)}</span>
            <span class="cart-item-del" data-id="${id}"><i class="fa-solid fa-xmark"></i></span>
        </div>`;
    });
    $('#cartBody').html(html);
    $('#cartTotal').text(formatMoney(total));
}

function agregarAlCarritoPos(card, servicio) {
    const id     = card.data('id');
    const nombre = card.data('nombre');
    const precio = parseFloat(card.data('precio'));
    if (cart[id]) {
        cart[id].cantidad++;
    } else {
        cart[id] = { item_id: id, nombre, precio, cantidad: 1, servicio: servicio || null };
    }
    renderCart();
}

$(document).on('click', '.add-item', function () {
    const card = $(this);
    const requiereHorario = card.data('requiere-horario') == 1;
    const asignados = String(card.data('servicios-asignados') || '').split(',').filter(Boolean);

    // Item con 2 o 3 horarios asignados hoy: hay que preguntar para cuál es (igual que en el
    // menú público), para que la cocina/entregas sepan a qué llamado pertenece.
    if (requiereHorario && asignados.length > 1) {
        elegirHorarioComedor(asignados).then(valor => {
            if (valor) agregarAlCarritoPos(card, valor);
        });
        return;
    }

    agregarAlCarritoPos(card, asignados.length === 1 ? asignados[0] : null);
});

$(document).on('click', '.btn-plus', function () {
    const id = $(this).data('id');
    if (cart[id]) { cart[id].cantidad++; renderCart(); }
});

$(document).on('click', '.btn-minus', function () {
    const id = $(this).data('id');
    if (cart[id]) {
        cart[id].cantidad--;
        if (cart[id].cantidad <= 0) delete cart[id];
        renderCart();
    }
});

$(document).on('click', '.cart-item-del', function () {
    const id = $(this).data('id');
    delete cart[id];
    renderCart();
});

function cartTotal() {
    return Object.values(cart).reduce((s, it) => s + it.precio * it.cantidad, 0);
}

// Tipo de pago toggle
$('.tipo-pago-btn').on('click', function () {
    $('.tipo-pago-btn').removeClass('active');
    $(this).addClass('active');
    const tipo = $(this).data('tipo');
    $('#formTipoPago').val(tipo);
    if (tipo === 'fiado') {
        $('#clienteNombreInput').attr('placeholder', 'Nombre del comensal (requerido para fiado)');
        $('#contadoExtra').hide();
    } else {
        $('#clienteNombreInput').attr('placeholder', 'Nombre del comensal...');
        $('#contadoExtra').show();
    }
});

// Pago exacto / necesito cambio (solo contado)
$('.pago-exacto-btn').on('click', function () {
    $('.pago-exacto-btn').removeClass('active');
    $(this).addClass('active');
    const exacto = $(this).data('exacto') === 1;
    $('#pagaConWrap').toggle(!exacto);
    if (exacto) {
        $('#inputPagaCon').val('');
        $('#cambioTexto').text('');
        $('#vueltoPendienteRow').hide();
        $('#chkVueltoPendiente').prop('checked', false);
    }
});

// Calcula el cambio en vivo y solo muestra la opción de "vuelto pendiente" si hay cambio.
$('#inputPagaCon').on('input', function () {
    const total = cartTotal();
    const monto = parseFloat($(this).val());
    const cambio = (monto || 0) - total;
    if (!monto || cambio <= 0) {
        $('#cambioTexto').text(monto ? 'Pago exacto.' : '');
        $('#vueltoPendienteRow').hide();
        $('#chkVueltoPendiente').prop('checked', false);
    } else {
        $('#cambioTexto').text('Cambio a entregar: ' + formatMoney(cambio));
        $('#vueltoPendienteRow').show();
    }
});

// Autocompletar / vincular comensal (sin que el comensal necesite loguearse)
let searchTimer;
let ultimaBusquedaComensal = '';

function mostrarChipComensal(id, nombre) {
    $('#clienteIdHidden').val(id);
    $('#clienteChipNombre').text(nombre);
    $('#clienteChip').show();
    $('#clienteBuscarWrap, #btnCrearComensalRapido, #clienteCrearForm').hide();
    $('#clienteSugerencias').hide();
}

function limpiarComensal() {
    $('#clienteIdHidden').val('');
    $('#clienteChip').hide();
    $('#clienteBuscarWrap').show();
    $('#clienteNombreInput').val('').focus();
    $('#btnCrearComensalRapido, #clienteCrearForm').hide();
}

$('#btnQuitarCliente').on('click', limpiarComensal);

$('#clienteNombreInput').on('input', function () {
    $('#clienteIdHidden').val('');
    $('#btnCrearComensalRapido, #clienteCrearForm').hide();
    clearTimeout(searchTimer);
    const q = $(this).val().trim();
    ultimaBusquedaComensal = q;
    if (q.length < 2) { $('#clienteSugerencias').hide(); return; }
    searchTimer = setTimeout(function () {
        $.get('/comedor/clientes/buscar', { q }).done(function (data) {
            if (!data.length) {
                $('#clienteSugerencias').hide();
                $('#btnCrearComensalRapido').show();
                return;
            }
            $('#btnCrearComensalRapido').hide();
            let html = '';
            data.forEach(c => {
                const saldo = parseFloat(c.saldo_pendiente);
                const badge = saldo > 0 ? ` <span class="badge badge-danger ml-1">$${saldo.toFixed(2)}</span>` : '';
                html += `<a href="#" class="list-group-item list-group-item-action py-1 px-2 cliente-sugerencia"
                    data-id="${c.id}" data-nombre="${c.nombre}" style="font-size:.85rem;">
                    ${c.nombre}${badge}</a>`;
            });
            $('#clienteSugerencias').html(html).show();
        });
    }, 280);
});

$(document).on('click', '.cliente-sugerencia', function (e) {
    e.preventDefault();
    mostrarChipComensal($(this).data('id'), $(this).data('nombre'));
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('#clienteNombreInput, #clienteSugerencias').length) {
        $('#clienteSugerencias').hide();
    }
});

// Alta rápida de comensal (no encontrado en la búsqueda)
$('#btnCrearComensalRapido').on('click', function () {
    $('#clienteCrearForm').show();
    $('#crearComensalDui, #crearComensalTelefono').val('');
});

$('#btnCancelarComensalRapido').on('click', function () {
    $('#clienteCrearForm').hide();
});

$('#btnGuardarComensalRapido').on('click', function () {
    const nombre = ultimaBusquedaComensal;
    if (!nombre) {
        Swal.fire('Falta el nombre', 'Escribe el nombre del comensal arriba primero.', 'warning');
        return;
    }
    const identificacion = $('#crearComensalDui').val().trim();
    const telefono = $('#crearComensalTelefono').val().trim();

    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
    $.post('/comedor/clientes/crear-rapido', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        nombre, identificacion, telefono,
    }).done(function (r) {
        if (r.ok) {
            mostrarChipComensal(r.id, r.nombre);
            if (r.ya_existia) {
                Swal.fire({ icon: 'info', title: 'Ese DUI ya estaba registrado, se vinculó ese comensal.', timer: 1800, showConfirmButton: false });
            }
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo crear el comensal.', 'error');
    }).always(function () {
        $('#btnGuardarComensalRapido').prop('disabled', false).html('<i class="fa-solid fa-check mr-1"></i>Guardar comensal');
    });
});

// Guardar pedido (normal, o "y marcar entregado" cuando el comensal se lo lleva de una vez)
function guardarPedidoPos(entregarAhora, btn) {
    const keys = Object.keys(cart);
    if (keys.length === 0) {
        Swal.fire('Carrito vacío', 'Agrega al menos un item al pedido.', 'warning');
        return;
    }
    const clienteNombre = $('#clienteIdHidden').val()
        ? $('#clienteChipNombre').text().trim()
        : $('#clienteNombreInput').val().trim();
    if (!clienteNombre) {
        Swal.fire('Cliente requerido', 'Ingresa o selecciona el comensal.', 'warning');
        return;
    }

    let montoRecibido = '';
    let vueltoPendiente = 0;
    if ($('#formTipoPago').val() === 'contado' && $('.pago-exacto-btn[data-exacto="0"]').hasClass('active')) {
        const monto = parseFloat($('#inputPagaCon').val());
        if (!monto || monto < cartTotal()) {
            Swal.fire('Monto inválido', 'Ingresa con cuánto paga (debe ser mayor o igual al total).', 'warning');
            return;
        }
        montoRecibido = monto;
        vueltoPendiente = $('#chkVueltoPendiente').is(':checked') ? 1 : 0;
    }

    const items = Object.values(cart).map(it => ({
        item_id:  it.item_id,
        nombre:   it.nombre,
        precio:   it.precio,
        cantidad: it.cantidad,
        subtotal: parseFloat((it.precio * it.cantidad).toFixed(2)),
        servicio: it.servicio || undefined,
    }));

    $('#itemsJsonInput').val(JSON.stringify(items));
    $('#formClienteId').val($('#clienteIdHidden').val());
    $('#formClienteNombre').val(clienteNombre);
    $('#formNotas').val($('#notasInput').val());
    $('#formMontoRecibido').val(montoRecibido);
    $('#formVueltoPendiente').val(vueltoPendiente);
    $('#formEntregarAhora').val(entregarAhora ? 1 : 0);

    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i>Guardando...');
    $('#pedidoForm').submit();
}

$('#btnGuardar').on('click', function () {
    guardarPedidoPos(false, $(this));
});

$('#btnGuardarEntregado').on('click', function () {
    guardarPedidoPos(true, $(this));
});
</script>

<?= $this->endSection() ?>
