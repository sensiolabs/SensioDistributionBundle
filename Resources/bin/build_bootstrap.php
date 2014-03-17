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

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;

if (PHP_SAPI !== 'cli') {
    echo 'Warning: '.__FILE__.' should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

function getRealpath($path, $message = 'Directory %s does not seem to be valid.')
{
    if (!$path = realpath($path)) {
        exit(sprintf($message, $path));
    }

    return $path;
}

$argv = $_SERVER['argv'];
$autoloadDir = $bootstrapDir = null;
$useNewDirectoryStructure = false;

// allow the base path to be passed as the first argument, or default
if (isset($argv[1])) {
    $bootstrapDir = getRealpath($argv[1]);
    if (isset($argv[2])) {
        $autoloadDir = getRealpath($argv[2]);
    }
    if (isset($argv[3])) {
        $useNewDirectoryStructure = true;
    }
}

if (null === $autoloadDir) {
    $autoloadDir = getRealpath(__DIR__.'/../../../../../../../../app', 'Looks like you don\'t have a standard layout.');
}
if (null === $bootstrapDir) {
    $bootstrapDir = $autoloadDir;
    if (file_exists(__DIR__.'/../../../../../../../../var/'.ScriptHandler::NEW_STRUCTURE_NOTIFIER)) {
        $bootstrapDir = getRealpath(__DIR__.'/../../../../../../../../var');
    }
}

require_once $autoloadDir.'/autoload.php';

// here we pass realpaths as resolution between absolute and relative path can be wrong
ScriptHandler::doBuildBootstrap($bootstrapDir, $autoloadDir, $useNewDirectoryStructure);
