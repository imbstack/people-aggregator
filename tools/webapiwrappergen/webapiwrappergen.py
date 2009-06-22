#!/usr/bin/python2.4

import cgi, codecs, getopt, os.path, pprint, simplejson, sys, urllib, types, xmlrpclib, yaml, time

"""

How do you implement an API with multiple access methods?

XML-RPC:

 POST http://peopleaggregator.net/api/xmlrpc

 server.peopleaggregator.getUserProfile({
     'login': 'myloginname',
     })

 -> Content-Type: text/xml
    XML-RPC encoded struct:
    {'login': 'myloginname',
     'name': 'My Name',
     'userId': 1234,
     'url': 'http://peopleaggregator.net/user/1234',
     }

REST:

 GET http://peopleaggregator.net/api/xml/getUserProfile?login=myloginname

 -> Content-Type: application/xml
    <response>
     <success>true</success>
     <login>myloginname</login>
     <name>My Name</name>
     <userId>1234</userId>
     <url>http://peopleaggregator.net/user/1234</url>
    </response>

JSON:

 GET http://peopleaggregator.net/api/json/getUserProfile?login=myloginname

 -> Content-Type: application/x-javascript
    {login:'myloginname',name:'My Name',userId:1234,url:'http://peopleaggregator.net/user/1234'}

In a .api file, which can be parsed by this script, that would look like:

api:
 name: PeopleAggregator API
 methods:
 -
  peopleaggregator.getUserProfile:
   desc: Returns a user's profile information.
   args:
   -
    login:
     type: string
     desc: User's login name.
   return:
    type: hash
    content:
    -
     login:
      type: string
      desc: User's login name.
    -
     name:
      type: string
      desc: User's full name.
    -
     userId:
      type: int
      desc: User's database ID.
    -
     profileUrl:
      type: string
      desc: User's profile URL.

"""

class HtmlOutputConverter:
    def __init__(self, f):
        self.f = f
    def write(self, s):
        try:
            if type(s) == types.UnicodeType:
                u = s
            else:
                u = s.decode("utf-8")
            self.f.write(u.encode("ascii", "xmlcharrefreplace"))
        except:
            print "exception occurred trying to convert/write this:",`s`
            raise

def esc(s):
    return cgi.escape(s, 1)

def php_encode(d):
    typ = type(d)
    if typ == types.DictType:
        return 'array(%s)' % ','.join(['%s=>%s' % (php_encode(k), php_encode(v)) for k,v in d.items()])
    if typ == types.ListType:
        return 'array(%s)' % ','.join([php_encode(v) for v in d])
    if typ in (types.StringType, types.IntType):
        return `d`
    if typ == types.BooleanType:
        return d and 'TRUE' or 'FALSE'
    raise Exception("unknown type: %s" % typ)

def xml_wrap(content, outer, attrmap=None):
    attrs = attrmap and ''.join([' %s="%s"' % (k,v) for k,v in attrmap.items()]) or ''
    return '<%s%s>%s%s</%s>\n' % (outer, attrs, content.endswith("\n") and "\n" or "", content, outer)

def xml_encode(d, name=None):
    ret = ''
    attrs = {}

    if type(d) == types.DictType:
        for k,v in d.items():
            ret += xml_encode(v, k)
    elif type(d) == types.ListType:
        for v in d:
            ret += xml_wrap(xml_encode(v), "item")
    elif type(d) == types.BooleanType:
        ret = d and 'true' or 'false'
    elif type(d) == types.UnicodeType:
        ret = d.encode("ascii", "xmlcharrefreplace")
    else:
        ret = str(d)
    if name:
        ret = ret = xml_wrap(ret, name, attrs)
    return ret

def indent_xml(x):
    bits = esc(x).split("\n")
    ret = []
    indent = 0
    for bit in bits:
        op, cl = bit.count("&lt;"), bit.count("&lt;/")
        op -= cl
        if cl and not op: indent -= 1
        ret.append('&nbsp;'*indent + bit)
        if op and not cl: indent += 1
    return "<br>\n".join(ret)

def mkhttp(method, url, ct, content):
    first = '%s <a href="../..%s">%s</a> HTTP/1.1' % (method.upper(), url, url)

    if method == 'get':
        return first

    if not method: # it's a response
        first = 'HTTP/1.1 200 OK'
    else:
        assert method == 'post', "unknown method: %s" % method
                
    return first + '''<br>
Content-Type: %s<br>
Content-Length: %d<br>
<br>
%s''' % (ct, len(content), content)

