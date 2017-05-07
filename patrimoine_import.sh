#cat patrimoine.sql | mysql -uroot -p data 
mysqlimport  --ignore-lines=1  --fields-optionally-enclosed-by='"'     --fields-terminated-by=','  -uroot -p data /tmp/patrimoine.csv
#mysqlimport --default-character-set=utf8 --ignore-line=1  --fields-optionally-enclosed-by='"'     --fields-terminated-by=';'  -uroot -p data /tmp/patrimoine.csv

echo 'update patrimoine set coordonnees=concat(latitude, ",", longitude);' | mysql -uroot -p data 

# http://whc.unesco.org/fr/list/xls/

# :%s/\([^;,]\+\),\1/\1/g | %s/\([^;,]\+\),\1/\1/g | %s/\([^;,]\+\),\1/\1/g
