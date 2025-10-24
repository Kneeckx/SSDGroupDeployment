#!/bin/bash
# Simple DIWA Auto-Deploy Script for Linode

echo "🚀 Starting DIWA Auto-Deployment..."

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run as root (use sudo)"
    exit 1
fi

# Update system
echo "📦 Updating system packages..."
apt update -y && apt upgrade -y

# Install LAMP stack
echo "🔧 Installing Apache, PHP, and SQLite..."
apt install -y apache2 php8.1 php8.1-sqlite3 php8.1-mbstring php8.1-xml php8.1-curl

# Enable Apache modules
echo "⚙️ Configuring Apache..."
a2enmod rewrite
a2enmod php8.1

# Copy files to web directory
echo "📂 Copying DIWA files..."
cp -r app/* /var/www/html/
rm -f /var/www/html/index.html

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
mkdir -p /var/www/html/database
chmod 777 /var/www/html/database

# Configure Apache virtual host
echo "🌐 Configuring virtual host..."
cat > /etc/apache2/sites-available/diwa.conf << 'EOF'
<VirtualHost *:80>
    ServerName _
    DocumentRoot /var/www/html
    DirectoryIndex index.php
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Enable site
a2ensite diwa.conf
a2dissite 000-default.conf

# Configure firewall
echo "🔥 Configuring firewall..."
ufw allow 'Apache Full'
ufw --force enable

# Restart Apache
echo "🔄 Restarting Apache..."
systemctl restart apache2
systemctl enable apache2

# Get public IP
PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || curl -s ipinfo.io/ip 2>/dev/null || echo "YOUR_SERVER_IP")

echo ""
echo "✅ DIWA Deployment Complete!"
echo ""
echo "🌐 Access your application at: http://$PUBLIC_IP"
echo "🔑 Admin Login: myadmin@test.com / mypassword123"
echo "👤 User Login: myuser@test.com / userpass123"
echo "🔄 Reset Database: http://$PUBLIC_IP/?reset=diwa"
echo ""
echo "📝 Next steps:"
echo "1. Visit your application URL"
echo "2. Go to /?reset=diwa to initialize the database"
echo "3. Login with the credentials above"
echo ""