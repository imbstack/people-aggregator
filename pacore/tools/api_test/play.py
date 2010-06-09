# Import this when playing with the API in Python:

# from play import *

# then you can call the API with xr.peopleaggregator.whatever({'foo': 'bar', ...})

import os, os.path, sys, xmlrpclib

HERE = os.path.dirname(os.path.abspath(__file__))
print "here: %s" % HERE

# grab simplejson from webapiwrappergen directory
sys.path.insert(0, os.path.join(HERE, '../webapiwrappergen'))
import simplejson

# grab local_config from api/Python
sys.path.insert(0, os.path.join(HERE, '../../api/Python'))
import local_config

# parse config file and find rpc url
config_vars = local_config.read_local_config(os.path.join(HERE, "../../local_config.php"))

if not config_vars.has_key("base_url"):
    raise Exception("""Can't find a line in local_config.php that looks like: $base_url = "http://something";""")
base_url = config_vars['base_url']
base_url = base_url.replace("%network_name%", "www")
print "Base URL: %s" % base_url

xmlrpc_url = '%s/api/xmlrpc' % base_url

xr = xmlrpclib.Server(xmlrpc_url, verbose=1)
