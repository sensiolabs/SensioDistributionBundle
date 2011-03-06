<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WebConfiguratorBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextField;

/**
 * CSRF Form.
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class CsrfForm extends Form
{
    public function configure()
    {
        $this->add(new TextField('csrf_secret'));
    }
}
