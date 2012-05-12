#! /bin/bash
# Script for generatig php documentation

DOCDIR=docs/
PKGDIR=Zm/

[ -d $DOCDIR ] && rm -r $DOCDIR
phpdoc --defaultpackagename ZimbraSoapPhp -i *test*.php,utils.php -d $PKGDIR -t $DOCDIR
