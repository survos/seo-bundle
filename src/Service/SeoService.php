<?php

declare(strict_types=1);

namespace Survos\SeoBundle\Service;

/**
 * SEO related Twig helpers.
 */
final class SeoService
{
    public const DEFAULT_MIN_TITLE = 3;
    public const DEFAULT_MAX_TITLE = 10;

    public const DEFAULT_MIN_DESCRIPTION = 13;
    public const DEFAULT_MAX_DESCRIPTION = 100;

    /**
     * @param array<string, int|string|null> $config
     */
    public function __construct(
        private array $config = [
        ],
    ) {
    }

    public function getConfigValue(string $key): int|string|null
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        } else {
            throw new \RuntimeException("Config key '$key' not found");
        }
    }

    /**
     * @return array<int,int>
     */
    public function getMinMax(string $key): array
    {
        return [
            (int) $this->getConfigValue('min'.$key.'Length'),
            (int) $this->getConfigValue('max'.$key.'Length')];
    }

    /**
     * @return array<string,mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
