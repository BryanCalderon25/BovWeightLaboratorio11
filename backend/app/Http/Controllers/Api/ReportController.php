<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Generar un reporte PDF del historial de peso de un animal.
     */
    public function generateAnimalReport(Request $request, $animalId)
    {
        $animal = Animal::with('farm', 'weightRecords')->findOrFail($animalId);

        if ($animal->farm->user_id !== $request->user()->id && !$request->user()->hasSharedAccess($animal->farm_id)) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        // Generar la vista HTML para el PDF (se requiere crear una vista resources/views/reports/animal.blade.php)
        // Por ahora, generaremos HTML básico directo para asegurar funcionalidad.
        
        $html = '<h1>Reporte de Pesaje - BovWeight CR</h1>';
        $html .= '<p><strong>Animal:</strong> ' . $animal->nombre . ' (Arete: ' . $animal->arete . ')</p>';
        $html .= '<p><strong>Raza:</strong> ' . $animal->raza . '</p>';
        $html .= '<p><strong>Finca:</strong> ' . $animal->farm->nombre . '</p>';
        $html .= '<p><strong>Fecha de Generación:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>';
        
        $html .= '<h2>Historial de Pesajes</h2>';
        $html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%">';
        $html .= '<tr><th>Fecha</th><th>Peso Estimado (kg)</th><th>Estado</th></tr>';
        
        foreach ($animal->weightRecords as $record) {
            $html .= '<tr>';
            $html .= '<td>' . $record->fecha_pesaje->format('d/m/Y') . '</td>';
            $html .= '<td>' . $record->peso_estimado . ' kg</td>';
            $html .= '<td>' . $record->estado_sincronizacion . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $pdf = Pdf::loadHTML($html);
        
        return $pdf->download('reporte_animal_' . $animal->arete . '.pdf');
    }

    /**
     * Generar un reporte PDF unificado según el tipo solicitado.
     */
    public function generateReport(Request $request)
    {
        $user = $request->user();
        $type = $request->query('tipo', 'general');
        $fincaId = $request->query('finca_id');

        // Determinar si es invitado o administrador
        $isGuest = $user->invited_farm_id && (!$user->guest_expires_at || now()->lt($user->guest_expires_at));

        if ($isGuest) {
            $fincaId = $user->invited_farm_id;
        }

        $fincaNombre = 'Todas las Fincas';
        if ($fincaId) {
            if ($isGuest) {
                $finca = \App\Models\Farm::findOrFail($fincaId);
            } else {
                $finca = \App\Models\Farm::where('id', $fincaId)->where('user_id', $user->id)->firstOrFail();
            }
            $fincaNombre = $finca->nombre;
        }

        // Construir consulta base de animales
        $queryAnimales = Animal::query();

        if ($isGuest) {
            $queryAnimales->where('farm_id', $user->invited_farm_id);
        } else {
            $queryAnimales->whereHas('farm', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($fincaId && !$isGuest) {
            $queryAnimales->where('farm_id', $fincaId);
        }

        $animales = $queryAnimales->with('farm', 'weightRecords')->get();

        // Estilos CSS unificados
        $css = '
        <style>
            body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #2B2D2F; line-height: 1.5; padding: 20px; }
            .header-container { display: flex; justify-content: space-between; border-bottom: 3px solid #656D4A; padding-bottom: 15px; margin-bottom: 25px; }
            .title { color: #656D4A; font-size: 26px; font-weight: bold; margin: 0; }
            .subtitle { color: #8B8E83; font-size: 14px; margin: 5px 0 0 0; }
            .meta-section { background-color: #F4F6F0; border-radius: 8px; padding: 15px; margin-bottom: 30px; }
            .meta-grid { width: 100%; border-collapse: collapse; }
            .meta-grid td { padding: 6px 12px; font-size: 13px; }
            .meta-label { font-weight: bold; color: #414833; }
            .table-title { color: #414833; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
            .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .data-table th { background-color: #656D4A; color: white; padding: 12px 10px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
            .data-table td { padding: 12px 10px; border-bottom: 1px solid #E3E5D7; font-size: 13px; }
            .data-table tr:nth-child(even) { background-color: #F9FAF7; }
            .badge { background-color: #A3B19B; color: #2B2D2F; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
            .badge-success { background-color: #D8E2DC; color: #4F5D54; }
            .footer { text-align: center; font-size: 10px; color: #8B8E83; margin-top: 50px; border-top: 1px solid #E3E5D7; padding-top: 15px; }
        </style>';

        $html = '<html><head>' . $css . '</head><body>';

        if ($type === 'pesajes') {
            // Obtener todos los registros de pesaje
            $queryPesajes = \App\Models\WeightRecord::query();

            if ($isGuest) {
                $queryPesajes->whereHas('animal', function ($q) use ($user) {
                    $q->where('farm_id', $user->invited_farm_id);
                });
            } else {
                $queryPesajes->whereHas('animal.farm', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            if ($fincaId && !$isGuest) {
                $queryPesajes->whereHas('animal', function ($q) use ($fincaId) {
                    $q->where('farm_id', $fincaId);
                });
            }

            $pesajes = $queryPesajes->with(['animal.farm'])->orderBy('fecha_pesaje', 'desc')->get();

            $html .= '
            <div class="header-container">
                <div>
                    <h1 class="title">BovWeight CR</h1>
                    <h3 class="subtitle">Bitácora de Pesaje e Inteligencia Artificial</h3>
                </div>
            </div>
            
            <div class="meta-section">
                <table class="meta-grid">
                    <tr>
                        <td class="meta-label">Tipo de Reporte:</td>
                        <td>Historial y Bitácora de Pesajes</td>
                        <td class="meta-label">Finca:</td>
                        <td>' . $fincaNombre . '</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Fecha de Generación:</td>
                        <td>' . now()->format('d/m/Y H:i:s') . '</td>
                        <td class="meta-label">Total Pesajes Registrados:</td>
                        <td>' . $pesajes->count() . '</td>
                    </tr>
                </table>
            </div>

            <h2 class="table-title">Registros Históricos</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre Animal</th>
                        <th>Arete</th>
                        <th>Raza</th>
                        <th>Finca</th>
                        <th>Peso Estimado</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($pesajes as $p) {
                $html .= '
                    <tr>
                        <td>' . $p->fecha_pesaje->format('d/m/Y') . '</td>
                        <td>' . $p->animal->nombre . '</td>
                        <td>' . $p->animal->arete . '</td>
                        <td>' . $p->animal->raza . '</td>
                        <td>' . $p->animal->farm->nombre . '</td>
                        <td><strong>' . round($p->peso_estimado) . ' kg</strong></td>
                        <td><span class="badge">' . (strpos($p->notas, 'YOLOv8') !== false ? 'Estimado IA' : 'Manual') . '</span></td>
                    </tr>';
            }

            $html .= '
                </tbody>
            </table>';

        } else {
            // Reporte General o Por Finca (Livestock inventory)
            $html .= '
            <div class="header-container">
                <div>
                    <h1 class="title">BovWeight CR</h1>
                    <h3 class="subtitle">Inventario General y Métricas de Hato</h3>
                </div>
            </div>
            
            <div class="meta-section">
                <table class="meta-grid">
                    <tr>
                        <td class="meta-label">Tipo de Reporte:</td>
                        <td>' . ($type === 'finca' ? 'Detallado por Finca' : 'Inventario General de Hato') . '</td>
                        <td class="meta-label">Finca:</td>
                        <td>' . $fincaNombre . '</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Fecha de Generación:</td>
                        <td>' . now()->format('d/m/Y H:i:s') . '</td>
                        <td class="meta-label">Total Cabezas Activas:</td>
                        <td>' . $animales->count() . '</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Peso Promedio Actual:</td>
                        <td><strong>' . ($animales->count() > 0 ? round($animales->avg('peso_actual'), 1) : 0) . ' kg</strong></td>
                        <td class="meta-label">Ganado Brahman:</td>
                        <td>' . $animales->where('raza', 'Brahman')->count() . ' cabezas</td>
                    </tr>
                </table>
            </div>

            <h2 class="table-title">Ganado Registrado</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Arete</th>
                        <th>Raza</th>
                        <th>Género</th>
                        <th>Finca</th>
                        <th>Peso Actual</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($animales as $animal) {
                $html .= '
                    <tr>
                        <td>' . $animal->nombre . '</td>
                        <td>' . $animal->arete . '</td>
                        <td>' . $animal->raza . '</td>
                        <td>' . $animal->genero . '</td>
                        <td>' . $animal->farm->nombre . '</td>
                        <td><strong>' . ($animal->peso_actual ? round($animal->peso_actual) . ' kg' : 'Sin pesar') . '</strong></td>
                        <td><span class="badge badge-success">' . ($animal->peso_actual ? 'Medido' : 'Pendiente') . '</span></td>
                    </tr>';
            }

            $html .= '
                </tbody>
            </table>';
        }

        $html .= '
            <div class="footer">
                BovWeight CR - Impulsando la Ganadería de Precision.
            </div>
        </body></html>';

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('reporte_' . $type . '_' . now()->format('Ymd_His') . '.pdf');
    }
}
