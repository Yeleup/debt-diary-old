<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 *
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function plusOrMinusDependingType(Expense $expense, User $currentUser): Expense
    {
        if ($expense->getExpenseType()) {
            $amount = (float) abs($expense->getAmount());
            if (!$expense->getExpenseType()->isAddAmountToEmployee()) {
                $amount = -1 * $amount;
            }

            if ($expense->getAssociatedUser()) {
                if ($expense->getUser() === $currentUser) {
                    $amount = -1 * $amount;
                }
            }

            $expense->setAmount($amount);
        }

        return $expense;
    }

    public function updateUserExpenseTotal(User $user): void
    {
        $totalExpenses = $this->createQueryBuilder('e')
            ->select('SUM(e.amount) as total')
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $user->setExpenseTotal($totalExpenses);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function sumByExpenseTypeAndDateRange(ExpenseType $expenseType, ?string $startDate, ?string $endDate, ?User $user): float
    {
        $qb = $this->createQueryBuilder('e')
            ->select('SUM(e.amount) as total')
            ->where('e.expenseType = :expenseType')
            ->setParameter('expenseType', $expenseType);

        if ($startDate) {
            $qb->andWhere('e.createdAt > :startDate')->setParameter('startDate', $startDate);
        }
        if ($endDate) {
            $qb->andWhere('e.createdAt < :endDate')->setParameter('endDate', $endDate);
        }
        if ($user) {
            $qb->andWhere('e.user = :user')->setParameter('user', $user);
        }

        return $qb->getQuery()->getSingleScalarResult() ?? 0;
    }
}
