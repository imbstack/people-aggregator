#!/bin/bash

# Script to run webapiwrappergen/webapiwrappergen.py to build the API
# descriptor (web/api/lib/api_desc.php) and documentation
# (web/api/doc/*), then run api_test/test_api.py to test it all.

ERR=no

pushd webapiwrappergen
./build.sh || ERR=yes
popd

if [ "$ERR" == "yes" ]; then
    echo "webapiwrappergen failed - exiting"
    exit 1;
fi

pushd api_test
./test_api.py || ERR=yes
popd

if [ "$ERR" == "yes" ]; then
    echo "test_api failed - exiting"
    exit 1;
fi
