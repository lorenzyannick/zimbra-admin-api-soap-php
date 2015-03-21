#! /bin/bash
# Script used to generate php documentation

DOCDIR=docs
PKGDIR=.

phpdoc -v --title="ZimbraSoapPhp" --defaultpackagename ZimbraSoapPhp -i config.php,*/utils.php -d $PKGDIR -t $DOCDIR --template="responsive-twig"
