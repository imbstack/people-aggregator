#!/usr/bin/python

import sys, os, os.path, time

def cmd(s):
    print s
    return os.system(s)

def main():
    args = {}
    try:
        args['slaveid'], args['home'] = sys.argv[1:3]
        loading_sql = 0
        if len(sys.argv) > 3:
            args['sqldump'], args['db_name'], args['local_user'], args['local_password'], args['master_host'], args['master_port'], args['master_user'], args['master_password'], = sys.argv[3:]
            loading_sql = 1
    except ValueError:
        print """Syntax: %s <slave id> <slave home data dir> [<path to sql dump of master> <database name> <local database username> <local database password> <master hostname> <master port> <slave username on master> <slave password on master> <master log filename> <master log position>]

(The database and local user will be created, and connected to the master using the supplied slave user details and master log filename and position.)

To prepare the master and generate the SQL dump, first make sure your systemwide my.cnf includes the log-bin, binlog-do-db and server-id variables:

log-bin=/var/log/mysql/mysql-bin.log
binlog-do-db=<database name>
server-id=1

Then log in using the mysql command line client and:

GRANT REPLICATION SLAVE ON *.* TO 'slave_user'@'%%' IDENTIFIED BY '<insert password here>';
FLUSH PRIVILEGES;

Now exit and take a backup of the database:

mysqldump --master-data <database name> > pa.sql

""" % sys.argv[0]
        return 1

    slaveid = args['slaveid'] = int(args['slaveid'])
    home = args['home'] = os.path.abspath(args['home'])
    args['port'] = 3306 + slaveid
    pidfn = args['pidfn'] = os.path.join(home, 'mysqld.pid')

    if loading_sql:
        args['master_port'] = int(args['master_port'])
        
    print "Setting up slave MySQL server ID %(slaveid)d in directory %(home)s" % args

    for p in (home, os.path.join(home, 'log'), os.path.join(home, 'data')):
        if not os.path.exists(p):
            print "Creating directory %s" % p
            os.mkdir(p)

    std_args = "--datadir=%(home)s/data --pid-file=%(pidfn)s --skip-locking --port=%(port)d --socket=%(home)s/mysqld.sock --log=%(home)s/log/mysql.log --log-slow-queries=%(home)s/log/mysql-slow.log --server-id=%(slaveid)d --log-error=%(home)s/log/error.log --log-bin=%(home)s/log/bin.log" % args
#     --log-bin-index=%(home)s/login/log-bin.index

    initializing = 0
    if not os.path.exists(os.path.join(home, 'data', 'mysql', 'user.MYD')):
        print "Initialising database"
        initializing = 1
        cmd("mysql_install_db %s" % std_args)

    if os.path.exists(pidfn):
        print "Killing existing instance"
        cmd("kill %s" % open(pidfn).readline().strip())
        for x in range(10):
            time.sleep(1)
            if not os.path.exists(pidfn):
                print "Looks like it's shut down"
                break
        if os.path.exists(pidfn):
            print "Possibly failed to shut down existing instance; this could be a problem!"

    cmd("/usr/sbin/mysqld %s &" % std_args)

    if initializing and loading_sql:
        print "Waiting a couple of seconds to make sure the database is up and running"
        time.sleep(2)
        print "Loading SQL dump from master and setting up slave relationship"
        cmdline = "mysql --host=127.0.0.1 --port=%(port)d -u root" % args
        sql = """CREATE DATABASE `%(db_name)s`;
GRANT ALL ON `%(db_name)s`.* TO %(local_user)s@localhost IDENTIFIED BY '%(local_password)s';
FLUSH PRIVILEGES;
USE `%(db_name)s`;
SLAVE STOP;
CHANGE MASTER TO MASTER_HOST='%(master_host)s', MASTER_PORT=%(master_port)d, MASTER_USER='%(master_user)s', MASTER_PASSWORD='%(master_password)s';
SOURCE %(sqldump)s;
SLAVE START;
""" % args
        print cmdline
        print sql
        os.popen(cmdline, "w").write(sql)
    
if __name__ == '__main__':
    sys.exit(main())

