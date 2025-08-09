# 📦 Inventory Management System

A comprehensive inventory management system built with PHP, MySQL, and Bootstrap for small to medium-sized businesses. This system provides complete stock tracking, supplier management, and transaction logging with role-based access control.

## 🚀 Technology Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5.3.3, Bootstrap Icons
- **Backend:** PHP 8.x (Core PHP with modular structure)
- **Database:** MySQL 8.x with InnoDB engine and prepared statements
- **Charts:** Chart.js for interactive data visualization
- **Server:** Apache (XAMPP/LAMP compatible)
- **Architecture:** Server-rendered HTML with responsive design
- **Authentication:** Session-based with role management
- **API:** Custom PHP endpoints for chart data and exports

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
├── api/
│   ├── chart-data.php          # Chart data API endpoint
│   ├── chart-data-simple.php   # Simplified chart data
│   └── export-print.php        # Export and print functionality
├── assets/
│   ├── css/
│   │   └── style.css           # Custom styles and responsive design
│   └── js/
│       ├── dashboard.js        # Dashboard functionality
│       └── dashboard-simple.js # Simplified dashboard scripts
├── auth/
│   ├── login.php              # User authentication
│   ├── logout.php             # Session termination
│   └── register.php           # User registration
├── categories/
│   ├── index.php              # List all categories
│   ├── create.php             # Add new category
│   ├── edit.php               # Edit category details
│   └── delete.php             # Remove category
├── dashboard/
│   └── index.php              # Main dashboard with analytics
├── debug/
│   ├── api-test.php           # API testing utilities
│   └── chart-debug.php        # Chart debugging tools
├── includes/
│   ├── config.php             # Database configuration & functions
│   ├── header.php             # Common header template
│   ├── footer.php             # Common footer template
│   └── sidebar.php            # Navigation sidebar
├── inventory/
│   ├── index.php              # Items listing and management
│   ├── create.php             # Add new inventory item
│   ├── edit.php               # Edit item details
│   └── delete.php             # Remove inventory item
├── stocks/
│   ├── index.php              # Stock movement logs
│   ├── create.php             # Log stock in/out operations
│   ├── edit.php               # Edit stock entries
│   └── delete.php             # Remove stock logs
├── suppliers/
│   ├── index.php              # Supplier management
│   ├── create.php             # Add new supplier
│   ├── edit.php               # Edit supplier information
│   └── delete.php             # Remove supplier
├── transactions/
│   ├── index.php              # Transaction history
│   ├── create.php             # Record new transactions
│   ├── edit.php               # Edit transaction details
│   └── delete.php             # Remove transactions
├── index.php                  # Landing page/main entry point
├── package.json               # Project configuration
├── README.md                  # Project documentation
├── AI-DESIGN-PROMPT.md        # AI design specifications
└── DESIGN.md                  # Design guidelines
```

## 🔐 Security Features

- **Password hashing** using PHP's `password_hash()` and `password_verify()`
- **Session-based authentication** with secure session management
- **Role-based access control** (Admin vs Staff permissions)
- **SQL injection prevention** using prepared statements
- **Input validation and sanitization** on all user inputs
- **CSRF protection** on forms

## 🎯 Key Features

### 📊 Dashboard & Analytics
- **Interactive Dashboard** with real-time statistics and charts
- **Chart.js Integration** for visual data representation
- **Stock Trends Visualization** with line charts
- **Category Distribution** with doughnut charts
- **Responsive Design** with Bootstrap 5 and offcanvas navigation
- **Export/Print Functionality** for reports and data

### 🔐 Authentication & Security
- **Role-based Access Control** (Admin/Staff permissions)
- **Secure Session Management** with proper logout handling
- **Password Encryption** using PHP's password_hash()
- **Modal Confirmations** for critical actions
- **Input Validation** and SQL injection prevention

### 📦 Inventory Management
- **Complete Item Management** with SKU tracking
- **Real-time Stock Monitoring** with quantity updates
- **Category Organization** for better product classification
- **Supplier Integration** with contact management
- **Low Stock Alerts** and reorder level tracking
- **Stock Movement Logging** with audit trails

### 💼 Transaction Processing
- **Purchase Recording** with supplier tracking
- **Sales Transactions** with automatic stock updates
- **Financial Tracking** with price and quantity management
- **Transaction History** with detailed logs
- **Reference Number** tracking for accountability

### 🎨 User Interface
- **Responsive Design** that works on all devices
- **Bootstrap 5** modern UI components
- **Mobile-First Approach** with collapsible sidebar
- **Success/Error Notifications** for user feedback
- **Intuitive Navigation** with clear menu structure
- **Data Tables** with search and sorting capabilities

### 📈 Reporting & Analytics
- **Dashboard Analytics** with key performance indicators
- **Stock Level Reports** with current inventory status
- **Transaction Reports** with sales and purchase history
- **Supplier Performance** tracking and management
- **Low Stock Alerts** with automatic notifications
- **Export Capabilities** for external reporting

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

### Responsive Design Features
- **Mobile-First Approach** with Bootstrap 5 responsive grid
- **Offcanvas Navigation** for mobile devices with hamburger menu
- **Responsive Charts** that adapt to different screen sizes
- **Adaptive Layouts** that work seamlessly across all devices
- **Touch-Friendly Interface** optimized for mobile interactions

### Design Components
- **Bootstrap 5.3.3** for modern, consistent styling
- **Bootstrap Icons** for scalable vector icons
- **Modal Dialogs** for create/edit operations and confirmations
- **Toast Notifications** and alert messages for user feedback
- **Data Tables** with sorting, search, and pagination
- **Dashboard Widgets** with interactive charts and key metrics
- **Success/Error Messages** with contextual styling
- **Professional Color Scheme** with primary, secondary, and accent colors

### Navigation & Layout
- **Fixed Sidebar** on desktop with collapsible sections
- **Mobile Offcanvas Menu** that slides in from the side
- **Breadcrumb Navigation** for easy location awareness
- **Consistent Header** with user controls and branding
- **Footer Information** with relevant links and credits

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
