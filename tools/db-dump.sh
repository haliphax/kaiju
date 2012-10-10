#!/bin/sh
MYPATH="/home/haliphax/vhosts/kaiju"
DBPASS=rGtA6sUdFaZPFQNe
rm $MYPATH/db.sql
mysqldump -ukaiju -p$DBPASS \
	--ignore-table=roadhaus_kaiju_test.ci_sessions \
	--ignore-table=roadhaus_kaiju_test.user \
	--ignore-table=roadhaus_kaiju_test.user_mod \
	--ignore-table=roadhaus_kaiju_test.user_priv_mapedit \
	--ignore-table=roadhaus_kaiju_test.user_actor \
	--ignore-table=roadhaus_kaiju_test.actor \
	--ignore-table=roadhaus_kaiju_test.actor_item \
	--ignore-table=roadhaus_kaiju_test.actor_effect \
	--ignore-table=roadhaus_kaiju_test.actor_npc \
	--ignore-table=roadhaus_kaiju_test.actor_skill \
	--ignore-table=roadhaus_kaiju_test.actor_class \
	--ignore-table=roadhaus_kaiju_test.actor_item_ammo \
	--ignore-table=roadhaus_kaiju_test.pdata \
    --ignore-table=roadhaus_kaiju_test.event \
    --ignore-table=roadhaus_kaiju_test.event_thread kaiju_dev \
    > $MYPATH/db.sql
mysqldump -ukaiju -p$DBPASS \
	-i -R -d kaiju_dev ci_sessions user user_mod user_priv_mapedit \
		actor actor_item actor_effect actor_npc actor_skill actor_class \
		actor_item_ammo pdata event event_thread \
	>> $MYPATH/db.sql

