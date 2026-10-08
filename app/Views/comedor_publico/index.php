<?= $this->extend('Layouts/publico') ?>
<?= $this->section('content') ?>

<?php if ($menuVacio): ?>
<div class="text-center py-5">
    <i class="fa-solid fa-bowl-food fa-3x text-muted mb-3" style="opacity:.3;"></i>
    <p class="text-muted">El menú del día aún no está disponible.<br>Vuelve más tarde.</p>
</div>
<?= $this->endSection() ?>
<?php return; endif; ?>

<style>
.item-svc { margin-top: 4px; display: flex; flex-wrap: wrap; gap: 4px; }
.svc-badge {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
    padding: 2px 6px;
    border-radius: 3px;
    background: #eef6fb;
    color: #1c5a96;
}
.svc-badge-allday { background: #eef2f5; color: #495057; }
.svc-badge-closed { background: #fbeaea; color: #a33a3a; text-transform: none; letter-spacing: 0; font-weight: 600; }
.item-card.item-disabled { opacity: .55; }
.item-card.item-disabled .item-name { text-decoration: line-through; }

.item-thumb-img {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    cursor: pointer;
    border: 1px solid #eee;
}
.item-thumb-img:active { transform: scale(.94); }

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
.horario-swal-btn span { display: block; }
.horario-swal-btn .horario-swal-sub { font-size: .72rem; font-weight: 500; color: #888; margin-top: 1px; }

.badge-horario-hist {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: .68rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 10px;
    background: var(--hbg, #eef2f5);
    color: var(--hcolor, #495057);
}

.qty-slot { position: relative; width: 108px; height: 36px; }
.qty-slot .add-btn {
    position: absolute;
    top: 0; right: 0;
    width: 36px; height: 36px;
    transition: opacity .18s ease, transform .18s ease;
}
.qty-slot .add-btn.hide {
    opacity: 0;
    transform: scale(.4);
    pointer-events: none;
}
.qty-slot .qty-control {
    position: absolute;
    top: 0; right: 0;
    margin-top: 0;
    display: flex;
    align-items: center;
    height: 36px;
    opacity: 0;
    transform: scale(.4);
    pointer-events: none;
    transition: opacity .18s ease, transform .18s ease;
}
.qty-slot .qty-control.show {
    opacity: 1;
    transform: scale(1);
    pointer-events: auto;
}

.cart-bar-left { cursor: pointer; }
.cart-drawer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: var(--cart-bar-height, 64px);
    background: #fff;
    border-radius: 16px 16px 0 0;
    box-shadow: 0 -4px 20px rgba(0,0,0,.15);
    max-height: 48vh;
    overflow-y: auto;
    transform: translateY(110%);
    transition: transform .25s cubic-bezier(.4,0,.2,1);
    z-index: 190;
    padding: 10px 16px 14px;
}
.cart-drawer.open { transform: translateY(0); }
.cart-drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 700;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
    margin-bottom: 4px;
}
.cart-drawer-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    color: #aaa;
    padding: 10px;
    margin: -10px;
}
.cart-drawer-close:active { transform: scale(.9); }
.cart-drawer-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 0;
    border-bottom: 1px solid #f3f3f3;
    gap: 10px;
}
.cart-drawer-row:last-child { border-bottom: none; }
.cart-drawer-name { font-size: .85rem; font-weight: 600; flex: 1; min-width: 0; }
.cart-drawer-tag { font-size: .72rem; color: #888; font-weight: 400; }
.cart-drawer-qty { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.cart-drawer-price { font-size: .85rem; font-weight: 700; color: #20c997; width: 58px; text-align: right; flex-shrink: 0; }

.historial-panel {
    position: fixed;
    top: 0; right: 0; bottom: 0;
    width: 100%;
    max-width: 480px;
    background: #f4f6f9;
    z-index: 300;
    transform: translateX(100%);
    transition: transform .3s cubic-bezier(.4,0,.2,1);
    overflow-y: auto;
    box-shadow: -4px 0 20px rgba(0,0,0,.2);
}
.historial-panel.open { transform: translateX(0); }
.historial-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--primary);
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 2;
}
.historial-header button {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.2rem;
    padding: 10px;
    margin: -10px;
    border-radius: 50%;
}
.historial-header button:active { background: rgba(255,255,255,.15); }
.historial-resumen { display: flex; gap: 10px; padding: 14px 16px 0; }
.historial-stat {
    flex: 1;
    background: #fff;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.historial-stat-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; color: #888; font-weight: 700; }
.historial-stat-value { font-size: 1.2rem; font-weight: 800; margin-top: 2px; }
.historial-pedido {
    background: #fff;
    border-radius: 12px;
    margin: 10px 16px 0;
    padding: 12px 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.historial-pedido-numero { font-size: .75rem; color: #888; font-family: monospace; }

.ptr-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 0;
    overflow: hidden;
    color: #888;
    font-size: .78rem;
    font-weight: 700;
}
.ptr-indicator i { transition: transform .2s ease; }
.ptr-indicator.armed i { transform: rotate(180deg); }

/* ── Botones con más vida: sombra, levantamiento al pasar el mouse, click con "resorte" ── */
.btn { transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease; }
.btn:active { transform: scale(.96); }

#btnPagoContado, #btnPagoFiado {
    border-radius: 16px;
    border-width: 2px;
    font-weight: 700;
    padding: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}
#btnPagoContado:hover, #btnPagoFiado:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0,0,0,.14); }

#btnEnviarPedido {
    border: none;
    border-radius: 14px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary), #20c997);
    box-shadow: 0 4px 14px rgba(0,0,0,.18);
}
#btnEnviarPedido:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0,0,0,.22); }

#btnLoginCliente, #btnRegistrarCliente {
    border: none;
    border-radius: 14px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary), #20c997);
    box-shadow: 0 4px 14px rgba(0,0,0,.15);
}
#btnLoginCliente:hover, #btnRegistrarCliente:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,.2); }

