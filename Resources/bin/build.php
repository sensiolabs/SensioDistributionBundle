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

// define script parameters
$VERSION = '$VERSION';
$TARGET  = '$TARGET';

// php on windows can't use the shebang line from system()
$interpreter = PHP_OS == 'WINNT' ? 'php.exe' : '';

// update the bootstrap files
system(sprintf('%s %s', $interpreter, escapeshellarg($baseDir.'/vendor/bundles/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php')));

// update assets
system(sprintf('%s %s assets:install %s', $interpreter, escapeshellarg($baseDir.'/app/console'), escapeshellarg($baseDir.'/web/')));

// vendors
$scripts = array();
$deps = parse_ini_file($baseDir.'/deps', true, INI_SCANNER_RAW);
foreach ($deps as $name => $dep) {
    if (!isset($dep['remove'])) {
        continue;
    }

    // install dir
    $installDir = isset($dep['target']) ? $TARGET.'/'.$dep['target'] : $TARGET.'/'.$name;

    $scripts[] = sprintf('cd %s && rm -rf %s', $installDir, $dep['remove']);
}
$scripts = implode(";\n", $scripts);

// create script
$command = <<<EOF

VERSION=`grep 'VERSION' vendor/symfony/src/Symfony/Component/HttpKernel/Kernel.php | sed -E "s/.*'(.+)'.*/\1/g"`

if [ ! -d "{$baseDir}/build" ]; then
    mkdir -p {$baseDir}/build
fi

# without vendors
rm -rf /tmp/Symfony;
mkdir /tmp/Symfony;
cp -r {$baseDir}/* /tmp/Symfony;
cd /tmp/Symfony;
sudo rm -rf build vendor app/cache/* app/logs/* .git* .DS_Store;
chmod 777 app/cache app/logs

# DS_Store cleanup
find . -name .DS_Store | xargs rm -rf -

cd ..
# avoid the creation of ._* files
export COPY_EXTENDED_ATTRIBUTES_DISABLE=true
export COPYFILE_DISABLE=true
tar zcpf {$baseDir}/build/Symfony_Standard_{$VERSION}.tgz Symfony
sudo rm -f {$baseDir}/build/Symfony_Standard_{$VERSION}.zip
zip -rq {$baseDir}/build/Symfony_Standard_{$VERSION}.zip Symfony

# with vendors
rm -rf /tmp/vendor;
mkdir /tmp/vendor;
TARGET=/tmp/vendor;

if [ ! -d "{$baseDir}/vendor" ]; then
    echo "The master vendor directory does not exist"
    exit
fi

cp -r {$baseDir}/vendor/* {$TARGET}/

{$scripts}

# cleanup
find {$TARGET} -name .git | xargs rm -rf -
find {$TARGET} -name .gitignore | xargs rm -rf -
find {$TARGET} -name .gitmodules | xargs rm -rf -
find {$TARGET} -name .svn | xargs rm -rf -

cd /tmp/
mv /tmp/vendor /tmp/Symfony/
tar zcpf {$baseDir}/build/Symfony_Standard_Vendors_{$VERSION}.tgz Symfony
sudo rm -f {$baseDir}/build/Symfony_Standard_Vendors_{$VERSION}.zip
zip -rq {$baseDir}/build/Symfony_Standard_Vendors_{$VERSION}.zip Symfony

rm -rf /tmp/Symfony;
EOF;

system($command);
