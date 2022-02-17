<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class ObjectState extends Constraint
{
    public string $message = 'netgen_layouts.ibexa.object_state.object_state_not_found';

    public string $invalidGroupMessage = 'netgen_layouts.ibexa.object_state.object_state_group_not_found';

    public string $notAllowedMessage = 'netgen_layouts.ibexa.object_state.object_state_not_allowed';

    /**
     * If not empty, the constraint will validate only if object state identifier
     * is in the list of provided object state identifiers.
     *
     * @var array<string, mixed>
     */
    public array $allowedStates = [];

    public function validatedBy(): string
    {
        return 'nglayouts_ibexa_object_state';
    }
}
