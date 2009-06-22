#!/usr/bin/python

import getopt, md5, os.path, pprint, random, re, StringIO, sys, time, traceback, types, urllib, xmlrpclib

HERE = os.path.dirname(os.path.abspath(sys.argv[0]))

# grab simplejson from webapiwrappergen directory
sys.path.insert(0, os.path.join(HERE, '../webapiwrappergen'))
import simplejson

# grab local_config from api/Python
sys.path.insert(0, os.path.join(HERE, '../../api/Python'))
import local_config

# read in api description
execfile(os.path.join(HERE, "../../web/api/doc/peopleaggregator_api_desc.py.txt"))

# if 1, show EVERYTHING
NOISY=0

# if 1, show sensible debugging info
DETAIL=1

# define your login name and password in config.py
config_path = os.path.join(HERE, 'config.txt')
if not os.path.exists(config_path):
    print """See http://wiki.peopleaggregator.org/Working_with_the_WSAPI_code for detail on how to run this test.

Here's the short version.  Please create a config.txt file in the same directory as test_api.py, with the following contents:

LOGIN = 'first username'
PASSWORD = 'password for first user'
LOGIN2 = 'second username'
PASSWORD2 = 'password for second user'
SAMPLE_USER_IDS = (2, 3) # user IDs of second and third users
SAMPLE_USER_NAMES = ('second username', 'third username') # login names of second and third users
SAMPLE_GROUP_IDS = (3, 4) # group IDs for two groups

"""
    raise SystemExit(1)
execfile(config_path)

def get(url):
    return urllib.urlopen(url).read()

def note(s):
    if DETAIL:
        print "   : %s" % s

class MultiCaller:
    "class that calls via both xml-rpc and json and compares results"
    def __init__(self, xr, json_url, xml_url, methodname=None):
        self._xr = xr
        self.json_url = json_url
        self.xml_url = xml_url
        self.methodname = methodname or []
    def __getattr__(self, attr):
        xrf = getattr(self._xr, attr)
        juf = self.json_url + '/' + attr
        xmf = self.xml_url + '/' + attr
        return MultiCaller(xrf, juf, xmf, self.methodname + [attr])
    def xr(self, *args):
        so = sys.stdout
        sys.stdout = StringIO.StringIO() # for httplib debugging
        try:
            try:
                xr_ret = self._xr(*args)
            finally:
                self.xr_debug_output = sys.stdout.getvalue()
                sys.stdout = so
        except:
            print "exception occurred during xml-rpc call.  debugging output follows:"
            print self.xr_debug_output
            raise
        return xr_ret
    def munge_rest_arg(self, arg):
        if arg is True:
            return "true"
        if arg is False:
            return "false"
        return arg
    def json(self, args):
        method_desc = api_desc['methods']['.'.join(self.methodname)]
        req_method = method_desc['type']

        ju = self.json_url
        data = urllib.urlencode(dict([(k,self.munge_rest_arg(v)) for k,v in args.items()]))

        if req_method == 'get':
            ju += "?" + data
            data = None
            
        if NOISY: print "json: %s" % ju
        jc = urllib.urlopen(ju, data).read()
        if NOISY: print "json response: %s" % jc
        try:
            ret = simplejson.loads(jc)
        except ValueError:
            print "Error decoding this JSON text: %s" % jc
            raise

        # remove \r\n in strings, to match the xml-rpc version
        ret = self.remove_crlf(ret)
        
        return ret
    def remove_crlf(self, data):
        if type(data) == type({}):
            for k,v in data.items():
                data[k] = self.remove_crlf(data[k])
        elif type(data) == type([]):
            for i in range(len(data)):
                data[i] = self.remove_crlf(data[i])
        elif type(data) in (type(""), type(u"")):
            data = data.replace("\r\n", "\n")
        return data
    def xml(self, args):
        method_desc = api_desc['methods']['.'.join(self.methodname)]
        req_method = method_desc['type']

        ju = self.xml_url
        data = urllib.urlencode(dict([(k,self.munge_rest_arg(v)) for k,v in args.items()]))

        if req_method == 'get':
            ju += "?" + data
            data = None
            
        if NOISY: print "xml: %s" % ju
        jc = urllib.urlopen(ju, data).read()
        print "xml response: %s" % jc

    def __call__(self, args, mode=None):
        # just json?
        if mode == 'json':
            return self.json(args)

        # do xml-rpc call
        xr_ret = self.xr(args)

        # just doing xml-rpc?
        if mode == 'xr':
            return xr_ret

        # xml-rpc call
        if NOISY: print "xml-rpc response: %s" % `xr_ret`
        # json call
        json_ret = self.json(args)
        # before comparing, kill the msg if this is an error response, because it can be different between call methods
        killed_msg = 0
        if not xr_ret['success']:
            msg = xr_ret['msg']
            json_msg = json_ret['msg']
            killed_msg = 1
            del xr_ret['msg']
            del json_ret['msg']
        # compare
        if xr_ret != json_ret:
            print "COMPARE XR RESPONSE: %s" % pprint.pformat(xr_ret)
            print "WITH JSON RESPONSE: %s" % pprint.pformat(json_ret)
            print "RAW XML-RPC DATA: %s" % self.xr_debug_output
            if killed_msg:
                print "   XR ERROR MSG: %s" % msg
                print "   JSON ERROR MSG: %s" % json_msg
            assert 0, "json and xml-rpc responses don't match"
        # return
        if killed_msg:
            xr_ret['msg'] = msg # put it back if deleted
        return xr_ret

