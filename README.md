# MegazollDomainedRoutingBundle

This bundle enables make routes with domain part.

## Installation

### Step 1. Add this bundle to your project as Git submodule

``` bash
$ git submodule add git://github.com/megazoll/MegazollDomainedRoutingBundle.git vendor/bundles/Megazoll/DomainedRoutingBundle
```

### Step 2. Register the namespace `Megazoll` to your project's autoloader bootstrap script

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Megazoll' => __DIR__.'/../vendor/bundles',
    // ...
));
```

### Step 3. Add this bundle to your application's kernel

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        // ...
        new Megazoll\DomainedRoutingBundle\MegazollDomainedRoutingBundle(),
        // ...
    );
}
```

### Step 4. Configure bundle in your YAML configuration

``` yaml
# app/config/config.yml

megazoll_domained_routing:
    base_domain:            example.com
    default_domain_pattern: www.example.com


services:
    router.default: @megazoll_domained_routing.router

# If you want spread authentication for subdomains.
framework:
    session:
        domain: .example.com

```

### Step 5. Configure your web server

``` nginx
# nginx

server {
    listen      80;
    server_name example.com *.example.com;
    ...
}

```

``` apache
# Apache

NameVirtualHost *:80
<VirtualHost *:80>
    ServerName  example.com
    ServerAlias *.example.com
    ...
</VirtualHost>

```

## Usage

``` php
<?php
// src/Acme/YourBundle/Controller/DomainController.php

/**
 * Fallback with parameter in url
 *
 * @Route("/domain/{subDomain}/")
 * @Route("/", name = "domain_action", options = {"domain_pattern" = "{subDomain}.%base_domain%"})
 */
public function domainActionAction($subDomain)
{
    ...
}
```
