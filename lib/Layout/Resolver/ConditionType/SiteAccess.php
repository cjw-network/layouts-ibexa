<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\Layout\Resolver\ConditionType;

use eZ\Publish\Core\MVC\Symfony\SiteAccess as EzSiteAccess;
use Netgen\Layouts\Ez\Validator\Constraint as EzConstraints;
use Netgen\BlockManager\Layout\Resolver\ConditionTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

final class SiteAccess implements ConditionTypeInterface
{
    public static function getType(): string
    {
        return 'ez_site_access';
    }

    public function getConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
            new Constraints\Type(['type' => 'array']),
            new Constraints\All(
                [
                    'constraints' => [
                        new Constraints\Type(['type' => 'string']),
                        new EzConstraints\SiteAccess(),
                    ],
                ]
            ),
        ];
    }

    public function matches(Request $request, $value): bool
    {
        $siteAccess = $request->attributes->get('siteaccess');
        if (!$siteAccess instanceof EzSiteAccess) {
            return false;
        }

        if (!is_array($value) || count($value) === 0) {
            return false;
        }

        return in_array($siteAccess->name, $value, true);
    }
}
