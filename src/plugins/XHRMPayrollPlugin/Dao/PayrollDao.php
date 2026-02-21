<?php

namespace XHRM\Payroll\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\Entity\SalaryComponent;
use XHRM\Entity\AttendanceRule;
use XHRM\Entity\OvertimeRule;
use XHRM\Entity\Holiday;
use XHRM\Entity\FinancialYear;
use XHRM\Entity\TaxSlab;
use XHRM\Entity\PayrollRun;
use XHRM\Entity\Payslip;
use XHRM\Entity\PayslipItem;
use XHRM\Entity\EmployeeLoan;

class PayrollDao extends BaseDao
{
    // ===========================
    // Salary Components
    // ===========================

    public function saveSalaryComponent(SalaryComponent $component): SalaryComponent
    {
        $this->persist($component);
        return $component;
    }

    public function getSalaryComponentById(int $id): ?SalaryComponent
    {
        return $this->getRepository(SalaryComponent::class)->find($id);
    }

    public function getSalaryComponentList(?string $type = null, ?bool $isActive = null): array
    {
        $qb = $this->createQueryBuilder(SalaryComponent::class, 'sc');
        $qb->orderBy('sc.sortOrder', 'ASC');
        if ($type !== null) {
            $qb->andWhere('sc.type = :type')->setParameter('type', $type);
        }
        if ($isActive !== null) {
            $qb->andWhere('sc.isActive = :isActive')->setParameter('isActive', $isActive);
        }
        return $qb->getQuery()->execute();
    }

    public function getActiveSalaryComponents(): array
    {
        return $this->getSalaryComponentList(null, true);
    }

