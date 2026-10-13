<?php

declare(strict_types=1);

namespace Survos\SeoBundle\Tests;

use Survos\SeoBundle\SurvosSeoBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class TestKernel extends BaseKernel
{
    public function registerBundles(): iterable
    {
        return [
            new TwigBundle(),
            new FrameworkBundle(),
            new SurvosSeoBundle(),
        ];
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config.yml');
    }

    public function getProjectDir(): string
    {
        return __DIR__.'/project-dir';
    }
}