class parse:
    def header(self, title):
        return '''<html><head>
<link rel="stylesheet" type="text/css" href="docs.css"/>
<title>%s: %s</title>
</head>
<body>
<div class="api"><h1><a href="%s.html">%s</a>: %s</h1>
''' % (self.name, title, self.ns, self.name, title)

    def footer(self):
        return '''</div><!-- api -->
</body>
</html>'''

    def parse_arg(self, out, context, name, arg):
        atype = arg['type']
        arg_out = {'type': atype} # for validation output
        print>>out, '<div class="%s">' % context
        if name: print>>out, '<h3>%s</h3>' % name
        print>>out, '<div class="detail">'
        print>>out, '<div class="type">Type: %s</div>' % atype
        if arg.has_key("desc"):
            desc = arg['desc']
            print>>out, '<div class="description">%s</div>' % desc

        if arg.has_key("default"):
            default = arg["default"]
        else:
            default = None

        if arg.has_key("optional"):
            arg_out["optional"] = arg["optional"]

        if atype == 'string':
            eg = arg['eg']
        elif atype == 'binary':
            eg = xmlrpclib.Binary("binary data goes here")
        elif atype == 'int':
            eg = int(arg['eg'])
            for k in ("min", "max"):
                if arg.has_key(k):
                    arg_out[k] = int(arg[k])
            if default is not None:
                default = int(default)
        elif atype == 'boolean':
            eg = arg['eg']
            if type(eg) == types.IntType: eg = eg and True or False
            if default is not None:
                default = default and True or False
            assert eg in (True, False), "invalid boolean: %s" % eg
        elif atype == 'hash':
            print>>out, '<h2 class="hash-content">Attributes:</h2>'
            eg = {}
            arg_out['allow_extra_keys'] = arg.get("allow_extra_keys", 0) and 1 or 0
            ct = arg_out['content'] = {}
            for attr in arg['content']:
                n, d = attr.items()[0]
                try:
                    eg[n], ct[n] = self.parse_arg(out, "attribute", n, d)
                except:
                    print "Error parsing hash key %s" % n
                    raise
        elif atype == 'array':
            print>>out, '<h2 class="array-content">Content of each item:</h2>'
            try:
                eg, arg_out['item'] = self.parse_arg(out, "item", "item", arg['item'])
                eg = [eg]
            except:
                print "Error parsing array item"
                raise
        elif atype == 'enum':
            arg_out['values'] = values = arg['values']
            print>>out, '<div class="enum-values">Allowed values: %s</div>' % ', '.join(['<code>%s</code>' % x for x in values])
            eg = arg['eg']
            assert eg in values, "example output is not a valid enum value: %s" % eg
        elif atype == 'datetime' or atype == 'date':
            eg = arg['eg']
        else:
            raise Exception("invalid variable type: %s" % atype)

        if default is not None:
            arg_out["default"] = default

        if arg.has_key("eg"):
            if type(eg) == types.UnicodeType:
#                print "encoding %s" % `eg`
                print_eg = eg.encode("ascii", "xmlcharrefreplace")
            else:
                print_eg = eg
            print>>out, '<div class="example">Example: <code>%s</code></div>' % print_eg
        if arg.has_key("min"):
            print>>out, '<div class="minimum">Minimum: <code>%s</code></div>' % arg['min']
        if arg.has_key("max"):
            print>>out, '<div class="maximum">Maximum: <code>%s</code></div>' % arg['max']
        if arg.has_key("default"):
            print>>out, '<div class="default">Default: <code>%s</code></div>' % arg['default']

        print>>out, '</div><!-- %s/detail -->' % context
        print>>out, '</div><!-- %s -->' % context

        return eg, arg_out

    def parse_method(self, name, method):

        f = "%s/doc/%s.html" % (self.outpath, name)
        print "Writing documentation for method %s to %s" % (name, f)
        out = open(f, "wt")
        out = HtmlOutputConverter(out)

        print>>out, self.header("method %s" % name)

        if method.has_key("alias"):
            # this method is an alias for another method
            alias = method['alias']
            print>>out, '<p>Alias for <a href="%s.html">%s</a>.</p>' % (alias, alias)
            print>>out, self.footer()
            return {'alias': alias}

        mtype = method['type']

        print>>out, '<p><a href="#arguments">Arguments</a> | <a href="#return">Return value</a> | <a href="#rest-xml">REST (XML) example</a> | <a href="#rest-json">REST (JSON) example</a> | <a href="#xml-rpc">XML-RPC example</a></p>'
        print>>out, '<div class="method">'
        print>>out, '<h2>Method: %s</h2>' % name
        print>>out, '<div class="detail">'
        print>>out, '<div class="description">%s</div>' % method['desc']
        print>>out, '<div class="doc-link"><a href="http://wiki.peopleaggregator.org/WSAPI_%s_method">User documentation for %s on the PeopleAggregator wiki</a></div>' % (name, name)
        print>>out, '<h3 id="arguments">Arguments (see also <a href="auth.php">Authentication</a>)</h3><div class="detail">'
        argstyle = method.get("argstyle", "named")
        args_desc = method['args']

        example = {}
        pos_example = []
        arg_order = []
        args_out = {} # for validation output

        if argstyle == "named":
            print>>out, '<p>If calling by XML-RPC, note that this method takes a single struct as input, with the following keys:</p>'
        elif argstyle == "positional":
            print>>out, '<p>If calling by XML-RPC, note that this method takes %d arguments, NOT a single struct, like most other methods in this API.</p>' % len(args_desc)
        else:
            raise Exception("invalid argstyle: %s" % argstyle)

