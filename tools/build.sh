#!/usr/bin/env sh
MYD=$(dirname $0)
RJ=/usr/bin/r.js
EXCL=global
BASE=$MYD/../www/js

# swap debug flag in global.js
sed -i 's/debug: true/debug: false/' $BASE/global.js

### exclusions list for individual JS minifying

# root JS
for x in $BASE/lib/*.js ; do
    NAME=${x#*lib/}
    NAME=${NAME%.js}
    EXCL=$EXCL,lib/$NAME
done

# plugins
for x in $BASE/lib/plugins/*.js ; do
    NAME=${x#*lib/plugins/}
    NAME=${NAME%.js}
    EXCL=$EXCL,lib/plugins/$NAME
done

# binding handlers
for x in $BASE/bindingHandlers/*.js ; do
    NAME=${x#*bindingHandlers/}
    NAME=${NAME%.js}
    EXCL=$EXCL,bindingHandlers/$NAME
done

# extenders
for x in $BASE/extenders/*.js ; do
    NAME=${x#*extenders/}
    NAME=${NAME%.js}
    EXCL=$EXCL,extenders/$NAME
done

# viewmodels
for x in $BASE/viewModels/*.js ; do
    NAME=${x#*viewModels/}
    NAME=${NAME%.js}
    EXCL=$EXCL,viewModels/$NAME
done

### go!

# optimize CSS
for x in $MYD/../www/css/*.css ; do
    if [ -z "$(echo $x | grep '.min.css')" ] ; then
        $RJ -o cssIn=$x out=${x%.css}.min.css optimizeCss=standard
    fi
done

# optimize build profiles
for x in $MYD/*.js ; do
    $RJ -o $x
done

# strip comments from minified CSS
sed -ri "s/\/\*.+?\*\///g" $MYD/../www/css/*.min.css

# minify actions
echo
echo "Actions"
echo "----------------"
for x in $BASE/actions/*/*.js ; do
    if [ -z "$(echo $x | grep '.min.js')" ] ; then
        NAME=${x#*actions/}
        NAME=${NAME%.js}
        echo "Minifying js/actions/$NAME.js"
        $RJ -o name=actions/$NAME out=$BASE/actions/$NAME.min.js baseUrl=$BASE exclude=$EXCL >/dev/null
        sed -ri 's/define\("[^"]+",/define(/' $BASE/actions/$NAME.min.js
    fi
done

# minify skills
echo
echo
echo "Skills"
echo "----------------"

for x in $BASE/skills/*.js ; do
    if [ -z "$(echo $x | grep '.min.js')" ] ; then
        NAME=${x#*skills/}
        NAME=${NAME%.js}
        echo "Minifying js/skills/$NAME.js"
        $RJ -o name=skills/$NAME out=$BASE/skills/$NAME.min.js baseUrl=$BASE exclude=$EXCL >/dev/null
        sed -ri 's/define\("[^"]+",/define(/' $BASE/skills/$NAME.min.js
    fi
done

echo

# swap debug flag in global.js
sed -i 's/debug: false/debug: true/' $BASE/global.js

# rebuild slim.html
#cp $MYD/../index.html $MYD/../slim.html
#sed -i 's/\.css/.min.css/g' slim.html
#sed -i 's/js\/game/js\/game.r/g' slim.html
