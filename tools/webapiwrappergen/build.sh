 
#!/bin/bash

P=`dirname $0`
echo "Building PA Core API" 
echo "--------------------"
python $P/webapiwrappergen.py -o $P/../../web/api $P/*.api || exit 1

if [ -d "$P/../../../paproject" ]; then
echo "Building PA Project API" 
echo "-----------------------"
  python $P/webapiwrappergen.py -o $P/../../../paproject/web/api $P/../../../paproject/tools/webapiwrappergen/*.api || exit 1
fi
