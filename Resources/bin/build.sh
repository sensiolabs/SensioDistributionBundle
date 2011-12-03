#!/bin/sh

# This file is part of the Symfony package.
#
# (c) Fabien Potencier <fabien@symfony.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.

DIR=`php -r "echo realpath(dirname(\\$_SERVER['argv'][0]));"`
cd $DIR
VERSION=`grep 'VERSION' vendor/symfony/src/Symfony/Component/HttpKernel/Kernel.php | sed -E "s/.*'(.+)'.*/\1/g"`
APP=${1:-Symfony}

if [ ! -d "$DIR/build" ]; then
    mkdir -p $DIR/build
fi

$DIR/vendor/bundles/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php
$DIR/app/console assets:install web/

# without vendors
rm -rf /tmp/$APP;
mkdir /tmp/$APP;
cp -r $DIR/* /tmp/$APP;
cd /tmp/$APP;
sudo rm -rf build vendor app/cache/* app/logs/* .git* .DS_Store;
chmod 777 app/cache app/logs

# DS_Store cleanup
find . -name .DS_Store | xargs rm -rf -

cd ..
# avoid the creation of ._* files
export COPY_EXTENDED_ATTRIBUTES_DISABLE=true
export COPYFILE_DISABLE=true
tar zcpf $DIR/build/${APP}_$VERSION.tgz $APP
sudo rm -f $DIR/build/${APP}_$VERSION.zip
zip -rq $DIR/build/${APP}_$VERSION.zip $APP

# with vendors
rm -rf /tmp/vendor;
mkdir /tmp/vendor;
TARGET=/tmp/vendor;

if [ ! -d "$DIR/vendor" ]; then
echo "The master vendor directory does not exist"
exit
fi

cp -r $DIR/vendor/* $TARGET/

# parse dept.ini and cleanup vendors
DEPS=("$DIR/vendor/bundles/Sensio/Bundle/DistributionBundle/deps" "$DIR/deps")
for deps in "${DEPS[@]}"; do
    if [ -r "$deps" ]; then
        exec < "$deps"
        while read section; do
            if [[ "$section" =~ \[.*\] ]]; then
                section=`echo $section | sed 's/.\(.*\)./\1/g'`
                eval `sed -e 's/[[:space:]]*\=[[:space:]]*/=/g' \
                          -e 's/;.*$//' \
                          -e 's/[[:space:]]*$//' \
                          -e 's/^[[:space:]]*//' \
                          -e "s/^\(.*\)=\([^\"']*\)$/\1=\"\2\"/" \
                          < "$deps" \
                          | sed -n -e "/^\[$section\]/,/^\s*\[/{/^[^;].*\=.*/p;}"`
                if [ ${remove+0} ]; then
                    if [ ${target+0} ]; then
                       target="$TARGET/$target"
                    else
                       target="$TARGET/$section"
                    fi
                    if [ -r "$target" ]; then
                        cd $target && rm -rf $remove
                    fi
                fi
                unset remove
                unset target
            fi
        done
    fi
done

# cleanup
find $TARGET -name .git | xargs rm -rf -
find $TARGET -name .gitignore | xargs rm -rf -
find $TARGET -name .gitmodules | xargs rm -rf -
find $TARGET -name .svn | xargs rm -rf -

cd /tmp/
mv /tmp/vendor /tmp/$APP/
tar zcpf $DIR/build/${APP}_Vendors_$VERSION.tgz $APP
sudo rm -f $DIR/build/${APP}_Vendors_$VERSION.zip
zip -rq $DIR/build/${APP}_Vendors_$VERSION.zip $APP

rm -rf /tmp/$APP;