.cart-bar-btn {
    border-radius: 24px;
    font-weight: 800;
    box-shadow: 0 2px 10px rgba(0,0,0,.3);
}
.cart-bar-btn:hover { transform: scale(1.05); }

.cuenta-tab { border-radius: 20px !important; font-weight: 700; }
.pago-exacto-btn { font-weight: 600; }

#btnMiHistorial {
    border-radius: 20px;
    font-weight: 700;
    transition: all .15s ease;
}
#btnMiHistorial:hover { background: var(--primary); color: #fff; transform: translateY(-1px); }

#btnPedidoAtras { border-radius: 14px; }

/* En pantallas muy angostas, el botón de historial se queda solo con el ícono para no apretar
   el nombre del cliente ni el link de cerrar sesión. */
@media (max-width: 380px) {
    .historial-btn-text { display: none; }
    #btnMiHistorial { padding-left: .55rem; padding-right: .55rem; }
}

/* Bootstrap aplica "display:flex !important" via .d-flex, así que un simple
   display:none en línea (PHP o jQuery .hide()) nunca gana. Esta clase propia,
   cargada después del CSS de Bootstrap, sí tiene la última palabra. */
.cuenta-bar-oculta { display: none !important; }
</style>

<!-- Barra de cuenta: logueado -->
<div id="barraCuentaSesion" class="d-flex justify-content-between align-items-center px-3 py-2<?= $clienteSesion ? '' : ' cuenta-bar-oculta' ?>"
     style="background:#fff;border-bottom:1px solid #e9ecef;font-size:.8rem;gap:10px;">
    <span style="min-width:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
        <i class="fa-solid fa-circle-user mr-1 text-primary"></i>Hola, <strong id="nombreClienteBar"><?= esc($clienteSesion['nombre'] ?? '') ?></strong>
    </span>
    <div class="d-flex align-items-center flex-shrink-0" style="gap:12px;">
        <button type="button" id="btnMiHistorial" class="btn btn-sm btn-outline-primary py-1">
            <i class="fa-solid fa-receipt" style="margin-right:4px;"></i><span class="historial-btn-text">Mi historial</span>
        </button>
        <a href="#" id="btnLogoutCliente" class="text-danger">Cerrar sesión</a>
    </div>
</div>

<!-- Barra de cuenta: sin sesión -->
<div id="barraCuentaAnonimo" class="d-flex justify-content-between align-items-center px-3 py-2<?= $clienteSesion ? ' cuenta-bar-oculta' : '' ?>"
     style="background:#fff;border-bottom:1px solid #e9ecef;font-size:.8rem;gap:10px;">
    <span class="text-muted">¿Ya tienes cuenta?</span>
    <button type="button" id="btnAbrirCuentaStandalone" class="btn btn-sm btn-outline-primary py-1">
        <i class="fa-solid fa-right-to-bracket mr-1"></i>Iniciar sesión
    </button>
</div>

<!-- Modal: cuenta independiente (sin necesidad de estar armando un pedido) -->
<div class="modal fade" id="modalCuenta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold">Mi cuenta</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pt-2">
                <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                    <button type="button" class="btn btn-outline-primary cuenta-standalone-tab active" data-tab="login">Iniciar sesión</button>
                    <button type="button" class="btn btn-outline-primary cuenta-standalone-tab" data-tab="registro">Crear cuenta</button>
                </div>
                <div id="tabLoginStandalone">
                    <div class="form-group">
                        <input type="text" id="loginDuiStandalone" class="form-control" placeholder="DUI">
                    </div>
                    <div class="form-group">
                        <input type="password" id="loginPasswordStandalone" class="form-control" placeholder="Contraseña">
                    </div>
                    <button type="button" class="btn btn-primary btn-block" id="btnLoginStandalone">
                        <i class="fa-solid fa-right-to-bracket mr-1"></i>Iniciar sesión
                    </button>
                </div>
                <div id="tabRegistroStandalone" style="display:none;">
                    <div class="form-group">
                        <input type="text" id="regNombreStandalone" class="form-control" placeholder="Nombre completo">
                    </div>
                    <div class="form-group">
                        <input type="text" id="regDuiStandalone" class="form-control" placeholder="DUI">
                    </div>
                    <div class="form-group">
                        <input type="text" id="regTelefonoStandalone" class="form-control" placeholder="Teléfono (opcional)">
                    </div>
                    <div class="form-group">
                        <input type="password" id="regPasswordStandalone" class="form-control" placeholder="Contraseña">
                    </div>
                    <button type="button" class="btn btn-primary btn-block" id="btnRegistrarStandalone">
                        <i class="fa-solid fa-user-plus mr-1"></i>Crear cuenta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel: mi historial (pedidos + saldo) -->
