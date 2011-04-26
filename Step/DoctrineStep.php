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
use Symfony\Bundle\WebConfiguratorBundle\Form\DoctrineStepType;

/**
 * Doctrine Step.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineStep implements StepInterface
{
    /**
     * @assert:Choice(callback="getDriverKeys")
     */
    public $driver;

    /**
     * @assert:NotBlank
     */
    public $host;

    /**
     * @assert:NotBlank
     */
    public $name;

    /**
     * @assert:NotBlank
     */
    public $user;

    public $password;

    public function __construct(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (0 === strpos($key, 'database_')) {
                $parameters[substr($key, 9)] = $value;
                $key = substr($key, 9);
                $this->$key = $value;
            }
        }
    }

    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new DoctrineStepType();
    }

    /**
     * @see StepInterface
     */
    public function checkRequirements()
    {
        $messages = array();

        if (!class_exists('\PDO')) {
            $messages[] = 'PDO extension is mandatory.';
        } else {
            $drivers = \PDO::getAvailableDrivers();
            if (0 == count($drivers)) {
                $messages[] = 'Please install PDO drivers.';
            }
        }

        return $messages;
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
    public function update(StepInterface $data)
    {
        $parameters = array();

        foreach ($data as $key => $value) {
            $parameters['database_'.$key] = $value;
        }

        return $parameters;
    }

    /**
     * @see StepInterface
     */
    public function getTemplate()
    {
        return 'SymfonyWebConfiguratorBundle:Step:doctrine.html.twig';
    }

    /**
     * @return array
     */
    static public function getDriverKeys()
    {
        return array_keys(static::getDrivers());
    }

    /**
     * @return array
     */
    static public function getDrivers()
    {
        return array(
            'pdo_mysql'  => 'MySQL (PDO)',
            'pdo_sqlite' => 'SQLite (PDO)',
            'pdo_pgsql'  => 'PosgreSQL (PDO)',
            'oci8'       => 'Oracle (native)',
            'ibm_db2'    => 'IBM DB2 (native)',
            'pdo_oci'    => 'Oracle (PDO)',
            'pdo_ibm'    => 'IBM DB2 (PDO)',
            'pdo_sqlsrv' => 'SQLServer (PDO)',
        );
    }
}
