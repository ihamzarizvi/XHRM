<?php

namespace XHRM\Payroll\Service;

use DateTime;
use XHRM\Core\Service\EmailService;
use XHRM\Entity\Payslip;
use XHRM\Entity\PayrollRun;
use XHRM\Payroll\Dao\PayrollDao;

class PayslipPdfService
{
    private ?PayrollDao $payrollDao = null;

    public function getPayrollDao(): PayrollDao
    {
        if ($this->payrollDao === null) {
            $this->payrollDao = new PayrollDao();
        }
        return $this->payrollDao;
    }

    /**
     * Generate HTML content for a payslip (printable/PDF-ready)
     */
    public function generatePayslipHtml(int $payslipId): string
    {
        $dao = $this->getPayrollDao();
        $payslip = $dao->getPayslipById($payslipId);
        if (!$payslip) {
            throw new \RuntimeException('Payslip not found');
        }

        $items = $dao->getPayslipItems($payslipId);
        $earnings = [];
        $deductions = [];

        foreach ($items as $item) {
            if ($item->getType() === 'earning') {
                $earnings[] = $item;
            } else {
                $deductions[] = $item;
            }
        }

        $employee = $payslip->getEmployee();
        $run = $payslip->getPayrollRun();

        // Get company name from config
        $companyName = 'XHRM';

        return $this->renderPayslipTemplate([
            'companyName' => $companyName,
            'payslip' => $payslip,
            'employee' => $employee,
            'run' => $run,
            'earnings' => $earnings,
            'deductions' => $deductions,
        ]);
    }

