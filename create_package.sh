#!/bin/sh

rm pocitadlo-libi-se.zip

mkdir pocitadlo-libi-se
mkdir pocitadlo-libi-se/pocitadlo-libi-se-common

cp \
  pocitadlo-libi-se-common/index.php \
  pocitadlo-libi-se-common/LICENSE \
  pocitadlo-libi-se-common/pocitadlolibise.css \
  pocitadlo-libi-se/pocitadlo-libi-se-common/

cp \
  index.php \
  LICENSE \
  pocitadlo-libi-se.php \
  pocitadlo-libi-se\

zip -r pocitadlo-libi-se.zip pocitadlo-libi-se/

rm -rf pocitadlo-libi-se/
