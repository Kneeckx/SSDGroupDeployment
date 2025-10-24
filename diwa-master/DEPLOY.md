# DIWA Deployment Instructions for Linode

## Quick Deploy Options

### Option 1: Using Linode Marketplace (Recommended)
1. Log into Linode Cloud Manager
2. Create → Marketplace
3. Search for "Docker" or "LAMP Stack"
4. Deploy with these settings:
   - Image: Ubuntu 22.04 LTS
   - Region: Choose closest to you
   - Plan: Nanode 1GB ($5/month)
5. Upload this project folder
6. Run: `chmod +x deploy.sh && ./deploy.sh`

### Option 2: Using Docker (If Docker is available)
1. Upload project to your Linode
2. Run: `docker-compose up -d`
3. Access at: http://YOUR_LINODE_IP

### Option 3: Manual LAMP Stack
1. Create Ubuntu 22.04 Linode
2. Upload project files to `/var/www/html`
3. Run the deployment script: `./deploy.sh`

## File Structure for Deployment
```
diwa-master/
├── package.json          # Node.js deployment config
├── Dockerfile            # Docker container config
├── docker-compose.yml    # Docker orchestration
├── deploy.sh             # Auto-deployment script
├── linode-deploy.yml     # Kubernetes/Linode config
├── app/                  # Your DIWA application
│   ├── index.php
│   ├── config.php
│   └── includes/
└── database/             # SQLite database location
```

## Access Credentials After Deployment
- Admin: myadmin@test.com / mypassword123
- User: myuser@test.com / userpass123
- Reset DB: http://YOUR_IP/?reset=diwa

## Deployment Steps
1. Zip the entire `diwa-master` folder
2. Upload to Linode via their file manager or SCP
3. SSH into your server
4. Extract the files
5. Run: `chmod +x deploy.sh && ./deploy.sh`
6. Access your app at: http://YOUR_LINODE_IP