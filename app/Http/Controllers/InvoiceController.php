<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Services\OdooService;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceController extends ResponseController
{
    use ValidateRequest;

    protected $odoo;

    public function __construct(OdooService $odoo)
    {
        $this->odoo = $odoo;
    }

    /**
     * Get the list of invoices with optional filters.
     */
    public function viewAll(Request $request)
    {
        //Validation rules
        $rules = [
            'supplier_id' => 'sometimes|string',
            'center_id' => 'sometimes|string',
            'business_line_id' => 'sometimes|string',
            'share_type_id' => 'sometimes|string',
            'type' => 'sometimes|string|in:in,out',
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
        $businessLineId = $data['business_line_id'] ?? null;
        $shareTypeId = $data['share_type_id'] ?? null;
        $type = $data['type'] ?? 'in';

        //Build the filters
        $filters = [];
        if ($supplierId) {
            $filters[] = ['supplier_id', '=', $supplierId];
        }
        if ($centerId) {
            $filters[] = ['centers', '=', $centerId];
        }
        if ($businessLineId) {
            $filters[] = ['business_line_id', '=', $businessLineId];
        }
        if ($shareTypeId) {
            $filters[] = ['share_type_id', '=', $shareTypeId];
        }
        if ($type) {
            $filters[] = ['type', '=', $type];
        }

        //Get the invoices
        $invoices = Invoice::with(['supplier', 'businessLine', 'shareType'])
            ->where($filters)
            ->orderBy('invoice_date', 'desc')
            ->get();

        //Return the data
        return $this->respondSuccess($invoices);
    }

    /**
     * Get the invoice by id.
     */
    public function viewById(Request $request, $id)
    {
        //Get the invoice
        $invoice = Invoice::with(['supplier', 'businessLine', 'shareType'])
            ->where('id', $id)
            ->first();

        //Check if the invoice exists
        if (!$invoice) {
            return $this->respondNotFound('Invoice not found');
        }

        //Return the data
        return $this->respondSuccess($invoice);
    }

    /**
     * Get the list of invoices from Odoo.
     */
    public function viewOdooInvoices(Request $request)
    {
        $invoices = $this->odoo->getIncomingInvoices();

        //Get all invoices
        $dbInvoices = Invoice::all();

        //Check if the invoices from Odoo are in the database, stop when 20 invoices are found in the database in a row
        $sum = 0;
        foreach ($invoices as &$invoice) {

            //Check if the invoice exists in the database
            $existingInvoice = $dbInvoices->firstWhere('odoo_invoice_id', $invoice['id']);

            if (!$existingInvoice) {
                //If the invoice dont exist in the daabase, add field not_added
                $invoice['not_added'] = true;
                //Reset the sum
                $sum = 0;
            } else {
                //If the invoice exist in the database, add field not_added
                $invoice['not_added'] = false;
                //Sum +1
                $sum++;
                //If the sum is 20, break the loop
                if ($sum >= 20) {
                    break;
                }
            }
        }

        return $this->respondSuccess($invoices);
    }

    /**
     * Add the all the new invoices from Odoo to the database.
     */
    public function addAllNewOdooInvoices(Request $request)
    {
        //Validation rules
        $rules = [
            'limit' => 'sometimes|numeric',
            'type' => 'sometimes|string|in:in,out',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        if (isset($data['limit'])) {
            $limit = $data['limit'];
        } else {
            $limit = 0;
        }

        if (isset($data['type'])) {
            $type = $data['type'];
        } else {
            $type = 'in';
        }

        //Get all invoices
        $invoicesDB = Invoice::all();
        //Get all suppliers
        $suppliersDB = Supplier::all();

        //Num new invoices created
        $numNewInvoices = 0;
        //Num new suppliers created
        $numNewSuppliers = 0;

        //Get the invoices from Odoo
        if($type == 'in') {
            $invoices = $this->odoo->getIncomingInvoices($limit);
        } else {
            $invoices = $this->odoo->getOutgoingInvoices($limit);
        }

        foreach ($invoices as $inv) {
            //Check if the invoice is posted state
            if($inv['state'] == 'posted') {

                // partner_id = [id, name]
                [$partnerId, $partnerName] = $inv['partner_id'];

                //Check if the supplier exists in the database
                $supplier = $suppliersDB->firstWhere('odoo_supplier_id', $partnerId);
                if ($supplier === null) {
                    //If the supplier does not exist, create it
                    $data = [
                        'name' => $partnerName,
                        'odoo_supplier_id' => $partnerId,
                        'type' => null,
                        'center_id' => null,
                    ];
                    $supplier = Supplier::create($data);

                    //Increment the num new suppliers
                    $numNewSuppliers++;

                    //Add the new supplier to the suppliers collection
                    $suppliersDB->push($supplier);
                }

                $invoice = $invoicesDB->firstWhere('odoo_invoice_id', $inv['id']);
                if ($invoice === null) {
                    //If the invoice does not exist, create it
                    $data = [
                        'odoo_invoice_id' => $inv['id'],
                        'reference' => $inv['name'],
                        'invoice_date' => $inv['invoice_date'],
                        'month' => Carbon::parse($inv['invoice_date'])->format('m'), // Resultado: "01"
                        'amount_total' => $inv['amount_total'],
                        'state' => $inv['state'],
                        'manual' => false,
                        'centers' => null,
                        'business_line_id' => null,
                        'share_type_id' => '6817dc38510684896685b888',
                        'supplier_id' => $supplier->id,
                        'type' => $type,
                    ];
                    Invoice::create($data);

                    //Increment the num new invoices
                    $numNewInvoices++;

                    //Add the new invoice to the invoices collection
                    $invoicesDB->push(new Invoice($data));
                } else {
                    //Remove the invoice from the collection for better performance
                    $invoicesDB = $invoicesDB->reject(function ($item) use ($inv) {
                        return $item->odoo_invoice_id == $inv['id'];
                    });
                }
            }
        }

        //Return the response
        $reponse = [
            'new_invoices' => $numNewInvoices,
            'new_suppliers' => $numNewSuppliers,
        ];

        //If no new invoices or suppliers were created, return a message
        if ($numNewInvoices == 0) {
            return $this->respondSuccess('No new invoices were created');
        }

        return $this->respondSuccess($reponse, 201);
    }

    /**
     * Update the invoice by id.
     */
    public function update(Request $request, $id)
    {
        //Validation rules
        $rules = [
            'reference' => 'sometimes|string|max:255',
            'invoice_date' => 'sometimes|date',
            'month' => 'sometimes|string',
            'amount_total' => 'sometimes|numeric',
            'manual' => 'sometimes|boolean',
            'share_type_id' => 'sometimes|string',
            'centers' => 'sometimes|array|nullable',
            'centers.*' => 'string',
            'type' => 'sometimes|string|in:in,out',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Get the invoice
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return $this->respondNotFound('Invoice not found');
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

        //Set manul to true
        $data['manual'] = true;

        //Update the invoice
        $invoice->update($data);

        //Return the response
        return $this->respondSuccess($invoice);
    }

    /**
     * Delete the invoice by id.
     */
    public function delete(Request $request, $id)
    {
        //Get the invoice
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return $this->respondNotFound('Invoice not found');
        }

        //Delete the invoice
        $invoice->delete();

        //Return the response
        return $this->respondSuccess('Invoice deleted successfully');
    }

    /**
     * Reset the invoice by id.
     */
    public function resetInvoice(Request $request, $id)
    {
        //Get the invoice
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return $this->respondNotFound('Invoice not found');
        }

        //Search for the invoice in Odoo
        $odooInvoice = $this->odoo->getInvoiceById($invoice->odoo_invoice_id);
        if ($odooInvoice === null) {
            return $this->respondNotFound('Invoice not found in Odoo');
        }

        //Check if the invoice is in posted state
        if ($odooInvoice[0]['state'] != 'posted') {
            return $this->respondUnprocessableEntity('Invoice is not in posted state');
        }

        //Update the invoice with the data from Odoo
        $invoice->state = $odooInvoice[0]['state'];
        $invoice->reference = $odooInvoice[0]['name'];
        $invoice->invoice_date = $odooInvoice[0]['invoice_date'];
        $invoice->month = Carbon::parse($odooInvoice[0]['invoice_date'])->format('m'); // Resultado: "01"
        $invoice->amount_total = $odooInvoice[0]['amount_total'];
        $invoice->manual = false;
        $invoice->centers = null;
        $invoice->business_line_id = null;
        $invoice->supplier_id = $invoice->supplier_id;
        $invoice->share_type_id = '6817dc38510684896685b888';
        $invoice->save();


        //Return the response
        return $this->respondSuccess($invoice);
    }

    public function create(Request $request)
    {
        //Validation rules
        $rules = [
            'odoo_invoice_id' => 'string|max:255|nullable',
            'reference' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'month' => 'required|string',
            'amount_total' => 'required|numeric',
            'manual' => 'required|boolean',
            'centers' => 'sometimes|array',
            'centers.*' => 'string',
            'business_line_id' => 'sometimes|string|nullable',
            'share_type_id' => 'sometimes|string',
            'supplier_id' => 'required|exists:suppliers,id',
            'type' => 'sometimes|string|in:in,out',
        ];

        //Validate the request data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Add type null if not provided
        if (!isset($data['state'])) {
            $data['state'] = null;
        }

        //Set manual to true
        $data['manual'] = true;

        //Create the invoice
        $invoice = Invoice::create($data);

        //Return the response
        return $this->respondSuccess($invoice, 201);
    }
}
