<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\Tests\Validator;

use eZ\Publish\API\Repository\Repository;
use Netgen\Layouts\Ez\Validator\ContentTypeValidator;
use Netgen\Layouts\Ez\Validator\ContentValidator;
use Netgen\Layouts\Ez\Validator\LocationValidator;
use Netgen\Layouts\Ez\Validator\ObjectStateValidator;
use Netgen\Layouts\Ez\Validator\SectionValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

final class RepositoryValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @var \Symfony\Component\Validator\ConstraintValidatorFactoryInterface
     */
    private $baseValidatorFactory;

    /**
     * @var array
     */
    private $validators;

    public function __construct(Repository $repository)
    {
        $this->baseValidatorFactory = new ConstraintValidatorFactory();

        $this->validators = [
            'ngbm_ezlocation' => new LocationValidator($repository),
            'ngbm_ezcontent' => new ContentValidator($repository),
            'ngbm_ez_content_type' => new ContentTypeValidator($repository),
            'ngbm_ez_section' => new SectionValidator($repository),
            'ngbm_ez_object_state' => new ObjectStateValidator($repository),
        ];
    }

    public function getInstance(Constraint $constraint): ConstraintValidatorInterface
    {
        $name = $constraint->validatedBy();

        return $this->validators[$name] ?? $this->baseValidatorFactory->getInstance($constraint);
    }
}
