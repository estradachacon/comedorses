<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
$badgeMap   = ['pagado' => 'success', 'pendiente' => 'warning text-dark', 'anulado' => 'secondary', 'solicitud' => 'info'];
$badgeClass = $badgeMap[$pedido['estado']] ?? 'light';
?>

<style>
.ver-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e3e6ea;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    margin-bottom: 14px;
    overflow: hidden;
}
.ver-card-header {
    padding: 10px 16px;
    font-weight: 700;
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #555;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
}
.ver-card-body { padding: 14px 16px; }
.item-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 7px 0;
    border-bottom: 1px dashed #f0f0f0;
    font-size: .9rem;
}
.item-row:last-child { border-bottom: none; }
.item-name { font-weight: 500; }
.item-qty  { font-size: .78rem; color: #888; margin-top: 2px; }
.item-sub  { font-weight: 600; white-space: nowrap; margin-left: 12px; }
.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0 4px;
    font-weight: 700;
    font-size: 1.05rem;
    border-top: 2px solid #e3e6ea;
    margin-top: 4px;
}
.kv-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: .88rem;
}
.kv-row:last-child { border-bottom: none; }
.kv-label { color: #888; }
.kv-value { font-weight: 600; text-align: right; }
.pago-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: .85rem;
}
.pago-row:last-child { border-bottom: none; }
</style>

<div class="container-fluid px-3 pb-4">

    <!-- ── ENCABEZADO ─────────────────────────────────────────────────── -->
    <div class="d-flex align-items-center mb-3 pt-1" style="gap:10px;">
        <a href="/comedor/pedidos" class="btn btn-outline-secondary btn-sm" style="flex-shrink:0;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div style="flex:1;min-width:0;">
            <div class="d-flex align-items-center flex-wrap" style="gap:6px;">
                <span class="font-weight-bold" style="font-size:1rem;"><?= esc($pedido['numero']) ?></span>
                <span class="badge badge-<?= $badgeClass ?>" style="font-size:.78rem;">
                    <?= ucfirst($pedido['estado']) ?>
                </span>
                <span class="badge badge-<?= $pedido['tipo_pago'] === 'contado' ? 'success' : 'warning text-dark' ?>" style="font-size:.78rem;">
                    <?= ucfirst($pedido['tipo_pago']) ?>
                </span>
            </div>
            <div class="text-muted" style="font-size:.78rem;">
                <?= date('d/m/Y', strtotime($pedido['fecha'])) ?>
                <?php if (!empty($pedido['created_at'])): ?>
                · <?= date('H:i', strtotime($pedido['created_at'])) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php if (tienePermiso('anular_pedido_comedor') && !$pedido['anulado']): ?>
        <button class="btn btn-sm btn-outline-danger" id="btnAnularPedido" style="flex-shrink:0;">
            <i class="fa-solid fa-ban mr-1"></i><span class="d-none d-sm-inline">Anular</span>
        </button>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($pedido['anulado']): ?>
    <div class="alert alert-secondary py-2 mb-3" style="border-radius:10px;">
        <i class="fa-solid fa-ban mr-1"></i>
        Este pedido fue anulado el <?= date('d/m/Y H:i', strtotime($pedido['fecha_anulacion'])) ?>.
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- ── COL IZQUIERDA: Items ──────────────────────────────────── -->
        <div class="col-12 col-md-7">

            <!-- Items del pedido -->
            <div class="ver-card">
                <div class="ver-card-header">
                    <i class="fa-solid fa-utensils mr-1"></i> Detalle del pedido
                </div>
                <div class="ver-card-body">
                    <?php foreach ($detalles as $d): ?>
                    <div class="item-row">
                        <div style="flex:1;min-width:0;">
                            <div class="item-name"><?= esc($d['item_nombre']) ?></div>
                            <div class="item-qty"><?= (int)$d['cantidad'] ?> × $<?= number_format($d['precio_unitario'], 2) ?></div>
                        </div>
                        <div class="item-sub">$<?= number_format($d['subtotal'], 2) ?></div>
                    </div>
                    <?php endforeach; ?>

                    <div class="total-row">
                        <span>Total</span>
                        <span>$<?= number_format($pedido['total'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Notas -->
            <?php if (!empty($pedido['notas'])): ?>
            <div class="ver-card">
                <div class="ver-card-header"><i class="fa-solid fa-note-sticky mr-1"></i> Notas</div>
                <div class="ver-card-body text-muted" style="font-size:.9rem;">
                    <?= esc($pedido['notas']) ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ── COL DERECHA: Info + Pagos ────────────────────────────── -->
        <div class="col-12 col-md-5">

            <!-- Información general -->
            <div class="ver-card">
                <div class="ver-card-header"><i class="fa-solid fa-circle-info mr-1"></i> Información</div>
                <div class="ver-card-body">
                    <div class="kv-row">
                        <span class="kv-label">Cliente</span>
                        <span class="kv-value"><?= esc($pedido['cliente_nombre']) ?></span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Total</span>
                        <span class="kv-value">$<?= number_format($pedido['total'], 2) ?></span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Pagado</span>
                        <span class="kv-value text-success">$<?= number_format($pedido['monto_pagado'], 2) ?></span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Saldo</span>
                        <span class="kv-value <?= $pedido['saldo'] > 0 ? 'text-danger' : 'text-success' ?>">
                            $<?= number_format($pedido['saldo'], 2) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Historial de pagos -->
            <?php if (!empty($pagos)): ?>
            <div class="ver-card">
                <div class="ver-card-header"><i class="fa-solid fa-money-bill-wave mr-1"></i> Pagos</div>
                <div class="ver-card-body">
                    <?php foreach ($pagos as $pg): ?>
                    <div class="pago-row">
                        <div>
                            <div class="font-weight-bold text-success">$<?= number_format($pg['monto'], 2) ?></div>
                            <?php if (!empty($pg['notas'])): ?>
                            <div class="text-muted" style="font-size:.78rem;"><?= esc($pg['notas']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size:.78rem;">
                            <?= date('d/m H:i', strtotime($pg['created_at'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$('#btnAnularPedido').on('click', function () {
    Swal.fire({
        title: '¿Anular este pedido?',
        text: 'Si era fiado con saldo pendiente, se revertirá la deuda del cliente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post('/comedor/pedidos/anular/<?= $pedido['id'] ?>', {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }).done(res => {
            if (res.ok) location.reload();
            else Swal.fire('Error', res.msg, 'error');
        });
    });
});
</script>
<?= $this->endSection() ?>
