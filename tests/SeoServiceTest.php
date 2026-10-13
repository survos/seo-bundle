<?php

declare(strict_types=1);

namespace SeoBundle\Tests;

use PHPUnit\Framework\TestCase;
use Survos\SeoBundle\Service\SeoService;

use function PHPUnit\Framework\assertEquals;

class SeoServiceTest extends TestCase
{
    public function testSeoWithoutConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $config = [
        ];
        $seo = new SeoService($config);
        $seo->getConfigValue('noKey');
    }

    public function testSeoWithConfig(): void
    {
        $config = [
            'minTitleLength' => SeoService::DEFAULT_MIN_TITLE,
            'maxTitleLength' => SeoService::DEFAULT_MAX_TITLE,
        ];
        $seo = new SeoService($config);
        assertEquals(SeoService::DEFAULT_MIN_TITLE, $seo->getConfigValue('minTitleLength'));
        assertEquals(SeoService::DEFAULT_MAX_TITLE, $seo->getConfigValue('maxTitleLength'));
        assertEquals([SeoService::DEFAULT_MIN_TITLE, SeoService::DEFAULT_MAX_TITLE], $seo->getMinMax('Title'));
    }
}
