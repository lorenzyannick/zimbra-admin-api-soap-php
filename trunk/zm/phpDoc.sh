#! /bin/bash
# Script for generatig php documentation

DOCDIR=docs
PKGDIR=.

phpdoc -v --defaultpackagename ZimbraSoapPhp -i config.php,*/utils.php,nbproject/* --sourcecode on -d $PKGDIR -t $DOCDIR
