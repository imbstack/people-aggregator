#!/bin/bash

# this script takes the current subversion repository and pushes it
# into the update.svn.broadbandmechanics.com/svn/release/ repository
# as a fully inlined 'import' under a properly named directory path

# get the version string for this version we are working with
source ../version.sh

# the URL of the repo where we want to store the release
REPO_URL=http://update.svn.broadbandmechanics.com/svn/release
REPO_PATH=$REPO_URL/peopleaggregator-$PA_VERSION

cd `dirname $0`
rm -rf current.export



pushd ../../..
SVN_URL=`svn info | grep URL | awk '{print $2}'`
echo "Exporting $SVN_URL"
svn export $SVN_URL pacore/script/official/current.export
svn info > pacore/script/official/current.export/pacore/db/source_svn_info.txt
META_FN=pacore/script/official/current.export/pacore/db/release_info.txt
echo "# Information about this release" > $META_FN
BUILD_DATE=`date`
OLD_REPO_VER=`svn info $REPO_URL | grep Revision | awk '{print $2}'`
REPO_VER=`expr $OLD_REPO_VER + 1`
echo "Build time: $BUILD_DATE" >> $META_FN

echo "Revision: $REPO_VER" >> $META_FN
popd

# run build.sh to get all docs and WS API files
pushd current.export/pacore
./build.sh
find . -name '*.pyc' | xargs rm -f
popd

# import this build into the SVN
svn import current.export $REPO_PATH -m "* importing release $PA_VERSION"
rm -rf current.export
