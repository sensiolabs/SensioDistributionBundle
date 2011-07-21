#!/usr/bin/env php
<?php

/*
 * This file is part of the Symfony Standard Edition.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!$baseDir = realpath(__DIR__.'/../../../../../../..')) {
    exit('Looks like you don\'t have a standard layout.');
}

require_once $baseDir.'/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\ClassLoader\ClassCollectionLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array('Symfony' => $baseDir.'/vendor/symfony/src'));
$loader->register();

$file = $baseDir.'/app/bootstrap.php.cache';
if (file_exists($file)) {
    unlink($file);
}

ClassCollectionLoader::load(array(
    'Symfony\\Component\\Config\\ConfigCache',
    'Symfony\\Component\\Config\\FileLocatorInterface',
    'Symfony\\Component\\Config\\FileLocator',

    'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
    'Symfony\\Component\\EventDispatcher\\EventDispatcher',
    'Symfony\\Component\\EventDispatcher\\Event',

    'Symfony\\Component\\DependencyInjection\\ContainerInterface',
    'Symfony\\Component\\DependencyInjection\\Container',
    'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface',
    //'Symfony\\Component\\DependencyInjection\\ContainerAware',

    'Symfony\\Component\\HttpKernel\\Bundle\\BundleInterface',
    'Symfony\\Component\\HttpKernel\\Bundle\\Bundle',
    'Symfony\\Component\\HttpKernel\\HttpKernelInterface',
    'Symfony\\Component\\HttpKernel\\HttpKernel',
    'Symfony\\Component\\HttpKernel\\KernelInterface',
    'Symfony\\Component\\HttpKernel\\Kernel',
    'Symfony\\Component\\HttpKernel\\Config\\FileLocator',
    'Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface',
    'Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver',
    'Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener',
    'Symfony\\Component\\HttpKernel\\KernelEvents',
    'Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent',
    'Symfony\\Component\\HttpKernel\\Event\\KernelEvent',
    'Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent',
    'Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent',

    'Symfony\\Component\\HttpFoundation\\ParameterBag',
    'Symfony\\Component\\HttpFoundation\\FileBag',
    'Symfony\\Component\\HttpFoundation\\ServerBag',
    'Symfony\\Component\\HttpFoundation\\HeaderBag',
    'Symfony\\Component\\HttpFoundation\\Request',
    'Symfony\\Component\\HttpFoundation\\ApacheRequest',
    'Symfony\\Component\\HttpFoundation\\ResponseHeaderBag',
    'Symfony\\Component\\HttpFoundation\\Response',

    'Symfony\\Component\\ClassLoader\\UniversalClassLoader',

    'Symfony\\Component\\Routing\\Matcher\\UrlMatcherInterface',
    'Symfony\\Component\\Routing\\RequestContextAwareInterface',
    'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface',
    'Symfony\\Component\\Routing\\RequestContext',
    'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
    'Symfony\\Component\\Routing\\Matcher\\RedirectableUrlMatcherInterface',
    'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
    'Symfony\\Component\\Routing\\RouterInterface',
    'Symfony\\Component\\Routing\\Router',

    'Symfony\\Component\\Templating\\EngineInterface',
    'Symfony\\Component\\Templating\\PhpEngine',
    'Symfony\\Component\\Templating\\TemplateNameParserInterface',
    'Symfony\\Component\\Templating\\TemplateNameParser',
    'Symfony\\Component\\Templating\\Loader\\LoaderInterface',
    'Symfony\\Component\\Templating\\TemplateReferenceInterface',
    'Symfony\\Component\\Templating\\TemplateReference',
    'Symfony\\Component\\Templating\\Storage\\Storage',
    'Symfony\\Component\\Templating\\Storage\\FileStorage',

    'Symfony\\Bundle\\FrameworkBundle\\HttpKernel',
    'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
    'Symfony\\Bundle\\FrameworkBundle\\ContainerAwareEventDispatcher',
    'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher',
    'Symfony\\Bundle\\FrameworkBundle\\Routing\\Router',
    'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver',
    'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerNameParser',
    //'Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller',
    'Symfony\\Bundle\\FrameworkBundle\\EventListener\\RouterListener',
    'Symfony\\Bundle\\FrameworkBundle\\EventListener\\SessionListener',
    'Symfony\\Bundle\\FrameworkBundle\\Templating\\GlobalVariables',
    'Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface',
    'Symfony\\Bundle\\FrameworkBundle\\Templating\\PhpEngine',
    'Symfony\\Bundle\\FrameworkBundle\\Templating\\TemplateNameParser',
    'Symfony\\Bundle\\FrameworkBundle\\Templating\\Loader\\FilesystemLoader',
    'Symfony\\Bundle\\FrameworkBundle\\Templating\\Loader\\TemplateLocator',
    'Symfony\\Bundle\\FrameworkBundle\\Templating\\TemplateReference',
), dirname($file), basename($file, '.php.cache'), false, false, '.php.cache');

file_put_contents($file, "<?php\n\nnamespace { require_once __DIR__.'/autoload.php'; }\n\n".substr(file_get_contents($file), 5));
