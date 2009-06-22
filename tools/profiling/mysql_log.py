#!/usr/bin/python

import time, re, os, os.path, sys, MySQLdb, traceback
PA_ROOT = os.path.abspath(os.path.join(os.path.split(sys.argv[0])[0], '..', '..'))
sys.path.insert(0, os.path.join(PA_ROOT, 'api', 'Python'))
import local_config

class processor:
    def __init__(self):
        config = local_config.read_local_config([
            os.path.join(PA_ROOT, 'default_config.php'),
            os.path.join(PA_ROOT, 'local_config.php'),
            ])
        self.dsn = config['peepagg_dsn']
        self.db_name = re.search(r".*/(.*?)$", self.dsn).group(1)
        self.f = open("/var/log/mysql/mysql.log")
        self.f.seek(0, 2) # move to end
        self.current = None
        self.current_db = None
        self.db_connect()

    def run(self):
        while 1:
            time.sleep(1)
            pos = self.f.tell()
            self.f.seek(0, 2)
            endpos = self.f.tell()
            if endpos != pos:
                # we have data - move back to pos and read it
                self.f.seek(pos)
                lines = self.f.read(endpos-pos).split("\n")
                self.handle(lines)

    def db_connect(self):
        user, pw, host, db = re.search(r"^mysql://(.*?)(?::(.*?))?\@(.*?)/(.*?)$", self.dsn).groups()
        self.db = MySQLdb.connect(user=user, passwd=pw or '', host=host, db=db)

    def handle_line(self, line):
        # parse normal line, with or without timestamp
        if line.startswith("\t"):
            reg = r"^()"
        else:
            reg = r"^(..................)"
        reg += "\s*(\d+)\s*(...........) (.*)$"
        
        #print "LINE:",`line`

        m = re.search(reg, line.replace("\t", " "*8))
        if not m:
#            print "Failed to handle line %s - probably a continuation" % line
            # continuation of another query?
            self.current[-1].append(line)
            return

        ts, pid, op, cmd = [x.strip() for x in m.groups()]

        #print "ts=%s pid=%s op=%s cmd=%s" % (ts, pid, op, cmd)
        if self.current:
            self.handle_query(self.current)
        self.current = [ts, pid, op, [cmd]]

    def handle(self, lines):
        #print "got some lines"
        for line in lines:
            if not line.strip(): continue
            self.handle_line(line)

    def handle_query(self, query):
        ts, pid, op, cmd = query
        cmd = '\n'.join(cmd).strip()
        if op == 'Init DB':
            self.current_db = cmd
        elif op == 'Query':
            if self.current_db == self.db_name:
                if cmd.lower().startswith("select"):
                    self.explain_query(cmd)

    def explain_query(self, query):
        print "=" * 80
        print "%s" % query
        print "-" * 80

        # explain it
        cur = self.db.cursor()
        cur.execute("EXPLAIN %s" % query)
        first = 1
        tables = []
        while 1:
            row = cur.fetchone()
            if not row: break
            if first:
                first = 0
            else:
                print
            for col,val in zip(cur.description, row):
                print "%13s: %s" % (col[0], val or '')
                if val and col[0] == 'table':
                    tables.append(val)

        # now dump all tables
        for table in tables:
            print "-" * 80
            self.show_create_table(table, query)

    def show_create_table(self, name, query):
        cur = self.db.cursor()
        try:
            cur.execute("SHOW CREATE TABLE %s" % name)
        except MySQLdb.ProgrammingError:
            #print "table doesn't exist"
            #print "in query: %s" % query
            words = re.split(r"[\s\,\t\n]", query)
            #print words
            idx = words.index(name)
            if idx != -1:
                name = words[idx-1]
                if name.lower() == 'as':
                    name = words[idx-2]
            #print "new name: %s" % name
            cur.execute("SHOW CREATE TABLE %s" % name)
        for row in cur:
            print row[1]

if __name__ == '__main__':
    processor().run()