#        print "ARGS_DESC:",args_desc
        # now process it
        for arg in args_desc:
            aname, arg = arg.items()[0]
            try:
                ex, args_out[aname] = self.parse_arg(out, 'argument', aname, arg)
                example[aname] = ex
                pos_example.append(ex)
                arg_order.append(aname)
            except:
                print "Error parsing argument %s to method %s.%s" % (aname, self.ns, name)
                raise
        print>>out, '</div><!-- detail -->'
        print>>out, '<h3 id="return">Return value (see also <a href="exceptions.php">Exceptions</a>)</h3>'
        try:
            ret_example, ret_out = self.parse_arg(out, 'return', '', method['return'])
        except:
            print "Error parsing return value for method %s.%s" % (self.ns, name)
            raise

        # now the examples

#        print "CALL",example
        ascii_example = example.copy()
        for k in ascii_example.keys():
            if type(ascii_example[k]) == types.UnicodeType:
                o = ascii_example[k].encode("iso-8859-1") #ascii", 'xmlcharrefreplace')
#                print "orig: %s; new: %s" % (`ascii_example[k]`, `o`)
                ascii_example[k] = o
        if mtype == 'get':
            example_url = '?'+urllib.urlencode(ascii_example)
            example_content = ''
        elif mtype == 'post':
            example_url = ''
            example_content = urllib.urlencode(ascii_example)
#        print "RETURN",ret_example

        # rest/xml

        print>>out, '<h3 id="rest-xml">Example REST (XML) request</h3>'
        print>>out, '<div class="detail">'
        print>>out, '<h4>Request</h4>'
        print>>out, '<div class="detail">'
        print>>out, '<code>%s</code>' % mkhttp(mtype, '/api/xml/%s%s' % (name.replace(".", "/"), example_url), 'application/x-www-form-urlencoded', example_content)
        print>>out, '</div><!-- detail -->'
        print>>out, '<h4>Response</h4>'
        print>>out, '<div class="detail">'
        print>>out, '<code>%s</code> ' % mkhttp('', '', 'application/xml',
                                                indent_xml(xml_encode({'response': ret_example})))
        print>>out, '</div><!-- detail -->'
        print>>out, '</div><!-- detail -->'

        # rest/json
        
        print>>out, '<h3 id="rest-json">Example REST (JSON) request</h3>'
        print>>out, '<div class="detail">'
        print>>out, '<h4>Request</h4>'
        print>>out, '<div class="detail">'
        print>>out, '<code>%s</code>' % mkhttp(mtype, '/api/json/%s%s' % (name.replace(".", "/"), example_url), 'application/x-www-form-urlencoded', example_content)
        print>>out, '</div><!-- detail -->'
        print>>out, '<h4>Response</h4>'
        print>>out, '<div class="detail">'
        print>>out, '<code>%s</code>' % mkhttp('', '', 'application/x-javascript',
                                               simplejson.dumps(ret_example))
        print>>out, '</div><!-- detail -->'
        print>>out, '</div><!-- detail -->'

        # xml-rpc
        
        print>>out, '<h3 id="xml-rpc">Example XML-RPC call</h3>'
        print>>out, '<div class="detail">'
        print>>out, '<h4>Request</h4>'
        print>>out, '<div class="detail">'
        if argstyle == 'named':
            xmlrpc_example = (example,)
        else:
            xmlrpc_example = tuple(pos_example)

