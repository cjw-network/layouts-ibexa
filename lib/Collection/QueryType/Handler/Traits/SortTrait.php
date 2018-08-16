<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Ez\Collection\QueryType\Handler\Traits;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Netgen\BlockManager\Parameters\ParameterBuilderInterface;
use Netgen\BlockManager\Parameters\ParameterCollectionInterface;
use Netgen\BlockManager\Parameters\ParameterType;

trait SortTrait
{
    /**
     * @var array
     */
    private static $sortClauses = [
        'default' => SortClause\DatePublished::class,
        'date_published' => SortClause\DatePublished::class,
        'date_modified' => SortClause\DateModified::class,
        'content_name' => SortClause\ContentName::class,
        'location_priority' => SortClause\Location\Priority::class,
        Location::SORT_FIELD_PATH => SortClause\Location\Path::class,
        Location::SORT_FIELD_PUBLISHED => SortClause\DatePublished::class,
        Location::SORT_FIELD_MODIFIED => SortClause\DateModified::class,
        Location::SORT_FIELD_SECTION => SortClause\SectionIdentifier::class,
        Location::SORT_FIELD_DEPTH => SortClause\Location\Depth::class,
        Location::SORT_FIELD_PRIORITY => SortClause\Location\Priority::class,
        Location::SORT_FIELD_NAME => SortClause\ContentName::class,
        Location::SORT_FIELD_NODE_ID => SortClause\Location\Id::class,
        Location::SORT_FIELD_CONTENTOBJECT_ID => SortClause\ContentId::class,
    ];

    /**
     * @var array
     */
    private static $sortDirections = [
        Location::SORT_ORDER_ASC => LocationQuery::SORT_ASC,
        Location::SORT_ORDER_DESC => LocationQuery::SORT_DESC,
    ];

    /**
     * Builds the parameters for sorting eZ content.
     */
    private function buildSortParameters(ParameterBuilderInterface $builder, array $groups = [], array $allowedSortTypes = []): void
    {
        $sortTypes = [
            'Published' => 'date_published',
            'Modified' => 'date_modified',
            'Alphabetical' => 'content_name',
            'Priority' => 'location_priority',
            'Defined by parent' => 'defined_by_parent',
        ];

        if (!empty($allowedSortTypes)) {
            $sortTypes = array_intersect($sortTypes, $allowedSortTypes);
        }

        $builder->add(
            'sort_type',
            ParameterType\ChoiceType::class,
            [
                'required' => true,
                'options' => $sortTypes,
                'groups' => $groups,
            ]
        );

        $builder->add(
            'sort_direction',
            ParameterType\ChoiceType::class,
            [
                'required' => true,
                'options' => [
                    'Descending' => LocationQuery::SORT_DESC,
                    'Ascending' => LocationQuery::SORT_ASC,
                ],
                'groups' => $groups,
            ]
        );
    }

    /**
     * Returns the clauses for sorting eZ content.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    private function getSortClauses(ParameterCollectionInterface $parameterCollection, ?Location $parentLocation = null): array
    {
        $sortType = $parameterCollection->getParameter('sort_type')->getValue() ?? 'default';
        $sortDirection = $parameterCollection->getParameter('sort_direction')->getValue() ?? LocationQuery::SORT_DESC;

        if ($sortType === 'defined_by_parent' && $parentLocation !== null) {
            if (method_exists($parentLocation, 'getSortClauses')) {
                return $parentLocation->getSortClauses();
            }

            // @deprecated Remove when support for eZ Publish 5 ends, together
            // with sort type and sort direction mappings in the trait.
            $sortType = $parentLocation->sortField;
            $sortDirection = self::$sortDirections[$parentLocation->sortOrder];
        }

        return [
            new self::$sortClauses[$sortType]($sortDirection),
        ];
    }
}
