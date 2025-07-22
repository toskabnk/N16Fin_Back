<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\InvoiceSyncLog;
use App\Services\OdooService;
use Carbon\Carbon;

class SyncOdooInvoices extends Command
{
    protected $signature = 'sync:odoo-invoices';
    protected $description = 'Sincroniza facturas desde Odoo: añade posted, elimina cancel';
    protected OdooService $odoo;

    public function __construct(OdooService $odoo)
    {
        parent::__construct();
        $this->odoo = $odoo;
    }

    public function handle()
    {
        $this->info('Iniciando sincronización de facturas con Odoo...');

        $odooInvoices = $this->odoo->getIncomingInvoices();
        $this->info("Facturas obtenidas de Odoo: " . count($odooInvoices));
        $existingInvoices = Invoice::all()->keyBy('odoo_invoice_id');
        $existingSuppliers = Supplier::all()->keyBy('odoo_supplier_id');

        $added = [];
        $removed = [];
        $addedSuppliers = [];

        foreach ($odooInvoices as $inv) {
            $odooId = $inv['id'];
            $state = $inv['state'];
            $existing = $existingInvoices->get($odooId);

            if ($state === 'posted') {
                if (!$existing) {
                    [$partnerId, $partnerName] = $inv['partner_id'];

                    $supplier = $existingSuppliers->get($partnerId);
                    if (!$supplier) {
                        $supplier = Supplier::create([
                            'name' => $partnerName,
                            'odoo_supplier_id' => $partnerId,
                            'type' => null,
                            'centers' => null,
                            'only_add_vat' => false,
                        ]);
                        $existingSuppliers->put($partnerId, $supplier);
                        if (!in_array($supplier->_id, $addedSuppliers)) {
                            $addedSuppliers[] = $supplier->_id;
                        }
                    }

                    // Si el proveedor tiene 'only_add_vat' en true, se añade el IVA al total
                    // Si es false, se usa el total 
                    $amount = $supplier->only_add_vat
                        ? round($inv['amount_untaxed'] * env('VAT_NUMBER', 1.21), 2)
                        : $inv['amount_total'];

                    $invoice = Invoice::create([
                        'odoo_invoice_id' => $odooId,
                        'reference' => $inv['name'],
                        'invoice_date' => $inv['invoice_date'],
                        'month' => Carbon::parse($inv['invoice_date'])->format('m'),
                        'amount_total' => $amount,
                        'state' => $state,
                        'manual' => false,
                        'centers' => $supplier->centers ?? null,
                        'business_line_id' => null,
                        'share_type_id' => '6817dc38510684896685b888',
                        'supplier_id' => $supplier->_id,
                        'type' => 'in',
                    ]);

                    $added[] = $invoice->_id;
                }
            }

            if ($state === 'cancel' && $existing) {
                $removed[] = $existing->_id;
                $existing->delete();
            }
        }

        InvoiceSyncLog::create([
            'synced_at' => now(),
            'added_invoice_ids' => $added,
            'removed_invoice_ids' => $removed,
            'added_supplier_ids' => $addedSuppliers,
        ]);

        $this->info("Sincronización completada: Añadidas: " . count($added) . ", Eliminadas: " . count($removed) . "." . PHP_EOL . "Suppliers añadidos: " . count($addedSuppliers) . ".");
    }
}
