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

use Symfony\Component\Form\FormContext;
use Symfony\Bundle\WebConfiguratorBundle\Form\CsrfForm;
use Symfony\Bundle\WebConfiguratorBundle\Exception\StepRequirementException;

/**
 * Csrf Step.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CsrfStep implements StepInterface
{
    /**
     * @validation:NotBlank
     */
    public $csrf_secret;

    public function __construct(array $parameters)
    {
        $this->csrf_secret = $parameters['csrf_secret'];
    }

    /**
     * @see StepInterface
     */
    public function getForm(FormContext $context)
    {
        return CsrfForm::create($context, 'config');
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
        return array('csrf_secret' => $data->csrf_secret);
    }

    /**
     * @see StepInterface
     */
    public function getTemplate()
    {
        return 'SymfonyWebConfiguratorBundle:Step:csrf.html.twig';
    }
}
