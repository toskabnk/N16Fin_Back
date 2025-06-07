<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OdooService
{
    protected $url;
    protected $db;
    protected $userId;
    protected $apiKey;

    public function __construct()
    {
        $this->url     = config('services.odoo.url');
        $this->db      = config('services.odoo.db');
        $this->userId  = config('services.odoo.user_id');
        $this->apiKey  = config('services.odoo.api_key');
    }

    private function getInvoices(int $limit, string $invoiceType): array
    {
        $payload = [
            'jsonrpc' => '2.0',
            'method'  => 'call',
            'id'      => 1,
            'params'  => [
                'service' => 'object',
                'method'  => 'execute_kw',
                'args'    => [
                    $this->db,
                    $this->userId,
                    $this->apiKey,
                    'account.move',
                    'search_read',
                    [
                        [['move_type', '=', $invoiceType]]
                    ],
                    [
                        'fields' => ['id','name','partner_id','invoice_date','amount_total','state'],
                        'limit'  => $limit,
                    ],
                ],
            ],
        ];

        $response = Http::post("{$this->url}/jsonrpc", $payload);

        return $response->json('result', []);
    }

    public function getIncomingInvoices(int $limit = 0): array{
        return $this->getInvoices($limit, 'in_invoice');
    }

    public function getOutgoingInvoices(int $limit = 0): array{
        return $this->getInvoices($limit, 'out_invoice');
    }

    public function getInvoiceById($id): array
    {
        $payload = [
            'jsonrpc' => '2.0',
            'method'  => 'call',
            'id'      => 1,
            'params'  => [
                'service' => 'object',
                'method'  => 'execute_kw',
                'args'    => [
                    $this->db,
                    $this->userId,
                    $this->apiKey,
                    'account.move',
                    'search_read',
                    [
                        [['id', '=', $id]]
                    ],
                    [
                        'fields' => ['id','name','partner_id','invoice_date','amount_total','state'],
                    ],
                ],
            ],
        ];

        $response = Http::post("{$this->url}/jsonrpc", $payload);

        return $response->json('result', []);
    }
}
