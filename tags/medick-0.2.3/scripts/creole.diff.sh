#!/bin/bash
# $Id$
MEDICK_HOME=/wwwroot/medick/trunk
CREOLE_HOME=~/work/creole/classes
cd $CREOLE_HOME
echo "-----> Updating creole svn tree....."
svn up
REVISION=`svn info | grep 'Revision' | awk '{ print $2 }'`
FILENAME=creole-`date +%Y%m%d.%S`-$REVISION.diff
cd $MEDICK_HOME
echo "-----> Creating diff..."
diff -ur -x 'CVS' -x '.svn' $MEDICK_HOME/libs/creole $CREOLE_HOME/creole > $FILENAME
SIZE=`ls -lh $FILENAME | awk '{ print $5 }'`
echo "-----> Diff is: $FILENAME"
echo "-----> Size is: $SIZE"
if [ $SIZE -eq 20 ]; then
    echo "Nothing new on creole tree."
    rm -rf $FILENAME
    exit 0
fi
echo "-----> Diff:"
more $FILENAME
echo "<----- End;"
echo "Apply this patch? [Y/N]:"
read -n 1 ANSWER
if [ $ANSWER == 'Y' ]; then
    patch -p0 < $FILENAME
fi
rm $FILENAME
echo "Done..."
