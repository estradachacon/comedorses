<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fa-solid fa-users mr-2 text-info"></i><?= esc($title) ?>
        </h4>
        <?php if (tienePermiso('gestionar_clientes_comedor')): ?>
        <a href="/comedor/clientes/nuevo" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus mr-1"></i>Nuevo Comensal
        </a>
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
            <table id="tablaClientes" class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Identificación</th>
                        <th>Teléfono</th>
                        <th class="text-right">Saldo Pendiente</th>
                        <?php if (tienePermiso('gestionar_clientes_comedor')): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><?= esc($c['nombre']) ?></td>
                        <td><?= esc($c['identificacion'] ?? '—') ?></td>
                        <td><?= esc($c['telefono'] ?? '—') ?></td>
                        <td class="text-right">
                            <?php if ($c['saldo_pendiente'] > 0): ?>
                                <span class="badge badge-danger badge-pill">$<?= number_format($c['saldo_pendiente'], 2) ?></span>
                            <?php else: ?>
                                <span class="text-success"><i class="fa-solid fa-check"></i> Al día</span>
                            <?php endif; ?>
                        </td>
                        <?php if (tienePermiso('gestionar_clientes_comedor')): ?>
                        <td>
                            <a href="/comedor/clientes/editar/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($clientes)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay comensales registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$('#tablaClientes').DataTable({ pageLength: 25 });
</script>

<?= $this->endSection() ?>
