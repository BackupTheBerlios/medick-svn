#!/bin/bash

#
# $Id$
#

# php doc path.
PHPDOC="/W/Devel/PhpDocumentor-1.3.0RC4"

# "log" function
function say {
	echo "[`date +"%Y-%m-%d %H:%M:%S"`] ===> $1"
}

# {{{ find what version we are building
version=`cat VERSION`
build="build/medick-$version"

say "Building release of medick-$version"
# }}}

# {{{ check if the build folder exists or if we have the force option
if [ -d $build ]; then
	say "$build exists"
	if [[ ("$1" == "-f") || ("$1" == "--force") ]]; then
		say "force a re-build"
		rm -rf $build
	else
		say "Use -f [--force] to force a re-build."
		exit
	fi
fi
# }}}

# {{{ prepare for build
framework=$build/framework
applications=$build/applications
doc=$build/doc
api=$build/api
mkdir -p build
mkdir -p $build
rm -rf $doc
rm -rf $api
mkdir -p $doc
mkdir -p $api
# }}}

# {{{ svn export 
say "Exporting applications to $applications"
svn export -q svn://svn.berlios.de/medick/applications $applications

say "Exporting trunk to $framework"
svn export -q svn://svn.berlios.de/medick/trunk $framework
# }}}}

# {{{ build the API docs 
doc_entries=( $framework/libs/action/ \
						  $framework/libs/active/ \
						  $framework/libs/context/ \
						  $framework/libs/configurator/ \
						  $framework/libs/logger/ \
						  $framework/libs/medick/ \
							$framework/README \
							$framework/TODO \
							$framework/LICENSE \
							$framework/CHANGELOG \
							$framework/VERSION )
							
say "Preparing API docs."							
for entry in ${doc_entries[@]}
do
	if echo "$entry" | grep -q libs
	then
		p=`echo $entry | sed -e "s/framework\/libs/doc/"`
		cp -r $entry $p
	else
		cp $entry $doc
	fi
done

say "Running phpdoc..."

php -dinclude_path=$PHPDOC -derror_reporting='E_ALL ^ E_NOTICE' $PHPDOC/pear-phpdoc --pear off -q -d $doc/ -t $api -ti "Medick API Documentation" -dn "medick.core" -s on -o "HTML:frames:earthli"

# copy our templates
# cp docs/medick.api.template/blank.html $api/.

say "Apply medick templates on API docs."
sed -e "s/#{date}/`date`/" -e "s/#{version}/$version/" docs/medick.api.template/blank.html > $api/blank.html
cp docs/medick.api.template/packages.html $api/.
cp docs/medick.api.template/media/stylesheet.css $api/media/.

# cleanup
say "Clean up."
rm -rf $doc $framework/docs $framework/scripts $framework/test/
mv $api $framework/docs

# }}}

# {{{ pack.
say "Packing files..."
cd build
tar -czf medick-$version.tgz medick-$version
zip -9 -q -r medick-$version.zip medick-$version
cd ../

say "Created tgz file to build/medick-$version.tgz"
say "Created zip file to build/medick-$version.zip"
# }}}

say "All Done."
