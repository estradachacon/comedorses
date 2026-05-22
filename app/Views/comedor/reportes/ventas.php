<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
.filter-bar { background:#fff; border-radius:10px; border:1px solid #e3e6ea; padding:14px 16px; margin-bottom:16px; }
.stat-mini  { border-radius:8px; padding:10px 14px; border-left:4px solid; margin-bottom:8px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.05); }
.dia-card   { background:#fff; border-radius:10px; border:1px solid #e3e6ea; padding:12px 14px; margin-bottom:7px; }
.item-rank  { display:flex; align-items:center; padding:8px 0; border-bottom:1px solid #f5f5f5; font-size:.88rem; }
.item-rank:last-child { border-bottom:none; }
.rank-bar-bg { background:#f0f0f0; border-radius:4px; height:6px; flex:1; margin:0 10px; }
.rank-bar    { background:#4e73df; border-radius:4px; height:6px; }
</style>

<div class="container-fluid px-3">
    <div class="d-flex align-items-center mb-3 pt-1" style="gap:10px;">
        <a href="/comedor/pedidos" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h5 class="mb-0 font-weight-bold"><i class="fa-solid fa-chart-line mr-2 text-success"></i><?= esc($title) ?></h5>
    </div>

    <!-- Filtros -->
    <form method="get" class="filter-bar">
        <div class="row" style="row-gap:10px;">
            <div class="col-6 col-md-3">
                <label class="small font-weight-bold text-muted d-block mb-1">DESDE</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="<?= esc($desde) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="small font-weight-bold text-muted d-block mb-1">HASTA</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="<?= esc($hasta) ?>">
            </div>
            <div class="col-12 col-md-6 d-flex align-items-end" style="gap:6px;">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fa-solid fa-filter mr-1"></i>Filtrar
                </button>
                <a href="/comedor/reportes/ventas" class="btn btn-outline-secondary btn-sm">Este mes</a>
            </div>
        </div>
    </form>

    <!-- Resumen global -->
    <div class="row mx-n1 mb-4">
        <div class="col-6 col-md-3 px-1">
            <div class="stat-mini" style="border-color:#4e73df;">
                <div style="font-size:.7rem;" class="text-primary text-uppercase font-weight-bold mb-1">Pedidos</div>
                <div class="h4 mb-0"><?= $resumen->pedidos ?? 0 ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1">
            <div class="stat-mini" style="border-color:#1cc88a;">
                <div style="font-size:.7rem;" class="text-success text-uppercase font-weight-bold mb-1">Total vendido</div>
                <div class="h4 mb-0">$<?= number_format($resumen->vendido ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1">
            <div class="stat-mini" style="border-color:#36b9cc;">
                <div style="font-size:.7rem;" class="text-info text-uppercase font-weight-bold mb-1">Cobrado</div>
                <div class="h4 mb-0">$<?= number_format($resumen->cobrado ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1">
            <div class="stat-mini" style="border-color:#e74a3b;">
                <div style="font-size:.7rem;" class="text-danger text-uppercase font-weight-bold mb-1">Por cobrar</div>
                <div class="h4 mb-0">$<?= number_format($resumen->pendiente ?? 0, 2) ?></div>
            </div>
        </div>
    </div>

    <!-- Desglose contado vs fiado -->
    <?php if (($resumen->vendido ?? 0) > 0): ?>
    <div class="row mb-4">
        <div class="col-12 col-md-6 mb-3">
            <div style="background:#fff;border-radius:10px;border:1px solid #e3e6ea;padding:14px 16px;">
                <div class="font-weight-bold mb-3" style="font-size:.85rem;">
                    <i class="fa-solid fa-money-bill-wave text-success mr-1"></i>Desglose de cobro
                </div>
                <?php
                $vendido = $resumen->vendido ?? 0;
                $pctContado = $vendido > 0 ? round(($resumen->contado / $vendido) * 100) : 0;
                $pctFiado   = 100 - $pctContado;
                ?>
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;">
                    <span>Contado</span>
                    <span class="font-weight-bold text-success">$<?= number_format($resumen->contado ?? 0, 2) ?> <span class="text-muted font-weight-normal">(<?= $pctContado ?>%)</span></span>
                </div>
                <div class="progress mb-3" style="height:8px;border-radius:4px;">
                    <div class="progress-bar bg-success" style="width:<?= $pctContado ?>%"></div>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;">
                    <span>Fiado (total)</span>
                    <span class="font-weight-bold text-warning">$<?= number_format($resumen->fiado_total ?? 0, 2) ?> <span class="text-muted font-weight-normal">(<?= $pctFiado ?>%)</span></span>
                </div>
                <div class="progress mb-3" style="height:8px;border-radius:4px;">
                    <div class="progress-bar bg-warning" style="width:<?= $pctFiado ?>%"></div>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.85rem;">
                    <span class="text-muted">Fiado cobrado</span>
                    <span class="font-weight-bold">$<?= number_format($resumen->fiado_cobrado ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Top items -->
        <?php if (!empty($topItems)): ?>
        <div class="col-12 col-md-6 mb-3">
            <div style="background:#fff;border-radius:10px;border:1px solid #e3e6ea;padding:14px 16px;">
                <div class="font-weight-bold mb-3" style="font-size:.85rem;">
                    <i class="fa-solid fa-star text-warning mr-1"></i>Top items vendidos
                </div>
                <?php $maxVendido = max(array_column($topItems, 'total_vendido')); ?>
                <?php foreach ($topItems as $it): ?>
                <div class="item-rank">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:.83rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= esc($it['item_nombre']) ?></div>
                        <div style="font-size:.72rem;color:#aaa;"><?= number_format($it['total_unidades'], 0) ?> uds.</div>
                    </div>
                    <div class="rank-bar-bg">
                        <div class="rank-bar" style="width:<?= $maxVendido > 0 ? round(($it['total_vendido']/$maxVendido)*100) : 0 ?>%"></div>
                    </div>
                    <div style="font-weight:700;font-size:.83rem;white-space:nowrap;">$<?= number_format($it['total_vendido'], 2) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Por día -->
    <?php if (!empty($porDia)): ?>
    <div class="font-weight-bold mb-2" style="font-size:.85rem;color:#555;">
        <i class="fa-solid fa-calendar-days mr-1"></i>Detalle por día
    </div>
    <?php foreach ($porDia as $d): ?>
    <div class="dia-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="font-weight-bold"><?= date('d/m/Y', strtotime($d['fecha'])) ?></div>
                <div class="text-muted" style="font-size:.78rem;"><?= $d['pedidos'] ?> pedido<?= $d['pedidos'] != 1 ? 's':'' ?></div>
            </div>
            <div class="text-right">
                <div class="font-weight-bold text-success">$<?= number_format($d['vendido'], 2) ?></div>
                <?php if ($d['pendiente'] > 0): ?>
                <div style="font-size:.78rem;color:#e74a3b;">Debe $<?= number_format($d['pendiente'], 2) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php elseif (($resumen->pedidos ?? 0) === 0): ?>
    <div class="text-center text-muted py-5">
        <i class="fa-solid fa-chart-line fa-2x mb-2" style="opacity:.2;"></i>
        <p class="mb-0">Sin ventas en el período seleccionado.</p>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?= $this->endSection() ?>
