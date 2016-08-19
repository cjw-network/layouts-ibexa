<?php

namespace Netgen\BlockManager\Ez\Block\BlockDefinition;

use Netgen\BlockManager\Block\BlockDefinition\BlockDefinitionHandler;
use Netgen\BlockManager\API\Values\Page\Block;

abstract class ContentFieldDefinitionHandler extends BlockDefinitionHandler implements ContentFieldDefinitionHandlerInterface
{
    /**
     * Returns the identifier of the eZ Publish content field to use.
     *
     * @param \Netgen\BlockManager\API\Values\Page\Block $block
     *
     * @return string
     */
    public function getFieldIdentifier(Block $block)
    {
        return $block->getParameter($this->getFieldIdentifierParameter());
    }

    /**
     * Returns the name of the parameter which will provide the field identifier.
     *
     * @return string
     */
    abstract public function getFieldIdentifierParameter();
}