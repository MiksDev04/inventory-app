# 📦 Inventory Management System

A comprehensive inventory management system built with PHP, MySQL, and Bootstrap for small to medium-sized businesses. This system provides complete stock tracking, supplier management, and transaction logging with role-based access control.

## 🚀 Technology Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5
- **Backend:** PHP 8.x (Core PHP, no frameworks)
- **Database:** MySQL 8.x with InnoDB engine
- **Server:** Apache (XAMPP/LAMP compatible)
- **Architecture:** Server-rendered HTML with modular PHP structure

## 🏗️ System Architecture

### Core Components

The system is built around six main entities that work together to provide comprehensive inventory management:

#### 👤 **Users**
- Role-based access control (Admin/Staff)
- Secure authentication with session management
- User activity tracking

#### 📦 **Items** 
- Complete product catalog with SKU management
- Stock level tracking with reorder alerts
- Price management and supplier linking

#### 🏷️ **Categories**
- Hierarchical product categorization
- Easy filtering and organization

#### 🏢 **Suppliers**
- Vendor management with contact information
- Purchase history and supplier performance tracking

#### 📊 **Stock Logs**
- Real-time inventory movement tracking
- Audit trail for all stock changes
- User accountability for all transactions

#### 💰 **Transactions**
- Complete purchase and sales recording
- Financial tracking and reporting
- Integration with stock movement

## 🗄️ Database Schema

```sql
-- Users table with role-based access
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Product categories for organization
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Supplier management
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Main inventory items
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT,
    supplier_id INT,
    quantity INT DEFAULT 0,
    unit_price DECIMAL(10, 2),
    reorder_level INT DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- Stock movement logging
CREATE TABLE stock_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    user_id INT NOT NULL,
    change_type ENUM('in', 'out') NOT NULL,
    quantity_changed INT NOT NULL,
    reason VARCHAR(255),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Financial transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('purchase', 'sale') NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(12, 2) AS (quantity * unit_price),
    reference_number VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);
```

## 📁 Project Structure

```
inventory-app/
├── assets/
│   ├── css/
│   │   └── custom.css
│   ├── js/
│   │   └── app.js
│   └── img/
├── includes/
│   ├── config.php          # Database configuration
│   ├── header.php          # Common header template
│   ├── footer.php          # Common footer template
│   └── functions.php       # Utility functions
├── modules/
│   ├── auth/
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── register.php
│   ├── categories/
│   │   ├── index.php       # List categories
│   │   ├── create.php      # Add new category
│   │   ├── edit.php        # Edit category
│   │   └── delete.php      # Delete category
│   ├── suppliers/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── items/
│   │   ├── index.php       # Inventory listing
│   │   ├── create.php      # Add new item
│   │   ├── edit.php        # Edit item details
│   │   ├── delete.php      # Remove item
│   │   └── stock.php       # Stock management
│   ├── stock-logs/
│   │   ├── index.php       # View stock movements
│   │   └── create.php      # Log stock changes
│   ├── transactions/
│   │   ├── index.php       # Transaction history
│   │   ├── purchase.php    # Record purchases
│   │   └── sale.php        # Record sales
│   └── reports/
│       ├── inventory.php   # Inventory reports
│       ├── low-stock.php   # Low stock alerts
│       └── export.php      # Data export
├── dashboard.php           # Main dashboard
├── index.php              # Landing page/redirect
└── package.json           # Project configuration
```

## 🔐 Security Features

- **Password hashing** using PHP's `password_hash()` and `password_verify()`
- **Session-based authentication** with secure session management
- **Role-based access control** (Admin vs Staff permissions)
- **SQL injection prevention** using prepared statements
- **Input validation and sanitization** on all user inputs
- **CSRF protection** on forms

## 🎯 Key Features

### For Administrators
- Complete system access and user management
- Full CRUD operations on all entities
- System configuration and maintenance
- Advanced reporting and analytics

