<?= $this->extend('Layouts/publico') ?>
<?= $this->section('content') ?>

<?php if ($menuVacio): ?>
<div class="text-center py-5">
    <i class="fa-solid fa-bowl-food fa-3x text-muted mb-3" style="opacity:.3;"></i>
    <p class="text-muted">El menú del día aún no está disponible.<br>Vuelve más tarde.</p>
</div>
<?= $this->endSection() ?>
<?php return; endif; ?>

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

<!-- Modal: ingresar nombre -->
<div class="modal fade" id="modalNombre" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa-solid fa-user mr-2 text-primary"></i>¿A nombre de quién?
                </h5>
            </div>
            <div class="modal-body pt-2">
                <input type="text" id="inputNombre" class="form-control form-control-lg"
                       placeholder="Tu nombre completo" autocomplete="name">
                <div class="form-group mt-3 mb-0">
                    <textarea id="inputNotas" class="form-control" rows="2"
                              placeholder="¿Alguna nota? (opcional)"></textarea>
                </div>
                <!-- Resumen del pedido -->
                <div class="mt-3" id="resumenPedido"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-block mt-1" id="btnConfirmarSolicitud">
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
                <p class="mb-3">
                    <span class="badge badge-success px-3 py-2" style="font-size:1rem;" id="exitoNumero"></span>
                </p>
                <p class="text-muted small">El cajero lo confirmará en breve. Total: <strong id="exitoTotal"></strong></p>
                <button class="btn btn-primary btn-block mt-3" id="btnNuevoPedido">
                    <i class="fa-solid fa-rotate-right mr-1"></i>Hacer otro pedido
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const cart = {};

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

// Abrir modal con resumen
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
    const total = keys.reduce((s, id) => s + cart[id].precio * cart[id].cantidad, 0);
    html += `<div class="d-flex justify-content-between font-weight-bold border-top pt-1 mt-1">
        <span>Total</span><span>${formatMoney(total)}</span></div>`;
    html += '</div>';
    $('#resumenPedido').html(html);
    $('#inputNombre').val('');
    $('#modalNombre').modal('show');
    setTimeout(() => $('#inputNombre').focus(), 400);
});

// Enviar solicitud
$('#btnConfirmarSolicitud').on('click', function () {
    const nombre = $('#inputNombre').val().trim();
    if (!nombre) {
        $('#inputNombre').addClass('is-invalid').focus();
        return;
    }
    $('#inputNombre').removeClass('is-invalid');

    const items = Object.values(cart).map(it => ({
        item_id:  it.item_id,
        nombre:   it.nombre,
        precio:   it.precio,
        cantidad: it.cantidad,
        subtotal: parseFloat((it.precio * it.cantidad).toFixed(2)),
    }));

    $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i>Enviando...');

    $.post('<?= base_url('menu/guardar') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        items_json:     JSON.stringify(items),
        cliente_nombre: nombre,
        notas:          $('#inputNotas').val(),
    }).done(function (r) {
        if (r.ok) {
            $('#modalNombre').modal('hide');
            $('#exitoNumero').text(r.numero);
            $('#exitoTotal').text('$' + r.total);
            $('#modalExito').modal('show');
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo enviar la solicitud.', 'error');
    }).always(function () {
        $('#btnConfirmarSolicitud').prop('disabled', false)
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
