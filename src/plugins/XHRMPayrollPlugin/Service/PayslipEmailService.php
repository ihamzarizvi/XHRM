<?php

namespace XHRM\Payroll\Service;

use XHRM\Core\Service\EmailService;
use XHRM\Entity\Payslip;
use XHRM\Entity\PayrollRun;
use XHRM\Payroll\Dao\PayrollDao;

class PayslipEmailService
{
    private ?PayrollDao $payrollDao = null;
    private ?PayslipPdfService $pdfService = null;

    public function getPayrollDao(): PayrollDao
    {
        if ($this->payrollDao === null) {
            $this->payrollDao = new PayrollDao();
        }
        return $this->payrollDao;
    }

    public function getPdfService(): PayslipPdfService
    {
        if ($this->pdfService === null) {
            $this->pdfService = new PayslipPdfService();
        }
        return $this->pdfService;
    }

    /**
     * Email all payslips for an approved payroll run
     */
    public function emailPayslipsForRun(int $runId): array
    {
        $dao = $this->getPayrollDao();
        $run = $dao->getPayrollRunById($runId);

        if (!$run || $run->getStatus() !== 'approved') {
            throw new \RuntimeException('Payroll run must be approved before emailing payslips');
        }

        $payslips = $dao->getPayslipList($runId);
        $results = [
            'total' => count($payslips),
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($payslips as $payslip) {
            try {
                $sent = $this->emailSinglePayslip($payslip);
                if ($sent) {
                    $results['sent']++;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'employeeId' => $payslip->getEmployee()->getEmployeeId(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Email a single payslip to the employee
     */
    public function emailSinglePayslip(Payslip $payslip): bool
    {
        $employee = $payslip->getEmployee();
        if (!$employee) {
            return false;
        }

        // Get employee email
        $email = $this->getEmployeeEmail($employee->getEmpNumber());
        if (empty($email)) {
            return false;
        }

        $run = $payslip->getPayrollRun();
        $periodStart = $run ? $run->getPeriodStart()->format('d M Y') : '';
        $periodEnd = $run ? $run->getPeriodEnd()->format('d M Y') : '';
        $empName = $employee->getFirstName() . ' ' . $employee->getLastName();
        $currency = $payslip->getCurrencyId();

        $subject = "Your Payslip — {$periodStart} to {$periodEnd}";

        $body = $this->buildEmailBody([
            'empName' => $empName,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'basicSalary' => $this->formatCurrency($payslip->getBasicSalary(), $currency),
            'grossSalary' => $this->formatCurrency($payslip->getGrossSalary(), $currency),
            'totalDeductions' => $this->formatCurrency($payslip->getTotalDeductions(), $currency),
            'netSalary' => $this->formatCurrency($payslip->getNetSalary(), $currency),
            'daysPresent' => $payslip->getDaysPresent(),
            'daysAbsent' => $payslip->getDaysAbsent(),
            'totalWorkingDays' => $payslip->getTotalWorkingDays(),
        ]);

        // Use XHRM's email service
        $emailService = new EmailService();
        $emailService->setMessageTo([$email]);
        $emailService->setMessageSubject($subject);
        $emailService->setMessageBody($body);

        $sent = $emailService->sendEmail();

        if ($sent) {
            // Mark payslip as emailed
            $payslip->setStatus('emailed');
            $payslip->setEmailedAt(new \DateTime());
            $this->getPayrollDao()->savePayslip($payslip);
        }

        return $sent;
    }

    /**
     * Build the email body HTML
     */
    private function buildEmailBody(array $data): string
    {
        return <<<HTML
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 0 auto; color: #1e293b;">
    <div style="background: linear-gradient(135deg, #1e40af, #3b82f6); padding: 30px; border-radius: 8px 8px 0 0; text-align: center;">
        <h1 style="color: #fff; margin: 0; font-size: 22px;">Payslip Notification</h1>
        <p style="color: rgba(255,255,255,0.8); margin: 5px 0 0; font-size: 14px;">{$data['periodStart']} — {$data['periodEnd']}</p>
    </div>
    
    <div style="background: #fff; padding: 30px; border: 1px solid #e2e8f0; border-top: none;">
        <p style="font-size: 15px; margin-bottom: 20px;">Dear <strong>{$data['empName']}</strong>,</p>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">
            Your payslip for the period <strong>{$data['periodStart']}</strong> to <strong>{$data['periodEnd']}</strong> has been processed. Here is your summary:
        </p>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; color: #64748b;">Attendance</td>
                <td style="padding: 10px 0; text-align: right; font-weight: 600;">{$data['daysPresent']} / {$data['totalWorkingDays']} days ({$data['daysAbsent']} absent)</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; color: #64748b;">Basic Salary</td>
                <td style="padding: 10px 0; text-align: right; font-weight: 600;">{$data['basicSalary']}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; color: #64748b;">Gross Salary</td>
                <td style="padding: 10px 0; text-align: right; font-weight: 600;">{$data['grossSalary']}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0; color: #dc2626;">Total Deductions</td>
                <td style="padding: 10px 0; text-align: right; font-weight: 600; color: #dc2626;">{$data['totalDeductions']}</td>
            </tr>
        </table>
        
        <div style="background: linear-gradient(135deg, #1e40af, #3b82f6); padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
            <div style="color: rgba(255,255,255,0.8); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Net Pay</div>
            <div style="color: #fff; font-size: 28px; font-weight: 800; margin-top: 5px;">{$data['netSalary']}</div>
        </div>
        
        <p style="font-size: 13px; color: #94a3b8; text-align: center;">
            You can view your full payslip with detailed breakdown by logging into the XHRM portal and navigating to <strong>Payroll → My Payslips</strong>.
        </p>
    </div>
    
    <div style="background: #f8fafc; padding: 15px; border-radius: 0 0 8px 8px; border: 1px solid #e2e8f0; border-top: none; text-align: center;">
        <p style="color: #94a3b8; font-size: 11px; margin: 0;">
            This is an automated email. Please do not reply.<br>
            XHRM Payroll System
        </p>
    </div>
</div>
HTML;
    }

    /**
     * Get employee's work email
     */
    private function getEmployeeEmail(int $empNumber): ?string
    {
        return $this->getPayrollDao()->getEmployeeWorkEmail($empNumber);
    }

    private function formatCurrency(string $amount, string $currency = 'PKR'): string
    {
        return $currency . ' ' . number_format((float) $amount, 2);
    }
}
