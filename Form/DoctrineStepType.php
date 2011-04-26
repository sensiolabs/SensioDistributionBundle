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

use Symfony\Bundle\WebConfiguratorBundle\Step\DoctrineStep;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * Doctrine Form Type.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineStepType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('driver', 'choice', array('choices' => DoctrineStep::getDrivers()))
            ->add('name', 'text')
            ->add('host', 'text')
            ->add('user', 'text')
            ->add('password', 'repeated', array(
                'required' => false,
                'type' => 'password',
                'first_name' => 'Password',
                'second_name' => 'Password again',
            ))
        ;
    }
}
