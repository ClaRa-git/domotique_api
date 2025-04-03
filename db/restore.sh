#!/usr/bin/sh

mariadb api_domotique -uroot -psuperAdmin < /root/init.sql
echo "Restauration terminée"
