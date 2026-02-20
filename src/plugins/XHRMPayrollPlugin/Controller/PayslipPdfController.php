<?php

namespace XHRM\Payroll\Controller;

use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Response;
use XHRM\Payroll\Service\PayslipPdfService;

class PayslipPdfController
{
    /**
     * Render payslip as printable HTML page
     */
    public function handle(Request $request): Response
    {
        $payslipId = $request->attributes->getInt('id');

        try {
            $pdfService = new PayslipPdfService();
            $html = $pdfService->generatePayslipHtml($payslipId);

            $response = new Response();
            $response->setContent($html);
            $response->headers->set('Content-Type', 'text/html');
            return $response;
        } catch (\Exception $e) {
            $response = new Response();
            $response->setContent('Error: ' . $e->getMessage());
            $response->setStatusCode(404);
            return $response;
        }
    }
}
