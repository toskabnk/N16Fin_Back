<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Invoice;
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
            'type' => 'sometimes|string',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Get the filters
        $type = $data['type'] ?? null;

        //Build the filters
        $filters = [];
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
            'center' => 'sometimes|array|nullable',
            'centers.*' => 'string',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $centersBD = Center::all();
        //If centers are provided, validate and update them
        if (isset($data['centers'])) {
            //Validate the centers
            if(count($data['centers']) == 0) {
                return $data['centers'] = null;
            }
            foreach ($data['centers'] as $centerId) {
                if (!$centersBD->contains('id', $centerId)) {
                    return $this->respondUnprocessableEntity("Center with id $centerId does not exist");
                }
            }
            //Save the centers as an array
            $data['centers'] = array_values($data['centers']); // Reindex the array to avoid gaps in the keys
        } else {
            //If centers are not provided, set them to null
            $data['centers'] = null;
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
            'centers' => 'sometimes|array|nullable',
            'centers.*' => 'string',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $centersBD = Center::all();
        //If centers are provided, validate and update them
        if (isset($data['centers'])) {
            //Validate the centers
            if(count($data['centers']) == 0) {
                return $data['centers'] = null;
            }
            foreach ($data['centers'] as $centerId) {
                if (!$centersBD->contains('id', $centerId)) {
                    return $this->respondUnprocessableEntity("Center with id $centerId does not exist");
                }
            }
            //Save the centers as an array
            $data['centers'] = array_values($data['centers']); // Reindex the array to avoid gaps in the keys
        } else {
            //If centers are not provided, set them to null
            $data['centers'] = null;
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

    public function updateCentersOnInvoices(Request $request)
    {
        //Validation rules
        $rules = [
            'supplier_id' => 'required',
            'manual' => 'sometimes|boolean',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Find the supplier by ID
        $supplier = Supplier::find($data['supplier_id']);

        //If not found, return error
        if (!$supplier) {
            return $this->respondNotFound('Supplier not found');
        }

        //Array to string centers
        $centers = $supplier->centers;

        //If manual is not provided, set it to false
        $manual = $data['manual'] ?? false;

        // Construir la query para las facturas
        $query = Invoice::where('supplier_id', $data['supplier_id']);

        // Si no se deben incluir manuales, filtrarlos
        if (!$manual) {
            $query->where('manual', '!=', true);
        }

        // Ejecutar updateMany
        $query->update([
            'centers' => json_encode($centers)
        ]);

        return $this->respondSuccess($supplier);
    }
}
