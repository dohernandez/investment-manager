#!/usr/bin/env bash

# Install symfony installer
mkdir -p /usr/local/bin
curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony
chmod a+x /usr/local/bin/symfony

# Create new symfony_project
symfony new symfony_project 3.4

# Move symfony_project to root
mv symfony_project/* .
rm -rf symfony_project

# Update application with the latest version of the libraries
composer update

echo "var/" >> .gitignore
