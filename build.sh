# master build script to build anything that needs to be built.

# note that all built files should be included in any distribution
# tarballs / zip files, but SHOULD NOT BE CHECKED INTO SOURCE CONTROL.

# ---

# build api descriptor and documentation
pushd tools/webapiwrappergen
./build.sh
popd
