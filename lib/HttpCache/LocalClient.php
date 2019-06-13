<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\HttpCache;

use EzSystems\PlatformHttpCacheBundle\RequestAwarePurger;
use Netgen\Layouts\HttpCache\ClientInterface;
use Symfony\Component\HttpFoundation\Request;

final class LocalClient implements ClientInterface
{
    /**
     * @var \EzSystems\PlatformHttpCacheBundle\RequestAwarePurger
     */
    private $requestAwarePurger;

    public function __construct(RequestAwarePurger $requestAwarePurger)
    {
        $this->requestAwarePurger = $requestAwarePurger;
    }

    public function purge(array $tags): void
    {
        if (count($tags) === 0) {
            return;
        }

        $purgeRequest = Request::create('http://localhost/', 'PURGE');
        $purgeRequest->headers->set('key', implode(' ', $tags));
        $this->requestAwarePurger->purgeByRequest($purgeRequest);
    }

    public function commit(): bool
    {
        return true;
    }
}
