#!/bin/bash
# KejaLink Local Database Setup Script

echo "🏠 KejaLink Local Database Setup"
echo "=================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Database configuration
DB_NAME="kejalink_local"
DB_USER="kejalink_dev"
DB_PASS="dev_password_123"

echo "This script will:"
echo "1. Create a MySQL database: $DB_NAME"
echo "2. Create a MySQL user: $DB_USER (password: $DB_PASS)"
echo "3. Import the database schema"
echo "4. Run migrations"
echo ""

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}❌ MySQL is not installed!${NC}"
    echo "Please install MySQL first:"
    echo "  sudo apt install mysql-server"
    exit 1
fi

# Ask for MySQL root password
echo -e "${YELLOW}Enter your MySQL root password:${NC}"
read -s MYSQL_ROOT_PASS
echo ""

# Test MySQL connection
if ! mysql -u root -p"$MYSQL_ROOT_PASS" -e "SELECT 1" &> /dev/null; then
    echo -e "${RED}❌ Failed to connect to MySQL. Check your root password.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ MySQL connection successful${NC}"

# Create database
echo "Creating database..."
mysql -u root -p"$MYSQL_ROOT_PASS" <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database '$DB_NAME' created${NC}"
else
    echo -e "${RED}❌ Failed to create database${NC}"
    exit 1
fi

# Create user
echo "Creating database user..."
mysql -u root -p"$MYSQL_ROOT_PASS" <<EOF
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ User '$DB_USER' created with all privileges${NC}"
else
    echo -e "${YELLOW}⚠ User may already exist, continuing...${NC}"
fi

# Import schema
echo "Importing database schema..."
if [ -f "mysql_schema.sql" ]; then
    mysql -u root -p"$MYSQL_ROOT_PASS" "$DB_NAME" < mysql_schema.sql
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Schema imported successfully${NC}"
    else
        echo -e "${RED}❌ Failed to import schema${NC}"
        exit 1
    fi
else
    echo -e "${RED}❌ mysql_schema.sql not found!${NC}"
    exit 1
fi

# Import migrations
echo "Running migrations..."
if [ -f "php-backend/migrations/001_add_password_reset_tokens.sql" ]; then
    mysql -u root -p"$MYSQL_ROOT_PASS" "$DB_NAME" < php-backend/migrations/001_add_password_reset_tokens.sql
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Password reset migration completed${NC}"
    else
        echo -e "${YELLOW}⚠ Migration may have already run${NC}"
    fi
fi

echo ""
echo -e "${GREEN}✅ Database setup complete!${NC}"
echo ""
echo "Database credentials:"
echo "  Host: localhost"
echo "  Database: $DB_NAME"
echo "  User: $DB_USER"
echo "  Password: $DB_PASS"
echo ""
echo "Next steps:"
echo "1. Create php-backend/config.local.php with these credentials"
echo "2. Start PHP server: cd php-backend && php -S localhost:8080"
echo "3. Create .env.local with: VITE_API_BASE_URL=http://localhost:8080"
echo "4. Start frontend: npm run dev"
echo ""
echo "See LOCAL_SETUP_GUIDE.md for detailed instructions."
