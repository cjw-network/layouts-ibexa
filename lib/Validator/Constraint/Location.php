<?php

namespace Netgen\BlockManager\Ez\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class Location extends Constraint
{
    /**
     * @var string
     */
    public $message = 'netgen_block_manager.ezlocation.location_not_found';

    /**
     * If set to true, the constraint will accept values for non existing locations.
     *
     * @var bool
     */
    public $allowNonExisting = false;

    public function validatedBy()
    {
        return 'ngbm_ezlocation';
    }
}
