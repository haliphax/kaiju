#!/bin/sh
cd /home/haliphax/vhosts/kaiju-dev/www
rm db.tar.bz2
mysqldump -ukaiju_rpg -p^K4iju~Rul3z \
	--ignore-table=kaiju_test.ci_sessions \
	--ignore-table=kaiju_test.user \
	--ignore-table=kaiju_test.user_mod \
	--ignore-table=kaiju_test.user_priv_mapedit \
	--ignore-table=kaiju_test.user_actor \
	--ignore-table=kaiju_test.actor \
	--ignore-table=kaiju_test.actor_item \
	--ignore-table=kaiju_test.actor_effect \
	--ignore-table=kaiju_test.actor_skill \
	--ignore-table=kaiju_test.actor_class \
	--ignore-table=kaiju_test.actor_item_ammo \
	--ignore-table=kaiju_test.pdata \
    --ignore-table=kaiju_test.event \
    --ignore-table=kaiju_test.event_thread kaiju_test \
    > db.sql
mysqldump -ukaiju_rpg -p^K4iju~Rul3z \
	-i -R -d kaiju_test ci_sessions user user_mod user_priv_mapedit \
		actor actor_item actor_effect actor_skill actor_class \
		actor_item_ammo pdata event event_thread \
	> empty.sql
tar cjf db.tar.bz2 db.sql empty.sql
chmod 600 db.tar.bz2
rm db.sql empty.sql
