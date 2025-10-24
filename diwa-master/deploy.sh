#!/bin/bash

# DIWA Deployment Script for Linode
echo "Starting DIWA deployment..."

# Update system packages
apt-get update -y
apt-get upgrade -y

# Install required packages
apt-get install -y apache2 php8.1 php8.1-sqlite3 php8.1-mbstring php8.1-xml php8.1-curl

# Enable Apache modules
a2enmod rewrite
a2enmod php8.1

# Configure Apache
cat > /etc/apache2/sites-available/diwa.conf << 'EOF'
<VirtualHost *:80>
    ServerName _
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/diwa_error.log
    CustomLog ${APACHE_LOG_DIR}/diwa_access.log combined
</VirtualHost>
EOF

# Enable the site
a2ensite diwa.conf
a2dissite 000-default.conf

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Create database directory
mkdir -p /var/www/html/database
chown -R www-data:www-data /var/www/html/database
chmod 777 /var/www/html/database

# Restart Apache
systemctl restart apache2
systemctl enable apache2

# Configure firewall
ufw allow 'Apache Full'
ufw --force enable

echo "DIWA deployment completed successfully!"
echo "Access your application at: http://$(curl -s ifconfig.me)"