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

/**
 * StepInterface.
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
interface StepInterface
{
    /**
     * __construct
     *
     * @param array $parameters
     */
    function __construct(array $parameters);

    /**
     * Returns the form used for configuration.
     *
     * @param FormContext $context
     * @return Symfony\Component\Form\Form
     */
    function getForm(FormContext $context);

    /**
     * Checks for requirements.
     *
     * @return array
     */
    function checkRequirements();

    /**
     * Checks for optional setting it could be nice to have.
     *
     * @return array
     */
    function checkOptionalSettings();

    /**
     * Returns the template to be renderer for this step.
     *
     * @return string
     */
    function getTemplate();

    /**
     * Updates form data parameters.
     *
     * @param array $parameters
     * @return array
     */
    function update(StepInterface $data);
}
