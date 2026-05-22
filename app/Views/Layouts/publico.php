<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?= setting('company_name') ?? 'Comedor' ?> — Menú</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php
    $favicon = setting('favicon');
    $faviconUrl = ($favicon && file_exists(FCPATH . 'upload/settings/' . $favicon))
        ? base_url('upload/settings/' . $favicon)
        : base_url('favicon.ico');
    ?>
    <link rel="shortcut icon" href="<?= esc($faviconUrl) ?>">
    <style>
        :root {
            --primary: <?= setting('primary_color') ?? '#1d2744' ?>;
        }
        * { box-sizing: border-box; }
        body {
            background: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding-bottom: 120px;
        }
        /* Header */
        .pub-header {
            background: var(--primary);
            color: #fff;
            padding: 1rem 1.25rem .8rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,.25);
        }
        .pub-logo {
            max-height: 36px;
            max-width: 120px;
            object-fit: contain;
        }
        .pub-title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }
        .pub-subtitle {
            font-size: .72rem;
            color: rgba(255,255,255,.65);
        }
        /* Categorías como pills */
        .cat-pills {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: .6rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            scrollbar-width: none;
        }
        .cat-pills::-webkit-scrollbar { display: none; }
        .cat-pill {
            flex-shrink: 0;
            padding: 4px 14px;
            border-radius: 20px;
            border: 2px solid #dee2e6;
            font-size: .8rem;
            font-weight: 600;
            color: #6c757d;
            cursor: pointer;
            background: #fff;
            transition: all .15s;
        }
        .cat-pill.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        /* Sección de categoría */
        .cat-section { padding: .5rem 0; }
        .cat-heading {
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #adb5bd;
            padding: .8rem 1rem .3rem;
        }
        /* Item card */
        .item-card {
            background: #fff;
            border-radius: 12px;
            margin: 0 1rem .65rem;
            padding: .85rem 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.07);
            transition: box-shadow .15s;
        }
        .item-card.selected {
            box-shadow: 0 0 0 2px var(--primary), 0 2px 8px rgba(0,0,0,.1);
        }
        .item-emoji {
            font-size: 2rem;
            width: 46px;
            text-align: center;
            flex-shrink: 0;
        }
        .item-info { flex: 1; min-width: 0; }
        .item-name {
            font-size: .9rem;
            font-weight: 700;
            color: #1a2b3c;
            line-height: 1.3;
        }
        .item-desc {
            font-size: .75rem;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-price {
            font-size: .95rem;
            font-weight: 700;
            color: #20c997;
            flex-shrink: 0;
        }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
        }
        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #f0f0f0;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .12s;
        }
        .qty-btn:hover { background: #dee2e6; }
        .qty-btn.add-btn {
            background: var(--primary);
            color: #fff;
        }
        .qty-btn.add-btn:hover { opacity: .85; }
        .qty-num {
            font-size: .95rem;
            font-weight: 700;
            min-width: 22px;
            text-align: center;
        }
        /* Barra flotante del carrito */
        .cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--primary);
            color: #fff;
            padding: .85rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 200;
            box-shadow: 0 -3px 15px rgba(0,0,0,.2);
            transform: translateY(100%);
            transition: transform .25s cubic-bezier(.4,0,.2,1);
        }
        .cart-bar.visible { transform: translateY(0); }
        .cart-bar-left { font-size: .85rem; }
        .cart-bar-count { font-size: .75rem; opacity: .8; }
        .cart-bar-total { font-size: 1.1rem; font-weight: 700; }
        .cart-bar-btn {
            background: #fff;
            color: var(--primary);
            border: none;
            border-radius: 24px;
            padding: .55rem 1.4rem;
            font-weight: 700;
            font-size: .9rem;
            cursor: pointer;
        }
        /* Modal confirmación nombre */
        .modal-content { border-radius: 18px 18px 0 0; }
        @media (min-width: 576px) {
            .modal-dialog { max-width: 420px; margin: auto; }
            .modal-content { border-radius: 18px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="pub-header d-flex align-items-center gap-3">
    <div class="mr-3">
        <?php $logo = setting('logo'); ?>
        <?php if ($logo && file_exists(FCPATH . 'upload/settings/' . $logo)): ?>
            <img src="<?= base_url('upload/settings/' . $logo) ?>" class="pub-logo" alt="logo">
        <?php else: ?>
            <i class="fa-solid fa-bowl-food fa-lg" style="opacity:.8;"></i>
        <?php endif; ?>
    </div>
    <div>
        <div class="pub-title"><?= esc(setting('company_name') ?? 'Comedor') ?></div>
        <div class="pub-subtitle">Menú del <?= date('d/m/Y') ?></div>
    </div>
</div>

<!-- Contenido -->
<?= $this->renderSection('content') ?>

<!-- Scripts base -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</body>
</html>
