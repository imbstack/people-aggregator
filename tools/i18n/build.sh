#!/bin/bash

pushd `dirname $0`

python compile_po.py

popd
