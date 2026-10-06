<?= $this->extend('Layouts/publico') ?>
<?= $this->section('content') ?>

<?php if ($menuVacio): ?>
<div class="text-center py-5">
    <i class="fa-solid fa-bowl-food fa-3x text-muted mb-3" style="opacity:.3;"></i>
    <p class="text-muted">El menú del día aún no está disponible.<br>Vuelve más tarde.</p>
</div>
<?= $this->endSection() ?>
<?php return; endif; ?>

<?php if ($clienteSesion): ?>
<div class="d-flex justify-content-between align-items-center px-3 py-2" style="background:#fff;border-bottom:1px solid #e9ecef;font-size:.8rem;">
    <span><i class="fa-solid fa-circle-user mr-1 text-primary"></i>Hola, <strong><?= esc($clienteSesion['nombre']) ?></strong></span>
    <a href="#" id="btnLogoutCliente" class="text-danger">Cerrar sesión</a>
</div>
<?php endif; ?>

<!-- Pills de categorías -->
<div class="cat-pills" id="catPills">
    <button class="cat-pill active" data-cat="todos">Todos</button>
    <?php foreach (array_keys($porCategoria) as $cat): ?>
    <button class="cat-pill" data-cat="<?= esc($cat, 'attr') ?>"><?= esc($cat) ?></button>
    <?php endforeach; ?>
</div>

<!-- Items del menú -->
<div id="menuContent">
    <?php foreach ($porCategoria as $categoria => $items): ?>
    <div class="cat-section" data-section="<?= esc($categoria, 'attr') ?>">
        <div class="cat-heading"><?= esc($categoria) ?></div>

        <?php foreach ($items as $item): ?>
        <div class="item-card" data-id="<?= $item['item_id'] ?>"
             data-nombre="<?= esc($item['nombre'], 'attr') ?>"
             data-precio="<?= $item['precio'] ?>">

            <div class="item-emoji">🍽️</div>

            <div class="item-info">
                <div class="item-name"><?= esc($item['nombre']) ?></div>
                <?php if (!empty($item['descripcion'])): ?>
                    <div class="item-desc"><?= esc($item['descripcion']) ?></div>
                <?php endif; ?>
                <div class="qty-control" style="display:none;" id="qtyCtrl_<?= $item['item_id'] ?>">
                    <button class="qty-btn btn-menos" data-id="<?= $item['item_id'] ?>">−</button>
                    <span class="qty-num" id="qty_<?= $item['item_id'] ?>">1</span>
                    <button class="qty-btn btn-mas" data-id="<?= $item['item_id'] ?>">+</button>
                </div>
            </div>

            <div class="d-flex flex-column align-items-end">
                <div class="item-price mb-1">$<?= number_format($item['precio'], 2) ?></div>
                <button class="qty-btn add-btn btn-agregar" data-id="<?= $item['item_id'] ?>"
                        style="width:32px;height:32px;">
                    <i class="fa-solid fa-plus" style="font-size:.8rem;"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Barra flotante del carrito -->
<div class="cart-bar" id="cartBar">
    <div class="cart-bar-left">
        <div class="cart-bar-count" id="cartCount">0 items</div>
        <div class="cart-bar-total" id="cartTotal">$0.00</div>
    </div>
    <button class="cart-bar-btn" id="btnPedir">
        <i class="fa-solid fa-paper-plane mr-1"></i> Solicitar
    </button>
</div>

