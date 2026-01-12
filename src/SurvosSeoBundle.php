<?php

/** generated from /home/tac/ca/survos/packages/maker-bundle/templates/skeleton/bundle/src/Bundle.tpl.php */

namespace Survos\SeoBundle;

use Survos\SeoBundle\DataCollector\SeoCollector;
use Survos\SeoBundle\Service\SeoService;
use Survos\SeoBundle\Twig\Extension\SeoExtension;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SurvosSeoBundle extends AbstractBundle
{
    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->autowire(SeoService::class)
            ->setPublic(true)
            ->setArgument('$config', $config)
        ;

        $builder->autowire(SeoCollector::class)
            ->setPublic(true)
            ->setArgument('$seoService', new Reference(SeoService::class))
            ->addTag('data_collector', [
                'template' => '@SurvosSeo/seo_collector.html.twig',
            ]);

        $definition = $builder
            ->autowire('survos.seo_twig', SeoExtension::class)
            ->addTag('twig.extension')
            ->setArgument('$seoService', new Reference(SeoService::class))
        ;
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
