import re

# read and parse default_config.php and local_config.php
def read_local_config(files):
    config_vars = {}

    if type(files) != type([]):
        files = [files]
    for fn in files:
        for line in open(fn).readlines():
            line = line.strip()
            if not line or line.startswith("//"): continue
            m = re.search(r'\$([a-zA-Z\_]+)\s*=\s*(.*?);', line)
            if m:
                k, v = m.groups()
                if v[0] in '"\'':
                    v = v[1:-1];
                config_vars[k] = v

    return config_vars