<!-- Modal: flujo de pago (tipo de pago -> contado/fiado -> cuenta) -->
<div class="modal fade" id="modalPedido" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modalPedidoTitulo">¿Cómo vas a pagar?</h5>
            </div>
            <div class="modal-body pt-2">
                <div id="resumenPedido" class="mb-3"></div>

                <!-- STEP 1: elegir tipo de pago -->
                <div id="stepTipoPago">
                    <button type="button" class="btn btn-outline-success btn-block btn-lg mb-2" id="btnPagoContado">
                        <i class="fa-solid fa-money-bill-wave mr-2"></i>Contado
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-block btn-lg" id="btnPagoFiado">
                        <i class="fa-solid fa-clock mr-2"></i>Fiado
                    </button>
                </div>

                <!-- STEP 2a: contado -->
                <div id="stepContado" style="display:none;">
                    <div class="form-group" id="grupoNombreContado">
                        <label class="small font-weight-bold text-muted">TU NOMBRE</label>
                        <input type="text" id="inputNombreContado" class="form-control" placeholder="Nombre completo" autocomplete="name">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">¿CÓMO PAGAS?</label>
                        <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                            <button type="button" class="btn btn-outline-secondary pago-exacto-btn active" data-exacto="1">Pago exacto</button>
                            <button type="button" class="btn btn-outline-secondary pago-exacto-btn" data-exacto="0">Necesito cambio</button>
                        </div>
                        <input type="number" min="0" step="0.01" id="inputMontoRecibido" class="form-control" placeholder="Pagas con $" style="display:none;">
                    </div>
                    <div class="form-group mb-0">
                        <textarea id="inputNotasContado" class="form-control" rows="2" placeholder="¿Alguna nota? (opcional)"></textarea>
                    </div>
                </div>

                <!-- STEP 2b: fiado, ya logueado -->
                <div id="stepFiadoLogueado" style="display:none;">
                    <p class="mb-2">Pedirás fiado a nombre de <strong id="fiadoLogNombre"></strong>.</p>
                    <div class="form-group mb-0">
                        <textarea id="inputNotasFiadoLog" class="form-control" rows="2" placeholder="¿Alguna nota? (opcional)"></textarea>
                    </div>
                </div>

                <!-- STEP 2c: fiado, sin cuenta -->
                <div id="stepFiadoCuenta" style="display:none;">
                    <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary cuenta-tab active" data-tab="login">Iniciar sesión</button>
                        <button type="button" class="btn btn-outline-primary cuenta-tab" data-tab="registro">Crear cuenta</button>
                    </div>

                    <div id="tabLogin">
                        <div class="form-group">
                            <input type="text" id="loginDui" class="form-control" placeholder="DUI">
                        </div>
                        <div class="form-group">
                            <input type="password" id="loginPassword" class="form-control" placeholder="Contraseña">
                        </div>
                        <button type="button" class="btn btn-primary btn-block" id="btnLoginCliente">
                            <i class="fa-solid fa-right-to-bracket mr-1"></i>Iniciar sesión
                        </button>
                    </div>

                    <div id="tabRegistro" style="display:none;">
                        <div class="form-group">
                            <input type="text" id="regNombre" class="form-control" placeholder="Nombre completo">
                        </div>
                        <div class="form-group">
                            <input type="text" id="regDui" class="form-control" placeholder="DUI">
                        </div>
                        <div class="form-group">
                            <input type="text" id="regTelefono" class="form-control" placeholder="Teléfono (opcional)">
                        </div>
                        <div class="form-group">
                            <input type="password" id="regPassword" class="form-control" placeholder="Contraseña">
                        </div>
                        <button type="button" class="btn btn-primary btn-block" id="btnRegistrarCliente">
                            <i class="fa-solid fa-user-plus mr-1"></i>Crear cuenta
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" id="btnPedidoAtras">Cancelar</button>
                <button type="button" class="btn btn-primary btn-block mt-1" id="btnEnviarPedido" style="display:none;">
                    <i class="fa-solid fa-check mr-1"></i>Enviar Solicitud
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: confirmación exitosa -->
<div class="modal fade" id="modalExito" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 text-center" style="border-radius:18px;">
            <div class="modal-body py-5 px-4">
                <div style="font-size:3.5rem;">🎉</div>
                <h4 class="font-weight-bold mt-2">¡Solicitud enviada!</h4>
                <p class="text-muted mb-1">Tu pedido fue recibido.</p>
                <p class="mb-1">
                    <span class="badge badge-success px-3 py-2" style="font-size:1rem;" id="exitoNumero"></span>
                </p>
                <p class="text-muted small mb-1">Total: <strong id="exitoTotal"></strong></p>
                <p class="text-muted small mb-3" id="exitoExtra"></p>
                <p class="text-muted small">El cajero lo confirmará en breve.</p>
                <button class="btn btn-primary btn-block mt-3" id="btnNuevoPedido">
                    <i class="fa-solid fa-rotate-right mr-1"></i>Hacer otro pedido
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const cart = {};
let clienteSesion = <?= $clienteSesion ? json_encode($clienteSesion) : 'null' ?>;
let tipoPagoActivo = null;

