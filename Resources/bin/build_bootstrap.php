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

$argv = $_SERVER['argv'];

// allow the base path to be passed as the first argument, or default
if (isset($argv[1])) {
    $baseDir = $argv[1];
} else {
    if (!$baseDir = realpath(__DIR__.'/../../../../../../..')) {
        exit('Looks like you don\'t have a standard layout.');
    }
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
    'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface',
    // Cannot be included because annotations will parse the big compiled class file
    //'Symfony\\Component\\DependencyInjection\\ContainerAware',
    'Symfony\\Component\\DependencyInjection\\ContainerInterface',
    'Symfony\\Component\\DependencyInjection\\Container',
    'Symfony\\Component\\HttpKernel\\HttpKernelInterface',
    'Symfony\\Component\\HttpKernel\\KernelInterface',
    'Symfony\\Component\\HttpKernel\\Kernel',
    'Symfony\\Component\\ClassLoader\\ClassCollectionLoader',
    'Symfony\\Component\\ClassLoader\\UniversalClassLoader',
    'Symfony\\Component\\HttpKernel\\Bundle\\Bundle',
    'Symfony\\Component\\HttpKernel\\Bundle\\BundleInterface',
    'Symfony\\Component\\Config\\ConfigCache',
    // cannot be included as commands are discovered based on the path to this class via Reflection
    //'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
), dirname($file), basename($file, '.php.cache'), false, false, '.php.cache');

file_put_contents($file, "<?php\n\nnamespace { require_once __DIR__.'/autoload.php'; }\n\n".substr(file_get_contents($file), 5));
