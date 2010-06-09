PeopleAggregator: Connect all your digital worlds together.
================

  http://www.peopleaggregator.com/ - home

  http://www.peopleaggregator.org/ - development info
  http://wiki.peopleaggregator.org/ - development documents
  http://update.peopleaggregator.org/ - distribution and update host

  http://www.peopleaggregator.net/ - demo site

  Copyright (C) 2005-06 Broadband Mechanics, Inc.
    http://www.broadbandmechanics.com/


Installation Instructions
=========================

See also: http://wiki.peopleaggregator.org/Installation_guide

- Ensure you are running PHP 5.

- Prepare a domain name for use with PeopleAggregator.  If you want to
  use PeopleAggregator's 'networks' feature, you will need to have
  wildcard DNS configured, e.g. something like this in your DNS setup:

    example.org   A 1.2.3.4
    *.example.org A 1.2.3.4

  (where 1.2.3.4 is the IP address of your server).

  If you can't configure wildcard DNS, don't panic as it's not
  necessary, but you won't be able to create networks on your PA
  install.

- Get the code.  You have two choices here:

OPTION 1 - INSTALL FROM TARBALL

- Download an installation tarball or zip file from the links under
  option #1 on http://update.peopleaggregator.org/

- Unpack the archive onto your web server and rename the main
  directory into something convenient (e.g. /var/www/pa).  Locate the
  log and web subdirectories (these instructions assume
  /var/www/pa/log and /var/www/pa/web).

  e.g.:

    tar -vzxf ~/peopleaggregator-0.01-release-1.tar.gz
    mv peopleaggregator-0.01-release-1 /var/www/pa

OPTION 2 - INSTALL FROM SUBVERSION

- Check out the code from the testing repository into a directory on
  your web server, e.g. /var/www/pa:

    svn checkout http://update.peopleaggregator.org/svn/release/pa /var/www/pa

NOW YOU HAVE THE CODE ...

- Set the mode of the log, networks, web/cache, web/config, web/files,
  web/files/*, and web/sb-files directories to allow the web server to
  write to them:

  e.g.:

    chmod a+w /var/www/pa/log
    chmod a+w /var/www/pa/networks
    chmod a+w /var/www/pa/web/cache
    chmod a+w /var/www/pa/web/config
    chmod a+w /var/www/pa/web/files
    chmod a+w /var/www/pa/web/files/thumbnails
    chmod a+w /var/www/pa/web/files/*images
    chmod a+w /var/www/pa/web/sb-files

- Create an Apache virtual host which has the web subdirectory as its
  DocumentRoot.  Make sure "AllowOverride All" is set.

  e.g. (in the appropriate file under /etc/httpd):

    <VirtualHost *:80>
      ServerName example.org
      ServerAlias *.example.org
      DocumentRoot /var/www/pa/web
      <Directory /var/www/pa/web>
         AllowOverride All
      </Directory>
    </VirtualHost>

- View the root of your new installation in a web browser.

    http://example.org/

- Click on the 'Click here to set up PeopleAggregator' link.

- If any of the prerequisites are not present (e.g. the server is
  missing PHP's GD or XML extensions), please install them and refresh
  the config page.

- Check the text under 'detecting urls' to make sure all your domains
  are properly accessible.  If you have set up wildcard DNS as
  specified above, this should say something ending in:

  * It looks like the server is set up to host *.example.org, so
    network spawning is possible.

  * Base URL: http://%network_name%.example.org; domain suffix:
    example.org

- If everything looks good, you can now fill in the fields under
  'configuration'.

- First is the admin password.  Put in something here that's not
  easily guessable, but make sure you remember it yourself, as you'll
  need it for content administration or updating the system.  (Note
  that if you forget it, it's stored in /var/www/pa/local_config.php).

- Now, the database.  You have two choices here:

OPTION 1 - AUTOMATIC DB SETUP

- PeopleAggregator can create your database and DB user for you if you
  give it MySQL's administrator (root) login and password.

- Enter your MySQL server, and pick a database name and username and
  password.  The database must not already exist.

- Enter your MySQL administrator (root) username and password.

OPTION 2 - MANUAL DB SETUP

- If you prefer to create your database manually, that's fine too.
  Create a database and a user, and give it all privileges on the
  database.  e.g.:

    mysql -u root -p
    (enter your password)

    CREATE DATABASE peopleaggregator;
    GRANT ALL ON peopleaggregator.* TO peopleaggregator@localhost IDENTIFIED BY 'a987234kjhadsf';
    FLUSH PRIVILEGES;

- Now enter the database name, username and password in the fields in
  your browser, and leave the administrator password blank.

AFTER THE DB IS READY ...

- Click 'Set up PeopleAggregator'

- With any luck, it should populate the database and initialize
  everything correctly.  Now all you have to do is:

- Move the created local_config.php file up into the /var/www/pa
  directory:

    mv /var/www/pa/web/config/local_config.php /var/www/pa/

- Click on the 'click here' link.

- And you should have a working PeopleAggregator!

- If you are asked for a private alpha login name and password, just
  enter 'paalpha' (without the quotes) into both fields.  (This screen
  will be removed when we go live).

Credits
=======

The Boss:

  Marc Canter, Broadband Mechanics

Development:

  Tekriti Software:

    Gaurav Bhatnagar (project management)
    Ashish Kumar (project management)
    Nibha Sachan (users, media, content archive)
    Arvind Upadhyaya (messaging, groups, tags)
    Gurpreet Singh (content creation and display)
    Manish Dhingra (test lead)
    Manoj Gupta (testing)
    Isha Dawar (testing)
  
  Broadband Mechanics:
  
    Phillip Pearson (API, updater, release scripts, BaseCamp, install docs)
    Martin Spernau (draggable blocks, remote authentication)
    Richard MacManus (documentation)
