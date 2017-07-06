<?php

namespace Netgen\BlockManager\Ez\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\BlockManager\Layout\Resolver\Form\TargetType\Mapper;
use Netgen\ContentBrowser\Form\Type\ContentBrowserType;

class Subtree extends Mapper
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
     * Returns the form options.
     *
     * @return array
     */
    public function getFormOptions()
    {
        return array(
            'item_type' => 'ezlocation',
        );
    }
}
