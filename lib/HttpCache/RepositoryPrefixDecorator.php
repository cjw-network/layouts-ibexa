<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\HttpCache;

use EzSystems\PlatformHttpCacheBundle\RepositoryTagPrefix;
use Netgen\Layouts\HttpCache\ClientInterface;

final class RepositoryPrefixDecorator implements ClientInterface
{
    /**
     * @var \Netgen\Layouts\HttpCache\ClientInterface
     */
    private $innerClient;

    /**
     * @var \EzSystems\PlatformHttpCacheBundle\RepositoryTagPrefix
     */
    private $prefixService;

    public function __construct(ClientInterface $innerClient, RepositoryTagPrefix $prefixService)
    {
        $this->innerClient = $innerClient;
        $this->prefixService = $prefixService;
    }

    public function purge(array $tags): void
    {
        $prefix = $this->prefixService->getRepositoryPrefix();

        $tags = array_map(
            static function (string $tag) use ($prefix): string {
                return $prefix . $tag;
            },
            $tags
        );

        $this->innerClient->purge($tags);
    }

    public function commit(): bool
    {
        return $this->innerClient->commit();
    }
}