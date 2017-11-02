<?php

namespace Netgen\BlockManager\Ez\Persistence\Doctrine\QueryHandler\LayoutResolver\TargetHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Netgen\BlockManager\Persistence\Doctrine\QueryHandler\LayoutResolver\TargetHandler;

final class Subtree implements TargetHandler
{
    public function handleQuery(QueryBuilder $query, $value)
    {
        $query->andWhere(
            $query->expr()->in('rt.value', array(':target_value'))
        )
        ->setParameter('target_value', $value, Connection::PARAM_INT_ARRAY);
    }
}
