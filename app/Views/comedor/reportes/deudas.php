<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
.stat-mini { border-radius:8px; padding:10px 14px; border-left:4px solid; margin-bottom:8px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.05); }
.deudor-card { background:#fff; border-radius:10px; border:1px solid #e3e6ea; padding:13px 15px; margin-bottom:8px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.saldo-bar-bg { background:#f5f5f5; border-radius:4px; height:5px; margin-top:6px; }
.saldo-bar    { background:#e74a3b; border-radius:4px; height:5px; }
</style>

<div class="container-fluid px-3">
    <div class="d-flex align-items-center mb-3 pt-1" style="gap:10px;">
        <a href="/comedor/deudores" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h5 class="mb-0 font-weight-bold"><i class="fa-solid fa-hand-holding-dollar mr-2 text-danger"></i><?= esc($title) ?></h5>
    </div>

    <!-- Resumen -->
    <div class="row mx-n1 mb-4">
        <div class="col-6 px-1">
            <div class="stat-mini" style="border-color:#e74a3b;">
                <div style="font-size:.7rem;" class="text-danger text-uppercase font-weight-bold mb-1">Clientes deudores</div>
                <div class="h4 mb-0"><?= $totalClientes ?></div>
            </div>
        </div>
        <div class="col-6 px-1">
            <div class="stat-mini" style="border-color:#f6c23e;">
                <div style="font-size:.7rem;" class="text-warning text-uppercase font-weight-bold mb-1">Total por cobrar</div>
                <div class="h4 mb-0">$<?= number_format($totalDeuda, 2) ?></div>
            </div>
        </div>
    </div>

    <?php if (empty($deudores)): ?>
    <div class="text-center text-muted py-5">
        <i class="fa-solid fa-circle-check fa-3x text-success mb-3" style="opacity:.6;"></i>
        <p class="mb-0 font-weight-bold">¡Sin deudas pendientes!</p>
        <p class="small">Todos los clientes están al corriente.</p>
    </div>
    <?php else: ?>

    <!-- Buscador -->
    <div style="position:relative;" class="mb-3">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:.85rem;"></i>
        <input type="text" id="buscar" class="form-control form-control-sm" style="padding-left:30px;" placeholder="Buscar cliente…">
    </div>

    <?php $maxSaldo = max(array_column($deudores, 'saldo_pendiente')); ?>

    <div id="listaDeudores">
    <?php foreach ($deudores as $d): ?>
    <div class="deudor-card" data-buscar="<?= strtolower(esc($d['nombre'], 'attr')) ?>">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex:1;min-width:0;">
                <div class="font-weight-bold" style="font-size:.95rem;"><?= esc($d['nombre']) ?></div>
                <?php if (!empty($d['telefono'])): ?>
                <div class="text-muted" style="font-size:.78rem;">
                    <i class="fa-solid fa-phone mr-1"></i><?= esc($d['telefono']) ?>
                </div>
                <?php endif; ?>
                <div class="text-muted" style="font-size:.76rem; margin-top:2px;">
                    <?= (int)$d['pedidos_pendientes'] ?> pedido<?= $d['pedidos_pendientes'] != 1 ? 's':'' ?> pendiente<?= $d['pedidos_pendientes'] != 1 ? 's':'' ?>
                    <?php if (!empty($d['primer_vencimiento'])): ?>
                    · desde <?= date('d/m/Y', strtotime($d['primer_vencimiento'])) ?>
                    <?php endif; ?>
                </div>
                <div class="saldo-bar-bg">
                    <div class="saldo-bar" style="width:<?= $maxSaldo > 0 ? round(($d['saldo_pendiente']/$maxSaldo)*100) : 0 ?>%"></div>
                </div>
            </div>
            <div class="text-right ml-3 flex-shrink-0">
                <div style="font-weight:700;font-size:1.05rem;color:#e74a3b;">
                    $<?= number_format($d['saldo_pendiente'], 2) ?>
                </div>
                <a href="/comedor/deudores" class="btn btn-sm btn-outline-danger mt-1" style="padding:2px 8px;font-size:.75rem;">
                    <i class="fa-solid fa-hand-holding-dollar mr-1"></i>Cobrar
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <div id="sinRes" class="text-center text-muted py-4" style="display:none;">Sin resultados.</div>

    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$('#buscar').on('input', function () {
    const q = $(this).val().toLowerCase().trim();
    let n = 0;
    $('.deudor-card').each(function () {
        const ok = !q || $(this).data('buscar').includes(q);
        $(this).toggle(ok);
        if (ok) n++;
    });
    $('#sinRes').toggle(n === 0 && q.length > 0);
});
</script>
<?= $this->endSection() ?>
