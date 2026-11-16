<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Disposición de botonera superior:
     * INICIO | INSTITUCIONAL | TELÉFONOS INTERNOS | WEB HU | PROTOCOLOS | PROPUESTAS LABORALES | SID
     */
    public function up(): void
    {
        $now = now();

        // 1. TELÉFONOS INTERNOS (antes GRILLA DE TELÉFONOS en COMUNICACIÓN EN LÍNEA)
        $telefonos = DB::table('menu_items')
            ->whereIn('title', ['GRILLA DE TELÉFONOS', 'TELÉFONOS INTERNOS'])
            ->first();

        if ($telefonos) {
            DB::table('menu_items')
                ->where('id', $telefonos->id)
                ->update([
                    'parent_id' => null,
                    'title' => 'TELÉFONOS INTERNOS',
                    'destination_type' => 'url',
                    'url' => $telefonos->url ?: 'http://172.22.112.22:10007/?q=',
                    'order' => 3,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('menu_items')->insert([
                'parent_id' => null,
                'title' => 'TELÉFONOS INTERNOS',
                'destination_type' => 'url',
                'url' => 'http://172.22.112.22:10007/?q=',
                'file_path' => null,
                'order' => 3,
                'is_active' => true,
                'is_quick_link' => false,
                'quick_link_order' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. WEB HU (antes PÁGINA WEB DEL HU en OTROS)
        $webHu = DB::table('menu_items')
            ->whereIn('title', ['PÁGINA WEB DEL HU', 'WEB HU'])
            ->first();

        if ($webHu) {
            DB::table('menu_items')
                ->where('id', $webHu->id)
                ->update([
                    'parent_id' => null,
                    'title' => 'WEB HU',
                    'destination_type' => 'url',
                    'url' => $webHu->url ?: 'https://hospital.uncuyo.edu.ar/',
                    'order' => 4,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('menu_items')->insert([
                'parent_id' => null,
                'title' => 'WEB HU',
                'destination_type' => 'url',
                'url' => 'https://hospital.uncuyo.edu.ar/',
                'file_path' => null,
                'order' => 4,
                'is_active' => true,
                'is_quick_link' => false,
                'quick_link_order' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 3. PROPUESTAS LABORALES (antes PROPUESTAS DE TRABAJO)
        DB::table('menu_items')
            ->where('title', 'PROPUESTAS DE TRABAJO')
            ->whereNull('parent_id')
            ->update([
                'title' => 'PROPUESTAS LABORALES',
                'order' => 6,
                'updated_at' => $now,
            ]);

        // 4. SID (antes en OTROS)
        $sid = DB::table('menu_items')
            ->where('title', 'SID')
            ->first();

        if ($sid) {
            DB::table('menu_items')
                ->where('id', $sid->id)
                ->update([
                    'parent_id' => null,
                    'destination_type' => $sid->destination_type ?: 'url',
                    'url' => $sid->url ?: 'https://sid.uncu.edu.ar/wp/',
                    'order' => 7,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('menu_items')->insert([
                'parent_id' => null,
                'title' => 'SID',
                'destination_type' => 'url',
                'url' => 'https://sid.uncu.edu.ar/wp/',
                'file_path' => null,
                'order' => 7,
                'is_active' => true,
                'is_quick_link' => false,
                'quick_link_order' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 5. Ordenar todos los elementos principales (fila principal 1..7 y resto 8..16)
        $topLevelOrder = [
            'INICIO' => 1,
            'INSTITUCIONAL' => 2,
            'TELÉFONOS INTERNOS' => 3,
            'WEB HU' => 4,
            'PROTOCOLOS' => 5,
            'PROPUESTAS LABORALES' => 6,
            'SID' => 7,
            'AUTOGESTIÓN DE TURNOS' => 8,
            'GESTIONES' => 9,
            'COMUNICACIÓN EN LÍNEA' => 10,
            'COVID-19' => 11,
            'UNIDAD QUIRÚRGICA' => 12,
            'FORMULARIOS ADMINISTRATIVOS' => 13,
            'DIGESTO HU' => 14,
            'BUSCADOR DE PROFESIONALES' => 15,
            'OTROS' => 16,
        ];

        foreach ($topLevelOrder as $title => $order) {
            DB::table('menu_items')
                ->whereNull('parent_id')
                ->where('title', $title)
                ->update([
                    'order' => $order,
                    'updated_at' => $now,
                ]);
        }

        // 6. Reordenar hijos de COMUNICACIÓN EN LÍNEA y OTROS sin huecos
        $parents = DB::table('menu_items')
            ->whereNull('parent_id')
            ->whereIn('title', ['COMUNICACIÓN EN LÍNEA', 'OTROS'])
            ->pluck('id');

        foreach ($parents as $parentId) {
            $children = DB::table('menu_items')
                ->where('parent_id', $parentId)
                ->orderBy('order')
                ->orderBy('id')
                ->pluck('id');

            foreach ($children as $index => $childId) {
                DB::table('menu_items')
                    ->where('id', $childId)
                    ->update(['order' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        $now = now();

        $comunicacion = DB::table('menu_items')
            ->whereNull('parent_id')
            ->where('title', 'COMUNICACIÓN EN LÍNEA')
            ->first();

        $otros = DB::table('menu_items')
            ->whereNull('parent_id')
            ->where('title', 'OTROS')
            ->first();

        if ($comunicacion) {
            DB::table('menu_items')
                ->where('title', 'TELÉFONOS INTERNOS')
                ->whereNull('parent_id')
                ->update([
                    'parent_id' => $comunicacion->id,
                    'title' => 'GRILLA DE TELÉFONOS',
                    'order' => 3,
                    'updated_at' => $now,
                ]);
        }

        if ($otros) {
            DB::table('menu_items')
                ->where('title', 'SID')
                ->whereNull('parent_id')
                ->update([
                    'parent_id' => $otros->id,
                    'order' => 1,
                    'updated_at' => $now,
                ]);

            DB::table('menu_items')
                ->where('title', 'WEB HU')
                ->whereNull('parent_id')
                ->update([
                    'parent_id' => $otros->id,
                    'title' => 'PÁGINA WEB DEL HU',
                    'order' => 4,
                    'updated_at' => $now,
                ]);
        }

        DB::table('menu_items')
            ->where('title', 'PROPUESTAS LABORALES')
            ->whereNull('parent_id')
            ->update([
                'title' => 'PROPUESTAS DE TRABAJO',
                'order' => 9,
                'updated_at' => $now,
            ]);

        $originalTopOrder = [
            'INICIO' => 1,
            'INSTITUCIONAL' => 2,
            'AUTOGESTIÓN DE TURNOS' => 3,
            'GESTIONES' => 4,
            'COMUNICACIÓN EN LÍNEA' => 5,
            'COVID-19' => 6,
            'PROTOCOLOS' => 7,
            'UNIDAD QUIRÚRGICA' => 8,
            'PROPUESTAS DE TRABAJO' => 9,
            'FORMULARIOS ADMINISTRATIVOS' => 10,
            'DIGESTO HU' => 11,
            'BUSCADOR DE PROFESIONALES' => 12,
            'OTROS' => 13,
        ];

        foreach ($originalTopOrder as $title => $order) {
            DB::table('menu_items')
                ->whereNull('parent_id')
                ->where('title', $title)
                ->update([
                    'order' => $order,
                    'updated_at' => $now,
                ]);
        }
    }
};
