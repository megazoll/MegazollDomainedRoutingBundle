<?php

namespace Megazoll\DomainedRoutingBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Vyacheslav Salakhutdinov <megazoll@gmail.com>
 */
class Router extends BaseRouter
{
    private $defaultDomainPattern;

    private $baseDomain;

    /**
     * {@inheritDoc}
     */
    public function __construct(ContainerInterface $container, $resource, array $options = array(), RequestContext $context = null, array $defaults = array())
    {
        $options = array_merge(
            $options,
            array(
                'matcher_class'         => 'Megazoll\\DomainedRoutingBundle\\Routing\\DomainedRedirectableUrlMatcher',
                'matcher_base_class'    => 'Megazoll\\DomainedRoutingBundle\\Routing\\DomainedRedirectableUrlMatcher',
            )
        );

        $this->defaultDomainPattern = isset($defaults['default_domain_pattern']) ? $defaults['default_domain_pattern'] : null;
        $this->baseDomain           = isset($defaults['base_domain']) ? $defaults['base_domain'] : null;

        parent::__construct($container, $resource, $options, $context, $defaults);
    }

    /**
     * {@inheritDoc}
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        $domainPattern = null;
        if ($this->collection->get($name)) {
            $domainPattern = $this->collection->get($name)->getOption('domain_pattern') ?: (
                $this->defaultDomainPattern != $this->context->getHost()
                ? $this->defaultDomainPattern
                : null
            );
        }

        if ($domainPattern) {

            if ($this->baseDomain) {
                $domainPattern = str_replace('%base_domain%', $this->baseDomain, $domainPattern);
            }

            $host = preg_replace_callback(
                '#\{(\w+)\}#i', function($matches) use (&$parameters) {
                    $domain = '{'.$matches[1].'}';
                    if (isset($parameters[$matches[1]])) {
                        $domain = $parameters[$matches[1]];
                        unset($parameters[$matches[1]]);
                    }

                    return $domain;
                }, $domainPattern
            );

            $originalHost = $this->context->getHost();

            $this->context->setHost($host);

            $url = parent::generate($name, $parameters, true);

            $this->context->setHost($originalHost);

            return $url;
        } else {
            return parent::generate($name, $parameters, $absolute);
        }
    }

}