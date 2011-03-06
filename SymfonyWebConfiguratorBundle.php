<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WebConfiguratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Bundle\WebConfiguratorBundle\Step\DoctrineStep;
use Symfony\Bundle\WebConfiguratorBundle\Step\CsrfStep;

/**
 * SymfonyWebConfiguratorBundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class SymfonyWebConfiguratorBundle extends Bundle
{
    public function boot()
    {
        $configurator = $this->container->get('symfony.webconfigurator');
        $configurator->addStep(new DoctrineStep($configurator->getParameters()));
        $configurator->addStep(new CsrfStep($configurator->getParameters()));
    }
}
