#!/bin/bash

# Start MySQL service
service mysql start

# Create the database and user
mysql -e "CREATE DATABASE IF NOT EXISTS bugbounty;"
mysql -e "GRANT ALL PRIVILEGES ON bugbounty.* TO 'root'@'localhost' IDENTIFIED BY 'rootpassword';"

# Start Apache server
apache2-foreground