#        print "XMLRPC CALL EXAMPLE:",`xmlrpc_example`
#        print "XMLRPC RET EXAMPLE:",`ret_example`
        print>>out, '<code>%s</code>' % mkhttp(
            'post', '/api/xmlrpc', 'text/xml',
            indent_xml(xmlrpclib.dumps(xmlrpc_example, methodname=name, encoding='utf-8')))
        print>>out, '</div><!-- detail -->'
        print>>out, '<h4>Response</h4>'
        print>>out, '<div class="detail">'
        print>>out, '<code>%s</code>' % mkhttp(
            '', '', 'text/xml',
            indent_xml(xmlrpclib.dumps((ret_example,), methodresponse=1, encoding='utf-8')))
        print>>out, '</div><!-- detail -->'
        print>>out, '</div><!-- detail -->'

        # all done with examples
        
        print>>out, '</div><!-- method/detail -->'
        print>>out, '</div><!-- method -->'
        print>>out, self.footer()

        ret = {
            'type': mtype,
            'args': args_out,
            'return': ret_out,
            'argstyle': argstyle,
            }
        if argstyle == 'positional':
            ret['argorder'] = arg_order
        return ret

    def parse_api(self, v):
        self.name = v['name']
        self.ns = v['namespace']
        f = "%s/doc/%s.html" % (self.outpath, self.ns)
        print "Writing top-level API doc file to %s" % f
        out = open(f, "wt")
        print>>out, self.header("documentation")
#        print>>out, '<div class="namespace">Namespace: %s</div>' % self.ns
        print>>out, '<p><a href="%s_api_desc.php.txt">API schema in PHP</a> | <a href="%s_api_desc.py.txt">API schema in Python</a></p>' % (self.ns, self.ns)
        methods = {}
        for method in v['methods']:
#            print>>sys.stderr, "method:", pprint.pformat(method)
            m, method = method.items()[0]
            try:
                methods[m] = self.parse_method(m, method)
            except:
                print "Error parsing method %s" % m
                raise
            print>>out, '<p>Method: <a href="%s.html">%s</a></p>' % (
                m,
                m,
                )
        print>>out, self.footer()

        self.api_desc = {
            'name': self.name,
#            'namespace': self.ns,
            'methods': methods,
            }
#        pprint.pprint(self.api_desc)

        fn = 'doc/%s_api_desc.php.txt' % self.ns
        f = "%s/%s" % (self.outpath, fn)
        print "Writing PHP API descriptor to %s" % f
        print>>open(f, "wt"), "<?php\n\n$api_desc = %s;\n\n?>" % php_encode(self.api_desc)

        f = "%s/doc/%s_api_desc.py.txt" % (self.outpath, self.ns)
        print "Writing Python API descriptor to %s" % f
        print>>open(f, "wt"), 'api_desc = %s' % `self.api_desc`

        f = "%s/doc/%s_api_desc.js" % (self.outpath, self.ns)
        print "Writing JSON API descriptor to %s" % f
        print>>open(f, "wt"), "%s_api_desc = %s" % (self.ns, simplejson.dumps(self.api_desc))

    def __init__(self, fn, outpath):
        self.fn = fn
        self.outpath = outpath
        
        print "Parsing %s, writing output to %s" % (fn, outpath)
        assert os.path.isdir(outpath), "%s does not exist - please create it" % outpath
        
        schema = yaml.load(open(fn).read())
        #pprint.pprint(schema)
        
        apis = []
        
        for k,v in schema.items():
            if k == 'api':
                apis.append(self.parse_api(v))
            else:
                print "unknown node at root level: %s" % k

if __name__ == '__main__':
    opts, args = getopt.getopt(sys.argv[1:], "o:")
    output_path = 'www/api'
    for k,v in opts:
        if k == '-o':
            output_path = v
        else:
            raise Exception("Unknown command line option %s" % k)

    apis = []
    for input_file in args:
        apis.append(parse(input_file, output_path).api_desc)
        
    f = os.path.join(output_path, "lib/api_desc.php")
    print "%d apis parsed - writing complete descriptor to %s" % (len(apis), f)
    all_apis = {
        'name': 'APIs built at %s: %s' % (time.ctime(), ", ".join([api['name'] for api in apis])),
        'methods': {},
        }
    for api in apis:
        all_apis['methods'].update(api['methods'])
    print>>open(f, "wt"), "<?php\n\n$api_desc = %s;\n\n?>" % php_encode(all_apis)
    
