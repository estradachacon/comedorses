<?php

// Formatea el número interno de un pedido del comedor (ej. "P202600009")
// a un formato legible para el usuario (ej. "Pedido 000009-2026").
// El valor almacenado en comedor_pedidos_head.numero no cambia, esto es solo presentación.
function formatearNumeroPedido(?string $numero): string
{
    if (!$numero) {
        return '';
    }

    if (preg_match('/^P(\d{4})(\d+)$/', $numero, $m)) {
        return 'Pedido ' . str_pad($m[2], 6, '0', STR_PAD_LEFT) . '-' . $m[1];
    }

    return $numero;
}

// Hora límite (24h, 'H:i') hasta la que se puede pedir cada servicio.
// Después de esa hora ya no se sube/prepara ese servicio. Ajustar aquí si cambian los horarios.
function horariosServicioComedor(): array
{
    return [
        'desayuno'   => '07:00',
        'refrigerio' => '10:00',
        'almuerzo'   => '12:00',
    ];
}

function etiquetaServicioComedor(string $servicio): string
{
    $map = ['desayuno' => 'Desayuno', 'refrigerio' => 'Refrigerio', 'almuerzo' => 'Almuerzo'];
    return $map[$servicio] ?? ucfirst($servicio);
}

// Convierte "07:00" -> "7:00 am", "12:00" -> "12:00 md" (mediodía).
function formatearHoraComedor(string $hora): string
{
    [$h, $m] = array_map('intval', explode(':', $hora));
    if ($h === 12 && $m === 0) {
        return '12:00 md';
    }
    $sufijo = $h < 12 ? 'am' : 'pm';
    $h12    = $h % 12 ?: 12;
    return sprintf('%d:%02d %s', $h12, $m, $sufijo);
}

// Servicios (desayuno/refrigerio/almuerzo) marcados para este item del menú del día.
function serviciosAsignadosComedor(array $item): array
{
    $out = [];
    foreach (array_keys(horariosServicioComedor()) as $servicio) {
        if (!empty($item[$servicio])) {
            $out[] = $servicio;
        }
    }
    return $out;
}

// De los servicios asignados, cuáles siguen dentro de su horario (aún no pasó la hora límite).
// Un item sin ningún servicio asignado, o con los 3 (todo el día), no tiene restricción de hora.
function serviciosAbiertosComedor(array $item, ?string $horaActual = null): array
{
    $asignados = serviciosAsignadosComedor($item);
    if (count($asignados) !== 1 && count($asignados) !== 2) {
        return $asignados;
    }

    $horaActual = $horaActual ?? date('H:i');
    $horarios   = horariosServicioComedor();
    return array_values(array_filter($asignados, fn ($s) => $horaActual < $horarios[$s]));
}

// ¿Se puede pedir este item en este momento?
function itemDisponibleAhoraComedor(array $item, ?string $horaActual = null): bool
{
    $asignados = serviciosAsignadosComedor($item);
    if (count($asignados) === 0 || count($asignados) === 3) {
        return true;
    }
    return count(serviciosAbiertosComedor($item, $horaActual)) > 0;
}

// Cuando un item tiene 2 o 3 servicios asignados (incluye "todo el día"), hay que preguntarle
// al cliente para cuál horario lo quiere, para que la cocina sepa cuándo subirlo. Con un solo
// servicio asignado no hace falta preguntar: ya se sabe para cuándo es.
function requiereElegirHorarioComedor(array $item): bool
{
    $cantidad = count(serviciosAsignadosComedor($item));
    return $cantidad === 2 || $cantidad === 3;
}