function formatMoney(v) { return '$' + parseFloat(v).toFixed(2); }

function renderCartBar() {
    const keys = Object.keys(cart);
    const total = keys.reduce((s, id) => s + cart[id].precio * cart[id].cantidad, 0);
    const count = keys.reduce((s, id) => s + cart[id].cantidad, 0);

    if (count === 0) {
        $('#cartBar').removeClass('visible');
    } else {
        $('#cartBar').addClass('visible');
        $('#cartCount').text(count + (count === 1 ? ' item' : ' items'));
        $('#cartTotal').text(formatMoney(total));
    }
}

// Agregar item
$(document).on('click', '.btn-agregar', function (e) {
    e.stopPropagation();
    const card = $(this).closest('.item-card');
    const id   = card.data('id');
    if (!cart[id]) {
        cart[id] = {
            item_id:  id,
            nombre:   card.data('nombre'),
            precio:   parseFloat(card.data('precio')),
            cantidad: 1,
        };
        card.addClass('selected');
        $('#qtyCtrl_' + id).show();
        $(this).hide();
    } else {
        cart[id].cantidad++;
        $('#qty_' + id).text(cart[id].cantidad);
    }
    renderCartBar();
});

// Más cantidad
$(document).on('click', '.btn-mas', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    if (cart[id]) {
        cart[id].cantidad++;
        $('#qty_' + id).text(cart[id].cantidad);
        renderCartBar();
    }
});

// Menos cantidad
$(document).on('click', '.btn-menos', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    if (!cart[id]) return;
    cart[id].cantidad--;
    if (cart[id].cantidad <= 0) {
        delete cart[id];
        const card = $('[data-id="' + id + '"].item-card');
        card.removeClass('selected');
        $('#qtyCtrl_' + id).hide();
        card.find('.btn-agregar').show();
    } else {
        $('#qty_' + id).text(cart[id].cantidad);
    }
    renderCartBar();
});

// Filtro por categoría
$('.cat-pill').on('click', function () {
    $('.cat-pill').removeClass('active');
    $(this).addClass('active');
    const cat = $(this).data('cat');
    if (cat === 'todos') {
        $('.cat-section').show();
    } else {
        $('.cat-section').hide();
        $('[data-section="' + cat + '"]').show();
    }
    $('html,body').animate({ scrollTop: $('#menuContent').offset().top - 80 }, 150);
});

function cartTotal() {
    return Object.values(cart).reduce((s, it) => s + it.precio * it.cantidad, 0);
}

function resetModalPedido() {
    tipoPagoActivo = null;
    $('#stepTipoPago').show();
    $('#stepContado, #stepFiadoLogueado, #stepFiadoCuenta').hide();
    $('#btnEnviarPedido').hide();
    $('#btnPedidoAtras').text('Cancelar');
    $('#modalPedidoTitulo').text('¿Cómo vas a pagar?');
}

