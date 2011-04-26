<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WebConfiguratorBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\WebConfiguratorBundle\Exception\StepRequirementException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * ConfiguratorController.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ConfiguratorController extends ContainerAware
{
    /**
     * @return Response A Response instance
     */
    public function stepAction($index = 0)
    {
        $configurator = $this->container->get('symfony.webconfigurator');

        $step = $configurator->getStep($index);
        $form = $this->container->get('form.factory')->create($step->getFormType(), $step);

        $request = $this->container->get('request');
        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $configurator->mergeParameters($step->update($form->getData()));
                $configurator->write();

                $index++;

                if ($index < $configurator->getStepCount()) {
                    return new RedirectResponse($this->container->get('router')->generate('_configurator_step', array('index' => $index)));
                }

                return new RedirectResponse($this->container->get('router')->generate('_configurator_final'));
            }
        }

        return $this->container->get('templating')->renderResponse($step->getTemplate(), array(
            'form'    => $form->createView(),
            'index'   => $index,
            'count'   => $configurator->getStepCount(),
            'version' => file_get_contents($this->container->getParameter('kernel.root_dir').'/../VERSION'),
        ));
    }

    public function checkAction()
    {
        $configurator = $this->container->get('symfony.webconfigurator');

        $steps = $configurator->getSteps();

        $majors = array();
        $minors = array();

        // Trying to get as much requirements as possible
        foreach ($steps as $step) {
            foreach ($step->checkRequirements() as $major) {
                $majors[] = $major;
            }

            foreach ($step->checkOptionalSettings() as $minor) {
                $minors[] = $minor;
            }
        }

        $url = $this->container->get('router')->generate('_configurator_step', array('index' => 0));

        if (empty($majors) && empty($minors)) {
            return new RedirectResponse($url);
        }

        return $this->container->get('templating')->renderResponse('SymfonyWebConfiguratorBundle::check.html.twig', array(
            'majors'  => $majors,
            'minors'  => $minors,
            'url'     => $url,
            'version' => file_get_contents($this->container->getParameter('kernel.root_dir').'/../VERSION'),
        ));
    }

    public function finalAction()
    {
        $configurator = $this->container->get('symfony.webconfigurator');
        $configurator->clean();

        return $this->container->get('templating')->renderResponse('SymfonyWebConfiguratorBundle::final.html.twig', array(
            'parameters'  => $configurator->render(),
            'ini_path'    => $this->container->getParameter('kernel.root_dir').'/config/parameters.ini',
            'is_writable' => $configurator->isFileWritable(),
            'version'     => file_get_contents($this->container->getParameter('kernel.root_dir').'/../VERSION'),
        ));
    }
}
