<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Ez\Collection\QueryType\Handler;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use Netgen\BlockManager\API\Values\Collection\Query;
use Netgen\BlockManager\Collection\QueryType\QueryTypeHandlerInterface;
use Netgen\BlockManager\Ez\ContentProvider\ContentProviderInterface;
use Netgen\BlockManager\Parameters\ParameterBuilderInterface;

/**
 * Handler for a query which retrieves the eZ locations from the repository
 * based on parameters provided in the query.
 *
 * @final
 */
class ContentSearchHandler implements QueryTypeHandlerInterface
{
    use Traits\ParentLocationTrait;
    use Traits\SortTrait;
    use Traits\QueryTypeFilterTrait;
    use Traits\MainLocationFilterTrait;
    use Traits\CurrentLocationFilterTrait;
    use Traits\ContentTypeFilterTrait;
    use Traits\SectionFilterTrait;
    use Traits\ObjectStateFilterTrait;

    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    /**
     * @var array
     */
    private $languages = [];

    public function __construct(
        LocationService $locationService,
        SearchService $searchService,
        ContentTypeHandler $contentTypeHandler,
        SectionHandler $sectionHandler,
        ObjectStateHandler $objectStateHandler,
        ContentProviderInterface $contentProvider
    ) {
        $this->searchService = $searchService;

        $this->setContentTypeHandler($contentTypeHandler);
        $this->setSectionHandler($sectionHandler);
        $this->setObjectStateHandler($objectStateHandler);
        $this->setContentProvider($contentProvider);
        $this->setLocationService($locationService);
    }

    /**
     * Sets the current siteaccess languages into the handler.
     *
     * @param string[]|null $languages
     */
    public function setLanguages(?array $languages = null): void
    {
        $this->languages = $languages ?? [];
    }

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
        $advancedGroup = [self::GROUP_ADVANCED];

        $this->buildParentLocationParameters($builder);
        $this->buildSortParameters($builder);
        $this->buildQueryTypeParameters($builder, $advancedGroup);
        $this->buildMainLocationParameters($builder, $advancedGroup);
        $this->buildCurrentLocationParameters($builder, $advancedGroup);
        $this->buildContentTypeFilterParameters($builder, $advancedGroup);
        $this->buildSectionFilterParameters($builder, $advancedGroup);
        $this->buildObjectStateFilterParameters($builder, $advancedGroup);
    }

    public function getValues(Query $query, int $offset = 0, ?int $limit = null): iterable
    {
        $parentLocation = $this->getParentLocation($query);

        if (!$parentLocation instanceof Location) {
            return [];
        }

        $locationQuery = $this->buildLocationQuery($query, $parentLocation);
        $locationQuery->offset = $offset;
        $locationQuery->limit = $limit ?? PHP_INT_MAX;

        // We're disabling query count for performance reasons, however
        // it can only be disabled if limit is not 0
        $locationQuery->performCount = $locationQuery->limit === 0;

        $searchResult = $this->searchService->findLocations(
            $locationQuery,
            ['languages' => $this->languages]
        );

        return array_map(
            static function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $searchResult->searchHits
        );
    }

    public function getCount(Query $query): int
    {
        $parentLocation = $this->getParentLocation($query);

        if (!$parentLocation instanceof Location) {
            return 0;
        }

        $locationQuery = $this->buildLocationQuery($query, $parentLocation);
        $locationQuery->limit = 0;

        $searchResult = $this->searchService->findLocations(
            $locationQuery,
            ['languages' => $this->languages]
        );

        return $searchResult->totalCount ?? 0;
    }

    public function isContextual(Query $query): bool
    {
        return $query->getParameter('use_current_location')->getValue() === true;
    }

    /**
     * Builds the query from current parameters.
     */
    private function buildLocationQuery(Query $query, Location $parentLocation): LocationQuery
    {
        $locationQuery = new LocationQuery();

        $criteria = [
            new Criterion\Subtree($parentLocation->pathString),
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\LogicalNot(new Criterion\LocationId($parentLocation->id)),
            $this->getMainLocationFilterCriteria($query),
            $this->getQueryTypeFilterCriteria($query, $parentLocation),
            $this->getContentTypeFilterCriteria($query),
            $this->getSectionFilterCriteria($query),
            $this->getObjectStateFilterCriteria($query),
        ];

        $currentLocation = $this->contentProvider->provideLocation();
        if ($currentLocation instanceof Location) {
            $criteria[] = $this->getCurrentLocationFilterCriteria($query, $currentLocation);
        }

        $criteria = array_filter(
            $criteria,
            static function ($criterion): bool {
                return $criterion instanceof Criterion;
            }
        );

        $locationQuery->filter = new Criterion\LogicalAnd($criteria);
        $locationQuery->sortClauses = $this->getSortClauses($query, $parentLocation);

        return $locationQuery;
    }
}