// Abrir modal de pedido con resumen
$('#btnPedir').on('click', function () {
    const keys = Object.keys(cart);
    if (!keys.length) return;

    let html = '<div class="border rounded p-2" style="font-size:.85rem;max-height:160px;overflow-y:auto;">';
    keys.forEach(id => {
        html += `<div class="d-flex justify-content-between">
            <span>${cart[id].cantidad}× ${cart[id].nombre}</span>
            <span class="text-success">${formatMoney(cart[id].precio * cart[id].cantidad)}</span>
        </div>`;
    });
    html += `<div class="d-flex justify-content-between font-weight-bold border-top pt-1 mt-1">
        <span>Total</span><span>${formatMoney(cartTotal())}</span></div>`;
    html += '</div>';
    $('#resumenPedido').html(html);

    resetModalPedido();
    $('#inputNombreContado, #inputNotasContado, #inputNotasFiadoLog, #inputMontoRecibido').val('');
    $('.pago-exacto-btn').removeClass('active');
    $('.pago-exacto-btn[data-exacto="1"]').addClass('active');
    $('#inputMontoRecibido').hide();

    $('#modalPedido').modal('show');
});

// Elegir Contado
$('#btnPagoContado').on('click', function () {
    tipoPagoActivo = 'contado';
    $('#stepTipoPago').hide();
    $('#stepContado').show();
    $('#grupoNombreContado').toggle(!clienteSesion);
    $('#modalPedidoTitulo').text('Pago al contado');
    $('#btnPedidoAtras').text('Atrás');
    $('#btnEnviarPedido').show();
});

// Elegir Fiado
$('#btnPagoFiado').on('click', function () {
    tipoPagoActivo = 'fiado';
    $('#stepTipoPago').hide();
    $('#btnPedidoAtras').text('Atrás');
    if (clienteSesion) {
        $('#fiadoLogNombre').text(clienteSesion.nombre);
        $('#stepFiadoLogueado').show();
        $('#modalPedidoTitulo').text('Pedido fiado');
        $('#btnEnviarPedido').show();
    } else {
        $('#stepFiadoCuenta').show();
        $('#modalPedidoTitulo').text('Necesitas una cuenta para fiar');
        $('#btnEnviarPedido').hide();
    }
});

// Volver / cancelar
$('#btnPedidoAtras').on('click', function () {
    if ($('#stepTipoPago').is(':visible')) {
        $('#modalPedido').modal('hide');
    } else {
        resetModalPedido();
    }
});

// Toggle pago exacto / cambio
$('.pago-exacto-btn').on('click', function () {
    $('.pago-exacto-btn').removeClass('active');
    $(this).addClass('active');
    const exacto = $(this).data('exacto') === 1;
    $('#inputMontoRecibido').toggle(!exacto);
    if (exacto) $('#inputMontoRecibido').val('');
});

// Tabs login / registro (fiado sin cuenta)
$('.cuenta-tab').on('click', function () {
    $('.cuenta-tab').removeClass('active');
    $(this).addClass('active');
    const tab = $(this).data('tab');
    $('#tabLogin').toggle(tab === 'login');
    $('#tabRegistro').toggle(tab === 'registro');
});

function pasarAFiadoLogueado(nombre) {
    clienteSesion = { nombre: nombre };
    $('#fiadoLogNombre').text(nombre);
    $('#stepFiadoCuenta').hide();
    $('#stepFiadoLogueado').show();
    $('#modalPedidoTitulo').text('Pedido fiado');
    $('#btnEnviarPedido').show();
}

// Login de cliente
$('#btnLoginCliente').on('click', function () {
    const identificacion = $('#loginDui').val().trim();
    const password = $('#loginPassword').val();
    if (!identificacion || !password) {
        Swal.fire('Datos requeridos', 'Ingresa tu DUI y contraseña.', 'warning');
        return;
    }
    $(this).prop('disabled', true);
    $.post('<?= base_url('menu/cuenta/login') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        identificacion, password,
    }).done(function (r) {
        if (r.ok) {
            pasarAFiadoLogueado(r.nombre);
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo iniciar sesión.', 'error');
    }).always(function () {
        $('#btnLoginCliente').prop('disabled', false);
    });
});

