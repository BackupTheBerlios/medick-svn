#!/bin/bash

# mktar.sh - create a tar file from a subversion working copy
# Based on http://svnpkg.tigris.org/source/browse/*checkout*/svnpkg/trunk/mktar/usr/lib/svnpkg/mktar.sh?content-type=text%2Fplain&rev=2
# Original Copyright 2004-2005 Dick Marinus <dick.marinus@etos.nl>
# $Id$

echo 'MkTAR, create a tar file from a SVN working copy'
PW=`pwd`
C_DATE=`date +%F`
mkdir -p ~/tmp/medick
cd ~/tmp/medick
echo 'Checking out trunk/medick...'
svn checkout -q svn://svn.berlios.de/medick/trunk medick
echo 'Done.'
TARNAME=medick
TMPDIR=~/tmp/mktar-$(id -un)-$$/
TMPFILE=~/tmp/mktar-$(id -un)-$$-file
# cleanup
rm -rf "${TMPDIR}" "${TMPFILE}"
mkdir -p ${TMPDIR}/${TARNAME}
cd medick
echo 'Removing .svn folders...'
# concat workingcopies together
find . ! -regex '.*/\.svn.*' -exec sh -c "
    if [ -d "{}" ] ; then
        mkdir -p "${TMPDIR}/${TARNAME}/{}"
    else
        DIRNAME=$(dirname {})
        mkdir -p "${TMPDIR}/${TARNAME}/${DIRNAME}"
        cat "${TMPDIR}/${TARNAME}/{}" "{}" > $TMPFILE 2>/dev/null
        mv "${TMPFILE}" "${TMPDIR}/${TARNAME}/{}"
    fi
" \;
cd - > /dev/null
echo 'Done.'
echo "Creating tar.gz file: ${TARNAME}-${C_DATE}.tar.gz"
tar czf ${TARNAME}.tar.gz -C "${TMPDIR}/" ${TARNAME}
mv ${TARNAME}.tar.gz ${PW}/${TARNAME}-${C_DATE}.tar.gz
cd $PW
echo 'Done.'
echo 'Cleaning-up...'
#clean up
rm -rf "${TMPDIR}" "${TMPFILE}"
rm -rf ~/tmp/medick
echo 'MkTAR Done.'
