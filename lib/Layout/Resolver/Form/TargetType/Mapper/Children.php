<?php

namespace Netgen\BlockManager\Ez\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\BlockManager\Layout\Resolver\Form\TargetType\Mapper;
use Netgen\BlockManager\Layout\Resolver\TargetTypeInterface;
use Netgen\Bundle\ContentBrowserBundle\Form\Type\ContentBrowserType;

class Children extends Mapper
{
    /**
     * Returns the form type that will be used to edit the value of this condition type.
     *
     * @return string
     */
    public function getFormType()
    {
        return ContentBrowserType::class;
    }

    /**
     * Maps the form type options from provided target type.
     *
     * @param \Netgen\BlockManager\Layout\Resolver\TargetTypeInterface $targetType
     *
     * @return array
     */
    public function mapOptions(TargetTypeInterface $targetType)
    {
        return array(
            'item_type' => 'ezlocation',
        );
    }
}
