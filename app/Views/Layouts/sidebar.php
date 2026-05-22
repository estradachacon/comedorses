<?php
$session = session();
$foto = $session->get('foto');
$sidebarFotoPath = ($foto && file_exists(FCPATH . 'upload/perfiles/' . $foto))
    ? base_url('upload/perfiles/' . $foto)
    : base_url('upload/profile/user.jpg');
$primaryColor = setting('primary_color') ?? '#1d2744';
?>

<div id="layoutSidenav_nav">
    <span class="close-mobile-nav"><i class="fa-solid fa-close"></i></span>
    <nav class="sb-sidenav accordion sb-sidenav-dark sb-sidenav-custom" id="sidenavAccordion">

        <!-- ── BRAND ────────────────────────────────────────── -->
        <div class="sidebar-brand" style="background-color:<?= $primaryColor ?>;">
            <a href="<?= base_url('dashboard') ?>" class="sidebar-brand-link">
                <?php if (setting('logo')): ?>
                    <img src="<?= base_url('upload/settings/' . setting('logo')) ?>"
                         alt="logo" class="sidebar-logo">
                <?php else: ?>
                    <span class="sidebar-brand-text">
                        <?= esc(setting('company_name') ?? 'ERP') ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- ── USER SECTION ──────────────────────────────────── -->
        <div class="sidebar-user-section">
            <img src="<?= esc($sidebarFotoPath) ?>" alt="avatar" class="sidebar-avatar">
            <div class="sidebar-user-meta">
                <div class="sidebar-user-name"><?= esc($session->get('user_name') ?? 'Usuario') ?></div>
                <div class="sidebar-branch-name">
                    <i class="fa-solid fa-location-dot" style="font-size:.6rem;"></i>
                    <?= esc($session->get('branch_name') ?? 'Sistema') ?>
                </div>
            </div>
        </div>

        <!-- ── MENU ──────────────────────────────────────────── -->
        <div class="sb-sidenav-menu">
            <div class="nav">

                <div class="sb-sidenav-menu-heading">Principal</div>

                <!-- DASHBOARD -->
                <a class="nav-link" href="/dashboard">
                    <div class="sb-nav-link-icon si-inicio"><i class="fa-solid fa-gauge-high"></i></div>
                    Dashboard
                </a>

                <!-- COMEDOR: TOMAR PEDIDO -->
                <?php if (tienePermiso('tomar_pedido_comedor')): ?>
                <a class="nav-link" href="/comedor/pedidos/nuevo">
                    <div class="sb-nav-link-icon" style="color:#20c997 !important;"><i class="fa-solid fa-bowl-food"></i></div>
                    Tomar Pedido
                </a>
                <?php endif; ?>

                <!-- COMEDOR: PEDIDOS DEL DÍA -->
                <?php if (tienePermiso('ver_pedidos_comedor')): ?>
                <a class="nav-link" href="/comedor/pedidos">
                    <div class="sb-nav-link-icon" style="color:#4e73df !important;"><i class="fa-solid fa-receipt"></i></div>
                    Pedidos del Día
                </a>
                <?php endif; ?>

                <!-- COMEDOR: DEUDORES -->
                <?php if (tienePermiso('ver_deudores_comedor')): ?>
                <a class="nav-link" href="/comedor/deudores">
                    <div class="sb-nav-link-icon" style="color:#e74a3b !important;"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                    Deudores
                </a>
                <?php endif; ?>

                <!-- COMEDOR: SOLICITUDES -->
                <?php if (tienePermiso('confirmar_solicitud_comedor')): ?>
                <a class="nav-link" href="/comedor/solicitudes">
                    <div class="sb-nav-link-icon" style="color:#fd7e14 !important;"><i class="fa-solid fa-paper-plane"></i></div>
                    Solicitudes
                </a>
                <?php endif; ?>

                <!-- COMEDOR: MENÚ DEL DÍA -->
                <?php if (tienePermiso('gestionar_menu_comedor')): ?>
                <a class="nav-link" href="/comedor/menu">
                    <div class="sb-nav-link-icon" style="color:#f6c23e !important;"><i class="fa-solid fa-clipboard-list"></i></div>
                    Menú del Día
                </a>
                <?php endif; ?>

                <!-- COMEDOR: REPORTES -->
                <?php if (tienePermiso('ver_pedidos_comedor') || tienePermiso('ver_deudores_comedor')): ?>
                <a class="nav-link collapsed" href="#"
                   data-toggle="collapse" data-target="#reportesComedor"
                   aria-expanded="false" aria-controls="reportesComedor">
                    <div class="sb-nav-link-icon" style="color:#36b9cc !important;"><i class="fa-solid fa-chart-line"></i></div>
                    Reportes
                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="reportesComedor" data-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <?php if (tienePermiso('ver_pedidos_comedor')): ?>
                            <a class="nav-link" href="/comedor/reportes/pedidos">Pedidos</a>
                            <a class="nav-link" href="/comedor/reportes/ventas">Ventas</a>
                        <?php endif; ?>
                        <?php if (tienePermiso('ver_deudores_comedor')): ?>
                            <a class="nav-link" href="/comedor/reportes/deudas">Deudas</a>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>

                <!-- COMEDOR: CATÁLOGO -->
                <?php if (tienePermiso('ver_items_comedor') || tienePermiso('ver_clientes_comedor')): ?>
                    <a class="nav-link collapsed" href="#"
                       data-toggle="collapse" data-target="#catalogo"
                       aria-expanded="false" aria-controls="catalogo">
                        <div class="sb-nav-link-icon si-inventario"><i class="fa-solid fa-book-open"></i></div>
                        Catálogo
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="catalogo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ver_items_comedor')): ?>
                                <a class="nav-link" href="/comedor/items">Items / Platillos</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('gestionar_items_comedor')): ?>
                                <a class="nav-link" href="/comedor/categorias">Categorías</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_clientes_comedor')): ?>
                                <a class="nav-link" href="/comedor/clientes">Comensales</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>


                <!-- ADMINISTRACIÓN -->
                <?php if (
                    tienePermiso('ver_configuracion') || tienePermiso('ver_sucursales') ||
                    tienePermiso('ver_usuarios') || tienePermiso('ver_roles') ||
                    tienePermiso('ver_reportes') || tienePermiso('ver_bitacora')
                ): ?>
                    <div class="sb-sidenav-menu-heading">Administración</div>

                    <?php if (tienePermiso('ver_configuracion') || tienePermiso('ver_sucursales') || tienePermiso('ver_usuarios') || tienePermiso('ver_roles')): ?>
                        <a class="nav-link collapsed" href="#"
                           data-toggle="collapse" data-target="#company_settings"
                           aria-expanded="false" aria-controls="company_settings">
                            <div class="sb-nav-link-icon si-admin"><i class="fa-solid fa-gear"></i></div>
                            Ajustes del Sistema
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="company_settings" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <?php if (tienePermiso('ver_usuarios') || tienePermiso('ver_roles') || tienePermiso('ver_vendedores')): ?>
                                    <a class="nav-link collapsed" href="#"
                                       data-toggle="collapse" data-target="#staffs"
                                       aria-expanded="false" aria-controls="staffs">
                                        Gestión de Usuarios
                                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse" id="staffs">
                                        <nav class="sb-sidenav-menu-nested nav">
                                            <?php if (tienePermiso('ver_usuarios')): ?>
                                                <a class="nav-link" href="/users">Lista de Usuarios</a>
                                            <?php endif; ?>
                                            <?php if (tienePermiso('ver_roles')): ?>
                                                <a class="nav-link" href="/roles">Roles y Permisos</a>
                                            <?php endif; ?>
                                            <?php if (tienePermiso('ver_vendedores')): ?>
                                                <a class="nav-link" href="/sellers">Vendedores</a>
                                            <?php endif; ?>
                                        </nav>
                                    </div>
                                <?php endif; ?>
                                <?php if (tienePermiso('ver_sucursales')): ?>
                                    <a class="nav-link" href="/branches">Sucursales</a>
                                <?php endif; ?>
                                <?php if (tienePermiso('ajustes_multimedia')): ?>
                                    <a class="nav-link" href="/content">Multimedia</a>
                                <?php endif; ?>
                                <?php if (tienePermiso('ver_configuracion')): ?>
                                    <a class="nav-link" href="/settings">Información del Sistema</a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>

                    <?php if (tienePermiso('ver_reportes')): ?>
                        <a class="nav-link" href="/reports">
                            <div class="sb-nav-link-icon si-reports"><i class="fa-solid fa-chart-line"></i></div>
                            Reportería
                        </a>
                    <?php endif; ?>

                    <?php if (tienePermiso('ver_bitacora')): ?>
                        <a class="nav-link" href="/logs">
                            <div class="sb-nav-link-icon si-log"><i class="fa-solid fa-book"></i></div>
                            Bitácora
                        </a>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentPath = window.location.pathname;
    if (currentPath === '/') {
        currentPath = '/dashboard';
    } else {
        currentPath = currentPath.startsWith('/') ? currentPath.substring(1) : currentPath;
        currentPath = currentPath.split('?')[0].split('#')[0];
    }

    document.querySelectorAll('.nav-link').forEach(link => {
        let href = link.getAttribute('href');
        if (!href) return;
        let normalized = href.startsWith('/') ? href.substring(1) : href;
        normalized = normalized.startsWith('#') ? normalized.substring(1) : normalized;

        if (currentPath === normalized) {
            link.classList.add('active');
            let parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
                const parentLink = document.querySelector(`a[data-target="#${parentCollapse.id}"]`);
                if (parentLink) {
                    parentLink.classList.remove('collapsed');
                    parentLink.setAttribute('aria-expanded', 'true');
                }
            }
        }
    });
});
</script>
