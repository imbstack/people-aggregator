#!/bin/env python
"""TurboGears installer"""

from distutils import log

TGVERSION = "1.0.1"
TGDOWNLOAD = "http://www.turbogears.org/download/"

#!python
"""Bootstrap setuptools installation

If you want to use setuptools in your package's setup.py, just include this
file in the same directory with it, and add this to the top of your setup.py::

    from ez_setup import use_setuptools
    use_setuptools()

If you want to require a specific version of setuptools, set a download
mirror, or use an alternate download directory, you can do so by supplying
the appropriate options to ``use_setuptools()``.

This file can also be run as a script to install or upgrade setuptools.
"""
import sys
DEFAULT_VERSION = "0.6c3"
DEFAULT_URL     = "http://cheeseshop.python.org/packages/%s/s/setuptools/" % sys.version[:3]

md5_data = {
    'setuptools-0.6b1-py2.3.egg': '8822caf901250d848b996b7f25c6e6ca',
    'setuptools-0.6b1-py2.4.egg': 'b79a8a403e4502fbb85ee3f1941735cb',
    'setuptools-0.6b2-py2.3.egg': '5657759d8a6d8fc44070a9d07272d99b',
    'setuptools-0.6b2-py2.4.egg': '4996a8d169d2be661fa32a6e52e4f82a',
    'setuptools-0.6b3-py2.3.egg': 'bb31c0fc7399a63579975cad9f5a0618',
    'setuptools-0.6b3-py2.4.egg': '38a8c6b3d6ecd22247f179f7da669fac',
    'setuptools-0.6b4-py2.3.egg': '62045a24ed4e1ebc77fe039aa4e6f7e5',
    'setuptools-0.6b4-py2.4.egg': '4cb2a185d228dacffb2d17f103b3b1c4',
    'setuptools-0.6c1-py2.3.egg': 'b3f2b5539d65cb7f74ad79127f1a908c',
    'setuptools-0.6c1-py2.4.egg': 'b45adeda0667d2d2ffe14009364f2a4b',
    'setuptools-0.6c2-py2.3.egg': 'f0064bf6aa2b7d0f3ba0b43f20817c27',
    'setuptools-0.6c2-py2.4.egg': '616192eec35f47e8ea16cd6a122b7277',
    'setuptools-0.6c3-py2.3.egg': 'f181fa125dfe85a259c9cd6f1d7b78fa',
    'setuptools-0.6c3-py2.4.egg': 'e0ed74682c998bfb73bf803a50e7b71e',
    'setuptools-0.6c3-py2.5.egg': 'abef16fdd61955514841c7c6bd98965e',
}

import sys, os

# TGBEGIN

def get_python():
    """Displays information about where to download Python for your system.
    Currently, this will always return False. The idea is that eventually
    it may actually download Python if you want it to (in which case, it
    would return True)."""
    if sys.platform == "darwin":
        print """
The recommended Python for TurboGears is Python 2.4.3. You can download
a Universal build here:

http://www.python.org/ftp/python/2.4.3/Universal-MacPython-2.4.3.dmg
"""
    elif sys.platform == "win32":
        print """
The recommended Python for TurboGears is Python 2.4.3. You can download
it here:

http://www.python.org/ftp/python/2.4.3/python-2.4.3.msi
"""
    else:
        print """
The recommended Python for TurboGears is Python 2.4.3. This version of
Python is often available in your operating system's native package
format (via apt-get or yum, for instance). You can also easily build
Python from source on Unix-like systems. Here is the source download
link for Python:

http://www.python.org/ftp/python/2.4.3/Python-2.4.3.tgz
"""
    return False

def get_yesno(default="y"):
    """Gets a valid 'y' or 'n' answer from the user."""
    while True:
        fromuser = raw_input("(y|n, default %s)" % default)
        if fromuser:
            val = fromuser[0].lower()
            if val == "y" or val == "n":
                break
        else:
            val = default
    return val

def check_python():
    if sys.version_info < (2,3):
        print """
Python 2.3 or higher is required to run TurboGears.
"""
        if not get_python():
            sys.exit(1)
    elif sys.version_info > (2,5):
        print """TurboGears does not yet support Python 2.5."""
        if not get_python():
            sys.exit(1)
    elif sys.version_info < (2,4):
        print """
TurboGears supports Python 2.3, but 2.4 is strongly recommended.
"""
        if not get_python():
            print """Would you like to continue with a Python 2.3 install?"""
            c = get_yesno(default="n")
            if c == "n":
                print """You can rerun the installer once you have an updated Python."""
                sys.exit(1)

