<?php
/** @var array $item */
$asignados       = $item['servicios_asignados'];
$abiertos        = $item['servicios_abiertos'];
$disponibleAhora = $item['disponible_ahora'];
$horarios        = horariosServicioComedor();
$fotoUrl         = !empty($item['foto']) ? base_url('upload/comedor_items/' . $item['foto']) : null;
?>
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
            <div class="qty-control">
                <button class="qty-btn btn-menos" data-id="<?= $item['item_id'] ?>">−</button>
                <span class="qty-num">1</span>
                <button class="qty-btn btn-mas" data-id="<?= $item['item_id'] ?>">+</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
