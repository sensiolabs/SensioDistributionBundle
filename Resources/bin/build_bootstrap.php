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
require_once __DIR__.'/../../Composer/ScriptHandler.php';

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array('Symfony' => $baseDir.'/vendor/symfony/src'));
$loader->register();

ScriptHandler::doBuildBootstrap($baseDir.'/app');