def check_directory():
    """Ensures that the current directory does not contain a TurboGears
    directory that setuptools will attempt to install from."""
    if os.path.exists("turbogears") or os.path.exists("TurboGears"):
        print """There is a 'turbogears' directory in the current directory.
        EasyInstall will normally try to install from this directory rather
        than picking up the installation package from the net."""
        if os.access("..", os.W_OK) and not \
            os.path.exists(os.path.join("..", "turbogears")) and not \
            os.path.exists(os.path.join("..", "TurboGears")):
            print """
This installer needs a place to store the setuptools installation
package to do its work. The parent directory of this one is writeable.
Should the installation proceed from there ('n' to exit)?"""
            upone = get_yesno()
            if upone == "n":
                sys.exit(1)
            os.chdir("..")
        else:
            print """
This installer needs to run in a directory that does not contain a
'turbogears' directory and it also needs to run in a directory that
you have write access to. Please change directories and rerun this
installer.
"""
            sys.exit(1)

def tg_main():
    print "TurboGears Installer"
    check_python()
    check_directory()
    print "Beginning setuptools/EasyInstall installation and TurboGears " \
          "download\n"
    if len(sys.argv) > 1:
        args = sys.argv[1:]
    else:
        args = []
    main(args)
    if post_install_msgs:
        print """
Important additional install information
----------------------------------------
"""
        print "\n\n".join(post_install_msgs)

post_install_msgs = []

def install_command():
    try:
        from setuptools.command import easy_install as eimodule
    except ImportError:
        import easy_install as eimodule
    
    original_class = eimodule.easy_install
    class easy_install(eimodule.easy_install):
        original_class.user_options.insert(0, ("future", None, 
                                "use Genshi and SQLAlchemy"))
        original_class.boolean_options.append("future")
        def initialize_options(self):
            self.future = False
            original_class.initialize_options(self)
                    
        def finalize_options(self):
            if self.future:
                #self.args.append("TurboGears[future,testtools] >= %s" % TGVERSION)
                self.args.append("TurboGears[future] >= %s" % TGVERSION)
            else:
                #self.args.append("TurboGears[standard,testtools] >= %s" % TGVERSION)
                self.args.append("TurboGears[standard] >= %s" % TGVERSION)
            if self.upgrade is None:
                self.upgrade = True
            if self.script_dir is None:
                if os.name == "posix":
                    self.script_dir = "/usr/local/bin"
                else:
                    path = os.environ.get("PATH", "")
                    import re
                    if not re.search(r'Python\d+\\Script', path):
                        post_install_msgs.append(
    """You may need to update the PATH in your environment to allow
    you to run the "tg-admin" command or any other Python scripts that
    you install. To do that on Windows:

    Go to System Properties -> Advanced -> Environment Variables and add or edit the PATH variable to have ;C:\Python24;C:\Python24\Scripts, assuming you installed Python to the default location.""")
            
            if self.find_links is None:
                self.find_links = []
            self.find_links.append(TGDOWNLOAD)
            original_class.finalize_options(self)
    
    # install our new class
    eimodule.easy_install = easy_install    
# TGEND

def _validate_md5(egg_name, data):
    if egg_name in md5_data:
        from md5 import md5
        digest = md5(data).hexdigest()
        if digest != md5_data[egg_name]:
            print >>sys.stderr, (
                "md5 validation of %s failed!  (Possible download problem?)"
                % egg_name
            )
            sys.exit(2)
    return data


def use_setuptools(
    version=DEFAULT_VERSION, download_base=DEFAULT_URL, to_dir=os.curdir,
    download_delay=15
):
    """Automatically find/download setuptools and make it available on sys.path

    `version` should be a valid setuptools version number that is available
    as an egg for download under the `download_base` URL (which should end with
    a '/').  `to_dir` is the directory where setuptools will be downloaded, if
    it is not already available.  If `download_delay` is specified, it should
    be the number of seconds that will be paused before initiating a download,
    should one be required.  If an older version of setuptools is installed,
    this routine will print a message to ``sys.stderr`` and raise SystemExit in
    an attempt to abort the calling script.
    """
    try:
        import setuptools
        if setuptools.__version__ == '0.0.1':
            print >>sys.stderr, (
            "You have an obsolete version of setuptools installed.  Please\n"
            "remove it from your system entirely before rerunning this script."
            )
            sys.exit(2)
    except ImportError:
        egg = download_setuptools(version, download_base, to_dir, download_delay)
        sys.path.insert(0, egg)
        import setuptools; setuptools.bootstrap_install_from = egg

    import pkg_resources
    try:
        pkg_resources.require("setuptools>="+version)

    except pkg_resources.VersionConflict, e:
        # XXX could we install in a subprocess here?
        print >>sys.stderr, (
            "The required version of setuptools (>=%s) is not available, and\n"
            "can't be installed while this script is running. Please install\n"
            " a more recent version first.\n\n(Currently using %r)"
        ) % (version, e.args[0])
        sys.exit(2)

