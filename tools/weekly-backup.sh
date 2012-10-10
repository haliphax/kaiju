#!/bin/sh

DBPASS=rGtA6sUdFaZPFQNe
PWD=$(pwd)

cd /home/haliphax/vhosts/kaiju
rm kaiju-dev.tar.bz2
mysqldump -ukaiju -p$DBPASS \
	-i --ignore-table=kaiju_test.event \
	--ignore-table=kaiju_test.event_thread \
	kaiju_dev \
	> kaiju-dev.sql
mysqldump -ukaiju -p$DBPASS \
	-d kaiju_dev event event_thread \
	> events.sql
tar cjf kaiju-dev.tar.bz2 kaiju-dev.sql events.sql
rm kaiju-dev.sql events.sql
uuencode kaiju-dev.tar.bz2 kaiju-dev.tar.bz2 | mail haliphax@gmail.com -s "kaiju weekly backup"
cd $PWD
