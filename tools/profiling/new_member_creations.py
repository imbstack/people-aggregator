#!/usr/bin/python

import sys, os.path, random, urllib, time

# figure out where we are
HERE = os.path.dirname(os.path.abspath(sys.argv[0]))
# put api/Python in the include path
sys.path.insert(0, os.path.join(HERE, '../../api/Python'))

import local_config, httplib2, BeautifulSoup

class Timer:
    def __init__(self):
        self.start = time.time()
    def stop(self, msg):
        print "TIMING: %.2f %s" % (time.time() - self.start, msg)

class Input:
    def __init__(self, typ, name, value):
        self.typ, self.name, self.value = typ, name, value
    def __repr__(self):
        return "<Input: type=%s, name=%s, value=%s>" % (self.typ, self.name, self.value)

class Form:
    def __init__(self, name, action, method):
        self.name, self.action, self.method = name, action, method
        self.inputs = {}
    def __repr__(self):
        return "<Form: name=%s, action=%s, method=%s>" % (self.name, self.action, self.method)
                 
class Page:
    def __init__(self, url, response, content):
        self.url = url
        self.response = response
        self.content = content
        self.soup = None
        self.forms = {}
        self.form_list = []

    def getform(self, name):
        if not len(self.forms): self.parseforms()
        return self.forms[name]

    def parse(self):
        if self.soup: return
        self.soup = BeautifulSoup.BeautifulSoup(self.content)

    def getsoup(self):
        if not self.soup: self.parse()
        return self.soup

    def parseforms(self):
        if not self.soup: self.parse()

        forms = {}
        forms_list = []
        for form in self.soup.findAll("form"):
            f = Form(form['name'], urllib.basejoin(self.url, form['action']), form['method'])
            forms[f.name] = f
            forms_list.append(f)
            print "FORM:",f
            for inp in form.findAll("input"):
                i = Input(inp['type'], inp['name'], inp.get('value'))
                print "INPUT:",i
                f.inputs[i.name] = i
            for ta in form.findAll("textarea"):
                t = Input("textarea", ta['name'], ''.join(ta.contents))
                print "TEXTAREA:",t
                f.inputs[t.name] = t

        self.forms = forms
        self.forms_list = forms_list
            

class Main:
    def __init__(self):
        self.cookies = []
        
    def main(self):
        print "Profiling new member registrations"
        
        self.config_vars = local_config.read_local_config(os.path.join(HERE, "../../local_config.php"))
        
        self.base_url = self.config_vars['base_url'].replace("%network_name%", "www")
        
        print "Making sure the base URL is valid"
        self.http = httplib2.Http()
        pa_txt_url = self.url("peopleaggregator.txt")
        u = self.get(pa_txt_url)
        if int(u.response['status']) != 200 or u.content.find("PeopleAggregator root directory") == -1:
            print r, c
            raise Exception("Can't access %s" % pa_txt_url)

        reps = 100
        self.quick = 0
        start = time.time()
        for i in range(reps):
            st = time.time()
            self.register()
            print "TIMING: one registration took %.2f" % (time.time() - st)
        duration = time.time() - start
        print "TIMING: %.2f to register user and post content, etc, %d times; %.2f s/repetition" % (duration, reps, duration/reps)

    def register(self):
        login_name = 'auto_%d' % random.randint(1000000, 9999999)

        self.cookies = []
        
        print "Registering a user: %s" % login_name

        if not self.quick:
            print "* getting front page"
            u = self.get(self.url(""))
        print "* getting reg page"
        u = self.get(self.url("register.php"))

        regform = u.getform('formRegisterUser')
        for k,v in (
            ('login_name', login_name),
            ('first_name', 'Automatic'),
            ('last_name', 'User'),
            ('password', 'automatic'),
            ('confirm_password', 'automatic'),
            ('email', '%s@myelin.co.nz' % login_name),
            ):
            regform.inputs[k].value = v
        u = self.submit(regform)

        if int(u.response['status']) == 302:
            print "Redirecting to %s" % u.response['location']
        else:
            soup = u.getsoup()
            err = soup.findAll("p", "required")
            if err:
                raise Exception("failed to register user: %s" % err)
            raise "Unknown error registering user"

        print "Registered."
        if not self.quick:
            print "Going to user page."
            u = self.get(u.response['location'])

            print "Clicking on 'create content' - audio"
            u = self.get(self.url("post_content.php?sb_mc_type=media/audio"))
            print "Clicking on 'create content' - review"
            u = self.get(self.url("post_content.php?sb_mc_type=review/localservice"))
        print "Clicking on 'create content' - blog post"
        u = self.get(self.url("post_content.php"))
        f = u.getform("formCreateContent")
        for k,v in (
            ('blog_title', 'this is a test bit of content'),
            ('description', """This is my first blog post.  Or at least it would be, if I were a real person.  Actually this is being posted by a robot.  So you'll see many copies of this text, posted many times, by many different users with numerical names."""),
            ('tags', 'automated'),
            ):
            f.inputs[k].value = v
        print "Posting some content"
        u = self.submit(f)
        print u.response
        print u.content

    def url(self, path):
        return "%s/%s" % (self.base_url, path)

    def _request(self, url, method="GET", data=None, headers=None):
        if not headers: headers = {}
        for c in self.cookies:
            headers['Cookie'] = c
        r, c = self.http.request(url, method, data, headers)
        if r.has_key("set-cookie"):
            self.cookies.append(r['set-cookie'])
        return (r, c)

    def get(self, url):
        tm = Timer()
        r, c = self._request(url)
        tm.stop("retrieved %s" % url)
        return Page(url, r, c)

    def post(self, url, data, headers):
        if type(data) == type({}):
            data = urllib.urlencode(data)
        print "POSTing to %s (data = %s)" % (url, data)
        start = time.time()
        r, c = self._request(url, "POST", data, headers)
        duration = time.time() - start
        print "TIMING: %.2f %s" % (duration, url)
        return Page(url, r, c)

    def submit(self, form):
        print "Submitting form %s" % form
        data = {}
        for i in form.inputs.values():
            data[i.name] = i.value or ''
#        print data
        return self.post(form.action, data, {'Content-Type': 'application/x-www-form-urlencoded'})

if __name__ == '__main__':
    Main().main()
    
