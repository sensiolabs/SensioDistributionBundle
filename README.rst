SensioDistributionBundle
========================

SensioDistributionBundle provides useful developer features that can be re-used
amongst several Symfony Distributions.

Composer Hooks
--------------

The bundle hooks up into the Composer process to automate the following actions
when running an install or an update:

* Update the ``bootstrap.php.cache`` file (and clears the cache);

* Install the assets under the web root directory;

* Updated the requirements file;

* Switch to the Symfony 3.0 directory structure by setting the
  ``SENSIOLABS_ENABLE_NEW_DIRECTORY_STRUCTURE`` environment variable to
  ``true`` (and only when creating a new project).

Web Configurator
----------------

The bundle provides a web configurator to ease the setup of a Symfony
project via a simple web interface.

Security
--------

The bundle includes the SensioLabs Security Checker. When included in a Symfony
application, the check is available:

.. code-block:: bash

    // In Symfony 2.x
    $ ./app/console security:check

    // As of Symfony 2.8 and 3.x
    $ ./bin/console security:check

Contributing
------------

To contribute to this bundle, you just need a GitHub account.
If you need some help to start, you can check the `Symfony guidelines`_ and `code style conventions`_.
Bug fixes should be submitted against the 4.0 branch when possible, and new features are accepted on master only.

Pull requests are welcome!

.. _Symfony guidelines: https://symfony.com/doc/current/contributing/code/patches.html
.. _code style conventions: https://symfony.com/doc/current/contributing/code/standards.html
