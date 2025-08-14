<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\CenterCost;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterCostController extends ResponseController
{
    use ValidateRequest;

    /**
     * Listar todos los costos, con filtros opcionales.
     */
    public function viewAll(Request $request)
    {
        $rules = [
            'center_id' => 'sometimes|string',
            'year'      => 'sometimes|string',
        ];

        $data = $this->validateData($request, $rules);
        if ($data instanceof JsonResponse) return $data;

        $query = CenterCost::query();

        if (isset($data['center_id'])) {
            $query->where('center_id', $data['center_id']);
        }
        if (isset($data['year'])) {
            $query->where('year', $data['year']);
        }

        $costs = $query->orderBy('year', 'desc')->get();

        return response()->json($costs, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Mostrar costos de un centro por ID y year.
     */
    public function viewByCenterAndYear(Request $request)
    {
        $centerId = $request->query('center_id');
        $year     = $request->query('year');

        $cost = CenterCost::where('center_id', $centerId)
            ->where('year', $year)
            ->first();

        if (!$cost) {
            return $this->respondNotFound('Cost data not found for this center and year');
        }

        return response()->json($cost, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Crear un registro de costos para un centro.
     */
    public function create(Request $request)
    {
        $rules = [
            'center_id'                     => 'required|exists:centers,_id',
            'year'                          => 'required|string',
            'conceptos'                     => 'required|array|min:1',
            'conceptos.*.id'                => 'required|string',
            'conceptos.*.nombre'            => 'required|string',
            'conceptos.*.tipo'              => 'required|in:editable,calculado',
            'conceptos.*.mensual'           => 'required|array',
            'conceptos.*.mensual.*.presup'  => 'nullable|numeric',
            'conceptos.*.mensual.*.real'    => 'nullable|numeric',
            'conceptos.*.mensual.*.desv'    => 'nullable|numeric',
            'conceptos.*.resumenes'         => 'nullable|array',
        ];

        $data = $this->validateData($request, $rules);
        if ($data instanceof JsonResponse) return $data;

        // Normaliza tipos y claves de mes
        $data['conceptos'] = $this->normalizeConceptos($data['conceptos']);

        // Verifica centro
        $center = Center::find($data['center_id']);
        if (!$center) {
            return $this->respondNotFound('Center not found');
        }

        $cost = CenterCost::create($data);

        return response()->json($cost, 201, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Actualizar un registro de costos.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'year'                          => 'sometimes|string',
            'conceptos'                     => 'sometimes|array|min:1',
            'conceptos.*.id'                => 'required_with:conceptos|string',
            'conceptos.*.nombre'            => 'required_with:conceptos|string',
            'conceptos.*.tipo'              => 'required_with:conceptos|in:editable,calculado',
            'conceptos.*.mensual'           => 'required_with:conceptos|array',
            'conceptos.*.mensual.*.presup'  => 'nullable|numeric',
            'conceptos.*.mensual.*.real'    => 'nullable|numeric',
            'conceptos.*.mensual.*.desv'    => 'nullable|numeric',
            'conceptos.*.resumenes'         => 'nullable|array',
        ];

        $data = $this->validateData($request, $rules);
        if ($data instanceof JsonResponse) return $data;

        if (isset($data['conceptos'])) {
            $data['conceptos'] = $this->normalizeConceptos($data['conceptos']);
        }

        $cost = CenterCost::find($id);
        if (!$cost) {
            return $this->respondNotFound('Cost data not found');
        }

        $cost->update($data);

        return response()->json($cost, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Eliminar un registro de costos.
     */
    public function delete(Request $request, $id)
    {
        $cost = CenterCost::find($id);
        if (!$cost) {
            return $this->respondNotFound('Cost data not found');
        }

        $cost->delete();

        return $this->respondSuccess('Cost data deleted successfully');
    }

    /**
     * Helpers de normalización

     */

    private function normalizeConceptos($conceptos): array
    {
        if (is_string($conceptos)) {
            $conceptos = json_decode($conceptos, true) ?: [];
        }
        if (!is_array($conceptos)) return [];

        foreach ($conceptos as &$c) {
            if (isset($c['mensual']) && is_array($c['mensual'])) {
                $c['mensual'] = $this->normalizeMensualKeys($c['mensual']);
            }
        }
        unset($c);

        return $conceptos;
    }

    /**
     * Normaliza las claves de 'mensual' a '01'..'12' y garantiza presencia de presup/real/desv. SE SUPONE QUE ORDENA
     */
    private function normalizeMensualKeys(array $mensual): array
    {
        $order = ['09', '10', '11', '12', '01', '02', '03', '04', '05', '06', '07', '08'];

        $pick = function ($arr, $k) {
            if (array_key_exists($k, $arr)) return $arr[$k];
            $noZero = (string) intval($k);
            return $arr[$noZero] ?? null;
        };

        $out = [];
        foreach ($order as $kk) {
            $v = $pick($mensual, $kk) ?? [];
            $out[$kk] = [
                'presup' => $v['presup'] ?? null,
                'real'   => $v['real']   ?? null,
                'desv'   => $v['desv']   ?? null,
            ];
        }
        return $out;
    }
}
