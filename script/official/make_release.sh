#!/bin/bash

# get the version string for this version we are working with
source ../version.sh

# REPOS=http://update.peopleaggregator.org/svn/$1/pa

REPO_URL=http://update.svn.broadbandmechanics.com/svn/$1
REPOS=$REPO_URL/peopleaggregator-$PA_VERSION


if [ -n "$2" ]; then
    REV="-r $2";
    echo "using revision $2"
else
    REV=""
fi

echo "making release from repository $REPOS"

ROOT=`dirname $0`
pushd $ROOT

rm -rf repos.tmp dist.tmp
rm -rf dist.tmp
mkdir dist.tmp

echo "checking out the update repository"
svn checkout $REV $REPOS repos.tmp

echo "now making distribution."
repos.tmp/pacore/script/make_dist.sh $1 || die "make_dist.sh failed"

echo "pulling out the dist files"
ls -al
mv repos.tmp/*.tar.gz dist.tmp/
mv repos.tmp/*.zip dist.tmp/

rm -rf repos

popd

echo "output files are in $ROOT/dist.tmp"
