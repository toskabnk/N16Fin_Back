<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\CenterSalary;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterSalaryController extends ResponseController
{
    use ValidateRequest;

    /**
     * Listar todos los salarios, con filtros opcionales.
     */
    public function viewAll(Request $request)
    {
        $rules = [
            'center_id' => 'sometimes|string',
            'year' => 'sometimes|string',
        ];

        $data = $this->validateData($request, $rules);
        if ($data instanceof JsonResponse) return $data;

        $query = CenterSalary::query();

        if (isset($data['center_id'])) {
            $query->where('center_id', $data['center_id']);
        }
        if (isset($data['year'])) {
            $query->where('year', $data['year']);
        }

        $salaries = $query->orderBy('year', 'desc')->get();

        return $this->respondSuccess($salaries);
    }

    /**
     * Mostrar salarios de un centro por ID y year.
     */
    public function viewByCenterAndYear(Request $request)
    {
        $centerId = $request->query('center_id');
        $year = $request->query('year');
        $salary = CenterSalary::where('center_id', $centerId)->where('year', $year)->first();

        if (!$salary) {
            return $this->respondNotFound('Salary data not found for this center and year');
            //return $this->respondSuccess(null);
        }
        return $this->respondSuccess($salary);
    }


    /**
     * Crear un registro de salarios para un centro.
     */
    public function create(Request $request)
    {

        $rules = [
            'center_id' => 'required|exists:centers,_id',
            'year' => 'required|string',
            'conceptos' => 'required|array|min:1',
            'conceptos.*.id' => 'required|string',
            'conceptos.*.nombre' => 'required|string',
            'conceptos.*.tipo' => 'required|in:editable,calculado',
            'conceptos.*.mensual' => 'required|array',
            'conceptos.*.mensual.*.presup' => 'nullable|numeric',
            'conceptos.*.mensual.*.real' => 'nullable|numeric',
            'conceptos.*.mensual.*.desv' => 'nullable|numeric',
            'conceptos.*.resumenes' => 'nullable|array',
        ];

        $data = $this->validateData($request, $rules);
        if ($data instanceof JsonResponse) return $data;

        // Check if the center exists
        $center = Center::find($data['center_id']);
        if (!$center) {
            return $this->respondNotFound('Center not found');
        }

        $salary = CenterSalary::create($data);

        return $this->respondSuccess($salary, 201);
    }

    /**
     * Actualizar un registro de salarios.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'year' => 'sometimes|string',
            'conceptos' => 'sometimes|array|min:1',
            'conceptos.*.id' => 'required_with:conceptos|string',
            'conceptos.*.nombre' => 'required_with:conceptos|string',
            'conceptos.*.tipo' => 'required_with:conceptos|in:editable,calculado',
            'conceptos.*.mensual' => 'required_with:conceptos|array',
            'conceptos.*.mensual.*.presup' => 'nullable|numeric',
            'conceptos.*.mensual.*.real' => 'nullable|numeric',
            'conceptos.*.mensual.*.desv' => 'nullable|numeric',
            'conceptos.*.resumenes' => 'nullable|array',
        ];

        $data = $this->validateData($request, $rules);
        if ($data instanceof JsonResponse) return $data;

        $salary = CenterSalary::find($id);
        if (!$salary) {
            return $this->respondNotFound('Salary data not found');
        }

        $salary->update($data);

        return $this->respondSuccess($salary);
    }

    /**
     * Eliminar un registro de salarios.
     */
    public function delete(Request $request, $id)
    {
        $salary = CenterSalary::find($id);
        if (!$salary) {
            return $this->respondNotFound('Salary data not found');
        }

        $salary->delete();

        return $this->respondSuccess('Salary data deleted successfully');
    }
}
