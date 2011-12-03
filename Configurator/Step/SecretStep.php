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

use Sensio\Bundle\DistributionBundle\Configurator\Form\SecretStepType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Secret Step.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecretStep extends Step
{
    /**
     * @Assert\NotBlank
     */
    public $secret;

    public function __construct(array $parameters)
    {
        $this->secret = $parameters['secret'];

        if ('ThisTokenIsNotSoSecretChangeIt' == $this->secret) {
            $this->secret = hash('sha1', uniqid(mt_rand()));
        }
    }

    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new SecretStepType();
    }

    /**
     * @see StepInterface
     */
    public function update(StepInterface $data)
    {
        return array('secret' => $data->secret);
    }

    /**
     * @see StepInterface
     */
    public function getTemplate()
    {
        return 'SensioDistributionBundle:Configurator/Step:secret.html.twig';
    }

    /**
     * @see StepInterface
     */
    public function getTitle()
    {
        return 'Global Secret';
    }

    /**
     * @see StepInterface
     */
    public function getDescription()
    {
        return 'Configure the global secret for your website (the secret is used for the CSRF protection among other things):';
    }
}
