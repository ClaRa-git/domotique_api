#!/usr/bin/sh
mariadb-dump domotique -uroot -psuperAdmin > /root/init.sql
echo "Sauvegarde terminée"