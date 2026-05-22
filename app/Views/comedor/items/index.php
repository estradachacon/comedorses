<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fa-solid fa-utensils mr-2 text-warning"></i><?= esc($title) ?>
        </h4>
        <?php if (tienePermiso('gestionar_items_comedor')): ?>
        <div>
            <a href="/comedor/categorias" class="btn btn-outline-secondary btn-sm mr-2">
                <i class="fa-solid fa-tags mr-1"></i>Categorías
            </a>
            <a href="/comedor/items/nuevo" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus mr-1"></i>Nuevo Item
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table id="tablaItems" class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Item</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th class="text-center">Disponible</th>
                        <?php if (tienePermiso('gestionar_items_comedor')): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="font-weight-bold"><?= esc($item['nombre']) ?></div>
                            <?php if ($item['descripcion']): ?>
                                <small class="text-muted"><?= esc($item['descripcion']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($item['categoria_nombre'] ?? '—') ?></td>
                        <td><strong>$<?= number_format($item['precio'], 2) ?></strong></td>
                        <td class="text-center">
                            <?php if (tienePermiso('gestionar_items_comedor')): ?>
                            <div class="custom-control custom-switch d-inline-block">
                                <input type="checkbox" class="custom-control-input toggle-disponible"
                                    id="disp_<?= $item['id'] ?>" data-id="<?= $item['id'] ?>"
                                    <?= $item['disponible'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="disp_<?= $item['id'] ?>"></label>
                            </div>
                            <?php else: ?>
                                <span class="badge badge-<?= $item['disponible'] ? 'success' : 'secondary' ?>">
                                    <?= $item['disponible'] ? 'Sí' : 'No' ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php if (tienePermiso('gestionar_items_comedor')): ?>
                        <td>
                            <a href="/comedor/items/editar/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay items registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$('#tablaItems').DataTable({ pageLength: 25 });

$(document).on('change', '.toggle-disponible', function () {
    const id  = $(this).data('id');
    const chk = this;
    $.post('/comedor/items/toggle/' + id, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
        .done(function (r) { if (!r.ok) chk.checked = !chk.checked; })
        .fail(function ()  { chk.checked = !chk.checked; });
});
</script>

<?= $this->endSection() ?>
