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
system(str_replace(array('$dir'), array(escapeshellarg($baseDir)), '
    rm -rf /tmp/Symfony;
    mkdir /tmp/Symfony;
    cp -r $dir/* /tmp/Symfony;
    cd /tmp/Symfony;
    sudo rm -rf vendor app/cache/* app/logs/* .git* .DS_Store;
    chmod 777 app/cache app/logs
'));

// create build folder
if (!is_dir($baseDir.'/build')) {
    mkdir($baseDir.'/build');
}

// generate
system(str_replace(array('$dir', '$version'), array(escapeshellarg($baseDir), $version), '
    cd /tmp;
    # avoid the creation of ._* files
    export COPY_EXTENDED_ATTRIBUTES_DISABLE=true;
    export COPYFILE_DISABLE=true;
    tar zcpf $dir/build/Symfony_Standard_$version.tgz Symfony;
    sudo rm -f $dir/build/Symfony_Standard_$version.zip;
    zip -rq $dir/build/Symfony_Standard_$version.zip Symfony
'));

// with vendors
if (!is_dir($baseDir.'/vendor')) {
    exit("The master vendor directory does not exist.\n");
}

system(str_replace(array('$dir'), array(escapeshellarg($baseDir)), '
    cd /tmp;
    rm -rf /tmp/vendor;
    mkdir /tmp/vendor;
    cp -r $dir/vendor/* /tmp/vendor
'));

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
system(str_replace(array('$dir', '$version'), array(escapeshellarg($baseDir), $version), '
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
    tar zcpf $dir/build/Symfony_Standard_Vendors_$version.tgz Symfony
    sudo rm -f $dir/build/Symfony_Standard_Vendors_$version.zip
    zip -rq $dir/build/Symfony_Standard_Vendors_$version.zip Symfony
    rm -rf /tmp/Symfony;
'));
