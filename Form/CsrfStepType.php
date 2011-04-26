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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * CSRF Form Type.
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class CsrfStepType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('csrf_secret', 'text');
    }
}
