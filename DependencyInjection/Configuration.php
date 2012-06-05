<?php

namespace Megazoll\DomainedRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * MegazollDomainedRoutingExtension configuration structure.
 *
 * @author Vyacheslav Salakhutdnov <megazoll@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('megazoll_domained_routing');

        $rootNode
            ->children()
              ->scalarNode('base_domain')->defaultNull()->end()
              ->scalarNode('default_domain_pattern')->defaultNull()->end()
            ->end();

        return $treeBuilder;
    }
}
