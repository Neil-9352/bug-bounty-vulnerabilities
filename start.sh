#!/bin/bash

# Initialize MySQL data directory if not already initialized
if [ ! -d "/var/lib/mysql/mysql" ]; then
    mysqld --initialize-insecure --user=mysql --datadir=/var/lib/mysql
fi

# Start MySQL server in the background
mysqld_safe --datadir=/var/lib/mysql &

# Wait for MySQL to fully start
sleep 10

# Create the database and user if not exists
mysql -u root -e "CREATE DATABASE IF NOT EXISTS bugbounty;"
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';"
mysql -u root -e "GRANT ALL PRIVILEGES ON bugbounty.* TO 'root'@'localhost';"

# Start Apache server (foreground)
apache2-foreground
