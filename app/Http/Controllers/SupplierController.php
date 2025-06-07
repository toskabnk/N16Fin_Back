<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends ResponseController
{
    use ValidateRequest;

    public function viewAll(Request $request)
    {
        //Validation rules
        $rules = [
            'supplier_id' => 'sometimes|string',
            'center_id' => 'sometimes|string',
            'type' => 'sometimes|string',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Get the filters
        $supplierId = $data['supplier_id'] ?? null;
        $centerId = $data['center_id'] ?? null;
        $type = $data['type'] ?? null;

        //Build the filters
        $filters = [];
        if ($supplierId) {
            $filters[] = ['supplier_id', '=', $supplierId];
        }
        if ($centerId) {
            $filters[] = ['centers', '=', $centerId];
        }
        if ($type) {
            $filters[] = ['type', '=', $type];
        }

        //Get the invoices with filters
        $invoices = Supplier::where($filters)->get();

        return $this->respondSuccess($invoices);
    }

    public function view($id)
    {
        //Find the invoice by ID
        $supplier = Supplier::find($id);

        //If not found, return error
        if (!$supplier) {
            return $this->respondNotFound('Supplier not found');
        }

        return $this->respondSuccess($supplier);
    }
    public function create(Request $request)
    {
        //Validation rules
        $rules = [
            'name' => 'required|string',
            'type' => 'required|string',
            'center_id' => 'sometimes|string',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Create the supplier
        $supplier = Supplier::create($data);

        return $this->respondSuccess($supplier, 201);
    }

    public function update(Request $request, $id)
    {
        //Find the supplier by ID
        $supplier = Supplier::find($id);

        //If not found, return error
        if (!$supplier) {
            return $this->respondNotFound('Supplier not found');
        }

        //Validation rules
        $rules = [
            'name' => 'sometimes|string',
            'type' => 'sometimes|string',
            'center_id' => 'sometimes|string',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Update the supplier
        $supplier->update($data);

        return $this->respondSuccess($supplier);
    }

    public function delete($id)
    {
        //Find the supplier by ID
        $supplier = Supplier::find($id);

        //If not found, return error
        if (!$supplier) {
            return $this->respondNotFound('Supplier not found');
        }

        //Check if the supplier has any invoices
        if ($supplier->invoices()->count() > 0) {
            return $this->respondBadRequest('Cannot delete supplier with existing invoices');
        }

        //Delete the supplier
        $supplier->delete();

        return $this->respondSuccess('Supplier deleted successfully');
    }
}