    /**
     * Render the payslip HTML template
     */
    private function renderPayslipTemplate(array $data): string
    {
        $payslip = $data['payslip'];
        $employee = $data['employee'];
        $run = $data['run'];
        $earnings = $data['earnings'];
        $deductions = $data['deductions'];
        $companyName = $data['companyName'];

        $earningsHtml = '';
        foreach ($earnings as $item) {
            $earningsHtml .= '<tr>
                <td>' . htmlspecialchars($item->getName()) . '</td>
                <td class="amount">' . $this->formatCurrency($item->getAmount(), $payslip->getCurrencyId()) . '</td>
            </tr>';
        }

        // Add overtime if present
        if ((float) $payslip->getOvertimeAmount() > 0) {
            $earningsHtml .= '<tr>
                <td>Overtime (' . $payslip->getOvertimeHours() . ' hrs)</td>
                <td class="amount">' . $this->formatCurrency($payslip->getOvertimeAmount(), $payslip->getCurrencyId()) . '</td>
            </tr>';
        }

        $deductionsHtml = '';
        foreach ($deductions as $item) {
            $deductionsHtml .= '<tr>
                <td>' . htmlspecialchars($item->getName()) . '</td>
                <td class="amount">' . $this->formatCurrency($item->getAmount(), $payslip->getCurrencyId()) . '</td>
            </tr>';
        }

        // Add tax if present
        if ((float) $payslip->getTaxAmount() > 0) {
            $deductionsHtml .= '<tr>
                <td>Income Tax</td>
                <td class="amount">' . $this->formatCurrency($payslip->getTaxAmount(), $payslip->getCurrencyId()) . '</td>
            </tr>';
        }

        $periodStart = $run ? $run->getPeriodStart()->format('d M Y') : '';
        $periodEnd = $run ? $run->getPeriodEnd()->format('d M Y') : '';
        $generatedAt = $run ? $run->getGeneratedAt()->format('d M Y') : (new DateTime())->format('d M Y');
        $empName = $employee ? $employee->getFirstName() . ' ' . $employee->getLastName() : '';
        $empId = $employee ? $employee->getEmployeeId() : '';
        $currency = $payslip->getCurrencyId();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {$empName} - {$periodStart} to {$periodEnd}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            background: #fff;
            font-size: 13px;
            line-height: 1.5;
        }
        .payslip {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: 800;
            color: #1e40af;
            letter-spacing: -0.5px;
        }
        .payslip-title {
            font-size: 18px;
            color: #64748b;
            font-weight: 600;
        }
        .payslip-period {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* Employee Info */
        .emp-info {
            display: flex;
            gap: 40px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .emp-info-group {
            flex: 1;
        }
        .info-label {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 2px;
        }

        /* Attendance Summary */
        .attendance-summary {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .att-box {
            flex: 1;
            text-align: center;
            padding: 10px 8px;
            background: #f1f5f9;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .att-box-label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 600;
        }
        .att-box-value {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 4px;
        }
        .att-good { color: #16a34a; }
        .att-bad { color: #dc2626; }
        .att-warn { color: #f59e0b; }

        /* Tables */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            padding: 8px 12px;
            margin-bottom: 0;
            border-radius: 6px 6px 0 0;
        }
        .section-earn { background: #dcfce7; color: #166534; }
        .section-deduct { background: #fee2e2; color: #991b1b; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .amount {
            text-align: right;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }
        .total-row {
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #e2e8f0;
        }
        .total-row td {
            padding: 10px 12px;
        }

        .tables-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .table-section {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        /* Net Pay */
        .net-pay {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 10px;
            color: #fff;
            margin-bottom: 20px;
        }
        .net-pay-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }
        .net-pay-amount {
            font-size: 32px;
            font-weight: 800;
            margin-top: 5px;
            letter-spacing: -0.5px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 11px;
        }

        /* Print styles */
        @media print {
            body { background: #fff; }
            .payslip { padding: 15px; }
            .net-pay {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .att-good, .att-bad, .att-warn {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="payslip">
        <!-- Header -->
        <div class="header">
            <div>
                <div class="company-name">{$companyName}</div>
                <div class="payslip-period">Generated on {$generatedAt}</div>
            </div>
            <div style="text-align: right;">
                <div class="payslip-title">PAYSLIP</div>
                <div class="payslip-period">{$periodStart} — {$periodEnd}</div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="emp-info">
            <div class="emp-info-group">
                <div class="info-label">Employee Name</div>
                <div class="info-value">{$empName}</div>
            </div>
            <div class="emp-info-group">
                <div class="info-label">Employee ID</div>
                <div class="info-value">{$empId}</div>
            </div>
            <div class="emp-info-group">
                <div class="info-label">Pay Period</div>
                <div class="info-value">{$payslip->getPayPeriodType()}</div>
            </div>
            <div class="emp-info-group">
                <div class="info-label">Currency</div>
                <div class="info-value">{$currency}</div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="attendance-summary">
            <div class="att-box">
                <div class="att-box-label">Working Days</div>
                <div class="att-box-value">{$payslip->getTotalWorkingDays()}</div>
            </div>
            <div class="att-box">
                <div class="att-box-label">Present</div>
                <div class="att-box-value att-good">{$payslip->getDaysPresent()}</div>
            </div>
            <div class="att-box">
                <div class="att-box-label">Absent</div>
                <div class="att-box-value att-bad">{$payslip->getDaysAbsent()}</div>
            </div>
            <div class="att-box">
                <div class="att-box-label">Leave</div>
                <div class="att-box-value">{$payslip->getDaysLeave()}</div>
            </div>
            <div class="att-box">
                <div class="att-box-label">Late</div>
                <div class="att-box-value att-warn">{$payslip->getLateCount()}</div>
            </div>
            <div class="att-box">
                <div class="att-box-label">OT Hours</div>
                <div class="att-box-value">{$payslip->getOvertimeHours()}</div>
            </div>
        </div>

        <!-- Earnings & Deductions -->
        <div class="tables-container">
            <div class="table-section">
                <div class="section-title section-earn">Earnings</div>
                <table>
                    {$earningsHtml}
                    <tr class="total-row">
                        <td>Gross Salary</td>
                        <td class="amount">{$this->formatCurrency($payslip->getGrossSalary(), $currency)}</td>
                    </tr>
                </table>
            </div>
            <div class="table-section">
                <div class="section-title section-deduct">Deductions</div>
                <table>
                    {$deductionsHtml}
                    <tr class="total-row">
                        <td>Total Deductions</td>
                        <td class="amount">{$this->formatCurrency($payslip->getTotalDeductions(), $currency)}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Net Pay -->
        <div class="net-pay">
            <div class="net-pay-label">Net Pay</div>
            <div class="net-pay-amount">{$this->formatCurrency($payslip->getNetSalary(), $currency)}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            This is a system-generated payslip and does not require a signature.<br>
            {$companyName} &bull; Payroll System &bull; {$generatedAt}
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Format currency amount
     */
    private function formatCurrency(string $amount, string $currency = 'PKR'): string
    {
        return $currency . ' ' . number_format((float) $amount, 2);
    }
}
