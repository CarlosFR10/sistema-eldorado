<?php
$viajes = \App\Models\Viaje::where('id', '>', 227)->with('bus')->get();
foreach ($viajes as $viaje) {
    $bus = $viaje->bus;
    $config = $bus->config_asientos ?: ['columnas' => 4, 'pasillo' => 2, 'especiales' => []];
    $columnas = max((int)($config['columnas'] ?? 4), 1);
    $pasillo = (int)($config['pasillo'] ?? 2);
    $especiales = $config['especiales'] ?? [];
    
    for ($numero = 1; $numero <= $bus->capacidad; $numero++) {
        $fila = (int)ceil($numero / $columnas);
        $colEnOriginal = (($numero - 1) % $columnas) + 1;
        $columna = $colEnOriginal >= $pasillo ? $colEnOriginal + 1 : $colEnOriginal;
        $piso = $bus->tipo_bus === 'doble_piso' && $numero > ($bus->capacidad / 2) ? 2 : 1;
        $tipo = in_array($numero, $especiales, true) ? 'preferencial' : 'normal';
        
        \App\Models\Asiento::create([
            'viaje_id' => $viaje->id,
            'numero' => $numero,
            'fila' => $fila,
            'columna' => $columna,
            'piso' => $piso,
            'tipo' => $tipo,
            'estado' => 'disponible',
        ]);
    }
    echo "Generated asientos for viaje $viaje->id (bus $bus->placa, capacidad $bus->capacidad)\n";
}
echo "Done!\n";