<div class="historial-panel" id="panelHistorial">
    <div class="historial-header">
        <button type="button" id="btnVolverMenu"><i class="fa-solid fa-arrow-left"></i></button>
        <div>
            <div class="font-weight-bold" style="font-size:.95rem;">Mi cuenta</div>
            <div style="font-size:.72rem;opacity:.85;" id="histNombre"></div>
        </div>
    </div>
    <div class="ptr-indicator" id="ptrIndicator">
        <i class="fa-solid fa-arrow-down" id="ptrIcon"></i><span id="ptrText">Jala para actualizar</span>
    </div>
    <div class="historial-resumen" id="historialResumen"></div>
    <div id="historialLista" style="padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));"></div>
</div>

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
        <?php
            $asignados       = $item['servicios_asignados'];
            $abiertos        = $item['servicios_abiertos'];
            $disponibleAhora = $item['disponible_ahora'];
            $horarios        = horariosServicioComedor();
        ?>
        <?php $fotoUrl = !empty($item['foto']) ? base_url('upload/comedor_items/' . $item['foto']) : null; ?>
        <div class="item-card<?= $disponibleAhora ? '' : ' item-disabled' ?>" data-id="<?= $item['item_id'] ?>"
             data-nombre="<?= esc($item['nombre'], 'attr') ?>"
             data-precio="<?= $item['precio'] ?>"
             data-requiere-horario="<?= $item['requiere_horario'] ? 1 : 0 ?>"
             data-servicios-abiertos="<?= esc(implode(',', $abiertos), 'attr') ?>">

            <?php if ($fotoUrl): ?>
            <img src="<?= esc($fotoUrl) ?>" class="item-thumb-img btn-ver-foto" alt="<?= esc($item['nombre'], 'attr') ?>" data-foto="<?= esc($fotoUrl, 'attr') ?>">
            <?php else: ?>
            <div class="item-emoji">🍽️</div>
            <?php endif; ?>

            <div class="item-info">
                <div class="item-name"><?= esc($item['nombre']) ?></div>
                <?php if (!empty($item['descripcion'])): ?>
                    <div class="item-desc"><?= esc($item['descripcion']) ?></div>
                <?php endif; ?>

                <?php if (count($asignados) === 3): ?>
                <div class="item-svc"><span class="svc-badge svc-badge-allday">Disponible todo el día</span></div>
                <?php elseif (!empty($asignados)): ?>
                <div class="item-svc">
                    <?php if ($disponibleAhora): ?>
                        <?php foreach ($abiertos as $s): ?>
                        <span class="svc-badge"><?= etiquetaServicioComedor($s) ?> · hasta <?= formatearHoraComedor($horarios[$s]) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="svc-badge svc-badge-closed">
                            Ya no disponible hoy (era <?= implode(' / ', array_map(fn ($s) => etiquetaServicioComedor($s) . ' hasta ' . formatearHoraComedor($horarios[$s]), $asignados)) ?>)
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>

            <div class="d-flex flex-column align-items-end">
                <div class="item-price mb-1">$<?= number_format($item['precio'], 2) ?></div>
                <?php if ($disponibleAhora): ?>
                <div class="qty-slot">
                    <button class="qty-btn add-btn btn-agregar" data-id="<?= $item['item_id'] ?>">
                        <i class="fa-solid fa-plus" style="font-size:.8rem;"></i>
                    </button>
                    <div class="qty-control" id="qtyCtrl_<?= $item['item_id'] ?>">
                        <button class="qty-btn btn-menos" data-id="<?= $item['item_id'] ?>">−</button>
                        <span class="qty-num" id="qty_<?= $item['item_id'] ?>">1</span>
                        <button class="qty-btn btn-mas" data-id="<?= $item['item_id'] ?>">+</button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Carrito flotante expandible (ver qué se va a pedir) -->
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-drawer-header">
        <span><i class="fa-solid fa-basket-shopping mr-1"></i>Tu pedido</span>
        <button type="button" class="cart-drawer-close" id="btnCerrarCarrito">&times;</button>
    </div>
    <div id="cartDrawerItems"></div>
</div>

<!-- Barra flotante del carrito -->
<div class="cart-bar" id="cartBar">
    <div class="cart-bar-grabber"></div>
    <div class="cart-bar-left" id="cartBarLeft">
        <div class="cart-bar-count" id="cartCount">0 items</div>
        <div class="cart-bar-total" id="cartTotal">$0.00</div>
        <div class="cart-bar-hint"><i class="fa-solid fa-chevron-up"></i>Deslízame o toca para ver tu pedido</div>
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
                    <p class="mb-2">Pedirás al contado a nombre de <strong id="contadoLogNombre"></strong>.</p>
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

                <!-- STEP 2c: sin cuenta (requerida para cualquier pedido) -->
                <div id="stepCuenta" style="display:none;">
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