class Tester:
    def __init__(self):
        self.config_vars = local_config.read_local_config(os.path.join(HERE, "../../local_config.php"))

        if not self.config_vars.has_key("base_url"):
            raise Exception("""Can't find a line in local_config.php that looks like: $base_url = "http://something";""")
        self.base_url = self.config_vars['base_url']
        self.base_url = self.base_url.replace("%network_name%", "www")
        print "Base URL: %s" % self.base_url

        self.xmlrpc_url = '%s/api/xmlrpc' % self.base_url
        print "XML-RPC URL: %s" % self.xmlrpc_url
        xr = xmlrpclib.Server(self.xmlrpc_url, verbose=1)

        json_url = '%s/api/json' % self.base_url
        xml_url = '%s/api/xml' % self.base_url
        
        self.mc = MultiCaller(xr, json_url, xml_url)

    # basic tests to make sure the 'plumbing' is working
    def test_1_xmlrpc_not_struct(self):
        "sending a string instead of a struct to xml-rpc"
        r = self.mc.peopleaggregator.echo.xr("test")
        note(`r`)
        assert not r['success']
        assert r['code'] == 'validation_request_wrapper'

    def test_1_missing_key(self):
        "missing out a required key in a call"
        r = self.mc.peopleaggregator.echo({'notEchoText': 'this is an incorrect call'})
        note(`r`)
        assert not r['success']
        assert r['code'] == 'validation_missing_key'
        
    def test_1_basic(self):
        "basic call test"
        msg = 'this is a test'
        ret = self.mc.peopleaggregator.echo({'echoText': msg})
        assert ret['success']
        assert ret['echoText'] == msg

    def test_1_xml_rest_basic(self):
        "basic rest/xml test"
        content = get("%s/api/xml/peopleaggregator/echo?echoText=foobar" % self.base_url)
        note("got %s" % content)
        assert content == '<?xml version="1.0"?>\n<response xmlns="http://peopleaggregator.com/api/xmlns#"><success>true</success><echoText>foobar</echoText></response>'

    def test_1_internationalization(self):
        "testing with sam ruby's internationalization string"
        input_string = u'I\xf1t\xebrn\xe2ti\xf4n\xe0liz\xe6ti\xf8n'
        for method in ("echo", "echoPost"):
            note("internationalization test using %s method" % method)
            func = getattr(self.mc.peopleaggregator, method)
                 
            r = func.xr({'echoText': input_string})
            assert r['success']
            s = r['echoText']
            note("internationalization string came back from xmlrpc as %s" % `s`)
            assert s == input_string

            r = func.xr({'echoText': input_string.encode("iso-8859-1")})
            assert r['success']
            s = r['echoText']
            note("internationalization string as iso-8859-1 came back from xmlrpc as %s" % `s`)
            assert s == input_string

            for encoding in ("utf-8", "iso-8859-1"):
                r = func.json({'echoText': input_string.encode(encoding)})
                assert r['success']
                s = r['echoText']
                note("internationalization string as %s came back from json as %s" % (encoding, `s`))
                assert s == input_string

    def check_alive(self):
        "basic rest/json test, used to ensure the server is alive"
        print "Making sure the server is alive before running the rest of the tests."
        content = get("%s/api/json/peopleaggregator/echo?echoText=foobar" % self.base_url)
        note("got %s" % `content`)
        assert content == '{"success":true,"echoText":"foobar"}', "Something is seriously broken, even a JSON echo request doesn't work!"
        note("the server looks ok, let's go...")

    # testing peopleaggregator.newUser
    def test_2_newuser(self):
        "newUser function"
        random_number = random.randint(11111, 99999)
        args = {
            'adminPassword': 'WRONG PASSWORD',
            'login': 'rnd_user_%d' % random_number,
            'firstName': 'Random',
            'lastName': 'User',
            'email': 'pp_%d@example.com' % random_number,
            'password': 'password_%d' % random_number,
            }

        ap = self.config_vars.get("admin_password", None)
        r = self.mc.peopleaggregator.newUser.xr(args)
        note("Passed incorrect password to newUser: %s" % `r`)
        assert not r['success']
        if ap is None:
            assert r['code'] == 'operation_not_permitted'
        else:
            assert r['code'] == 'user_invalid_password'

        if ap is None:
            note("No admin password configured; can't test newUser call completely")
            return

        args['adminPassword'] = ap
        r = self.mc.peopleaggregator.newUser.xr(args)
        note("Creating new user: %s" % `r`)
        assert r['success']

    # testing peopleaggregator.getUserList
    def test_2_userlist(self):
        "getUserList function"
        per_page = 11
        print "get first page of users"
        r = self.mc.peopleaggregator.getUserList({'page': 1, 'resultsPerPage': per_page})
        assert r['success']
        total = r['totalUsers']
        seen = 0
        pages = r['totalPages']
        for page in range(1, pages+1):
            r = self.mc.peopleaggregator.getUserList({'page': page, 'resultsPerPage': per_page})
            assert r['success']
            seen += len(r['users'])
            print "Getting page %d of users" % page
            if NOISY: pprint.pprint(r)
        assert seen == total, "paging through user list revealed %d users but the total is meant to be %d" % (seen, total)
        note("paged through the user list (%d users, %d pages) ok" % (seen, pages))
        
    def test_2_getUserRelations(self):
        "getUserRelations function"
        per_page = 11
        login = SAMPLE_USER_NAMES[1]
        r = self.mc.peopleaggregator.getUserRelations({'login': login, 'resultsPerPage': per_page, 'page': 1})
        note(`r`)
        assert r['success']
        assert r['login'] == login
        
    def test_2_relations_bad_login(self):
        "getUserRelations function, called with a nonexistent login name"
        r = self.mc.peopleaggregator.getUserRelations({'login': 'aksdf876asjfka;lsjdhfka', 'resultsPerPage': 11, 'page': 1})
        note(`r`)
        assert not r['success']
        assert r['code'] == 'user_not_found'

    def test_2_relations_invalid_page(self):
        "getUserRelations function, called with page=0"
        r = self.mc.peopleaggregator.getUserRelations({'login': SAMPLE_USER_NAMES[0], 'resultsPerPage': 11, 'page': 0})
        note(`r`)
        assert not r['success']
        assert r['code'] == 'validation_out_of_range'
        
    def test_2_xmlrpc_relations_invalid_page_number_string(self):
        "getUserRelations function, called with page='1' (invalid)"
        r = self.mc.peopleaggregator.getUserRelations.xr({'login': SAMPLE_USER_NAMES[0], 'resultsPerPage': 11, 'page': '1'})
        note(`r`)
        assert not r['success']
        assert r['code'] == 'validation_incorrect_type'

    def test_2_relations_default_arg(self):
        "getUserRelations called with no resultsPerPage attribute, to see if defaulting is working"
        r = self.mc.peopleaggregator.getUserRelations({'login': SAMPLE_USER_NAMES[0]})
        r2 = self.mc.peopleaggregator.getUserRelations({'login': SAMPLE_USER_NAMES[0], 'resultsPerPage': 100, 'page': 1})
        assert r == r2, "defaulting is not working - specifying resultsPerPage=100 and page=1 should be the same as omitting them"

    def test_2_bad_token(self):
        "try to validate a completely rubbish token"
        r = self.mc.peopleaggregator.checkToken({'authToken': 'this is not a token'})
        note(`r`)
        assert not r['success']

    def test_2_login(self):
        "log in"

        # get user ids
        r = self.mc.peopleaggregator.getUserProfile({'login': LOGIN})
        note(`r`)
        assert r['success']
        self.user_id = r['id']

        r = self.mc.peopleaggregator.getUserProfile({'login': LOGIN2})
        note(`r`)
        assert r['success']
        self.user_id_2 = r['id']

        # try a bad password
        r = self.mc.peopleaggregator.login.xr({'login': LOGIN, 'password': 'x'})
        note(`r`)
        assert not r['success']

        # log in - get a token
        r = self.mc.peopleaggregator.login.xr({'login': LOGIN, 'password': PASSWORD})
        note(`r`)
        assert r['success']
        self.auth_token = r['authToken']
        self.auth_token_expires = time.time() + r['tokenLifetime']
        note("token expires at "+time.ctime(self.auth_token_expires))

        # checking token
        note("checking token - should succeed")
        r = self.mc.peopleaggregator.checkToken.json({'authToken': self.auth_token})
        note(`r`)
        assert r['success']

        # and make sure we can modify the token slightly and make it invalid
        note("calling with bad token - should give user_token_invalid")
        r = self.mc.peopleaggregator.checkToken({'authToken': self.auth_token + "x"})
        note(`r`)
        assert not r['success']
        assert r['code'] == 'user_token_invalid'

        # now get another token for the second user
        r = self.mc.peopleaggregator.login.xr({'login': LOGIN2, 'password': PASSWORD2})
        note(`r`)
        assert r['success']
        self.auth_token_2 = r['authToken']

    # anything requiring self.auth_token has to be in test_3_* as the auth_token is generated in test_2_login
    def test_3_add_relation(self):
        "deleteUserRelation and addUserRelation test"

        # if it exists, delete it -- ignore response
        self.mc.peopleaggregator.deleteUserRelation.xr({'authToken': self.auth_token, 'login': LOGIN2})

        for meth in ("xr", "json"):
            note("mode: %s" % meth)
            # now we know it's been deleted, so try deleting again - which should definitely fail
            r = self.mc.peopleaggregator.deleteUserRelation.__call__({'authToken': self.auth_token, 'login': LOGIN2}, meth)
            note("delete nonexistent relation: "+`r`)
            assert not r['success']
            assert r['code'] == 'relation_not_exist'

            # now add it
            rtype = 'relation'
            r = self.mc.peopleaggregator.newUserRelation.__call__({'authToken': self.auth_token, 'login': LOGIN2, 'relation': rtype}, meth)
            note("add new relation: "+`r`)
            assert r['success']

            # get relation detail - should come back the same as what we created
            r = self.mc.peopleaggregator.getUserRelation.__call__({'login': LOGIN, 'relation_login': LOGIN2}, meth)
            note("get relation detail: "+`r`)
            assert r['success']
            assert r['relation'] == rtype

            # now try adding again - this should fail
            r = self.mc.peopleaggregator.newUserRelation.__call__({'authToken': self.auth_token, 'login': LOGIN2, 'relation': rtype}, meth)
            note("add relation that already exists: "+`r`)
            assert not r['success']
            assert r['code'] == 'relation_already_exists'

            # now change the type of relationship
            rtype = 'havent met'
            r = self.mc.peopleaggregator.editUserRelation.__call__({'authToken': self.auth_token, 'login': LOGIN2, 'relation': rtype}, meth)
            note("edit existing relation: "+`r`)
            assert r['success']

            # get relation detail - should come back the same as what we created
            r = self.mc.peopleaggregator.getUserRelation.__call__({'login': LOGIN, 'relation_login': LOGIN2}, meth)
            note("get edited relation: "+`r`)
            assert r['success']
            assert r['relation'] == rtype

            # now delete it again - and make sure it works this time
            r = self.mc.peopleaggregator.deleteUserRelation.__call__({'authToken': self.auth_token, 'login': LOGIN2}, meth)
            note("delete relation: "+`r`)
            assert r['success']
            
    def test_3_get_content(self):
        "test getContent call"

        for context in (
            'global',
            'group:%d' % SAMPLE_GROUP_IDS[0],
            'user:%d' % SAMPLE_USER_IDS[0],
            'user:%d' % SAMPLE_USER_IDS[1],
            'tag:2',
            'search:notting',
            'search:jockum notting hill',
            ):
            for detail in ("summary", "content", "all"):
                page = 1
                total_items = 100 # fake value, will be updated in first iteratation
                seen_items = 0
                while seen_items < total_items:
                    args = {
                        'page': page,
                        'detailLevel': detail,
                        'resultsPerPage': 10,
                        'context': context,
                        }
                    r = self.mc.peopleaggregator.getContent(args)
                    assert r['success'], "getContent call failed: %s / %s (args=%s)" % (r['code'], r['msg'], `args`)
                    total_items = r['totalResults']
                    
                    n_items = len(r['items'])
                    seen_items += n_items
                    
                    note("getContent: retrieved page %d, containing %d/%d items (seen %d so far) for context %s detail %s" % (page, n_items, total_items, seen_items, context, detail))
                    
                    # prepare for next time
                    page += 1

    def test_3_get_group_list(self):
        "get the group list - first the global list, then a user's group memberships"
        
        for context in (
            'global',
            self.user_id,
            'user:1',
            ):
            page = 1
            total_items = 100 # fake value, will be updated in first iteratation
            seen_items = 0
            while seen_items < total_items:
                r = self.mc.peopleaggregator.getGroups({
                    'page': page,
                    'resultsPerPage': 10,
                    'context': context,
                    })
                assert r['success']
                total_items = r['totalResults']

                n_items = len(r['groups'])
                seen_items += n_items

                note("getGroups: retrieved page %d, containing %d/%d items (seen %d so far) for context %s" % (page, n_items, total_items, seen_items, context))

                # prepare for next time
                page += 1

    def test_3_join_group(self):
        "join and leave a group"

        gid = "group:%d" % SAMPLE_GROUP_IDS[0]
        
        note("preparation - leaving group %s" % gid)
        r = self.mc.peopleaggregator.leaveGroup.xr({
            'authToken': self.auth_token_2,
            'id': gid,
            })
        note(`r`)

        note("now joining it")
        r = self.mc.peopleaggregator.joinGroup.xr({
            'authToken': self.auth_token_2,
            'id': gid,
            })
        note(`r`)
        assert r['success']
        assert r['joinState'] == 'joined'

        note("trying to join a second time")
        r = self.mc.peopleaggregator.joinGroup.xr({
            'authToken': self.auth_token_2,
            'id': gid,
            })
        note(`r`)
        assert not r['success']

        note("and leaving again")
        r = self.mc.peopleaggregator.leaveGroup.xr({
            'authToken': self.auth_token_2,
            'id': gid,
            })
        note(`r`)
        assert r['success']

        note("trying to leave without joining")
        r = self.mc.peopleaggregator.leaveGroup.xr({
            'authToken': self.auth_token_2,
            'id': gid,
            })
        note(`r`)
        assert not r['success']

    def test_3_incorrectly_delete_album(self):
        note("making sure we can't delete albums using deleteGroup()")
        r = self.mc.peopleaggregator.getAlbums({'authToken': self.auth_token})
        note(`r`)
        assert r['success']
        # dump xml as well
        print self.mc.peopleaggregator.getAlbums.xml({'authToken': self.auth_token})
        orig_albums = r['albums']
        for album in orig_albums:
            group_id = 'group:%d' % int(album['id'].split(":")[-1])
            r = self.mc.peopleaggregator.deleteGroup.xr({
                'authToken': self.auth_token,
                'id': group_id,
                })
            note("attempt to delete album %s: %s" % (album['id'], `r`))
            assert not r['success']
        r = self.mc.peopleaggregator.getAlbums({'authToken': self.auth_token})
        note(`r`)
        assert r['success']
        for album in orig_albums:
            assert album in r['albums'], "album has been deleted by deleteGroup call"

    def test_3_add_group(self):
        "create a group"

        group_name = 'Auto-test group (SHOULD BE DELETED AFTER TEST FINISHES)'

        note("seeing if the group already exists")
        r = self.mc.peopleaggregator.findGroup.json({
            'name': 'auto-test group',
            })
        note(`r`)
        assert r['success']
        for g in r['groups']:
            note("found group %s - deleting it" % `g`)
            r = self.mc.peopleaggregator.deleteGroup.xr({
                'authToken': self.auth_token,
                'id': g['id'],
                })
            note(`r`)
            assert r['success']
            note("group %s deleted" % g['id'])

        note("now making sure the deleted groups are still deleted")
        r = self.mc.peopleaggregator.findGroup.json({
            'name': 'auto-test group',
            })
        note(`r`)
        assert r['success']
        assert not len(r['groups'])

        note("getting a category")
        r = self.mc.peopleaggregator.getCategories.xr({})
        note(`r`)
        assert r['success']
        cat_id = r['categories'][0]['id']
        
        note("creating a group")
        r = self.mc.peopleaggregator.newGroup.xr({
            'authToken': self.auth_token,
            'name': group_name,
            'category': cat_id,
            'tags': 'testing, testing2, testing3',
            'description': "A group created by the auto-test tool.  One day the auto-tester will be able to delete groups too...",
            'image': 'http://example.org/foobar.jpg',
            'registrationType': 'open',
            'accessType': 'public',
            'moderationType': 'direct',
            })
        note(`r`)
        assert r['success']
        gid = r['id']

        note("trying to delete it (using non-owner credentials - should fail)")
        r = self.mc.peopleaggregator.deleteGroup.xr({
            'authToken': self.auth_token_2,
            'id': gid,
            })
        note(`r`)
        assert not r['success']
        assert r['code'] == 'user_access_denied'

        note("now deleting it (id=%s)" % gid)
        r = self.mc.peopleaggregator.deleteGroup.json({
            'authToken': self.auth_token,
            'id': gid,
            })
        note(`r`)
        assert r['success']

        note("trying to join the deleted group")
        r = self.mc.peopleaggregator.joinGroup.json({
            'authToken': self.auth_token,
            'id': gid,
            })
        note(`r`)
        assert not r['success']

        note("trying to use an invalid ID")
        r = self.mc.peopleaggregator.deleteGroup.xr({
            'authToken': self.auth_token,
            'id': 'message:1',
            })
        note(`r`)
        assert not r['success']
        assert r['code'] == 'invalid_id'

    def test_3_add_content(self):
        "create some content"

        note("posting to my blog")
        r = self.mc.peopleaggregator.newContent.json({
            'authToken': self.auth_token,
            'title': 'this is a test post',
            'content': 'this is the content of the test post',
            'tags': 'one, two, one two three, four five six',
            'trackbacks': 'http://topicexchange.com/t/test/',
            })
        note(`r`)
        assert r['success']
        assert re.search(r"^user:\d+:\d+$", r['id']), "invalid content id, should be 'user:123:123': %s" % r['id']

        note("posting to someone else's blog - should fail")
        r = self.mc.peopleaggregator.newContent.json({
            'authToken': self.auth_token,
            'context': 'user:%d' % SAMPLE_USER_IDS[0],
            'title': 'this is a test post',
            'content': 'this is the content of the test post',
            'tags': 'one, two, one two three, four five six',
            })
        note(`r`)
        assert not r['success']
        assert r['code'] == 'operation_not_permitted'

        note("posting to a group blog")
        note("finding a group")
        r = self.mc.peopleaggregator.getGroups({
            'context': self.user_id,
            })
        assert r['success']
        gid = r['groups'][0]['id']

        note("posting to group %s" % gid)
        r = self.mc.peopleaggregator.newContent.json({
            'authToken': self.auth_token,
            'context': gid,
            'title': 'this is a test post to a group blog',
            'content': 'this is the content of the test post',
            'tags': 'one, two, one two three, four five six',
            'trackbacks': 'http://topicexchange.com/t/test/',
            })
        note(`r`)
        assert r['success']
        assert re.search(r"^%s:\d+$" % gid, r['id']), "invalid content id, should be '%s:123': %s" % (gid, r['id'])

    def test_3_messaging(self):
        "test sendMessage and getMessages"

        note("listing folders")
        r = self.mc.peopleaggregator.getFolders.xr({
            'authToken': self.auth_token,
            })
        note(`r`)
        assert r['success']

        note("reading inbox")
        r = self.mc.peopleaggregator.getMessages.xr({
            'authToken': self.auth_token,
            'folder': 'inbox',
            })
        note(`r`)
        assert r['success']

        magic = md5.md5(str(random.random())).hexdigest()
        note("sending a message (%s), and making sure it goes into my sent folder" % magic)
        r = self.mc.peopleaggregator.sendMessage.xr({
            'authToken': self.auth_token,
            'recipients': '   '+",".join(SAMPLE_USER_NAMES)+'   ',
            'title': 'test message %s' % magic,
            'content': 'this message was sent from test_api.py.\n\ndoes it work?  how about <b>html</b>?',
            })
        note(`r`)
        assert r['success']

        r = self.mc.peopleaggregator.getMessages.json({
            'authToken': self.auth_token,
            'folder': 'sent',
            })
        note("titles: %s" % `[m['title'] for m in r['messages']]`)
        assert r['success']
        assert r['messages'][0]['title'].find(magic) != -1, "message we just sent doesn't show up as the most recent sent message"

        magic = md5.md5(str(random.random())).hexdigest()
        note("sending a message to myself (%s), then reading inbox to make sure it's there" % magic)
        title = 'test message to myself %s' % magic
        r = self.mc.peopleaggregator.sendMessage.json({
            'authToken': self.auth_token,
            'recipients': LOGIN,
            'title': title,
            'content': 'test message',
            })
        assert r['success']

        r = self.mc.peopleaggregator.getMessages.json({
            'authToken': self.auth_token,
            'folder': 'inbox',
            })
        note("titles: %s" % `[m['title'] for m in r['messages']]`)
        assert r['success']
        assert r['messages'][0]['title'].find(magic) != -1, "message just sent to myself doesn't show up as the most recent message in the inbox"

    def test_3_group_topics(self):
        "create a group topic and post to it"

        # find a group we are a member of
        note("finding a group")
        r = self.mc.peopleaggregator.getGroups({
            'context': self.user_id,
            })
        assert r['success']

        gid = r['groups'][0]['id']
        assert re.search(r"^group:\d+", gid), "invalid group id: %s" % gid
        note("posting to group %s" % gid)

        for allow_anon in (True, False):
            # create a topic
            note("create topic (allow_anon=%s)" % allow_anon)
            r = self.mc.peopleaggregator.newBoardMessage.json({
                'authToken': self.auth_token,
                'context': gid,
                'title': 'title of new group topic (posted by test_api.py)' + (allow_anon and " - which allows anon posting" or ""),
                'content': 'content of new group topic.  this group ' + (allow_anon and "allows" or "does not allow") + " posting anonymously",
                'allowAnonymous': allow_anon,
                })
            note(`r`)
            assert r['success']
            tid = r['id']
            assert re.search(r"^msg:\d+$", tid), "invalid topic id: %s" % tid

            # reply to topic
            note("replying to topic")
            r = self.mc.peopleaggregator.newBoardMessage.xr({
                'authToken': self.auth_token,
                'context': tid,
                'title': 'this is a reply to a topic',
                'content': 'here is the content for the reply',
                })
            note(`r`)
            assert r['success']
            msg1_id = r['id']
            assert re.search(r"^msg:\d+$", msg1_id), "invalid message id: %s" % msg1_id

            # make sure we can or can't reply anonymously, as required
            note("replying anonymously (this " + (allow_anon and "should" or "shouldn't") + " be allowed)")
            r = self.mc.peopleaggregator.newBoardMessage.json({
                'context': tid,
                'title': "this " + (allow_anon and "should" or "shouldn't") + " work",
                'content': "(we're anonymous)",
                })
            note(`r`)
            assert r['success'] == allow_anon
            if allow_anon:
                msg2_id = r['id']
                if r['success']:
                    assert re.search(r"^msg:\d+$", msg2_id), "invalid message id: %s" % msg2_id
            else:
                assert not r.has_key('id')

            # get the topic and make sure there are two (or three) replies
            note("reading topic")
            r = self.mc.peopleaggregator.getBoardMessages.xr({
                'authToken': self.auth_token,
                'context': tid,
                })
            note(`r`)
            assert r['success']
            msgs = r['messages']
            assert len(msgs) == (allow_anon and 3 or 2)
            msgs.sort(lambda a,b: cmp(a['id'], b['id']))
            msg0 = msgs[0]
            assert msg0['id'] == tid
            assert msg0['title'].startswith("title of new")
            assert msg0['content'].startswith("content of new")
            msg1 = msgs[1]
            assert msg1['id'] == msg1_id
            assert msg1['title'].startswith("this is a reply")
            assert msg1['content'].startswith("here is the content")
            if allow_anon:
                msg2 = msgs[2]
                assert msg2['id'] == msg2_id
                assert msg2['title'].startswith("this should work")
                assert msg2['content'] == "(we're anonymous)"

    def test_3_new_file(self):
        "upload a file"

        note("getting list of personal albums - anonymously.")
        r = self.mc.peopleaggregator.getAlbums({
            'context': self.user_id,
            })
        note(`r`)
        assert r['success']
        anon_album_ids = [a['id'] for a in r['albums']]
        anon_album_ids.sort()
        
        note("getting list of personal albums.")
        r = self.mc.peopleaggregator.getAlbums({
            'authToken': self.auth_token,
            })
        note(`r`)
        assert r['success']
        albums = r['albums']
        auth_album_ids = [a['id'] for a in albums if a['id'].find(":default:") == -1]
        auth_album_ids.sort()
        assert anon_album_ids == auth_album_ids, "list of album ids for user %s is different for anonymous and authenticated requests" % self.user_id
        for album in r['albums']:
            assert album['id'].startswith("user")

            note("getting list of files in personal %(type)s album %(title)s (%(id)s)" % album)
            args = {
                'authToken': self.auth_token,
                'context': album['id'],
                }
            r = self.mc.peopleaggregator.getFiles(args)
            note(`r`)
            assert r['success']
            for f in r['files']:
                if f.get('url', '').find("pa-on-white") != -1:
                    note("deleting %(id)s - %(url)s - as it's a test file" % f)
                    r = self.mc.peopleaggregator.deleteFile.json({
                        'authToken': self.auth_token,
                        'id': f['id'],
                        })
                    note(`r`)
                    assert r['success']
                    note("making sure it's deleted")
                    r = self.mc.peopleaggregator.deleteFile.json({
                        'authToken': self.auth_token,
                        'id': f['id'],
                        })
                    note(`r`)
                    assert not r['success']

        note("getting list of group albums.")
        r = self.mc.peopleaggregator.getAlbums({
            'authToken': self.auth_token,
            'context': 'group',
            })
        note(`r`)
        assert r['success']
        for album in r['albums']:
            assert album['id'].startswith("group")

        note("getting full list of albums.")
        r = self.mc.peopleaggregator.getAlbums({
            'authToken': self.auth_token,
            'context': 'all',
            })
        note(`r`)
        assert r['success']
        #FIXME: implement!
        pers_album = video_album = grp_album = None
        for album in r['albums']:
            if album['id'].startswith("group"):
                if not grp_album: grp_album = album
            else:
                if 'image' in album['type']:
                    if not pers_album: pers_album = album
                if 'video' in album['type']:
                    if not video_album: video_album = album
        assert pers_album, "no non-group image albums found.  there should be at least a default one somewhere...?"
        assert video_album, "no non-group video albums found.  there should be at least a default one somewhere...?"
        assert grp_album, "no group albums found.  please join a group (with user %s)!" % LOGIN

        data = open(os.path.join(HERE, "../../web/images/pa-on-white.png")).read()

        note("trying to upload an image into a video album (%s; shouldn't work)" % video_album['id'])
        r = self.mc.peopleaggregator.newFile.xr({
            'authToken': self.auth_token,
            'filename': "pa-on-white.png",
            'type': 'image',
            'title': "trying to upload an image into a video album - SHOULDN'T WORK",
            'content': 'does it need more of a description?  :-)',
            'data': xmlrpclib.Binary(data),
            'context': video_album['id'],
            })
        note(`r`)
        assert not r['success']
        assert r['code'] == 'operation_not_permitted'

        default_context = '%s:album:default:video' % self.user_id
        note("making sure that the code to get default albums is working (%s)" % default_context)
        r = self.mc.peopleaggregator.newFile.xr({
            'authToken': self.auth_token,
            'filename': "pa-on-white.png",
            'type': 'video',
            'title': "testing default albums",
            'content': "this isn't really a video, but it's close enough.",
            'data': xmlrpclib.Binary(data),
            'context': default_context,
            })
        note(`r`)
        assert r['success']

        for album in (pers_album, grp_album):
            note("making a media object with just a url in album %(id)s (%(title)s) - should work" % album)
            fake_url = 'http://example.org/hypothetical.png'
            r = self.mc.peopleaggregator.newFile.json({
                'authToken': self.auth_token,
                'title': "a hypothetical linked image",
                'content': 'does it need more of a description?  :-)',
                'url': fake_url,
                'context': album['id'],
                })
            note(`r`)
            assert r['success']
            assert r['url'] == fake_url
            
            note("uploading a file to album %(id)s (%(title)s) - should work" % album)
            #FIXME: can only do this by xml-rpc.  try json etc later.
            r = self.mc.peopleaggregator.newFile.xr({
                'authToken': self.auth_token,
                'filename': "pa-on-white `\t~!@#$%^&*()/\\{}_-+=?<>,.png",
                'title': "peopleaggregator logo on white background",
                'content': 'does it need more of a description?  :-)',
                'data': xmlrpclib.Binary(data),
                'context': album['id'],
                })
            note(`r`)
            assert r['success']
            new_file_id = r['id']
            note("downloading the file again to see if it worked")
            read_data = urllib.urlopen(r['url']).read()
            assert read_data == data, "Uploaded file (%d bytes) does not match what we sent (%d bytes)" % (read_data, data)

            if album == pers_album:
                note("making sure another user can see the file by listing the album")
                r = self.mc.peopleaggregator.getFiles.xr({
                    'authToken': self.auth_token_2,
                    'context': album['id'],
                    })
                note(`r`)
                assert r['success']
                found = 0
                for f in r['files']:
                    if f['id'] == new_file_id:
                        found = 1
                        break
                assert found, "new file not visible to user %s" % self.user_id_2

        note("making sure we can't upload to someone else's albums")
        r = self.mc.peopleaggregator.getAlbums({
            'context': self.user_id,
            'authToken': self.auth_token_2,
            })
        note(`r`)
        # find first video album
        album = None
        for a in r['albums']:
            if 'video' in a['type']:
                album = a
                break
        assert album is not None, "brokenness -- we should have a video album by now"
        
        note("making sure another user can't post to our album (%s)" % album['id'])
        r = self.mc.peopleaggregator.newFile.xr({
            'authToken': self.auth_token_2,
            'filename': "pa-on-white.png",
            'type': 'video',
            'title': "testing default albums",
            'content': "this isn't really a video, but it's close enough.",
            'data': xmlrpclib.Binary(data),
            'context': album['id'],
            })
        note(`r`)
        assert not r['success']
        assert r['code'] == 'user_access_denied'

    def test_3_getuserprofile(self):
        "getUserProfile test"

        r = self.mc.peopleaggregator.getUserProfile({
            'login': LOGIN,
            })
        assert r['success']
        note("profile for %s: %s" % (LOGIN, `r`))

    def test_3_blogger_metaweblog(self):
        "get list of blogs - using blogger api"

        note("this should work:")
        blogs = self.mc.blogger.getUsersBlogs.xr('fake appkey', LOGIN, PASSWORD)
        note(`blogs`)
        assert type(blogs) == types.ListType
        note("trying metaWeblog.getUsersBlogs alias")
        blogs2 = self.mc.metaWeblog.getUsersBlogs.xr('fake appkey', LOGIN, PASSWORD)
        assert blogs == blogs2

        note("this should fail (bad password):") #FIXME: should return xmlrpc fault?
        r = self.mc.blogger.getUsersBlogs.xr('fake appkey', LOGIN, PASSWORD + 'x')
        note(`r`)
        assert not r['success']

        note("this should fail:") #FIXME: should return xmlrpc fault?
        r = self.mc.blogger.getUsersBlogs.xr({'test': 'foo'})
        note(`r`)
        assert not r['success']
        
        note("sending a post")
        for blog in blogs[:2]:
            note("... to blog %s" % blog['blogid'])
            title = 'Test post to blog %s via metaweblog api' % blog['blogName']
            desc = "this is the content of the test post.  interesting, isn't it?"
            r = self.mc.metaWeblog.newPost.xr(blog['blogid'], LOGIN, PASSWORD, {
                'title': title,
                'description': desc,
                }, True)
            note("post id: %s" % `r`)
            assert type(r) == types.StringType
            postid = r
            note("... and trying to get it back")
            r = self.mc.metaWeblog.getRecentPosts.xr(blog['blogid'], LOGIN, PASSWORD, 10)
            note(`r`)
            found = 0
            for post in r:
                if post['postid'] == postid:
                    note("found it - post id %s" % postid)
                    found = 1
                    assert post['title'] == title, "incorrect title"
                    assert post['description'] == desc, "incorrect body"
            assert found, "just-posted post doesn't appear in recent post list"

        blogid = blogs[0]['blogid']
        note("sending a post to blog %s and then deleting it" % blogid)
        postid = self.mc.metaWeblog.newPost.xr(blogid, LOGIN, PASSWORD, {
            'title': 'THIS POST WILL GET DELETED',
            'description': "this is the content of the test post.  interesting, isn't it?",
            }, True)
        note("post id: %s" % postid)
        assert type(postid) == types.StringType

        r = self.mc.metaWeblog.deletePost.xr("fake appkey", postid, LOGIN, PASSWORD, True)
        assert r is True
        note("deleted.")

        note("single post using the blogger api")
        postid = self.mc.blogger.newPost.xr(
            "fake appkey",
            blogid, LOGIN, PASSWORD,
            "this post is coming in through the blogger api, so it doesn't get a title :(", True)
        note("post id: %s" % postid)
        assert type(postid) == types.StringType

        note("trying some things that should give USER_ACCESS_DENIED")
        for blogid in ("user:999", "group:1"):
            note("blog %s" % blogid)
            r = self.mc.metaWeblog.newPost.xr(blogid, LOGIN, PASSWORD, {
                'title': "this shouldn't work",
                'description': "if this works, the permission system is broken",
                }, True)
            note(`r`)
            assert not r['success']
            note("FIXME: that should have been an xml-rpc fault")

    def test_3_list_ads(self):
        "listAds test"
        note("retrieving some ads")
        r = self.mc.peopleaggregator.listAds({"page_type": "invalid_page"})
        assert not r['success']
        r = self.mc.peopleaggregator.listAds({"page_type": "user_public"})
        note(`r`)
        assert r['success']
        
    # test runner
    def run(self, pat=''):
        # make sure peepagg is working
        self.check_alive()
        
        tests = [fn for fn in dir(self) if fn.startswith("test_")]
        tests.sort()
        good = 0
        fails = []
        for test in tests:
            if test.find("login") == -1:
                if test.find(pat) == -1: continue # skip stuff not matching pattern (but allow 'login' test)

            func = getattr(self, test)
            
            print "TEST %s: %s" % (test, func.__doc__)
            try:
                func()
                print "   -> test succeeded"
                good += 1
            except KeyboardInterrupt:
                raise
            except:
                traceback.print_exc()
                print "   -> TEST FAILED"
                fails.append(test)
        bad = len(fails)
        print "TEST SUMMARY: %d passed, %d failed (total %d)" % (good, bad, good+bad)
        if bad:
            print "FAILED TESTS:"
            for test in fails:
                print "- %s" % test
        else:
            print "ALL TESTS PASSED :-)"

if __name__ == '__main__':
    pat = ''

    args, opts = getopt.getopt(sys.argv[1:], "p:", [])
    for opt,arg in args:
        if opt == '-p':
            pat = arg
            print "Pattern: %s" % arg
        else:
            raise Exception("invalid option '%s'" % opt)
    
    Tester().run(pat)
    
