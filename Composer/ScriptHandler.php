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
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not build bootstrap file.'.PHP_EOL;

            return;
        }

        static::executeBuildBootstrap($appDir, $options['process-timeout']);
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
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not clear the cache.'.PHP_EOL;

            return;
        }

        static::executeCommand($event, $appDir, 'cache:clear --no-warmup', $options['process-timeout']);
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
            echo 'The symfony-web-dir ('.$webDir.') specified in composer.json was not found in '.getcwd().', can not install assets.'.PHP_EOL;

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
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not install the requirements file.'.PHP_EOL;

            return;
        }

        copy(__DIR__.'/../Resources/skeleton/app/SymfonyRequirements.php', $appDir.'/SymfonyRequirements.php');
        copy(__DIR__.'/../Resources/skeleton/app/check.php', $appDir.'/check.php');

        $webDir = $options['symfony-web-dir'];

        if (is_file($webDir.'/config.php')) {
            copy(__DIR__.'/../Resources/skeleton/web/config.php', $webDir.'/config.php');
        }
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
            'Symfony\\Component\\DependencyInjection\\Container',
            'Symfony\\Component\\HttpKernel\\Kernel',
            'Symfony\\Component\\ClassLoader\\ClassCollectionLoader',
            'Symfony\\Component\\ClassLoader\\ApcClassLoader',
            'Symfony\\Component\\HttpKernel\\Bundle\\Bundle',
            'Symfony\\Component\\Config\\ConfigCache',
            'Symfony\\Bundle\\FrameworkBundle\\HttpKernel',
            // cannot be included as commands are discovered based on the path to this class via Reflection
            //'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
            'Assetic\\Factory\\AssetFactory',
            'Assetic\\ValueSupplierInterface',
            'Doctrine\\Bundle\\DoctrineBundle\\DataCollector\\DoctrineDataCollector',
            'Doctrine\\Bundle\\DoctrineBundle\\Twig\\DoctrineExtension',
            'Doctrine\\Common\\Lexer',
            'Doctrine\\Common\\Persistence\\AbstractManagerRegistry',
            'Doctrine\\Common\\Persistence\\ConnectionRegistry',
            'Doctrine\\Common\\Persistence\\ManagerRegistry',
            'Doctrine\\Common\\Persistence\\Proxy',
            'Doctrine\\Common\\Util\\ClassUtils',
            'JMS\\SecurityExtraBundle\\Security\\Authorization\\Expression\\ContainerAwareExpressionHandler',
            'JMS\\SecurityExtraBundle\\Security\\Authorization\\Expression\\ExpressionHandlerInterface',
            'JMS\\SecurityExtraBundle\\Security\\Authorization\\Expression\\ExpressionVoter',
            'JMS\\SecurityExtraBundle\\Security\\Authorization\\Expression\\LazyLoadingExpressionVoter',
            'JMS\\SecurityExtraBundle\\Security\\Authorization\\RememberingAccessDecisionManager',
            'JMS\\SecurityExtraBundle\\Twig\\SecurityExtension',
            'Monolog\\Handler\\FingersCrossed\\ActivationStrategyInterface',
            'Monolog\\Handler\\FingersCrossed\\ErrorLevelActivationStrategy',
            'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ConfigurationAnnotation',
            'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ConfigurationInterface',
            'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter',
            'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\CacheListener',
            'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ControllerListener',
            'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ParamConverterListener',
            'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\TemplateListener',
            'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DateTimeParamConverter',
            'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DoctrineParamConverter',
            'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterInterface',
            'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterManager',
            'Symfony\\Bridge\\Doctrine\\DataCollector\\DoctrineDataCollector',
            'Symfony\\Bridge\\Doctrine\\ManagerRegistry',
            'Symfony\\Bridge\\Doctrine\\RegistryInterface',
            'Symfony\\Bridge\\Twig\\Form\\TwigRendererEngineInterface',
            'Symfony\\Bridge\\Twig\\Form\\TwigRendererInterface',
            'Symfony\\Bridge\\Twig\\NodeVisitor\\TranslationNodeVisitor',
            'Symfony\\Bundle\\AsseticBundle\\DefaultValueSupplier',
            'Symfony\\Bundle\\AsseticBundle\\Factory\\AssetFactory',
            'Symfony\\Bundle\\FrameworkBundle\\DataCollector\\RouterDataCollector',
            'Symfony\\Bundle\\FrameworkBundle\\Fragment\\ContainerAwareHIncludeFragmentRenderer',
            'Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface',
            'Symfony\\Bundle\\SecurityBundle\\DataCollector\\SecurityDataCollector',
            'Symfony\\Bundle\\SecurityBundle\\Templating\\Helper\\LogoutUrlHelper',
            'Symfony\\Bundle\\SecurityBundle\\Twig\\Extension\\LogoutUrlExtension',
            'Symfony\\Bundle\\SwiftmailerBundle\\EventListener\\EmailSenderListener',
            'Symfony\\Component\\DependencyInjection\\ParameterBag\\FrozenParameterBag',
            'Symfony\\Component\\DependencyInjection\\ParameterBag\\ParameterBag',
            'Symfony\\Component\\DependencyInjection\\ParameterBag\\ParameterBagInterface',
            'Symfony\\Component\\Form\\AbstractRendererEngine',
            'Symfony\\Component\\Form\\Extension\\Csrf\\CsrfProvider\\CsrfProviderInterface',
            'Symfony\\Component\\Form\\Extension\\Csrf\\CsrfProvider\\DefaultCsrfProvider',
            'Symfony\\Component\\Form\\Extension\\Csrf\\CsrfProvider\\SessionCsrfProvider',
            'Symfony\\Component\\Form\\FormRenderer',
            'Symfony\\Component\\Form\\FormRendererEngineInterface',
            'Symfony\\Component\\Form\\FormRendererInterface',
            'Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBag',
            'Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface',
            'Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBag',
            'Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface',
            'Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface',
            'Symfony\\Component\\HttpFoundation\\Session\\Storage\\MetadataBag',
            'Symfony\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector',
            'Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector',
            'Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface',
            'Symfony\\Component\\HttpKernel\\DataCollector\\EventDataCollector',
            'Symfony\\Component\\HttpKernel\\DataCollector\\ExceptionDataCollector',
            'Symfony\\Component\\HttpKernel\\DataCollector\\LoggerDataCollector',
            'Symfony\\Component\\HttpKernel\\DataCollector\\MemoryDataCollector',
            'Symfony\\Component\\HttpKernel\\DataCollector\\RequestDataCollector',
            'Symfony\\Component\\HttpKernel\\DataCollector\\RouterDataCollector',
            'Symfony\\Component\\HttpKernel\\DataCollector\\TimeDataCollector',
            'Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener',
            'Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener',
            'Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener',
            'Symfony\\Component\\HttpKernel\\EventListener\\StreamedResponseListener',
            'Symfony\\Component\\HttpKernel\\Exception\\FlattenException',
            'Symfony\\Component\\HttpKernel\\Fragment\\FragmentHandler',
            'Symfony\\Component\\HttpKernel\\Fragment\\FragmentRendererInterface',
            'Symfony\\Component\\HttpKernel\\Fragment\\HIncludeFragmentRenderer',
            'Symfony\\Component\\HttpKernel\\Fragment\\InlineFragmentRenderer',
            'Symfony\\Component\\HttpKernel\\Fragment\\RoutableFragmentRenderer',
            'Symfony\\Component\\HttpKernel\\Profiler\\FileProfilerStorage',
            'Symfony\\Component\\HttpKernel\\Profiler\\Profiler',
            'Symfony\\Component\\HttpKernel\\Profiler\\ProfilerStorageInterface',
            'Symfony\\Component\\HttpKernel\\UriSigner',
            'Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationTrustResolver',
            'Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationTrustResolverInterface',
            'Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface',
            'Symfony\\Component\\Security\\Core\\Authentication\\Provider\\DaoAuthenticationProvider',
            'Symfony\\Component\\Security\\Core\\Authentication\\Provider\\UserAuthenticationProvider',
            'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AuthenticatedVoter',
            'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\RoleHierarchyVoter',
            'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\RoleVoter',
            'Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactory',
            'Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactoryInterface',
            'Symfony\\Component\\Security\\Core\\Role\\RoleHierarchy',
            'Symfony\\Component\\Security\\Core\\Role\\RoleHierarchyInterface',
            'Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface',
            'Symfony\\Component\\Security\\Core\\User\\InMemoryUserProvider',
            'Symfony\\Component\\Security\\Core\\User\\User',
            'Symfony\\Component\\Security\\Core\\User\\UserChecker',
            'Symfony\\Component\\Security\\Core\\User\\UserCheckerInterface',
            'Symfony\\Component\\Security\\Core\\User\\UserInterface',
            'Symfony\\Component\\Security\\Http\\RememberMe\\RememberMeServicesInterface',
            'Symfony\\Component\\Security\\Http\\RememberMe\\ResponseListener',
            'Symfony\\Component\\Templating\\EngineInterface',
            'Symfony\\Component\\Templating\\Helper\\Helper',
            'Symfony\\Component\\Templating\\Helper\\HelperInterface',
            'Symfony\\Component\\Templating\\StreamingEngineInterface',
            'Symfony\\Component\\Translation\\IdentityTranslator',
            'Symfony\\Component\\Translation\\MessageSelector',
            'Symfony\\Component\\Translation\\TranslatorInterface',
            'Twig_ExistsLoaderInterface',
            'Twig_NodeVisitorInterface',
            'Assetic\\Extension\\Twig\\ValueContainer',
            'Twig_Extension_Staging',
            'Twig_Error',
            'Assetic\\Extension\\Twig\\AsseticExtension',
            'Doctrine\\Common\\Annotations\\Reader',
            'Doctrine\\Common\\Annotations\\DocLexer',
            'Doctrine\\Common\\Annotations\\PhpParser',
            'Symfony\\Bundle\\TwigBundle\\Loader\\FilesystemLoader',
            'Symfony\\Bridge\\Twig\\Form\\TwigRendererEngine',
            'Symfony\Bridge\Twig\Form\TwigRenderer',
            'Symfony\\Bundle\\AsseticBundle\\Twig\\AsseticExtension',
            'Symfony\\Bridge\\Twig\\TwigEngine',
            'Symfony\\Bundle\\TwigBundle\\TwigEngine',
            'Symfony\\Bridge\\Twig\\Extension\\CodeExtension',
            'Symfony\\Bridge\\Twig\\Extension\\RoutingExtension',
            'Symfony\\Bridge\\Twig\\Extension\\YamlExtension',
            'Symfony\\Bridge\\Twig\\Extension\\HttpKernelExtension',
            'Symfony\\Bundle\\TwigBundle\\Extension\\AssetsExtension',
            'Symfony\\Bundle\\TwigBundle\\Extension\\ActionsExtension',
            'Symfony\\Bridge\\Twig\\Extension\\SecurityExtension',
            'Symfony\\Bridge\\Twig\\Extension\\TranslationExtension',
            'Doctrine\\Common\\Annotations\\FileCacheReader',
            'Doctrine\\Bundle\\DoctrineBundle\\Registry',
        ), dirname($file), basename($file, '.php.cache'), false, false, '.php.cache');

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
        $process->run(function ($type, $buffer) { echo $buffer; });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
        }
    }

    protected static function executeBuildBootstrap($appDir, $timeout = 300)
    {
        $php = escapeshellarg(self::getPhp());
        $cmd = escapeshellarg(__DIR__.'/../Resources/bin/build_bootstrap.php');
        $appDir = escapeshellarg($appDir);

        $process = new Process($php.' '.$cmd.' '.$appDir, null, null, null, $timeout);
        $process->run(function ($type, $buffer) { echo $buffer; });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('An error occurred when generating the bootstrap file.');
        }
    }

    protected static function getOptions(CommandEvent $event)
    {
        $options = array_merge(array(
            'symfony-app-dir' => 'app',
            'symfony-web-dir' => 'web',
            'symfony-assets-install' => 'hard'
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
