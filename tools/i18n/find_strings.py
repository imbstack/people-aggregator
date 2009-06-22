#!/usr/bin/python

"""

find_strings.py: (in development) script to find possible strings that
need to be marked up for translation.

"""

import glob, sys, os.path, re

def main():
    print "Looking for strings to translate"
    root = os.path.abspath(os.path.join(os.path.split(sys.argv[0])[0], '..', '..'))
    print "PAGES"
    for f in glob.glob("%s/web/*.php" % root):
        print "=========== %s =============" % f
        text = open(f).read()

        strings = []

        pos = 0
        in_str = 0
        esc = 0
        for c in text:
            if in_str:
                if esc:
                    current += c
                    esc -= 1
                elif c == '\\':
                    current += c
                    esc = 1
                elif c == str_start:
                    strings.append((start_pos, pos, current))
                    in_str = 0
                else:
                    current += c
            else:
                if c in "'\"":
                    start_pos = pos + 1
                    current = ''
                    in_str = 1
                    str_start = c
                else:
                    pass
            pos += 1
        for start_pos, end_pos, s in strings:
            if not s.strip(): continue
            assert s == text[start_pos:end_pos], "string %s doesn't match text from %d:%d (%s) in file" % (`s`, start_pos, end_pos, `text[start_pos:end_pos]`)

            if text[start_pos-4:start_pos-1] == '__(' or text[start_pos-2] == '[' or text[end_pos+1] == ']': continue

            if s.startswith("/") or s.endswith(".tpl"): continue

            if s.strip() in ('edit', 'name', 'desc', 'network_header', 'network_id',
                             'top', 'middle', 'bottom', 'delete', 'tagline', 'category',
                             'action', 'address', 'extra', 'changed', 'message', 'skip', 'stats', 'basic',
                             ): continue

            preceding = text[:start_pos-1].rstrip()
            if preceding.endswith("can("): continue
            
            following = text[end_pos+2:].lstrip()
            if following.startswith("=>"): continue
            
            bad = 0
            for stopword in ('Beta', 'path_prefix', 'base_url', 'includes/', '->', 'location:', 'Location:', '="',
                             'header_image', 'setup_module', 'network_group',
                             ):
                if stopword in s:
                    bad = 1
                    break
            if bad: continue

            print s#,"      ",text[start_pos-5:end_pos+5]
                       
main()
