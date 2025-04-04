#!/usr/bin/sh

mariadb domotique -uroot -psuperAdmin < /root/init.sql
echo "Restauration terminée"
