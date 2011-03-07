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
use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextField;
use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Form\PasswordField;
use Symfony\Component\Form\RepeatedField;
use Symfony\Component\Form\HiddenField;

/**
 * Doctrine Form.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineForm extends Form
{
    public function configure()
    {
        $this->add(new ChoiceField('driver', array('choices' => DoctrineStep::getDrivers())));
        $this->add(new TextField('name'));
        $this->add(new TextField('host'));
        $this->add(new TextField('user'));
        $this->add(new RepeatedField(new PasswordField('password', array('required' => false)), array('required' => false, 'first_key' => 'Password', 'second_key' => 'Again')));
    }
}