// Registro de cliente
$('#btnRegistrarCliente').on('click', function () {
    const nombre = $('#regNombre').val().trim();
    const identificacion = $('#regDui').val().trim();
    const telefono = $('#regTelefono').val().trim();
    const password = $('#regPassword').val();
    if (!nombre || !identificacion || !password) {
        Swal.fire('Datos requeridos', 'Nombre, DUI y contraseña son obligatorios.', 'warning');
        return;
    }
    $(this).prop('disabled', true);
    $.post('<?= base_url('menu/cuenta/registrar') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        nombre, identificacion, telefono, password,
    }).done(function (r) {
        if (r.ok) {
            pasarAFiadoLogueado(r.nombre);
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo crear la cuenta.', 'error');
    }).always(function () {
        $('#btnRegistrarCliente').prop('disabled', false);
    });
});

// Cerrar sesión de cliente
$('#btnLogoutCliente').on('click', function (e) {
    e.preventDefault();
    $.post('<?= base_url('menu/cuenta/logout') ?>', { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
        .always(function () { location.reload(); });
});

// Enviar solicitud (contado o fiado)
$('#btnEnviarPedido').on('click', function () {
    const items = Object.values(cart).map(it => ({
        item_id:  it.item_id,
        nombre:   it.nombre,
        precio:   it.precio,
        cantidad: it.cantidad,
        subtotal: parseFloat((it.precio * it.cantidad).toFixed(2)),
    }));

    const payload = {
        items_json: JSON.stringify(items),
        tipo_pago:  tipoPagoActivo,
    };

    if (tipoPagoActivo === 'contado') {
        const nombre = clienteSesion ? clienteSesion.nombre : $('#inputNombreContado').val().trim();
        if (!nombre) {
            $('#inputNombreContado').addClass('is-invalid').focus();
            return;
        }
        payload.cliente_nombre = nombre;
        payload.notas = $('#inputNotasContado').val();

        const exacto = $('.pago-exacto-btn.active').data('exacto') === 1;
        if (!exacto) {
            const monto = parseFloat($('#inputMontoRecibido').val());
            if (!monto || monto < cartTotal()) {
                Swal.fire('Monto inválido', 'Ingresa con cuánto vas a pagar (debe ser mayor o igual al total).', 'warning');
                return;
            }
            payload.monto_recibido = monto;
        }
    } else {
        payload.cliente_nombre = clienteSesion ? clienteSesion.nombre : '';
        payload.notas = $('#inputNotasFiadoLog').val();
    }

    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i>Enviando...');

    payload['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';

    $.post('<?= base_url('menu/guardar') ?>', payload).done(function (r) {
        if (r.ok) {
            $('#modalPedido').modal('hide');
            $('#exitoNumero').text(r.numero);
            $('#exitoTotal').text('$' + r.total);
            $('#exitoExtra').text(tipoPagoActivo === 'fiado' ? 'Quedará como pendiente de pago (fiado).' : '');
            $('#modalExito').modal('show');
        } else if (r.requiere_cuenta) {
            $('#btnPagoFiado').trigger('click');
            Swal.fire('Cuenta requerida', r.msg, 'info');
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo enviar la solicitud.', 'error');
    }).always(function () {
        $('#btnEnviarPedido').prop('disabled', false)
            .html('<i class="fa-solid fa-check mr-1"></i>Enviar Solicitud');
    });
});

// Nuevo pedido
$('#btnNuevoPedido').on('click', function () {
    $('#modalExito').modal('hide');
    // Limpiar carrito
    Object.keys(cart).forEach(id => {
        delete cart[id];
        const card = $('[data-id="' + id + '"].item-card');
        card.removeClass('selected');
        $('#qtyCtrl_' + id).hide();
        card.find('.btn-agregar').show();
        $('#qty_' + id).text(1);
    });
    renderCartBar();
});
</script>

<?= $this->endSection() ?>
