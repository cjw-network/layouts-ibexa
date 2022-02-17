<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Layout\Resolver\TargetHandler\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Netgen\Layouts\Persistence\Doctrine\QueryHandler\TargetHandlerInterface;

final class Subtree implements TargetHandlerInterface
{
    public function handleQuery(QueryBuilder $query, $value): void
    {
        $query->andWhere(
            $query->expr()->in('rt.value', [':target_value']),
        )
        ->setParameter('target_value', $value, Connection::PARAM_INT_ARRAY);
    }
}
