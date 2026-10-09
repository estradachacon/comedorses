<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/comedor/items" class="btn btn-outline-secondary btn-sm mr-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h4 class="mb-0"><i class="fa-solid fa-utensils mr-2 text-warning"></i><?= esc($title) ?></h4>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" style="max-width:600px;">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm" style="max-width:600px;">
        <div class="card-body">
            <?php
                $action = $item ? '/comedor/items/actualizar/' . $item['id'] : '/comedor/items/crear';
                $fotoUrl = !empty($item['foto']) ? base_url('upload/comedor_items/' . $item['foto']) : base_url('upload/no-image.png');
            ?>
            <form action="<?= $action ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Foto del item</label>
                    <div class="text-center mb-2">
                        <img id="previewFotoItem" src="<?= esc($fotoUrl) ?>" alt=""
                             style="width:100%;max-height:180px;object-fit:contain;border-radius:8px;border:1px solid #e3e6ea;background:#f8f9fa;">
                    </div>
                    <div class="d-flex" style="gap:8px;">
                        <label class="btn btn-sm btn-outline-primary flex-fill mb-0">
                            <i class="fa-solid fa-camera mr-1"></i>Tomar foto
                            <input type="file" id="inputFotoCapturar" accept="image/*" capture="environment" style="display:none;">
                        </label>
                        <label class="btn btn-sm btn-outline-secondary flex-fill mb-0">
                            <i class="fa-solid fa-image mr-1"></i>Subir de galería
                            <input type="file" id="inputFotoGaleria" accept="image/*" style="display:none;">
                        </label>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.72rem;">JPG, PNG o WEBP · máx. 3MB (opcional)</div>
                    <input type="file" name="foto" id="inputFotoReal" style="display:none;">
                </div>

                <div class="form-group">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control"
                        value="<?= esc($item['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">— Sin categoría —</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= (isset($item) && $item['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= esc($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Precio <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input type="number" name="precio" class="form-control" step="0.01" min="0"
                            value="<?= esc($item['precio'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"><?= esc($item['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="disponible" name="disponible" value="1"
                            <?= (!$item || $item['disponible']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="disponible">Disponible para venta</label>
                    </div>
                </div>

                <div class="d-flex" style="gap:8px;">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fa-solid fa-save mr-1"></i>
                        <?= $item ? 'Actualizar' : 'Guardar' ?>
                    </button>
                    <a href="/comedor/items" class="btn btn-outline-secondary flex-fill">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Las dos opciones visibles (cámara / galería) alimentan el mismo input real del formulario,
// usando DataTransfer porque no se puede asignar un FileList directamente por JS.
function manejarArchivoFoto(inputOrigen) {
    const file = inputOrigen.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('inputFotoReal').files = dt.files;

    const reader = new FileReader();
    reader.onload = e => $('#previewFotoItem').attr('src', e.target.result);
    reader.readAsDataURL(file);
}

$('#inputFotoCapturar, #inputFotoGaleria').on('change', function () {
    manejarArchivoFoto(this);
});
</script>

<?= $this->endSection() ?>
