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

use Symfony\Component\Form\Type\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * CSRF Form Type.
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class CsrfType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('csrf_secret', 'text');
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Symfony\Bundle\WebConfiguratorBundle\Step\CsrfStep',
        );
    }
}
