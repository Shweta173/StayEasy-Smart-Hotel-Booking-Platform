<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse;
    
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of all invoices
     */
    public function index(Request $request)
    {
        return $this->invoiceService->index($request);
    }

    /**
     * Display the specified invoice
     */
    public function show(Request $request, $id)
    {
        return $this->invoiceService->show($request, $id);
    }

    /**
     * Send invoice email to guest
     */
    public function sendEmail(Request $request, $id)
    {
        return $this->invoiceService->sendEmail($request, $id);
    }

    /**
     * Void an invoice
     */
    public function void(Request $request, $id)
    {
        return $this->invoiceService->void($request, $id);
    }

    /**
     * Download PDF for an invoice
     */
    public function downloadPdf($id)
    {
        return $this->invoiceService->downloadPdf($id);
    }
}
