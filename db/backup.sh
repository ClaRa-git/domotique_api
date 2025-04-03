#!/usr/bin/sh
mariadb-dump api_domotique -uroot -psuperAdmin > /root/init.sql
echo "Sauvegarde terminée"