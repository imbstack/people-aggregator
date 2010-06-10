#!/bin/bash

# this script takes the current subversion repository and pushes it
# into the update.peopleaggregator.org/svn/testing/pa repository

# note: requires svn_load_dirs.pl to be in the path.
REPO_URL=http://update.peopleaggregator.org/svn/testing
INFO_REPO_URL=http://update.peopleaggregator.org/svn/release

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
echo "Repository: $INFO_REPO_URL" >> $META_FN
echo "Revision: $REPO_VER" >> $META_FN
popd
pushd current.export/pacore
./build.sh
find . -name '*.pyc' | xargs rm -f
popd
svn_load_dirs.pl $REPO_URL pa current.export
rm -rf current.export
