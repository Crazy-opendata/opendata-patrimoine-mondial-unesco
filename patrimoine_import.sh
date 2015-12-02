cat patrimoine.sql | mysql -uroot -p data 
mysqlimport --default-character-set=utf8 --ignore-line=1  --fields-optionally-enclosed-by='"'     --fields-terminated-by=';'  -uroot -p data /path/to/patrimoine.csv
# http://whc.unesco.org/fr/list/xls/

# :%s/\([^;,]\+\),\1/\1/g | %s/\([^;,]\+\),\1/\1/g | %s/\([^;,]\+\),\1/\1/g
