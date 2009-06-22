pushd `dirname $0`

# messages.po gets recreated every time, while the translated files are kept
rm -f messages.po

# figure out where text could be lurking
FILES=`find ../../web ../../api ../../ext ! -name 'akismet.php' -name '*.php' -or -name '*.tpl'`
CMD="xgettext -L PHP --keyword=__ $FILES"

$CMD

# and extract/update it all
for L in `cat languages.txt`; do
    echo "Updating language: $L"
    F=../../web/languages/$L/messages.po
    if [ ! -f $F ]; then
	cp messages.po $F
    fi
    $CMD -j -o $F
done

popd
