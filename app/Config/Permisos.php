<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Permisos extends BaseConfig
{
    public array $modulos = [

        'Comedor' => [
            'ver_pedidos_comedor',
            'tomar_pedido_comedor',
            'anular_pedido_comedor',
            'ver_items_comedor',
            'gestionar_items_comedor',
            'gestionar_menu_comedor',
            'ver_clientes_comedor',
            'gestionar_clientes_comedor',
            'ver_deudores_comedor',
            'registrar_pago_deudor_comedor',
            'gestionar_entregas_comedor',
        ],

        'Notificaciones' => [
            'ver_notificacion_deudores_comedor',
        ],

        'Alertas Operativas' => [
            'confirmar_solicitud_comedor',
        ],

        'Ajustes del sistema' => [
            'ver_configuracion',
            'ver_almacenamiento',
            'ver_bitacora',
        ],

        'Gestión de usuarios' => [
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',
            'ver_roles',
            'editar_roles',
            'eliminar_roles',
            'crear_roles',
            'asignar_permisos',
        ],
    ];
}