### For Staff Users
- Inventory viewing and basic stock operations
- Stock in/out logging with reason tracking
- Transaction recording for sales/purchases
- Limited access to sensitive operations

### Inventory Management
- **Real-time stock tracking** with automatic quantity updates
- **Low stock alerts** when items reach reorder levels
- **SKU-based item identification** for unique product tracking
- **Supplier integration** for purchase management
- **Category organization** for better product classification

### Audit Trail
- **Complete stock movement history** with user accountability
- **Transaction logging** for all financial activities
- **Change tracking** with timestamps and user identification
- **Reason codes** for all inventory adjustments

## 🚀 Installation & Setup

### Prerequisites
- XAMPP, WAMP, or LAMP server
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Modern web browser

### Installation Steps

1. **Clone or download** the project to your web server directory:
   ```bash
   cd C:\xampp\htdocs\
   # Place the inventory-app folder here
   ```

2. **Configure database connection** in `includes/config.php`:
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'inventory_system';
   $username = 'root';
   $password = '1234';
   ```

3. **Create the database** and import the schema:
   ```sql
   CREATE DATABASE inventory_system;
   USE inventory_system;
   -- Execute the schema SQL provided above
   ```

4. **Set up the web server** (XAMPP example):
   - Start Apache and MySQL services
   - Navigate to `http://localhost/inventory-app`

5. **Create initial admin user** (run this SQL query):
   ```sql
   INSERT INTO users (username, password, role) 
   VALUES ('admin', '$2y$10$example_hash', 'admin');
   ```

## 🔄 Workflow Examples

### Adding New Stock
1. Navigate to **Items** → **Stock Management**
2. Select the item to restock
3. Enter quantity received and supplier reference
4. System automatically:
   - Updates item quantity
   - Logs the stock movement
   - Records the transaction
   - Updates reorder status

### Processing a Sale
1. Go to **Transactions** → **New Sale**
2. Select items and quantities sold
3. Enter sale price and reference
4. System automatically:
   - Reduces stock quantities
   - Logs stock movements as 'out'
   - Records financial transaction
   - Updates low stock alerts if applicable

### Managing Suppliers
1. **Suppliers** → **Add New Supplier**
2. Enter contact information and terms
3. Link items to suppliers during item creation
4. Track purchase history and performance

## 📊 Reporting Capabilities

- **Inventory Status Reports** - Current stock levels and values
- **Low Stock Alerts** - Items below reorder levels
- **Movement Reports** - Stock in/out analysis
- **Supplier Reports** - Purchase history and performance
- **Financial Reports** - Sales and purchase summaries
- **Audit Reports** - Complete change history

## 🛠️ Development Guidelines

### Code Standards
- Follow PSR coding standards for PHP
- Use prepared statements for all database queries
- Implement proper error handling and logging
- Comment complex business logic
- Use semantic HTML and Bootstrap classes

### Adding New Features
1. Create new module directory under `modules/`
2. Follow the established CRUD pattern
3. Include proper authentication checks
4. Add navigation links to header template
5. Test with both admin and staff roles

## 🎨 UI/UX Design

- **Bootstrap 5** for responsive, modern interface
- **Consistent navigation** with role-based menu items
- **Modal dialogs** for create/edit operations
- **Toast notifications** for user feedback
- **Data tables** with sorting and search capabilities
- **Dashboard widgets** for key metrics and alerts

## 📈 Performance Considerations

- **Database indexing** on frequently queried columns
- **Pagination** for large data sets
- **Optimized queries** with proper JOIN statements
- **Session management** for user state
- **Caching strategies** for frequently accessed data

## 🔧 Configuration Options

Edit `includes/config.php` to customize:
- Database connection settings
- Session timeout duration
- Default pagination limits
- Low stock alert thresholds
- File upload restrictions

## 📝 License

This project is open source and available under the MIT License.

## 👨‍💻 Support

For technical support or feature requests, please refer to the project documentation or contact the development team.

---

*Built with ❤️ for efficient inventory management*
