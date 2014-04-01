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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Composer\Script\CommandEvent;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ScriptHandler
{
    /**
     * Builds the bootstrap file.
     *
     * The bootstrap file contains PHP file that are always needed by the application.
     * It speeds up the application bootstrapping.
     *
     * @param $event CommandEvent A instance
     */
    public static function buildBootstrap(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            $event->getIO()->write(sprintf('The symfony-app-dir (%s) specified in composer.json was not found in %s, can not build bootstrap file.', $appDir, getcwd()));

            return;
        }

        static::executeBuildBootstrap($event, $appDir, $options['process-timeout']);
    }

    /**
     * Clears the Symfony cache.
     *
     * @param $event CommandEvent A instance
     */
    public static function clearCache(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            $event->getIO->write(sprintf('The symfony-app-dir (%s) specified in composer.json was not found in %s, can not clear the cache.', $appDir, getcwd()));

            return;
        }

        if (!$options['symfony-cache-warmup']) {
            $warmup = ' --no-warmup';
        }

        static::executeCommand($event, $appDir, 'cache:clear'.$warmup, $options['process-timeout']);
    }

    /**
     * Installs the assets under the web root directory.
     *
     * For better interoperability, assets are copied instead of symlinked by default.
     *
     * Even if symlinks work on Windows, this is only true on Windows Vista and later,
     * but then, only when running the console with admin rights or when disabling the
     * strict user permission checks (which can be done on Windows 7 but not on Windows
     * Vista).
     *
     * @param $event CommandEvent A instance
     */
    public static function installAssets(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];
        $webDir = $options['symfony-web-dir'];

        $symlink = '';
        if ($options['symfony-assets-install'] == 'symlink') {
            $symlink = '--symlink ';
        } elseif ($options['symfony-assets-install'] == 'relative') {
            $symlink = '--symlink --relative ';
        }

        if (!is_dir($webDir)) {
            $event->getIO->write(sprintf('The symfony-web-dir (%s) specified in composer.json was not found in %s, can not install assets.', $webDir, getcwd()));

            return;
        }

        static::executeCommand($event, $appDir, 'assets:install '.$symlink.escapeshellarg($webDir));
    }

    /**
     * Updated the requirements file.
     *
     * @param $event CommandEvent A instance
     */
    public static function installRequirementsFile(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            $event->getIO->write(sprintf('The symfony-app-dir (%s) specified in composer.json was not found in %s, can not install the requirements file.', $appDir, getcwd()));

            return;
        }

        copy(__DIR__.'/../Resources/skeleton/app/SymfonyRequirements.php', $appDir.'/SymfonyRequirements.php');
        copy(__DIR__.'/../Resources/skeleton/app/check.php', $appDir.'/check.php');

        $webDir = $options['symfony-web-dir'];

        // if the user has already removed the config.php file, do nothing
        // as the file must be removed for production use
        if (is_file($webDir.'/config.php')) {
            copy(__DIR__.'/../Resources/skeleton/web/config.php', $webDir.'/config.php');
        }
    }

    public static function installAcmeDemoBundle(CommandEvent $event)
    {
        $rootDir = __DIR__ . '/../../../../../../..';
        $options = self::getOptions($event);

        if (file_exists($rootDir.'/src/Acme/DemoBundle')) {
            return;
        }

        if (!$event->getIO()->askConfirmation('Would you like to install Acme demo bundle? [yes/NO] ', false)) {
            return;
        }

        $appDir = $options['symfony-app-dir'];

        $kernelFile = $appDir.'/AppKernel.php';

        $fs = new Filesystem();
        $fs->mirror(__DIR__.'/../Resources/skeleton/acme-demo-bundle', $rootDir.'/src', null, array('override'));

        $ref = '$bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();';
        $bundleDeclaration = "\$bundles[] = new Acme\\DemoBundle\\AcmeDemoBundle();";
        $content = file_get_contents($kernelFile);

        if (false === strpos($content, $bundleDeclaration)) {
            $updatedContent = str_replace($ref, $bundleDeclaration."\n            ".$ref, $content);
            if ($content === $updatedContent) {
                throw new \RuntimeException('Unable to patch %s.', $kernelFile);
            }
            $fs->dumpFile($kernelFile, $updatedContent);
        }

        self::patchAcmeDemoBundleConfiguration($appDir, $fs);
    }

    private static function patchAcmeDemoBundleConfiguration($appDir, Filesystem $fs)
    {
        $routingFile = $appDir.'/config/routing_dev.yml';
        $securityFile = $appDir.'/config/security.yml';

        $routingData = file_get_contents($routingFile).<<<EOF

# AcmeDemoBundle routes (to be removed)
_acme_demo:
    resource: "@AcmeDemoBundle/Resources/config/routing.yml"
EOF;
        $fs->dumpFile($routingFile, $routingData);

        $securityData = <<<EOF
security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    providers:
        in_memory:
            memory:
                users:
                    user:  { password: userpass, roles: [ 'ROLE_USER' ] }
                    admin: { password: adminpass, roles: [ 'ROLE_ADMIN' ] }

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        demo_login:
            pattern:  ^/demo/secured/login$
            security: false

        demo_secured_area:
            pattern:    ^/demo/secured/
            form_login:
                check_path: _demo_security_check
                login_path: _demo_login
            logout:
                path:   _demo_logout
                target: _demo
            #anonymous: ~
            #http_basic:
            #    realm: "Secured Demo Area"

    access_control:
        #- { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: https }
EOF;

        $fs->dumpFile($securityFile, $securityData);
    }

    public static function doBuildBootstrap($appDir)
    {
        $file = $appDir.'/bootstrap.php.cache';
        if (file_exists($file)) {
            unlink($file);
        }

        $classes = array(
            'Symfony\\Component\\HttpFoundation\\ParameterBag',
            'Symfony\\Component\\HttpFoundation\\HeaderBag',
            'Symfony\\Component\\HttpFoundation\\FileBag',
            'Symfony\\Component\\HttpFoundation\\ServerBag',
            'Symfony\\Component\\HttpFoundation\\Request',
            'Symfony\\Component\\HttpFoundation\\Response',
            'Symfony\\Component\\HttpFoundation\\ResponseHeaderBag',

            'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface',
            // Cannot be included because annotations will parse the big compiled class file
            //'Symfony\\Component\\DependencyInjection\\ContainerAware',
            'Symfony\\Component\\DependencyInjection\\Container',
            'Symfony\\Component\\HttpKernel\\Kernel',
            'Symfony\\Component\\ClassLoader\\ClassCollectionLoader',
            'Symfony\\Component\\ClassLoader\\ApcClassLoader',
            'Symfony\\Component\\HttpKernel\\Bundle\\Bundle',
            'Symfony\\Component\\Config\\ConfigCache',
            // cannot be included as commands are discovered based on the path to this class via Reflection
            //'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
        );

        // introspect the autoloader to get the right file
        // we cannot use class_exist() here as it would load the class
        // which won't be included into the cache then.
        // we know that composer autoloader is first (see bin/build_bootstrap.php)
        $autoloaders = spl_autoload_functions();
        if (is_array($autoloaders[0]) && method_exists($autoloaders[0][0], 'findFile') && $autoloaders[0][0]->findFile('Symfony\\Bundle\\FrameworkBundle\\HttpKernel')) {
            $classes[] = 'Symfony\\Bundle\\FrameworkBundle\\HttpKernel';
        } else {
            $classes[] = 'Symfony\\Component\\HttpKernel\\DependencyInjection\\ContainerAwareHttpKernel';
        }

        ClassCollectionLoader::load($classes, dirname($file), basename($file, '.php.cache'), false, false, '.php.cache');

        file_put_contents($file, sprintf("<?php

namespace { \$loader = require_once __DIR__.'/autoload.php'; }

%s

namespace { return \$loader; }
            ", substr(file_get_contents($file), 5)));
    }

    protected static function executeCommand(CommandEvent $event, $appDir, $cmd, $timeout = 300)
    {
        $php = escapeshellarg(self::getPhp());
        $console = escapeshellarg($appDir.'/console');
        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }

        $process = new Process($php.' '.$console.' '.$cmd, null, null, null, $timeout);
        $process->run(function ($type, $buffer) use ($event) { $event->getIO()->write($buffer, false); });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
        }
    }

    protected static function executeBuildBootstrap(CommandEvent $event, $appDir, $timeout = 300)
    {
        $php = escapeshellarg(self::getPhp());
        $cmd = escapeshellarg(__DIR__.'/../Resources/bin/build_bootstrap.php');
        $appDir = escapeshellarg($appDir);

        $process = new Process($php.' '.$cmd.' '.$appDir, null, null, null, $timeout);
        $process->run(function ($type, $buffer) use ($event) { $event->getIO()->write($buffer, false); });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('An error occurred when generating the bootstrap file.');
        }
    }

    protected static function getOptions(CommandEvent $event)
    {
        $options = array_merge(array(
            'symfony-app-dir' => 'app',
            'symfony-web-dir' => 'web',
            'symfony-assets-install' => 'hard',
            'symfony-cache-warmup' => false,
        ), $event->getComposer()->getPackage()->getExtra());

        $options['symfony-assets-install'] = getenv('SYMFONY_ASSETS_INSTALL') ?: $options['symfony-assets-install'];

        $options['process-timeout'] = $event->getComposer()->getConfig()->get('process-timeout');

        return $options;
    }

    protected static function getPhp()
    {
        $phpFinder = new PhpExecutableFinder;
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }
}
