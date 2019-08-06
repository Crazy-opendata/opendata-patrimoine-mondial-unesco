# Procedure d'import
# Télécharger le XLS http://whc.unesco.org/fr/list/xls/
# Convertir les champs latitude, longitude, aire qui contiennent des "," en "." pour qu'ils soient numériques
# Faire conversion en CSV
# - Choisir « séparé par des , » et « encapsulé dans des " »
# - Cocher la case "mettre des guillemets autour de colonnes texte"

USER="<user>"
PASS="<pass>"

echo "Copy file to /tmp/patrimoine.csv"
cp whc-sites-2019.csv /tmp/patrimoine.csv

echo "Applying little patches for coordinates and multiple locations"
vim -c 'source patch.vim' -c ':wq' /tmp/patrimoine.csv

echo "Creating patrimoine structure"
cat patrimoine_structure.sql | mysql -u$USER -p$PASS data 

echo "Importing data"
mysqlimport  --ignore-lines=1  --fields-optionally-enclosed-by='"'     --fields-terminated-by=','  -u$USER -p$PASS data /tmp/patrimoine.csv
#mysqlimport --default-character-set=utf8 --ignore-line=1  --fields-optionally-enclosed-by='"'     --fields-terminated-by=';'  -u$USER -p data /tmp/patrimoine.csv

echo "Patching data"
echo 'update patrimoine set multiple=0, coordonnees=concat(latitude, ",", longitude);' | mysql -u$USER -p$PASS data 


# <i></i><small></small><br /><I></I><em></em><sup></sup><u></u><U></U>
echo 'update patrimoine set name_fr=replace(replace(name_fr, "<i>", ""), "</i>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_fr=replace(replace(name_fr, "<I>", ""), "</I>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_fr=replace(replace(name_fr, "<em>", ""), "</em>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_fr=replace(replace(name_fr, "<U>", ""), "</U>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_fr=replace(replace(name_fr, "<u>", ""), "</u>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_fr=replace(replace(name_fr, "<sup>", ""), "</sup>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_fr=replace(replace(name_fr, "<small>", ""), "</small>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_fr=replace(replace(replace(name_fr, "<br>", ""), "<br/>", ""), "<br />", "");' | mysql -u$USER -p$PASS data

echo 'update patrimoine set name_en=replace(replace(name_en, "<i>", ""), "</i>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_en=replace(replace(name_en, "<I>", ""), "</I>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_en=replace(replace(name_en, "<em>", ""), "</em>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_en=replace(replace(name_en, "<U>", ""), "</U>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_en=replace(replace(name_en, "<u>", ""), "</u>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_en=replace(replace(name_en, "<sup>", ""), "</sup>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_en=replace(replace(name_en, "<small>", ""), "</small>", "");' | mysql -u$USER -p$PASS data
echo 'update patrimoine set name_en=replace(replace(replace(name_en, "<br>", ""), "<br/>", ""), "<br />", "");' | mysql -u$USER -p$PASS data


echo "update patrimoine set name_multiple='Hôtel de ville d\'Anvers' where id_no=943 and id_multiple='003';" | mysql -u$USER -p$PASS data
echo "update patrimoine set name_multiple='Cathédrale Notre-Dame d\'Anvers' where id_no=943 and id_multiple='002';" | mysql -u$USER -p$PASS data
echo "update patrimoine set name_multiple='Beffroi, halle aux draps et Mammelokker, Gand' where id_no=943 and id_multiple='008';" | mysql -u$USER -p$PASS data
echo "update patrimoine set name_multiple='Beffroi et Halles, Bruges' where id_no=943 and id_multiple='004';" | mysql -u$USER -p$PASS data


