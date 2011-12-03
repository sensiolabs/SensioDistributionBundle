<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\DistributionBundle\Configurator\Form;

use Sensio\Bundle\DistributionBundle\Configurator\Step\MailerStep;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * Mailer Form Type.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MailerStepType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('transport', 'choice', array(
                'required' => false,
                'choices'  => MailerStep::getTransports(),
            ))
            ->add('host', 'text')
            ->add('port', 'text', array('required' => false))
            ->add('user', 'text')
            ->add('password', 'repeated', array(
                'required'        => false,
                'type'            => 'password',
                'first_name'      => 'password',
                'second_name'     => 'password_again',
                'invalid_message' => 'The password fields must match.',
            ))
        ;
    }

    public function getName()
    {
        return 'distributionbundle_mailer_step';
    }
}
