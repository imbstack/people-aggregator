#!/usr/bin/python

# run through php files and look for code we don't like

import os, os.path, re

class Checker:
    def __init__(self):
        self.loaded = None
        
    def note(self, msg):
        print "[%s] %s" % (self.fn, msg)
        
    def check_for(self, s):
        if self.txt.find(s) != -1:
            self.note("Found text %s" % `s`)

    def start_file(self):
        if self.loaded != self.fn:
            self.txt = open(self.fn).read()
            self.loaded = self.fn
            self.err_found = 0

            self.dirname, self.leaf = os.path.split(os.path.abspath(self.fn))

            # strip comments
            self.code = re.compile(r"/\*.*?\*/\s*", re.S).sub("", self.txt)

    def check_php(self):
        self.start_file()

        if not self.fn.endswith("Dal.php"):
            self.check_for("Dal::get_connection")
            self.check_for("disconnect")
            self.check_for("::isError")

        if self.leaf.endswith(".php"):
            # php files
            if self.dirname.endswith("/web"):
                # make sure we include config or page in the first 20
                # lines, otherwise this might be a library file in the
                # wrong place.

                # skip files which are just text
                if self.txt.find("<?") != -1:
                    first_lines = self.code[:1024]
                    if first_lines.find("page.php") == -1 and first_lines.find("config.inc") == -1:
                        self.note("Not including page.php or config.inc in first 1024 bytes; might be a misplaced library")

    ignore_patterns = [
        'dist.tmp',
        '.svn',
        'phpdoc',
        'PhpDocumentor',
        ]
    
    def run(self):
        for path, dirs, files in os.walk("../.."):
            ignore = 0
            for pat in self.ignore_patterns:
                if path.find(pat) != -1:
                    ignore = 1
                    break
            if ignore: continue

            for leaf in files:
                self.fn = os.path.join(path, leaf)
                if self.fn.endswith(".php"):
                    self.check_php()

if __name__ == '__main__':
    Checker().run()