<!-- Modal: ver foto del item en grande -->
<div class="modal fade" id="modalFotoItem" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:18px;background:transparent;box-shadow:none;">
            <button type="button" class="close text-white" data-dismiss="modal" style="position:absolute;top:-36px;right:0;font-size:1.8rem;opacity:.9;text-shadow:0 1px 4px rgba(0,0,0,.5);">&times;</button>
            <img id="imgFotoItemGrande" src="" alt="" style="width:100%;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.4);">
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

// La barra inferior cambió de alto varias veces (padding, línea de "deslízame") y el carrito
// flotante se posiciona "bottom: altura de la barra" para quedar justo encima de ella sin que
// la barra lo tape. En vez de adivinar un valor fijo en CSS, se mide el alto real de la barra.
function ajustarAlturaCarritoBar() {
    const alto = document.getElementById('cartBar').offsetHeight;
    document.documentElement.style.setProperty('--cart-bar-height', alto + 'px');
}

function renderCartBar() {
    const keys = Object.keys(cart);
    const total = keys.reduce((s, id) => s + cart[id].precio * cart[id].cantidad, 0);
    const count = keys.reduce((s, id) => s + cart[id].cantidad, 0);

    if (count === 0) {
        $('#cartBar').removeClass('visible');
        $('#cartDrawer').removeClass('open');
    } else {
        $('#cartBar').addClass('visible');
        $('#cartCount').text(count + (count === 1 ? ' item' : ' items'));
        $('#cartTotal').text(formatMoney(total));
    }
    ajustarAlturaCarritoBar();
}

$(window).on('resize orientationchange', ajustarAlturaCarritoBar);
ajustarAlturaCarritoBar();

function renderCartDrawer() {
    const keys = Object.keys(cart);
    if (!keys.length) {
        $('#cartDrawerItems').html('<p class="text-muted text-center py-3 mb-0" style="font-size:.85rem;">Tu carrito está vacío.</p>');
        return;
    }
    let html = '';
    keys.forEach(id => {
        const it = cart[id];
        const tag = it.servicio ? `<span class="cart-drawer-tag">(${etiquetaHorario(it.servicio)})</span>` : '';
        html += `
        <div class="cart-drawer-row">
            <div class="cart-drawer-name">${it.nombre} ${tag}</div>
            <div class="cart-drawer-qty">
                <button type="button" class="qty-btn drawer-menos" data-id="${id}" style="width:26px;height:26px;">−</button>
                <span style="min-width:18px;text-align:center;font-weight:700;font-size:.85rem;">${it.cantidad}</span>
                <button type="button" class="qty-btn drawer-mas" data-id="${id}" style="width:26px;height:26px;">+</button>
            </div>
            <div class="cart-drawer-price">${formatMoney(it.precio * it.cantidad)}</div>
        </div>`;
    });
    $('#cartDrawerItems').html(html);
}

// Mantiene sincronizados: la barra inferior, el carrito flotante y el número visible en cada card del menú.
function syncUI() {
    renderCartBar();
    renderCartDrawer();
    Object.keys(cart).forEach(id => $('#qty_' + id).text(cart[id].cantidad));
}

function etiquetaHorario(s) {
    const map = { desayuno: 'Desayuno', refrigerio: 'Refrigerio', almuerzo: 'Almuerzo' };
    return map[s] || s;
}

const HORARIO_META = {
    desayuno:   { icon: 'fa-mug-hot',     label: 'Desayuno',   sub: 'Por la mañana',  bg: '#fff4e0', color: '#c97a0a' },
    refrigerio: { icon: 'fa-cookie-bite', label: 'Refrigerio', sub: 'A media mañana', bg: '#e9f1fb', color: '#1c5a96' },
    almuerzo:   { icon: 'fa-utensils',    label: 'Almuerzo',   sub: 'Al mediodía',    bg: '#eaf7ec', color: '#237a3f' },
};

