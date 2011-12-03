<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\DistributionBundle\Configurator\Step;

use Sensio\Bundle\DistributionBundle\Configurator\Form\MailerStepType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Mailer Step.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MailerStep extends Step
{
    /**
     * @Assert\Choice(callback="getTransportKeys")
     */
    public $transport;

    /**
     * @Assert\NotBlank
     */
    public $host;

    /**
     * @Assert\Min(0)
     */
    public $port;

    /**
     * @Assert\NotBlank
     */
    public $user;

    public $password;

    public function __construct(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (0 === strpos($key, 'mailer_')) {
                $parameters[substr($key, 7)] = $value;
                $key = substr($key, 7);
                $this->$key = $value;
            }
        }
    }

    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new MailerStepType();
    }

    /**
     * @see StepInterface
     */
    public function checkRequirements()
    {
        $messages = array();

        if (!class_exists('\Swift_Mailer')) {
            $messages[] = 'Swiftmailer library is mandatory.';
        }

        return $messages;
    }

    /**
     * @see StepInterface
     */
    public function update(StepInterface $data)
    {
        $parameters = array();

        foreach ($data as $key => $value) {
            $parameters['mailer_'.$key] = $value;
        }

        return $parameters;
    }

    /**
     * @see StepInterface
     */
    public function getTitle()
    {
        return 'Mailer';
    }

    /**
     * @see StepInterface
     */
    public function getDescription()
    {
        return 'If your website needs a mailer connection, please configure it here.';
    }

    /**
     * @return array
     */
    static public function getTransportKeys()
    {
        return array_keys(static::getTransports());
    }

    /**
     * @return array
     */
    static public function getTransports()
    {
        return array(
            'smtp'     => 'SMTP',
            'gmail'    => 'GMail',
            'mail'     => 'PHP Mail',
            'sendmail' => 'Sendmail',
        );
    }
}
