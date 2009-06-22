#!/usr/bin/env python

import os, os.path, sys

def run(cmd):
    return os.popen(cmd, "r").read()

def main():
    root = os.path.abspath(os.path.split(sys.argv[0])[0])
    os.chdir("%s/../../" % root)
    out = open("pacore/db/dist_files.txt", 'wt')
    getList(out)
    os.chdir("pacore/")
    print>>out, "\n\n"
    getList(out)
    if os.path.isdir("../paproject"):
      print>>out, "\n\n"
      os.chdir("../paproject")
      getList(out)
       
def getList(out):
    print>>out, "# Subversion repository info for this build:"
    allow_lines = ('Revision', 'URL', 'Repository Root')
    data = {}
    for line in run("svn info").split("\n"):
        bits = line.split(":", 1)
        if len(bits) < 2: continue
        
        if bits[0] in allow_lines:
            data[bits[0]] = bits[1].strip()

    if not data.has_key("Repository Root"):
        print "Can't find a 'Repository Root' line in the response from 'svn info'.  This could be because your Subversion install is too old: try the static builds at http://www.uncc.org/svntools/clients/linux/"
        print
        
    for k in allow_lines:
        assert data.has_key(k), "'svn info' result missing '%s' key" % k
        print>>out, "%s: %s" % (k, data[k])

    assert data['URL'].startswith(data['Repository Root'])
    print>>out, "Repository Local Path: %s" % data['URL'][len(data['Repository Root']):]

    print>>out, "\n# Files and directories:"
    for blk in run("svn info -R").split("\n\n"):
        blk = blk.strip()
        if not blk: continue
        path = kind = hash = sched = None
        for line in blk.split("\n"):
            line = line.strip()
            k, v = line.split(": ")
#            print k,v
            if k == "Path":
                path = v
            elif k == "Schedule":
                sched = v
            elif k == "Node Kind":
                kind = v
            elif k == "Checksum":
                hash = v
#        print path, kind, hash
        assert path and kind, "missing data"
        if sched != "normal":
            print "WARNING: schedule %s for %s %s" % (sched, kind, path)
        elif kind == 'directory':
            print>>out, "Dir: %s" % path
        elif kind == "file":
            assert hash, "missing hash for file %s" % path
            print>>out, "File: %s %s" % (hash, path)
        else:
            raise Exception("object %s has invalid kind %s" % (path, kind))

    #svn info | grep Revision >> $F
    #echo "# Files and directories in this archive:" >> $F
    #svn info -R | perl -pe 'if (/^Path: (.*)$/) { if (-d $1) { $_="Dir: $1\n"; } else { $_="File: ".`md5sum "$1"`; } } else { $_=""; }' >> $F
    #
    #popd

if __name__ == '__main__':
    try:
        cwd = os.getcwd()
        main()
    finally:
        os.chdir(cwd)
