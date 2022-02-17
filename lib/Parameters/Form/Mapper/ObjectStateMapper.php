<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Parameters\Form\Mapper;

use Netgen\Layouts\Ibexa\Form\ObjectStateType;
use Netgen\Layouts\Parameters\Form\Mapper;
use Netgen\Layouts\Parameters\ParameterDefinition;

final class ObjectStateMapper extends Mapper
{
    public function getFormType(): string
    {
        return ObjectStateType::class;
    }

    public function mapOptions(ParameterDefinition $parameterDefinition): array
    {
        return [
            'multiple' => $parameterDefinition->getOption('multiple'),
            'states' => $parameterDefinition->getOption('states'),
        ];
    }
}
