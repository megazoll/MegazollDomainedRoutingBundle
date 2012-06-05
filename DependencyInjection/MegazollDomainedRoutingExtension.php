<?php

namespace Megazoll\DomainedRoutingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * MegazollDomainedRoutingExtension
 *
 * @author Vyacheslav Salakhutdinov <megazoll@gmail.com>
 */
class MegazollDomainedRoutingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('megazoll_domained_routing.base_domain', $config['base_domain']);
        $container->setParameter('megazoll_domained_routing.default_domain_pattern', $config['default_domain_pattern']);
    }
}