    public function deleteSalaryComponents(array $ids): int
    {
        $qb = $this->createQueryBuilder(SalaryComponent::class, 'sc');
        $qb->delete()->where($qb->expr()->in('sc.id', ':ids'))->setParameter('ids', $ids);
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Attendance Rules
    // ===========================

    public function saveAttendanceRule(AttendanceRule $rule): AttendanceRule
    {
        $this->persist($rule);
        return $rule;
    }

    public function getAttendanceRuleById(int $id): ?AttendanceRule
    {
        return $this->getRepository(AttendanceRule::class)->find($id);
    }

    public function getAttendanceRuleList(): array
    {
        return $this->getRepository(AttendanceRule::class)->findAll();
    }

    public function getDefaultAttendanceRule(): ?AttendanceRule
    {
        return $this->getRepository(AttendanceRule::class)->findOneBy(['isDefault' => true]);
    }

    public function deleteAttendanceRules(array $ids): int
    {
        $qb = $this->createQueryBuilder(AttendanceRule::class, 'ar');
        $qb->delete()->where($qb->expr()->in('ar.id', ':ids'))->setParameter('ids', $ids);
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Overtime Rules
    // ===========================

    public function saveOvertimeRule(OvertimeRule $rule): OvertimeRule
    {
        $this->persist($rule);
        return $rule;
    }

    public function getOvertimeRuleById(int $id): ?OvertimeRule
    {
        return $this->getRepository(OvertimeRule::class)->find($id);
    }

    public function getOvertimeRuleList(): array
    {
        return $this->getRepository(OvertimeRule::class)->findAll();
    }

    public function getActiveOvertimeRules(): array
    {
        return $this->getRepository(OvertimeRule::class)->findBy(['isActive' => true]);
    }

    public function deleteOvertimeRules(array $ids): int
    {
        $qb = $this->createQueryBuilder(OvertimeRule::class, 'otr');
        $qb->delete()->where($qb->expr()->in('otr.id', ':ids'))->setParameter('ids', $ids);
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Holidays
    // ===========================

    public function saveHoliday(Holiday $holiday): Holiday
    {
        $this->persist($holiday);
        return $holiday;
    }

    public function getHolidayById(int $id): ?Holiday
    {
        return $this->getRepository(Holiday::class)->find($id);
    }

    public function getHolidayList(?int $year = null): array
    {
        $qb = $this->createQueryBuilder(Holiday::class, 'h');
        $qb->orderBy('h.date', 'ASC');
        if ($year !== null) {
            $start = new \DateTime("{$year}-01-01");
            $end = new \DateTime("{$year}-12-31");
            $qb->andWhere('h.date BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }
        return $qb->getQuery()->execute();
    }

    public function getHolidaysBetween(\DateTime $start, \DateTime $end): array
    {
        $qb = $this->createQueryBuilder(Holiday::class, 'h');
        $qb->andWhere('h.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('h.date', 'ASC');
        return $qb->getQuery()->execute();
    }

    public function deleteHolidays(array $ids): int
    {
        $qb = $this->createQueryBuilder(Holiday::class, 'h');
        $qb->delete()->where($qb->expr()->in('h.id', ':ids'))->setParameter('ids', $ids);
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Financial Years
    // ===========================

    public function saveFinancialYear(FinancialYear $fy): FinancialYear
    {
        $this->persist($fy);
        return $fy;
    }

    public function getFinancialYearById(int $id): ?FinancialYear
    {
        return $this->getRepository(FinancialYear::class)->find($id);
    }

    public function getFinancialYearList(): array
    {
        return $this->getRepository(FinancialYear::class)->findAll();
    }

    public function getActiveFinancialYear(): ?FinancialYear
    {
        return $this->getRepository(FinancialYear::class)->findOneBy(['status' => 'active']);
    }

    public function deleteFinancialYears(array $ids): int
    {
        $qb = $this->createQueryBuilder(FinancialYear::class, 'fy');
        $qb->delete()->where($qb->expr()->in('fy.id', ':ids'))->setParameter('ids', $ids);
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Tax Slabs
    // ===========================

    public function saveTaxSlab(TaxSlab $slab): TaxSlab
    {
        $this->persist($slab);
        return $slab;
    }

    public function getTaxSlabById(int $id): ?TaxSlab
    {
        return $this->getRepository(TaxSlab::class)->find($id);
    }

    public function getTaxSlabList(?int $financialYearId = null): array
    {
        $qb = $this->createQueryBuilder(TaxSlab::class, 'ts');
        $qb->orderBy('ts.minIncome', 'ASC');
        if ($financialYearId !== null) {
            $qb->andWhere('ts.financialYear = :fyId')->setParameter('fyId', $financialYearId);
        }
        return $qb->getQuery()->execute();
    }

    public function getTaxSlabsForYear(int $financialYearId): array
    {
        return $this->getTaxSlabList($financialYearId);
    }

    public function deleteTaxSlabs(array $ids): int
    {
        $qb = $this->createQueryBuilder(TaxSlab::class, 'ts');
        $qb->delete()->where($qb->expr()->in('ts.id', ':ids'))->setParameter('ids', $ids);
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Payroll Runs
    // ===========================

    public function savePayrollRun(PayrollRun $run): PayrollRun
    {
        $this->persist($run);
        return $run;
    }

    public function getPayrollRunById(int $id): ?PayrollRun
    {
        return $this->getRepository(PayrollRun::class)->find($id);
    }

    public function getPayrollRunList(?string $status = null): array
    {
        $qb = $this->createQueryBuilder(PayrollRun::class, 'pr');
        $qb->orderBy('pr.generatedAt', 'DESC');
        if ($status !== null) {
            $qb->andWhere('pr.status = :status')->setParameter('status', $status);
        }
        return $qb->getQuery()->execute();
    }

    public function deletePayrollRuns(array $ids): int
    {
        // Only allow deleting draft runs
        $qb = $this->createQueryBuilder(PayrollRun::class, 'pr');
        $qb->delete()
            ->where($qb->expr()->in('pr.id', ':ids'))
            ->andWhere('pr.status = :status')
            ->setParameter('ids', $ids)
            ->setParameter('status', 'draft');
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Payslips
    // ===========================

    public function savePayslip(Payslip $payslip): Payslip
    {
        $this->persist($payslip);
        return $payslip;
    }

    public function getPayslipById(int $id): ?Payslip
    {
        return $this->getRepository(Payslip::class)->find($id);
    }

    public function getPayslipList(?int $runId = null): array
    {
        $qb = $this->createQueryBuilder(Payslip::class, 'ps');
        if ($runId !== null) {
            $qb->andWhere('ps.payrollRun = :runId')->setParameter('runId', $runId);
        }
        return $qb->getQuery()->execute();
    }

    public function getPayslipsByEmployee(int $empNumber): array
    {
        $qb = $this->createQueryBuilder(Payslip::class, 'ps');
        $qb->andWhere('ps.employee = :emp')->setParameter('emp', $empNumber);
        $qb->orderBy('ps.id', 'DESC');
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Payslip Items
    // ===========================

    public function savePayslipItem(PayslipItem $item): PayslipItem
    {
        $this->persist($item);
        return $item;
    }

    public function getPayslipItems(int $payslipId): array
    {
        return $this->getRepository(PayslipItem::class)->findBy(['payslip' => $payslipId]);
    }

    // ===========================
    // Employee Loans
    // ===========================

    public function saveLoan(EmployeeLoan $loan): EmployeeLoan
    {
        $this->persist($loan);
        return $loan;
    }

    public function getLoanById(int $id): ?EmployeeLoan
    {
        return $this->getRepository(EmployeeLoan::class)->find($id);
    }

    public function getLoanList(?string $status = null): array
    {
        $qb = $this->createQueryBuilder(EmployeeLoan::class, 'el');
        if ($status !== null) {
            $qb->andWhere('el.status = :status')->setParameter('status', $status);
        }
        return $qb->getQuery()->execute();
    }

    public function getActiveLoansForEmployee(int $empNumber): array
    {
        return $this->getRepository(EmployeeLoan::class)->findBy([
            'employee' => $empNumber,
            'status' => 'active',
        ]);
    }

    public function deleteLoans(array $ids): int
    {
        $qb = $this->createQueryBuilder(EmployeeLoan::class, 'el');
        $qb->delete()->where($qb->expr()->in('el.id', ':ids'))->setParameter('ids', $ids);
        return $qb->getQuery()->execute();
    }

    // ===========================
    // Native SQL Helpers
    // ===========================

    public function getActiveEmployees(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT emp_number, emp_firstname, emp_lastname 
                FROM hs_hr_employee 
                WHERE termination_id IS NULL 
                ORDER BY emp_number";
        return $conn->fetchAllAssociative($sql);
    }

    public function getEmployeeBasicSalary(int $empNumber): float
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT ebsal_basic_salary FROM hs_hr_emp_basicsalary 
                WHERE emp_number = :empNumber 
                ORDER BY id DESC LIMIT 1";
        $result = $conn->fetchOne($sql, ['empNumber' => $empNumber]);
        return (float) ($result ?: 0);
    }

    public function getAttendanceRecords(int $empNumber, \DateTime $start, \DateTime $end): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT 
                    DATE(punch_in_utc_time) as attendance_date,
                    punch_in_note,
                    TIMESTAMPDIFF(HOUR, punch_in_utc_time, punch_out_utc_time) as total_hours
                FROM ohrm_attendance_record
                WHERE employee_id = :empNumber
                AND DATE(punch_in_utc_time) BETWEEN :start AND :end
                AND punch_out_utc_time IS NOT NULL
                GROUP BY DATE(punch_in_utc_time)";
        return $conn->fetchAllAssociative($sql, [
            'empNumber' => $empNumber,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);
    }

    public function getApprovedLeaveCount(int $empNumber, \DateTime $start, \DateTime $end): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(*) FROM ohrm_leave
                WHERE emp_number = :empNumber
                AND date BETWEEN :start AND :end
                AND status = 3";
        return (int) $conn->fetchOne($sql, [
            'empNumber' => $empNumber,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);
    }

    public function getEmployeeWorkEmail(int $empNumber): ?string
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT emp_work_email FROM hs_hr_employee WHERE emp_number = :emp";
        $email = $conn->fetchOne($sql, ['emp' => $empNumber]);
        return $email ?: null;
    }
}
