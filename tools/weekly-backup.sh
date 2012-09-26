#!/bin/sh
cd /home/haliphax/vhosts/kaiju-dev
rm kaiju-dev.tar.bz2
mysqldump -ukaiju_rpg -p^K4iju~Rul3z \
	-i --ignore-table=kaiju_test.event \
	--ignore-table=kaiju_test.event_thread \
	kaiju_test \
	> kaiju-dev.sql
mysqldump -ukaiju_rpg -p^K4iju~Rul3z \
	-d kaiju_test event event_thread \
	> events.sql
tar cjf kaiju-dev.tar.bz2 kaiju-dev.sql events.sql
rm kaiju-dev.sql events.sql
uuencode kaiju-dev.tar.bz2 kaiju-dev.tar.bz2 | mail haliphax@gmail.com -s "kaiju-dev weekly backup"
