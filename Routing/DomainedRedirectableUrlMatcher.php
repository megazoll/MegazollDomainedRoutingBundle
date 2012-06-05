<?php

namespace Megazoll\DomainedRoutingBundle\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Vyacheslav Salakhutdinov <megazoll@gmail.com>
 */
class DomainedRedirectableUrlMatcher extends RedirectableUrlMatcher
{
    private $defaultDomainPattern;

    private $baseDomain;

    /**
     * Constructor.
     *
     * @param RouteCollection $routes   A RouteCollection instance
     * @param RequestContext  $context  The context
     * @param array           $defaults Default variables for domain routing
     */
    public function __construct(RouteCollection $routes, RequestContext $context, array $defaults)
    {
        $this->defaultDomainPattern = isset($defaults['default_domain_pattern']) ? $defaults['default_domain_pattern'] : null;
        $this->baseDomain           = isset($defaults['base_domain']) ? $defaults['base_domain'] : null;

        parent::__construct($routes, $context);
    }

    /**
     * Tries to match a domained URL with a set of routes.
     *
     * @param string          $pathinfo The path info to be parsed
     * @param RouteCollection $routes   Set of routes
     *
     * @return array An array of parameters
     */
    private function matchDomainedCollection($pathinfo, RouteCollection $routes)
    {
        foreach ($routes as $name => $route) {
            if ($route instanceof RouteCollection) {
                if (false === strpos($route->getPrefix(), '{') && $route->getPrefix() !== substr($pathinfo, 0, strlen($route->getPrefix()))) {
                    continue;
                }

                if (!$ret = $this->matchDomainedCollection($pathinfo, $route)) {
                    continue;
                }

                return $ret;
            }

            $compiledRoute = $route->compile();
            $routeOptions  = $compiledRoute->getOptions();

            $domainPattern = isset($routeOptions['domain_pattern']) ? $routeOptions['domain_pattern'] : $this->defaultDomainPattern;

            if (!$domainPattern) {
                continue;
            }

            if ($this->baseDomain) {
                $domainPattern = str_replace('%base_domain%', $this->baseDomain, $domainPattern);
            }

            // check the static prefix of the URL first. Only use the more expensive preg_match when it matches
            if ('' !== $compiledRoute->getStaticPrefix() && 0 !== strpos($pathinfo, $compiledRoute->getStaticPrefix())) {
                continue;
            }

            if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
                continue;
            }

            $domainParameters = array();
            if ($domainPattern) {
                if (!preg_match('#\{(\w+)\}#i', $domainPattern, $domainParameterMatches)) {
                    continue;
                }

                // TODO Move pattern to requirements.
                $domainPatternRegex = '#^'.preg_replace('#\{(\w+)\}#i', '((?(?!www)[\w\-])+)', $domainPattern).'$#';
                if (!preg_match($domainPatternRegex, $this->getContext()->getHost(), $domainMatches)) {
                    continue;
                }

                $domainParameters = array_combine($domainParameterMatches, $domainMatches);
                array_shift($domainParameters);
            }

            // check HTTP method requirement
            if ($req = $route->getRequirement('_method')) {
                // HEAD and GET are equivalent as per RFC
                if ('HEAD' === $method = $this->context->getMethod()) {
                    $method = 'GET';
                }

                if (!in_array($method, $req = explode('|', strtoupper($req)))) {
                    $this->allow = array_merge($this->allow, $req);

                    continue;
                }
            }

            return array_merge($this->mergeDefaults($matches, $route->getDefaults()), array('_route' => $name), $domainParameters);
        }
    }

    protected function matchCollection($pathinfo, RouteCollection $routes)
    {
        $pathinfo = urldecode($pathinfo);

        if ($parameters = $this->matchDomainedCollection($pathinfo, $routes)) {
            return $parameters;
        } else {
            return parent::matchCollection($pathinfo, $routes);
        }
    }
}
