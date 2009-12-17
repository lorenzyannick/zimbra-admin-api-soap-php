#! /bin/bash
# Script for generatig php documentation from NetBeans through run configuration
echo "Début de la génération de la documentation..."
phpdoc -c $2
echo "Génération de la doc PHP terminée le `date`."
firefox $3/index.html
