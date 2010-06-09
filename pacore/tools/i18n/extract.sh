pushd `dirname $0`

# messages.po gets recreated every time, while the translated files are kept
rm -f messages.po

# figure out where text could be lurking
FILES=`find ../../web ../../api ../../ext ! -name 'akismet.php' -name '*.php' -or -name '*.tpl' -or -name '*.xml'`


CMD="xgettext -L PHP --keyword=__ $FILES"

$CMD

# and extract/update it all
for L in `cat languages.txt`; do
    echo "Updating language: $L"
    F=../../web/languages/$L/messages.po
    if [ ! -f $F ]; then
    	cp messages.po $F
    fi
    # $CMD -j -o $F
    # use msgmerge to retain translations that still match
    # but dump all translations and source references that are no longer in the code
    msgmerge -U $F messages.po
done

popd
