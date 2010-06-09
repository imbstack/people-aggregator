#!/bin/bash

pushd `dirname $0`

grep config_site_name -r ../.. | grep -vE "(svn|~)"
