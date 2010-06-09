#!/bin/bash

function die {
    echo "$*"
    exit 1
}

ROOT=`dirname $0`

echo "Making distribution archive"
echo "reading version from $ROOT/version.sh"
source $ROOT/version.sh

pushd $ROOT/../..

SVN_URL=`svn info | grep URL | awk '{print $2}'`
SVN_REV=`svn info | grep Revision | awk '{print $2}'`
echo "svn url: $SVN_URL"
echo "svn revision: $SVN_REV"

# dirname for PA distribution.  tarball will be $PA_PATH.tar.gz, zip
# will be $PA_PATH.zip.

PA_PATH=peopleaggregator-$PA_VERSION-$1-$SVN_REV

echo "* flattening dist.tmp directory"
rm -rf dist.tmp
mkdir dist.tmp
pushd dist.tmp

echo "* exporting everything from subversion"
svn export $SVN_URL $PA_PATH

echo "* building dist_files.txt"
../pacore/script/build_dist_files_txt.py || die "couldn't build dist_files.txt!";

cp ../pacore/db/dist_files.txt $PA_PATH/pacore/db/dist_files.txt

echo "* running build script"
pushd $PA_PATH/pacore
./build.sh || die "build.sh failed";
popd # exit $PA_PATH

echo "* making distribution archive ($PA_PATH.tar.gz, $PA_PATH.zip)"
tar -czf ../$PA_PATH.tar.gz $PA_PATH
zip -r ../$PA_PATH.zip $PA_PATH

popd # exit dist.tmp

echo "* all done!"

popd # exit $ROOT/..
