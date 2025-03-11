#!/bin/bash

# Initialize MySQL data directory if not already initialized
if [ ! -d "/var/lib/mysql/mysql" ]; then
    mysqld --initialize-insecure --user=mysql --datadir=/var/lib/mysql
fi

# Start MySQL server in the background
mysqld_safe --datadir=/var/lib/mysql &

# Wait for MySQL to fully start
sleep 10

# Ensure MySQL root user has no password
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '';"
mysql -e "CREATE DATABASE IF NOT EXISTS bugbounty;"

# Start Apache server in the foreground
apache2-foreground
