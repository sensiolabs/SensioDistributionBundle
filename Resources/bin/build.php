#!/usr/bin/env php
<?php

/*
 * This file is part of the Symfony Standard Edition.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!$baseDir = realpath(__DIR__.'/../../../../../../..')) {
    exit('Looks like you don\'t have a standard layout.');
}

$version = system('grep \'VERSION\' vendor/symfony/src/Symfony/Component/HttpKernel/Kernel.php | sed -E "s/.*\'(.+)\'.*/\1/g"');

// php on windows can't use the shebang line from system()
$interpreter = PHP_OS == 'WINNT' ? 'php.exe' : '';

// update the bootstrap files
system(sprintf('%s %s', $interpreter, escapeshellarg($baseDir.'/vendor/bundles/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php')));

// update assets
system(sprintf('%s %s assets:install %s', $interpreter, escapeshellarg($baseDir.'/app/console'), escapeshellarg($rootDir.'/web/')));

// remove the cache
system(sprintf('%s %s cache:clear --no-warmup', $interpreter, escapeshellarg($baseDir.'/app/console')));

// without vendors
system(sprintf('
    rm -rf /tmp/Symfony;
    mkdir /tmp/Symfony;
    cp -r %s/* /tmp/Symfony;
    cd /tmp/Symfony;
    sudo rm -rf vendor app/cache/* app/logs/* .git* .DS_Store;
    chmod 777 app/cache app/logs
', escapeshellarg($baseDir)));

// create build folder
if (!is_dir($baseDir.'/build')) {
    mkdir($baseDir.'/build');
}

// generate
system(sprintf('
    cd /tmp;
    # avoid the creation of ._* files
    export COPY_EXTENDED_ATTRIBUTES_DISABLE=true;
    export COPYFILE_DISABLE=true;
    tar zcpf %s/build/Symfony_Standard_%s.tgz Symfony;
    sudo rm -f %s/build/Symfony_Standard_%s.zip;
    zip -rq %s/build/Symfony_Standard_%s.zip Symfony
', escapeshellarg($baseDir), $version, escapeshellarg($baseDir), $version, escapeshellarg($baseDir), $version));

// with vendors
if (!is_dir($baseDir.'/vendor')) {
    exit("The master vendor directory does not exist.\n");
}

system(sprintf('
    cd %s;
    rm -rf /tmp/vendor;
    mkdir /tmp/vendor;
    cp -r %s/vendor/* /tmp/vendor
', escapeshellarg($baseDir), escapeshellarg($baseDir)));

// remove from each vendor
$deps = parse_ini_file($baseDir.'/deps', true, INI_SCANNER_RAW);
foreach ($deps as $name => $dep) {
    if (!isset($dep['remove'])) {
        continue;
    }

    // install dir
    $installDir = isset($dep['target']) ? '/tmp/vendor/'.$dep['target'] : '/tmp/vendor/'.$name;

    system(sprintf('cd %s && rm -rf %s', escapeshellarg($installDir), escapeshellarg($dep['remove'])));
}

// generate
system(sprintf('
    cd /tmp;
    mv /tmp/vendor /tmp/Symfony/;
    cd /tmp/Symfony;
    find . -name .git | xargs rm -rf -;
    find . -name .gitignore | xargs rm -rf -;
    find . -name .gitmodules | xargs rm -rf -;
    find . -name .svn | xargs rm -rf -
    cd ..;
    # avoid the creation of ._* files
    export COPY_EXTENDED_ATTRIBUTES_DISABLE=true;
    export COPYFILE_DISABLE=true;
    tar zcpf %s/build/Symfony_Standard_Vendors_%s.tgz Symfony
    sudo rm -f %s/build/Symfony_Standard_Vendors_%s.zip
    zip -rq %s/build/Symfony_Standard_Vendors_%s.zip Symfony
    rm -rf /tmp/Symfony;
', escapeshellarg($baseDir), $version, escapeshellarg($baseDir), $version, escapeshellarg($baseDir), $version));