def download_setuptools(
    version=DEFAULT_VERSION, download_base=DEFAULT_URL, to_dir=os.curdir,
    delay = 15
):
    """Download setuptools from a specified location and return its filename

    `version` should be a valid setuptools version number that is available
    as an egg for download under the `download_base` URL (which should end
    with a '/'). `to_dir` is the directory where the egg will be downloaded.
    `delay` is the number of seconds to pause before an actual download attempt.
    """
    import urllib2, shutil
    egg_name = "setuptools-%s-py%s.egg" % (version,sys.version[:3])
    url = download_base + egg_name
    saveto = os.path.join(to_dir, egg_name)
    src = dst = None
    if not os.path.exists(saveto):  # Avoid repeated downloads
        try:
            from distutils import log
            if delay:
                log.warn("""
---------------------------------------------------------------------------
This script requires setuptools version %s to run (even to display
help).  I will attempt to download it for you (from
%s), but
you may need to enable firewall access for this script first.
I will start the download in %d seconds.

(Note: if this machine does not have network access, please obtain the file

   %s

and place it in this directory before rerunning this script.)
---------------------------------------------------------------------------""",
                    version, download_base, delay, url
                ); from time import sleep; sleep(delay)
            log.warn("Downloading %s", url)
            src = urllib2.urlopen(url)
            # Read/write all in one block, so we don't create a corrupt file
            # if the download is interrupted.
            data = _validate_md5(egg_name, src.read())
            dst = open(saveto,"wb"); dst.write(data)
        finally:
            if src: src.close()
            if dst: dst.close()
    return os.path.realpath(saveto)

def main(argv, version=DEFAULT_VERSION):
    """Install or upgrade setuptools and EasyInstall"""

    try:
        import setuptools
    except ImportError:
        egg = None
        try:
            egg = download_setuptools(version, delay=0)
            sys.path.insert(0,egg)
            from setuptools.command.easy_install import main
            #TGBEGIN
            install_command()
            #TGEND
            return main(list(argv)+[egg])   # we're done here
        finally:
            if egg and os.path.exists(egg):
                os.unlink(egg)
    else:
        if setuptools.__version__ == '0.0.1':
            # tell the user to uninstall obsolete version
            use_setuptools(version)

    req = "setuptools>="+version
    import pkg_resources
    try:
        pkg_resources.require(req)
    except pkg_resources.VersionConflict:
        try:
            from setuptools.command.easy_install import main
        except ImportError:
            from easy_install import main
        #TGBEGIN
        install_command()
        #TGEND
        main(list(argv)+[download_setuptools(delay=0)])
        sys.exit(0) # try to force an exit
    else:
        from setuptools.command.easy_install import main
        #TGBEGIN
        install_command()
        #TGEND
        main(argv)


def update_md5(filenames):
    """Update our built-in md5 registry"""

    import re
    from md5 import md5

    for name in filenames:
        base = os.path.basename(name)
        f = open(name,'rb')
        md5_data[base] = md5(f.read()).hexdigest()
        f.close()

    data = ["    %r: %r,\n" % it for it in md5_data.items()]
    data.sort()
    repl = "".join(data)

    import inspect
    srcfile = inspect.getsourcefile(sys.modules[__name__])
    f = open(srcfile, 'rb'); src = f.read(); f.close()

    match = re.search("\nmd5_data = {\n([^}]+)}", src)
    if not match:
        print >>sys.stderr, "Internal error!"
        sys.exit(2)

    src = src[:match.start(1)] + repl + src[match.end(1):]
    f = open(srcfile,'w')
    f.write(src)
    f.close()


if __name__=='__main__':
    if len(sys.argv)>2 and sys.argv[1]=='--md5update':
        update_md5(sys.argv[2:])
    else:
        tg_main()
