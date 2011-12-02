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

/**
 * Step.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
abstract class Step implements StepInterface
{
    protected $title;
    protected $description;

    public function __construct(array $parameters)
    {
    }

    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return null;
    }

    /**
     * @see StepInterface
     */
    public function checkRequirements()
    {
        return array();
    }

    /**
     * @see StepInterface
     */
    public function checkOptionalSettings()
    {
        return array();
    }

    /**
     * @see StepInterface
     */
    public function getTemplate()
    {
        return 'SensioDistributionBundle:Configurator/Step:custom.html.twig';
    }

    /**
     * @see StepInterface
     */
    public function update(StepInterface $data)
    {
        return array();
    }

    /**
     * Gets step title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->name;
    }

    /**
     * Gets step description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
