<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ExpenseReport;
use App\Entity\ExpenseType;
use App\Repository\ExpenseRepository;
use App\Repository\ExpenseTypeRepository;
use Symfony\Component\Security\Core\Security;

class ExpenseReportProvider implements ProviderInterface
{
    public function __construct(
        protected ExpenseTypeRepository $expenseTypeRepository,
        protected ExpenseRepository $expenseRepository,
        protected Security $security
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $expenseTypes = $this->expenseTypeRepository->findBy(['parent' => null],[]);

        $expenseReports = [];
        foreach ($expenseTypes as $expenseType) {
            if ($expenseType->getParent() === null) {
                $report = $this->convertToExpenseReport($expenseType, $context);

                if ($report->getAmount() != 0) {
                    $expenseReports[] = $report;
                }
            }
        }

        return $expenseReports;
    }

    private function convertToExpenseReport(ExpenseType $expenseType, $context): ExpenseReport
    {
        $startDate = $context['filters']['startDate'] ?? null;
        $endDate = $context['filters']['endDate'] ?? null;

        $user = null;
        if (isset($context['filters']['own'])) {
            $user = $this->security->getUser();
        }

        $report = new ExpenseReport();
        $report->setId($expenseType->getId());
        $report->setTitle($expenseType->getTitle());
        $amount = $this->expenseRepository->sumByExpenseTypeAndDateRange($expenseType, $startDate, $endDate, $user);
        $amount += $this->calculateAmountForChildren($expenseType, $startDate, $endDate, $user);
        $report->setAmount($amount);

        $childrenReports = [];
        foreach ($expenseType->getChildren() as $child) {
            $childrenReports[] = $this->convertToExpenseReport($child, $context);
        }
        $report->setChildren($childrenReports);

        return $report;
    }

    private function calculateAmountForChildren(ExpenseType $expenseType, $startDate, $endDate, $user): float
    {
        $amount = 0;
        foreach ($expenseType->getChildren() as $child) {
            $amount += $this->expenseRepository->sumByExpenseTypeAndDateRange($child, $startDate, $endDate, $user);
            $amount += $this->calculateAmountForChildren($child, $startDate, $endDate, $user);
        }
        return $amount;
    }
}
