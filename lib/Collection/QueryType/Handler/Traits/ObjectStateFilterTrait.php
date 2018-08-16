<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Ez\Collection\QueryType\Handler\Traits;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler;
use Netgen\BlockManager\Ez\Parameters\ParameterType as EzParameterType;
use Netgen\BlockManager\Parameters\ParameterBuilderInterface;
use Netgen\BlockManager\Parameters\ParameterCollectionInterface;
use Netgen\BlockManager\Parameters\ParameterType;

trait ObjectStateFilterTrait
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    private $objectStateHandler;

    /**
     * Sets the objectState handler used by the trait.
     */
    private function setObjectStateHandler(Handler $handler): void
    {
        $this->objectStateHandler = $handler;
    }

    /**
     * Builds the parameters for filtering by object states.
     */
    private function buildObjectStateFilterParameters(ParameterBuilderInterface $builder, array $groups = []): void
    {
        $builder->add(
            'filter_by_object_state',
            ParameterType\Compound\BooleanType::class,
            [
                'groups' => $groups,
            ]
        );

        $builder->get('filter_by_object_state')->add(
            'object_states',
            EzParameterType\ObjectStateType::class,
            [
                'multiple' => true,
                'groups' => $groups,
            ]
        );
    }

    /**
     * Returns the criteria used to filter content by object state.
     */
    private function getObjectStateFilterCriteria(ParameterCollectionInterface $parameterCollection): ?Criterion
    {
        if ($parameterCollection->getParameter('filter_by_object_state')->getValue() !== true) {
            return null;
        }

        $objectStates = $parameterCollection->getParameter('object_states')->getValue();
        if (empty($objectStates)) {
            return null;
        }

        $criteria = [];
        foreach ($this->getObjectStateIds($objectStates) as $stateIds) {
            $criteria[] = new Criterion\ObjectStateId($stateIds);
        }

        return new Criterion\LogicalAnd($criteria);
    }

    /**
     * Returns object state IDs for all provided object state identifiers.
     *
     * State identifiers are in format "<group_identifier>|<state_identifier>"
     */
    private function getObjectStateIds(array $stateIdentifiers): array
    {
        $idList = [];

        foreach ($stateIdentifiers as $identifier) {
            $identifier = explode('|', $identifier);
            if (count($identifier) !== 2) {
                continue;
            }

            try {
                $stateGroup = $this->objectStateHandler->loadGroupByIdentifier($identifier[0]);
                $objectState = $this->objectStateHandler->loadByIdentifier($identifier[1], $stateGroup->id);
                $idList[$stateGroup->id][] = $objectState->id;
            } catch (NotFoundException $e) {
                continue;
            }
        }

        return $idList;
    }
}
