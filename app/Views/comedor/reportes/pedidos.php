<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
.rep-card { background:#fff; border-radius:10px; border:1px solid #e3e6ea; box-shadow:0 1px 4px rgba(0,0,0,.05); margin-bottom:8px; overflow:hidden; }
.rep-row   { display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid #f5f5f5; font-size:.88rem; }
.rep-row:last-child { border-bottom:none; }
.rep-num   { font-weight:700; font-size:.82rem; color:#4e73df; }
.rep-badge { font-size:.72rem; }
.filter-bar { background:#fff; border-radius:10px; border:1px solid #e3e6ea; padding:14px 16px; margin-bottom:16px; }
.stat-mini  { border-radius:8px; padding:10px 14px; border-left:4px solid; margin-bottom:8px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.05); }
</style>

<div class="container-fluid px-3">
    <div class="d-flex align-items-center mb-3 pt-1" style="gap:10px;">
        <a href="/comedor/pedidos" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h5 class="mb-0 font-weight-bold"><i class="fa-solid fa-file-lines mr-2 text-primary"></i><?= esc($title) ?></h5>
    </div>

    <!-- Filtros -->
    <form method="get" class="filter-bar">
        <div class="row" style="row-gap:10px;">
            <div class="col-6 col-md-2">
                <label class="small font-weight-bold text-muted d-block mb-1">DESDE</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="<?= esc($desde) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="small font-weight-bold text-muted d-block mb-1">HASTA</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="<?= esc($hasta) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="small font-weight-bold text-muted d-block mb-1">ESTADO</label>
                <select name="estado" class="form-control form-control-sm">
                    <option value="todos"     <?= $estado==='todos'     ?'selected':'' ?>>Todos</option>
                    <option value="pagado"    <?= $estado==='pagado'    ?'selected':'' ?>>Pagado</option>
                    <option value="pendiente" <?= $estado==='pendiente' ?'selected':'' ?>>Pendiente</option>
                    <option value="anulado"   <?= $estado==='anulado'   ?'selected':'' ?>>Anulado</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="small font-weight-bold text-muted d-block mb-1">TIPO PAGO</label>
                <select name="tipo_pago" class="form-control form-control-sm">
                    <option value="todos"   <?= $pago==='todos'   ?'selected':'' ?>>Todos</option>
                    <option value="contado" <?= $pago==='contado' ?'selected':'' ?>>Contado</option>
                    <option value="fiado"   <?= $pago==='fiado'   ?'selected':'' ?>>Fiado</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end" style="gap:6px;">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fa-solid fa-filter mr-1"></i>Filtrar
                </button>
                <a href="/comedor/reportes/pedidos" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </div>
    </form>

    <!-- Resumen -->
    <div class="row mx-n1 mb-3">
        <div class="col-6 col-md-3 px-1">
            <div class="stat-mini" style="border-color:#4e73df;">
                <div style="font-size:.7rem;" class="text-primary text-uppercase font-weight-bold mb-1">Pedidos</div>
                <div class="h5 mb-0"><?= $resumen['pedidos'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1">
            <div class="stat-mini" style="border-color:#1cc88a;">
                <div style="font-size:.7rem;" class="text-success text-uppercase font-weight-bold mb-1">Vendido</div>
                <div class="h5 mb-0">$<?= number_format($resumen['vendido'], 2) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1">
            <div class="stat-mini" style="border-color:#36b9cc;">
                <div style="font-size:.7rem;" class="text-info text-uppercase font-weight-bold mb-1">Cobrado</div>
                <div class="h5 mb-0">$<?= number_format($resumen['cobrado'], 2) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 px-1">
            <div class="stat-mini" style="border-color:#f6c23e;">
                <div style="font-size:.7rem;" class="text-warning text-uppercase font-weight-bold mb-1">Pendiente</div>
                <div class="h5 mb-0">$<?= number_format($resumen['pendiente'], 2) ?></div>
            </div>
        </div>
    </div>

    <!-- Buscador -->
    <?php if (!empty($pedidos)): ?>
    <div style="position:relative;" class="mb-3">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:.85rem;"></i>
        <input type="text" id="buscar" class="form-control form-control-sm" style="padding-left:30px;" placeholder="Buscar por cliente o número…">
    </div>
    <?php endif; ?>

    <!-- Lista -->
    <?php if (empty($pedidos)): ?>
        <div class="text-center text-muted py-5">
            <i class="fa-solid fa-file-lines fa-2x mb-2" style="opacity:.2;"></i>
            <p class="mb-0">Sin resultados para los filtros seleccionados.</p>
        </div>
    <?php else: ?>
    <?php
    $mapE = ['pagado'=>'success','pendiente'=>'warning text-dark','anulado'=>'secondary'];
    ?>
    <div id="listaRep">
    <?php foreach ($pedidos as $p): ?>
    <div class="rep-card" data-buscar="<?= strtolower(esc($p['cliente_nombre'],'attr').' '.esc($p['numero'],'attr')) ?>">
        <div class="rep-row">
            <div style="flex:1;min-width:0;">
                <div class="d-flex align-items-center flex-wrap mb-1" style="gap:5px;">
                    <a href="/comedor/pedidos/ver/<?= $p['id'] ?>" class="rep-num"><?= esc($p['numero']) ?></a>
                    <span class="badge badge-<?= $mapE[$p['estado']] ?? 'light' ?> rep-badge"><?= ucfirst($p['estado']) ?></span>
                    <span class="badge badge-<?= $p['tipo_pago']==='contado'?'success':'warning text-dark' ?> rep-badge"><?= ucfirst($p['tipo_pago']) ?></span>
                </div>
                <div style="font-weight:600;font-size:.9rem;"><?= esc($p['cliente_nombre']) ?></div>
                <div class="text-muted" style="font-size:.75rem;">
                    <?= date('d/m/Y', strtotime($p['fecha'])) ?>
                    <?php if (!empty($p['cajero_nombre'])): ?> · <?= esc($p['cajero_nombre']) ?><?php endif; ?>
                </div>
            </div>
            <div class="text-right ml-3 flex-shrink-0">
                <div style="font-weight:700;font-size:1rem;">$<?= number_format($p['total'], 2) ?></div>
                <?php if ($p['saldo'] > 0): ?>
                <div style="font-size:.78rem;color:#e74a3b;font-weight:600;">Debe $<?= number_format($p['saldo'], 2) ?></div>
                <?php endif; ?>
                <a href="/comedor/pedidos/ver/<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary mt-1" style="padding:1px 8px;">
                    <i class="fa-solid fa-eye"></i>
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
    $('.rep-card').each(function () {
        const ok = !q || $(this).data('buscar').includes(q);
        $(this).toggle(ok);
        if (ok) n++;
    });
    $('#sinRes').toggle(n === 0 && q.length > 0);
});
</script>
<?= $this->endSection() ?>
