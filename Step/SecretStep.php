<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WebConfiguratorBundle\Step;

use Symfony\Bundle\WebConfiguratorBundle\Exception\StepRequirementException;
use Symfony\Bundle\WebConfiguratorBundle\Form\SecretStepType;

/**
 * Secret Step.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecretStep implements StepInterface
{
    /**
     * @assert:NotBlank
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
    public function checkRequirements()
    {
        return array();
    }

    /**
     * checkOptionalSettings
     */
    public function checkOptionalSettings()
    {
        return array();
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
        return 'SymfonyWebConfiguratorBundle:Step:secret.html.twig';
    }
}
