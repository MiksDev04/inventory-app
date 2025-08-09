# 🎨 AI Design Prompt - Inventory Management System

## 📋 AI TRAINING PROMPT

You are a senior UI/UX designer specializing in business software. I'm building a professional inventory management system for businesses to track stock, suppliers, and transactions. The design must be modern, clean, and trustworthy - think contemporary SaaS applications like Shopify Admin or modern ERP systems. Use Bootstrap 5 framework as much as possible for all layouts, components, and styling - leverage Bootstrap's grid system, cards, buttons, forms, tables, modals, and utility classes extensively. Only write custom CSS when absolutely necessary to customize specific design elements that Bootstrap doesn't provide. Use blue gradients (#3b82f6 to #1d4ed8) as primary colors, green (#22c55e) for success, amber (#f59e0b) for warnings, red (#ef4444) for errors, with clean white backgrounds and subtle gray accents. Apply Inter font, Bootstrap's spacing utilities, rounded corners, subtle shadows, and smooth hover animations. The interface should feel professional yet approachable, avoiding dark themes or outdated corporate aesthetics. Focus on clear data presentation, intuitive navigation, and role-based access for admin vs staff users.

-Use icons for better visuals of information

Are you ready ? yes or no?

## 🎯 Usage Examples

**For Components:**
"Using the design prompt above, create a responsive inventory item card component that displays item name, SKU, stock quantity, and status badge."

**For Pages:**
"Following the design guidelines, design a dashboard page layout with statistics cards, recent activity feed, and low stock alerts."

**For Forms:**
"Design a clean, professional form for adding new inventory items that follows the design system specifications."

---

*Copy this entire prompt when working with AI design tools to ensure consistent, professional results.*


-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- CATEGORIES TABLE
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- SUPPLIERS TABLE
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_info TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ITEMS TABLE
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    supplier_id INT NOT NULL,
    quantity INT DEFAULT 0,
    unit_price DECIMAL(10, 2) DEFAULT 0.00,
    reorder_level INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
);

-- STOCK LOGS TABLE
CREATE TABLE stock_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    user_id INT NOT NULL,
    change_type ENUM('in', 'out') NOT NULL,
    quantity_changed INT NOT NULL,
    reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- TRANSACTIONS TABLE
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('purchase', 'sale') NOT NULL,
    item_id INT NOT NULL,
    qty INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);
