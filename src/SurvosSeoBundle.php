<?php

declare(strict_types=1);

namespace Survos\SeoBundle;

use Survos\Kit\AbstractSurvosBundle;
use Survos\Kit\SurvosKitBundle;
use Survos\SeoBundle\DataCollector\SeoCollector;
use Survos\SeoBundle\Service\SeoService;
use Survos\SeoBundle\Twig\Extension\SeoExtension;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

#[RequiredBundle(SurvosKitBundle::class)]
// Symfony\Component\HttpKernel\Bundle\Bundle <-- Flex auto-registration marker (see Survos\Kit\AbstractSurvosBundle)
class SurvosSeoBundle extends AbstractSurvosBundle
{
    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        parent::loadExtension($config, $container, $builder);

        $builder->autowire(SeoService::class)
            ->setPublic(true)
            ->setArgument('$config', $config)
        ;

        $builder
            ->autowire('survos.seo_twig', SeoExtension::class)
            ->addTag('twig.extension')
            ->setArgument('$seoService', new Reference(SeoService::class))
        ;

        // Profiler integration, debug only: the collector crawls every HTML response,
        // which is pure overhead in prod, and its template extends @WebProfiler, which
        // only exists in dev anyway.
        if ($builder->getParameter('kernel.debug') && class_exists(AbstractDataCollector::class)) {
            $builder->autowire(SeoCollector::class)
                ->setArgument('$seoService', new Reference(SeoService::class))
                ->addTag('data_collector', [
                    'template' => '@SurvosSeo/seo_collector.html.twig',
                    'id' => 'survos_seo',
                ]);
        }
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();

        $rootNode
            ->children()
            ->scalarNode('branding')
                ->info('branding will be added if the title is short enough.')
                ->defaultValue('')->end()
            ->integerNode('minTitleLength')
                ->info('minimum title length')
                ->defaultValue(30)->end()
            ->integerNode('maxTitleLength')
                ->info('maximum title length')
                ->defaultValue(150)->end()
            ->integerNode('minDescriptionLength')->defaultValue(10)->end()
            ->integerNode('maxDescriptionLength')->defaultValue(255)->end()
            ->booleanNode('enabled')->defaultTrue()->end()
            ->end();
    }
}
