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
.item-card {
    cursor: pointer;
    transition: transform .12s, box-shadow .12s;
    border: 2px solid transparent;
    border-radius: 10px;
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
.cart-item-name { flex: 1; font-size: .85rem; }
.cart-qty-btn { border: none; background: #f0f0f0; border-radius: 4px; width: 24px; height: 24px; font-size: .85rem; cursor: pointer; }
.cart-qty-btn:hover { background: #dee2e6; }
.cart-item-qty { width: 28px; text-align: center; font-weight: 700; font-size: .9rem; }
.cart-item-sub { font-size: .85rem; font-weight: 600; min-width: 52px; text-align: right; }
.cart-item-del { color: #dc3545; cursor: pointer; font-size: .85rem; }
.cart-total-row { font-size: 1.1rem; font-weight: 700; }
</style>

<div class="pos-layout">

    <!-- ── MENÚ ─────────────────────────────────────────────────── -->
    <div class="pos-menu">
        <div class="d-flex align-items-center mb-3">
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
                    data-precio="<?= $item['precio'] ?>">
                    <div class="card-body p-1">
                        <i class="fa-solid fa-bowl-food fa-2x text-warning mb-2"></i>
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
        <div class="px-3 pt-3 pb-1">
            <label class="small font-weight-bold text-muted">CLIENTE</label>
            <div class="input-group input-group-sm mb-1">
                <input type="text" id="clienteNombreInput" class="form-control"
                    placeholder="Nombre del comensal..." autocomplete="off">
            </div>
            <div id="clienteSugerencias" class="list-group" style="position:absolute;z-index:999;width:290px;display:none;"></div>
            <input type="hidden" id="clienteIdHidden" value="">
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
            <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                <button type="button" class="btn btn-outline-success tipo-pago-btn active" data-tipo="contado">
                    <i class="fa-solid fa-money-bill-wave mr-1"></i>Contado
                </button>
                <button type="button" class="btn btn-outline-warning tipo-pago-btn" data-tipo="fiado">
                    <i class="fa-solid fa-clock mr-1"></i>Fiado
                </button>
            </div>
            <input type="text" class="form-control form-control-sm mb-2" id="notasInput" placeholder="Notas (opcional)">
            <button class="btn btn-primary btn-block" id="btnGuardar">
                <i class="fa-solid fa-check mr-1"></i>Registrar Pedido
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
</form>

<script>
const cart = {};

function formatMoney(v) {
    return '$' + parseFloat(v).toFixed(2);
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
        html += `<div class="cart-item" data-id="${id}">
            <button class="cart-qty-btn btn-minus" data-id="${id}">-</button>
            <span class="cart-item-qty">${it.cantidad}</span>
            <button class="cart-qty-btn btn-plus" data-id="${id}">+</button>
            <span class="cart-item-name">${it.nombre}</span>
            <span class="cart-item-sub">${formatMoney(sub)}</span>
            <span class="cart-item-del" data-id="${id}"><i class="fa-solid fa-xmark"></i></span>
        </div>`;
    });
    $('#cartBody').html(html);
    $('#cartTotal').text(formatMoney(total));
}

$(document).on('click', '.add-item', function () {
    const id     = $(this).data('id');
    const nombre = $(this).data('nombre');
    const precio = parseFloat($(this).data('precio'));
    if (cart[id]) {
        cart[id].cantidad++;
    } else {
        cart[id] = { item_id: id, nombre, precio, cantidad: 1 };
    }
    renderCart();
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

// Tipo de pago toggle
$('.tipo-pago-btn').on('click', function () {
    $('.tipo-pago-btn').removeClass('active');
    $(this).addClass('active');
    $('#formTipoPago').val($(this).data('tipo'));
    if ($(this).data('tipo') === 'fiado') {
        $('#clienteNombreInput').attr('placeholder', 'Nombre del comensal (requerido para fiado)');
    } else {
        $('#clienteNombreInput').attr('placeholder', 'Nombre del comensal...');
    }
});

// Autocompletar clientes
let searchTimer;
$('#clienteNombreInput').on('input', function () {
    $('#clienteIdHidden').val('');
    clearTimeout(searchTimer);
    const q = $(this).val().trim();
    if (q.length < 2) { $('#clienteSugerencias').hide(); return; }
    searchTimer = setTimeout(function () {
        $.get('/comedor/clientes/buscar', { q }).done(function (data) {
            if (!data.length) { $('#clienteSugerencias').hide(); return; }
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
    $('#clienteNombreInput').val($(this).data('nombre'));
    $('#clienteIdHidden').val($(this).data('id'));
    $('#clienteSugerencias').hide();
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('#clienteNombreInput, #clienteSugerencias').length) {
        $('#clienteSugerencias').hide();
    }
});

// Guardar pedido
$('#btnGuardar').on('click', function () {
    const keys = Object.keys(cart);
    if (keys.length === 0) {
        Swal.fire('Carrito vacío', 'Agrega al menos un item al pedido.', 'warning');
        return;
    }
    const clienteNombre = $('#clienteNombreInput').val().trim();
    if (!clienteNombre) {
        Swal.fire('Cliente requerido', 'Ingresa el nombre del comensal.', 'warning');
        return;
    }

    const items = Object.values(cart).map(it => ({
        item_id:  it.item_id,
        nombre:   it.nombre,
        precio:   it.precio,
        cantidad: it.cantidad,
        subtotal: parseFloat((it.precio * it.cantidad).toFixed(2)),
    }));

    $('#itemsJsonInput').val(JSON.stringify(items));
    $('#formClienteId').val($('#clienteIdHidden').val());
    $('#formClienteNombre').val(clienteNombre);
    $('#formNotas').val($('#notasInput').val());

    $('#btnGuardar').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i>Guardando...');
    $('#pedidoForm').submit();
});
</script>

<?= $this->endSection() ?>