// Selector de horario con tarjetas grandes (en vez del radio escueto de SweetAlert) para que
// el comensal elija de un vistazo para cuándo quiere el item.
function elegirHorarioComedor(abiertos) {
    return new Promise(resolve => {
        const html = '<div class="horario-swal-grid">' + abiertos.map(s => {
            const m = HORARIO_META[s] || { icon: 'fa-clock', label: etiquetaHorario(s), sub: '', bg: '#f4f6f9', color: '#333' };
            return `<button type="button" class="horario-swal-btn" data-value="${s}" style="--hbg:${m.bg};--hcolor:${m.color};">
                <i class="fa-solid ${m.icon}"></i>
                <span><span class="d-block">${m.label}</span>${m.sub ? `<span class="horario-swal-sub">${m.sub}</span>` : ''}</span>
            </button>`;
        }).join('') + '</div>';

        let elegido = null;
        Swal.fire({
            title: '¿Para cuándo lo quieres?',
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

function quitarDelCarrito(id) {
    delete cart[id];
    const card = $('[data-id="' + id + '"].item-card');
    card.removeClass('selected');
    $('#qtyCtrl_' + id).removeClass('show');
    card.find('.btn-agregar').removeClass('hide');
}

function agregarAlCarrito(card, btnEl, servicio) {
    const id = card.data('id');
    if (!cart[id]) {
        cart[id] = {
            item_id:  id,
            nombre:   card.data('nombre'),
            precio:   parseFloat(card.data('precio')),
            cantidad: 1,
            servicio: servicio || null,
        };
        card.addClass('selected');
        $('#qtyCtrl_' + id).addClass('show');
        $(btnEl).addClass('hide');
    } else {
        cart[id].cantidad++;
    }
    syncUI();
}

// Agregar item
$(document).on('click', '.btn-agregar', function (e) {
    e.stopPropagation();
    const btn  = this;
    const card = $(this).closest('.item-card');
    const requiereHorario = card.data('requiere-horario') == 1;
    const abiertos = String(card.data('servicios-abiertos') || '').split(',').filter(Boolean);

    // Item disponible en más de un horario a la vez: preguntar para cuál lo quiere
    if (requiereHorario && abiertos.length > 1) {
        elegirHorarioComedor(abiertos).then(valor => {
            if (valor) agregarAlCarrito(card, btn, valor);
        });
        return;
    }

    // Un solo horario posible entre los dos originales (el otro ya cerró): se asigna directo
    agregarAlCarrito(card, btn, requiereHorario && abiertos.length === 1 ? abiertos[0] : null);
});

// Más cantidad (desde la card del menú)
$(document).on('click', '.btn-mas', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    if (cart[id]) {
        cart[id].cantidad++;
        syncUI();
    }
});

// Menos cantidad (desde la card del menú)
$(document).on('click', '.btn-menos', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    if (!cart[id]) return;
    cart[id].cantidad--;
    if (cart[id].cantidad <= 0) {
        quitarDelCarrito(id);
    }
    syncUI();
});

// +/- desde el carrito flotante
$(document).on('click', '.drawer-mas', function () {
    const id = $(this).data('id');
    if (cart[id]) {
        cart[id].cantidad++;
        syncUI();
    }
});

$(document).on('click', '.drawer-menos', function () {
    const id = $(this).data('id');
    if (!cart[id]) return;
    cart[id].cantidad--;
    if (cart[id].cantidad <= 0) {
        quitarDelCarrito(id);
    }
    syncUI();
});

// Abrir/cerrar el carrito flotante
$('#cartBarLeft').on('click', function () {
    if (Object.keys(cart).length) $('#cartDrawer').toggleClass('open');
});
$('#btnCerrarCarrito').on('click', function () {
    $('#cartDrawer').removeClass('open');
});

// Deslizar hacia arriba la barra inferior abre el carrito; deslizar hacia abajo el
// encabezado del carrito lo cierra (gesto, además del tap/clic ya soportado).
(function () {
    const barra = document.getElementById('cartBar');
    let inicioBarra = null;
    barra.addEventListener('touchstart', function (e) { inicioBarra = e.touches[0].clientY; }, { passive: true });
    barra.addEventListener('touchend', function (e) {
        if (inicioBarra === null) return;
        const dy = e.changedTouches[0].clientY - inicioBarra;
        inicioBarra = null;
        if (dy < -20 && Object.keys(cart).length) $('#cartDrawer').addClass('open');
    }, { passive: true });

    const encabezadoCarrito = document.querySelector('.cart-drawer-header');
    let inicioCarrito = null;
    encabezadoCarrito.addEventListener('touchstart', function (e) { inicioCarrito = e.touches[0].clientY; }, { passive: true });
    encabezadoCarrito.addEventListener('touchend', function (e) {
        if (inicioCarrito === null) return;
        const dy = e.changedTouches[0].clientY - inicioCarrito;
        inicioCarrito = null;
        if (dy > 20) $('#cartDrawer').removeClass('open');
    }, { passive: true });
})();

// Ver foto del item en grande (sin que el tap active el agregar al carrito de la card)
$(document).on('click', '.btn-ver-foto', function (e) {
    e.stopPropagation();
    $('#imgFotoItemGrande').attr('src', $(this).data('foto'));
    $('#modalFotoItem').modal('show');
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
    $('#stepContado, #stepFiadoLogueado, #stepCuenta').hide();
    $('#btnEnviarPedido').hide();
    $('#btnPedidoAtras').text('Cancelar');
    $('#modalPedidoTitulo').text('¿Cómo vas a pagar?');
}

// Abrir modal de pedido con resumen
$('#btnPedir').on('click', function () {
    const keys = Object.keys(cart);
    if (!keys.length) return;

    $('#cartDrawer').removeClass('open');

    let html = '<div class="border rounded p-2" style="font-size:.85rem;max-height:160px;overflow-y:auto;">';
    keys.forEach(id => {
        const horarioTag = cart[id].servicio ? ' <span class="text-muted">(' + etiquetaHorario(cart[id].servicio) + ')</span>' : '';
        html += `<div class="d-flex justify-content-between">
            <span>${cart[id].cantidad}× ${cart[id].nombre}${horarioTag}</span>
            <span class="text-success">${formatMoney(cart[id].precio * cart[id].cantidad)}</span>
        </div>`;
    });
    html += `<div class="d-flex justify-content-between font-weight-bold border-top pt-1 mt-1">
        <span>Total</span><span>${formatMoney(cartTotal())}</span></div>`;
    html += '</div>';
    $('#resumenPedido').html(html);

    resetModalPedido();
    $('#inputNotasContado, #inputNotasFiadoLog, #inputMontoRecibido').val('');
    $('.pago-exacto-btn').removeClass('active');
    $('.pago-exacto-btn[data-exacto="1"]').addClass('active');
    $('#inputMontoRecibido').hide();

    $('#modalPedido').modal('show');
});

// Elegir Contado
$('#btnPagoContado').on('click', function () {
    tipoPagoActivo = 'contado';
    $('#stepTipoPago').hide();
    $('#btnPedidoAtras').text('Atrás');
    if (clienteSesion) {
        $('#contadoLogNombre').text(clienteSesion.nombre);
        $('#stepContado').show();
        $('#modalPedidoTitulo').text('Pago al contado');
        $('#btnEnviarPedido').show();
    } else {
        $('#stepCuenta').show();
        $('#modalPedidoTitulo').text('Necesitas una cuenta para pedir');
        $('#btnEnviarPedido').hide();
    }
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
        $('#stepCuenta').show();
        $('#modalPedidoTitulo').text('Necesitas una cuenta para pedir');
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

// ── Login/registro: funciones compartidas por el modal de pedido y el standalone ──
function intentarLogin(identificacion, password) {
    return $.post('<?= base_url('menu/cuenta/login') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        identificacion, password,
    });
}
function intentarRegistro(nombre, identificacion, telefono, password) {
    return $.post('<?= base_url('menu/cuenta/registrar') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        nombre, identificacion, telefono, password,
    });
}

// Enciende/apaga la barra de cuenta en vivo, sin recargar la página.
function marcarSesionActiva(nombre) {
    clienteSesion = { nombre: nombre };
    $('#nombreClienteBar').text(nombre);
    $('#barraCuentaSesion').removeClass('cuenta-bar-oculta');
    $('#barraCuentaAnonimo').addClass('cuenta-bar-oculta');
}
function marcarSesionCerrada() {
    clienteSesion = null;
    $('#barraCuentaSesion').addClass('cuenta-bar-oculta');
    $('#barraCuentaAnonimo').removeClass('cuenta-bar-oculta');
    $('#panelHistorial').removeClass('open');
}

// Después de iniciar sesión o crear cuenta DENTRO de un pedido, continúa en el paso
// que corresponda según lo elegido en STEP 1 (y ya deja la barra de arriba actualizada).
function cuentaLista(nombre) {
    marcarSesionActiva(nombre);
    $('#stepCuenta').hide();
    if (tipoPagoActivo === 'fiado') {
        $('#fiadoLogNombre').text(nombre);
        $('#stepFiadoLogueado').show();
        $('#modalPedidoTitulo').text('Pedido fiado');
    } else {
        $('#contadoLogNombre').text(nombre);
        $('#stepContado').show();
        $('#modalPedidoTitulo').text('Pago al contado');
    }
    $('#btnEnviarPedido').show();
}

// Login/registro dentro del flujo de pedido
$('#btnLoginCliente').on('click', function () {
    const identificacion = $('#loginDui').val().trim();
    const password = $('#loginPassword').val();
    if (!identificacion || !password) {
        Swal.fire('Datos requeridos', 'Ingresa tu DUI y contraseña.', 'warning');
        return;
    }
    $(this).prop('disabled', true);
    intentarLogin(identificacion, password).done(function (r) {
        if (r.ok) {
            cuentaLista(r.nombre);
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo iniciar sesión.', 'error');
    }).always(function () {
        $('#btnLoginCliente').prop('disabled', false);
    });
});

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
    intentarRegistro(nombre, identificacion, telefono, password).done(function (r) {
        if (r.ok) {
            cuentaLista(r.nombre);
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo crear la cuenta.', 'error');
    }).always(function () {
        $('#btnRegistrarCliente').prop('disabled', false);
    });
});

// ── Cuenta independiente (sin necesidad de estar armando un pedido) ──
$('#btnAbrirCuentaStandalone').on('click', function () {
    $('#loginDuiStandalone, #loginPasswordStandalone, #regNombreStandalone, #regDuiStandalone, #regTelefonoStandalone, #regPasswordStandalone').val('');
    $('.cuenta-standalone-tab').removeClass('active');
    $('.cuenta-standalone-tab[data-tab="login"]').addClass('active');
    $('#tabLoginStandalone').show();
    $('#tabRegistroStandalone').hide();
    $('#modalCuenta').modal('show');
});

$('.cuenta-standalone-tab').on('click', function () {
    $('.cuenta-standalone-tab').removeClass('active');
    $(this).addClass('active');
    const tab = $(this).data('tab');
    $('#tabLoginStandalone').toggle(tab === 'login');
    $('#tabRegistroStandalone').toggle(tab === 'registro');
});

$('#btnLoginStandalone').on('click', function () {
    const identificacion = $('#loginDuiStandalone').val().trim();
    const password = $('#loginPasswordStandalone').val();
    if (!identificacion || !password) {
        Swal.fire('Datos requeridos', 'Ingresa tu DUI y contraseña.', 'warning');
        return;
    }
    $(this).prop('disabled', true);
    intentarLogin(identificacion, password).done(function (r) {
        if (r.ok) {
            marcarSesionActiva(r.nombre);
            $('#modalCuenta').modal('hide');
            Swal.fire({ icon: 'success', title: '¡Bienvenido, ' + r.nombre + '!', timer: 1200, showConfirmButton: false });
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo iniciar sesión.', 'error');
    }).always(function () {
        $('#btnLoginStandalone').prop('disabled', false);
    });
});

$('#btnRegistrarStandalone').on('click', function () {
    const nombre = $('#regNombreStandalone').val().trim();
    const identificacion = $('#regDuiStandalone').val().trim();
    const telefono = $('#regTelefonoStandalone').val().trim();
    const password = $('#regPasswordStandalone').val();
    if (!nombre || !identificacion || !password) {
        Swal.fire('Datos requeridos', 'Nombre, DUI y contraseña son obligatorios.', 'warning');
        return;
    }
    $(this).prop('disabled', true);
    intentarRegistro(nombre, identificacion, telefono, password).done(function (r) {
        if (r.ok) {
            marcarSesionActiva(r.nombre);
            $('#modalCuenta').modal('hide');
            Swal.fire({ icon: 'success', title: '¡Cuenta creada, bienvenido!', timer: 1200, showConfirmButton: false });
        } else {
            Swal.fire('Error', r.msg, 'error');
        }
    }).fail(function () {
        Swal.fire('Error', 'No se pudo crear la cuenta.', 'error');
    }).always(function () {
        $('#btnRegistrarStandalone').prop('disabled', false);
    });
});

// Cerrar sesión de cliente (sin recargar la página)
$('#btnLogoutCliente').on('click', function (e) {
    e.preventDefault();
    $.post('<?= base_url('menu/cuenta/logout') ?>', { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
        .always(function () { marcarSesionCerrada(); });
});

// Mi historial: vista paralela que se desliza desde la derecha
$('#btnMiHistorial').on('click', function () {
    $('#panelHistorial').addClass('open');
    cargarHistorial();
});

$('#btnVolverMenu').on('click', function () {
    $('#panelHistorial').removeClass('open');
});

// Cerrar el historial si se hace clic fuera del panel
$(document).on('click', function (e) {
    if ($('#panelHistorial').hasClass('open') && !$(e.target).closest('#panelHistorial, #btnMiHistorial').length) {
        $('#panelHistorial').removeClass('open');
    }
});

// Traduce el estado crudo del pedido a algo que el cliente entienda de un vistazo,
// según en qué parte del proceso va: recién pedido, aceptado, o ya entregado (y cómo quedó el pago).
function estadoAmigablePedido(p) {
    const total  = parseFloat(p.total) || 0;
    const pagado = parseFloat(p.monto_pagado) || 0;
    const saldo  = parseFloat(p.saldo) || 0;
    const monto  = parseFloat(p.monto_recibido);

    if (p.estado === 'anulado') {
        return { texto: 'Anulado', clase: 'secondary' };
    }
    if (p.estado === 'solicitud') {
        return { texto: 'Pendiente', clase: 'info', nota: 'Esperando que el comedor lo confirme.', notaClase: 'muted' };
    }
    if (!p.entregado_at) {
        return { texto: 'Confirmado', clase: 'info', nota: 'Ya está en control, falta entregarlo.', notaClase: 'muted' };
    }

    // Ya entregado: el badge refleja cómo quedó el pago.
    if (p.tipo_pago === 'contado') {
        if (monto && monto > total) {
            return { texto: 'Pagado', clase: 'success', nota: `Te deben cambio: $${(monto - total).toFixed(2)}`, notaClase: 'info' };
        }
        return { texto: 'Pago completado', clase: 'success' };
    }

    // Fiado
    if (saldo <= 0) {
        return { texto: 'Pago completado', clase: 'success' };
    }
    if (pagado > 0) {
        return { texto: 'Debe parcial', clase: 'warning', nota: `Pendiente: $${saldo.toFixed(2)}`, notaClase: 'danger' };
    }
    return { texto: 'Se debe', clase: 'danger', nota: `Pendiente: $${saldo.toFixed(2)}`, notaClase: 'danger' };
}

function cargarHistorial() {
    $('#histNombre').text(clienteSesion.nombre);
    $('#historialResumen').html('');
    $('#historialLista').html('<div class="text-center py-5"><i class="fa-solid fa-spinner fa-spin"></i></div>');

    return $.get('<?= base_url('menu/historial') ?>').done(function (r) {
        if (!r.ok) {
            $('#historialLista').html('<p class="text-center text-muted py-4">' + r.msg + '</p>');
            return;
        }

        const saldo  = parseFloat(r.cliente.saldo_pendiente) || 0;
        const vuelto = parseFloat(r.cliente.vuelto_pendiente) || 0;
        let resumenHtml = '';
        if (saldo > 0) {
            resumenHtml += `<div class="historial-stat">
                <div class="historial-stat-label">Debes</div>
                <div class="historial-stat-value text-danger">$${saldo.toFixed(2)}</div>
            </div>`;
        }
        if (vuelto > 0) {
            resumenHtml += `<div class="historial-stat">
                <div class="historial-stat-label">Te deben</div>
                <div class="historial-stat-value text-info">$${vuelto.toFixed(2)}</div>
            </div>`;
        }
        if (!resumenHtml) {
            resumenHtml = `<div class="historial-stat">
                <div class="historial-stat-label">Estado</div>
                <div class="historial-stat-value text-success">Al día</div>
            </div>`;
        }
        $('#historialResumen').html(resumenHtml);

        if (!r.pedidos.length) {
            $('#historialLista').html('<p class="text-center text-muted py-4">Todavía no has hecho ningún pedido.</p>');
            return;
        }

        let html = '';
        r.pedidos.forEach(p => {
            const itemsTxt = (p.items || []).map(it => it.texto).join(', ');
            const estado = estadoAmigablePedido(p);

            // Badge(s) del tiempo (desayuno/refrigerio/almuerzo) al que corresponde el pedido.
            // Un mismo pedido puede tener items de más de un horario, así que se muestra uno
            // por cada horario distinto presente (sin repetir, y sin badge para items "todo el día").
            const serviciosEnPedido = [...new Set((p.items || []).map(it => it.servicio).filter(Boolean))];
            const horariosHtml = serviciosEnPedido.map(s => {
                const m = HORARIO_META[s];
                return m ? `<span class="badge-horario-hist" style="--hbg:${m.bg};--hcolor:${m.color};"><i class="fa-solid ${m.icon}"></i>${m.label}</span>` : '';
            }).join('');

            html += `
            <div class="historial-pedido">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="min-width:0;">
                        <div class="font-weight-bold" style="font-size:.9rem;">$${parseFloat(p.total).toFixed(2)}</div>
                        <div class="historial-pedido-numero">${p.numero_formateado}</div>
                    </div>
                    <span class="badge badge-${estado.clase}">${estado.texto}</span>
                </div>
                <div class="text-muted small mt-1">${itemsTxt}</div>
                ${horariosHtml ? `<div class="mt-1" style="display:flex;gap:4px;flex-wrap:wrap;">${horariosHtml}</div>` : ''}
                ${estado.nota ? `<div class="small font-weight-bold mt-1 text-${estado.notaClase}">${estado.nota}</div>` : ''}
            </div>`;
        });
        $('#historialLista').html(html);
    });
}

// Pull-to-refresh: jalar el panel de historial hacia abajo desde arriba del todo recarga.
(function () {
    const panel = document.getElementById('panelHistorial');
    const indicador = document.getElementById('ptrIndicator');
    const icono = document.getElementById('ptrIcon');
    const texto = document.getElementById('ptrText');
    const UMBRAL = 70;
    let startY = 0, jalando = false, armado = false, cargando = false;

    panel.addEventListener('touchstart', function (e) {
        jalando = panel.scrollTop <= 0 && !cargando;
        armado = false;
        if (jalando) startY = e.touches[0].clientY;
    }, { passive: true });

    panel.addEventListener('touchmove', function (e) {
        if (!jalando) return;
        const dy = e.touches[0].clientY - startY;
        if (dy <= 0) { indicador.style.height = '0px'; return; }
        const altura = Math.min(dy * 0.5, UMBRAL);
        indicador.style.height = altura + 'px';
        const ahoraArmado = altura >= UMBRAL - 5;
        if (ahoraArmado !== armado) {
            armado = ahoraArmado;
            indicador.classList.toggle('armed', armado);
            texto.textContent = armado ? 'Suelta para actualizar' : 'Jala para actualizar';
        }
    }, { passive: true });

    panel.addEventListener('touchend', function () {
        if (!jalando) return;
        jalando = false;
        if (armado && !cargando) {
            cargando = true;
            texto.textContent = 'Actualizando...';
            icono.className = 'fa-solid fa-spinner fa-spin';
            cargarHistorial().always(function () {
                indicador.style.height = '0px';
                indicador.classList.remove('armed');
                icono.className = 'fa-solid fa-arrow-down';
                texto.textContent = 'Jala para actualizar';
                cargando = false;
            });
        } else {
            indicador.style.height = '0px';
            indicador.classList.remove('armed');
        }
        armado = false;
    });
})();

// Enviar solicitud (contado o fiado)
$('#btnEnviarPedido').on('click', function () {
    const items = Object.values(cart).map(it => ({
        item_id:  it.item_id,
        nombre:   it.nombre,
        precio:   it.precio,
        cantidad: it.cantidad,
        subtotal: parseFloat((it.precio * it.cantidad).toFixed(2)),
        servicio: it.servicio || undefined,
    }));

    const payload = {
        items_json: JSON.stringify(items),
        tipo_pago:  tipoPagoActivo,
    };

    if (tipoPagoActivo === 'contado') {
        payload.cliente_nombre = clienteSesion.nombre;
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
            $('#stepTipoPago, #stepContado, #stepFiadoLogueado').hide();
            $('#stepCuenta').show();
            $('#modalPedidoTitulo').text('Necesitas una cuenta para pedir');
            $('#btnPedidoAtras').text('Atrás');
            $('#btnEnviarPedido').hide();
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
    $('#cartDrawer').removeClass('open');
    // Limpiar carrito
    Object.keys(cart).forEach(id => {
        const card = $('[data-id="' + id + '"].item-card');
        card.removeClass('selected');
        $('#qtyCtrl_' + id).removeClass('show');
        card.find('.btn-agregar').removeClass('hide');
        delete cart[id];
    });
    syncUI();
});
</script>

<?= $this->endSection() ?>
