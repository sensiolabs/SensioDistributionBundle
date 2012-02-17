<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\DistributionBundle\Composer;

use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ScriptHandler
{
    public static function buildBootstrap($event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $appDir = $extra['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not build bootstrap file.'.PHP_EOL;
            return;
        }

        static::doBuildBootstrap($appDir);
    }

    public static function clearCache($event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $appDir = $extra['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not clear the cache.'.PHP_EOL;
            return;
        }

        static::executeCommand($appDir, 'cache:clear --no-warmup');
    }

    public static function installAssets($event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $appDir = $extra['symfony-app-dir'];
        $webDir = $extra['symfony-web-dir'];

        if (!is_dir($webDir)) {
            echo 'The symfony-web-dir ('.$webDir.') specified in composer.json was not found in '.getcwd().', can not install assets.'.PHP_EOL;
            return;
        }

        static::executeCommand($appDir, 'assets:install '.escapeshellarg($webDir));
    }

    public static function doBuildBootstrap($appDir)
    {
        $file = $appDir.'/bootstrap.php.cache';
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
    }

    protected static function executeCommand($appDir, $cmd)
    {
        $phpFinder = new PhpExecutableFinder;
        $php = escapeshellcmd($phpFinder->find());
        $console = escapeshellarg($appDir.'/console');

        $process = new Process($php.' '.$console.' '.$cmd);
        $process->run(function ($type, $buffer) { echo $buffer; });
    }
}
