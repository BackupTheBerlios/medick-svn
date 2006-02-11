#!/bin/bash
# $Id$

mkdir -p vendor
cd vendor
echo 'Script for updating/downloading Medick req. from CVS'

echo '===================================================='
echo 'creole.....'
echo '[ HINT ] Type << guest >> as password...'
cvs -d:pserver:guest@cvs.tigris.org:/cvs login
echo 'checking out creole'
cvs -d:pserver:guest@cvs.tigris.org:/cvs co -d creole/ creole/creole/classes/creole
echo '===================================================='
echo ' simpletest.....'
echo '[ HINT ] Press << enter >> for password'
cvs -d:pserver:anonymous@cvs.sourceforge.net:/cvsroot/simpletest login
echo 'checking out simpletest.....'
cvs -z3 -d:pserver:anonymous@cvs.sourceforge.net:/cvsroot/simpletest co -d simpletest -P simpletest/

echo '<======== Updater [ DONE ]'
cd ../

