<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ExpenseReport;
use App\Entity\ExpenseType;
use App\Repository\ExpenseRepository;
use App\Repository\ExpenseTypeRepository;

class ExpenseReportProvider implements ProviderInterface
{
    public function __construct(
        protected ExpenseTypeRepository $expenseTypeRepository,
        protected ExpenseRepository $expenseRepository,
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

        $report = new ExpenseReport();
        $report->setId($expenseType->getId());
        $report->setTitle($expenseType->getTitle());
        $amount = $this->expenseRepository->sumByExpenseTypeAndDateRange($expenseType, $startDate, $endDate);
        $amount += $this->calculateAmountForChildren($expenseType, $startDate, $endDate);
        $report->setAmount($amount);

        $childrenReports = [];
        foreach ($expenseType->getChildren() as $child) {
            $childrenReports[] = $this->convertToExpenseReport($child, $context);
        }
        $report->setChildren($childrenReports);

        return $report;
    }

    private function calculateAmountForChildren(ExpenseType $expenseType, $startDate, $endDate): float
    {
        $amount = 0;
        foreach ($expenseType->getChildren() as $child) {
            $amount += $this->expenseRepository->sumByExpenseTypeAndDateRange($child, $startDate, $endDate);
            $amount += $this->calculateAmountForChildren($child, $startDate, $endDate);
        }
        return $amount;
    }
}
