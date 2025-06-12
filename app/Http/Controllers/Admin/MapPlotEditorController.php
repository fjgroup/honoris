<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Models\City;
use App\Models\Map;
use App\Models\MapPlot;

class MapPlotEditorController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        // Valida los parámetros de la URL para asegurarnos de que son números
        $request->validate([
            'city_id' => 'nullable|integer|exists:cities,id',
            'map_id' => 'nullable|integer|exists:maps,id',
        ]);

        $selectedCityId = (int) $request->input('city_id');
        $selectedMapId = (int) $request->input('map_id');

        // Carga todas las ciudades para el primer selector
        $cities = City::orderBy('name')->get(['id', 'name']);

        // Si hay una ciudad seleccionada, carga sus mapas
        $mapsForCity = $selectedCityId
            ? Map::where('city_id', $selectedCityId)->orderBy('name')->get(['id', 'name'])
            : [];

        // Si hay un mapa seleccionado, carga sus detalles y sus plots
        $selectedMap = null;
        $mapPlots = [];
        if ($selectedMapId) {
            $selectedMap = Map::find($selectedMapId); // Carga el modelo completo del mapa
            $mapPlots = MapPlot::where('map_id', $selectedMapId)->orderBy('plot_identifier')->get();
        }

        return Inertia::render('Admin/Maps/InteractiveMapPlotEditor', [
            // Pasamos todos los datos como props
            'cities' => $cities,
            'mapsForCity' => $mapsForCity,
            'mapPlots' => $mapPlots,
            'selectedMap' => $selectedMap,

            // También pasamos los filtros actuales para que los selectores se inicialicen correctamente
            'filters' => [
                'city_id' => $selectedCityId ?: null,
                'map_id' => $selectedMapId ?: null,
            ]
        ]);
    }
}
