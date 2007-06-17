@ECHO OFF
REM $Id: updater.sh 94 2005-07-17 13:14:44Z aurelian $

SET CVS="C:\Program Files\TortoiseCVS\cvs.exe"

MKDIR vendor
CD vendor
ECHO "Script for updating/downloading Medick req. from CVS"

ECHO "===================================================="
ECHO "creole....."
ECHO "[ HINT ] Type { guest } as password..."
%CVS% -d:pserver:guest@cvs.tigris.org:/cvs login
ECHO "checking out creole"
%CVS% -d:pserver:guest@cvs.tigris.org:/cvs co -d creole/ creole/creole/classes/creole
ECHO "===================================================="
ECHO "simpletest....."
ECHO "[ HINT ] Press { enter } for password"
%CVS% -d:pserver:anonymous@cvs.sourceforge.net:/cvsroot/simpletest login
ECHO "checking out simpletest....."
%CVS% -z3 -d:pserver:anonymous@cvs.sourceforge.net:/cvsroot/simpletest co -d simpletest -P simpletest/

ECHO "<======== Updater [ DONE ]"
CD ../

