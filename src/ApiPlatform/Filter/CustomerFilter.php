<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class CustomerFilter extends AbstractFilter
{

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ('search' !== $property || empty($value)) {
            return;
        }

        // Алиас для вашего основного объекта (например, "o" для "object")
        $alias = $queryBuilder->getRootAliases()[0];

        // Добавьте ваш кастомный запрос к QueryBuilder
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like(sprintf('%s.name', $alias), ':name'),
                    $queryBuilder->expr()->like(sprintf('%s.place', $alias), ':place'),
                    $queryBuilder->expr()->like(sprintf('%s.contact', $alias), ':contact')
                )
            )
            ->setParameter('name', '%' . $value . '%')
            ->setParameter('place', '%' . $value . '%')
            ->setParameter('contact', '%' . $value . '%')
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["$property"] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter with strategy: '.$strategy,
            ];
        }
        return $description;
    }
}