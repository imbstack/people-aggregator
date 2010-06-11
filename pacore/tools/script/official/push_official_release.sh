#!/bin/bash

# Script to build an official tarball to go up on http://update.peopleaggregator.org/

pushd `dirname $0`

echo "building official release"
./make_official_release.sh

echo "pushing official release up to update.peopleaggregator.org/dist"
ls dist.tmp > latest_releases.txt
mv latest_releases.txt dist.tmp/
scp dist.tmp/* $USER@update.peopleaggregator.org:/var/www/update.peopleaggregator.org/htdocs/dist/
