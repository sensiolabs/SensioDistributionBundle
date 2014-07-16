SensioDistributionBundle
========================

SensioDistributionBundle is an add-on to the Symfony Standard Edition, which
hooks up into the Composer process to automate the following actions when
running an install or an update:

* Update the ``bootstrap.php.cache`` file (and clears the cache);

* Install the assets under the web root directory;

* Updated the requirements file;

* Install the Acme Demo bundle by setting the ``SENSIOLABS_FORCE_ACME_DEMO``
  environment variable to ``true`` (and only when creating a new project);

* Switch to the Symfony 3.0 directory structure by setting the
  ``SENSIOLABS_ENABLE_NEW_DIRECTORY_STRUCTURE`` environment variable to
  ``true`` (and only when creating a new project).

The bundle also provides a web configurator to ease the setup of a Symfony
project via a simple web interface.
