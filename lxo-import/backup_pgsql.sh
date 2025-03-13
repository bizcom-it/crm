#!/bin/bash
log='/var/log/backup'
dbs='myhard_de'
dow=`date +%d`
echo date >> $log
for db in $dbs; do
	echo $db >> $log;
	#su postgres -c 'pg_dump '$db' > /tmp/'$dow'_'$db'.sql';
	#gzip /tmp/$dow'_'$db.sql
	#mv /tmp/$dow'_'$db.sql.gz /var/www/lx-office-erp/
	#/usr/bin/ncftpput -V -u myhard -p Uqa7fF9A myhard.de /backup /var/www/lx-office-erp/$dow'_'$db.sql.gz
	su postgres -c 'pg_dump '$db' > /tmp/'$db'.sql';
	gzip /tmp/$db.sql
	mv /tmp/$db.sql.gz /var/www/lx-office-erp/
	/usr/bin/ncftpput -V -u myhard -p Uqa7fF9A myhard.de /public_html/backup /var/www/lx-office-erp/$db.sql.gz
done
/usr/bin/ncftpput -V -u myhard -p Uqa7fF9A myhard.de /public_html/backup /usr/lib/lx-office-erp/lxo-import/cleanup.csv
wget http://www.myhard.de/cleanup.php

