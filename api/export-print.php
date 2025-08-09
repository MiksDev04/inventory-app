<?php
session_start();
include '../includes/config.php';

// Check if user is logged in
if (!isLogIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? '';

try {
    switch ($action) {
        case 'export':
            handleExport($type);
            break;
            
        case 'print':
            handlePrint($type);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}

function handleExport($type) {
    switch ($type) {
        case 'stock-trends':
            exportStockTrends();
            break;
            
        case 'category-distribution':
            exportCategoryDistribution();
            break;
            
        case 'dashboard-summary':
            exportDashboardSummary();
            break;
            
        case 'low-stock':
            exportLowStock();
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid export type']);
    }
}

function handlePrint($type) {
    switch ($type) {
        case 'stock-trends':
            printStockTrends();
            break;
            
        case 'category-distribution':
            printCategoryDistribution();
            break;
            
        case 'dashboard-summary':
            printDashboardSummary();
            break;
            
        case 'low-stock':
            printLowStock();
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid print type']);
    }
}

function exportStockTrends() {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="stock_trends_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, ['Date', 'Stock In', 'Stock Out', 'Net Change']);
    
    // Get data for last 7 days
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $displayDate = date('M j, Y', strtotime("-{$i} days"));
        
        $stockIn = getTotal("SELECT COALESCE(SUM(quantity_changed), 0) FROM stock_logs WHERE DATE(created_at) = '$date' AND change_type = 'in'");
        $stockOut = getTotal("SELECT COALESCE(SUM(quantity_changed), 0) FROM stock_logs WHERE DATE(created_at) = '$date' AND change_type = 'out'");
        $netChange = $stockIn - $stockOut;
        
        fputcsv($output, [$displayDate, $stockIn, $stockOut, $netChange]);
    }
    
    fclose($output);
}

function exportCategoryDistribution() {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="category_distribution_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, ['Category', 'Item Count', 'Percentage']);
    
    // Get category data
    $categories = get("SELECT c.name, COUNT(i.id) as item_count 
                      FROM categories c 
                      LEFT JOIN items i ON c.id = i.category_id 
                      GROUP BY c.id, c.name 
                      ORDER BY item_count DESC");
    
    $totalItems = array_sum(array_column($categories, 'item_count'));
    
    foreach ($categories as $category) {
        $percentage = $totalItems > 0 ? round(($category['item_count'] / $totalItems) * 100, 2) : 0;
        fputcsv($output, [$category['name'], $category['item_count'], $percentage . '%']);
    }
    
    fclose($output);
}

function exportDashboardSummary() {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="dashboard_summary_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Get dashboard statistics
    $totalItems = getTotal('SELECT COUNT(*) FROM items');
    $lowStockItems = getTotal('SELECT COUNT(*) FROM items WHERE quantity < 5');
    $totalSuppliers = getTotal('SELECT COUNT(*) FROM suppliers');
    $totalCategories = getTotal('SELECT COUNT(*) FROM categories');
    $inventoryValue = getTotal('SELECT COALESCE(SUM(unit_price * quantity), 0) FROM items');
    
    // CSV Headers and Data
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Total Items', $totalItems]);
    fputcsv($output, ['Low Stock Items', $lowStockItems]);
    fputcsv($output, ['Total Suppliers', $totalSuppliers]);
    fputcsv($output, ['Total Categories', $totalCategories]);
    fputcsv($output, ['Inventory Value', '$' . number_format($inventoryValue, 2)]);
    fputcsv($output, ['Report Generated', date('Y-m-d H:i:s')]);
    
    fclose($output);
}

function exportLowStock() {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="low_stock_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, ['Item Name', 'Category', 'Supplier', 'Current Stock', 'Unit Price', 'Total Value', 'Status']);
    
    // Get low stock items
    $lowStockItems = get("SELECT i.*, c.name as category_name, s.name as supplier_name 
                         FROM items i 
                         LEFT JOIN categories c ON i.category_id = c.id 
                         LEFT JOIN suppliers s ON i.supplier_id = s.id 
                         WHERE i.quantity < 5 
                         ORDER BY i.quantity ASC, i.name ASC");
    
    foreach ($lowStockItems as $item) {
        $status = $item['quantity'] == 0 ? 'Out of Stock' : ($item['quantity'] <= 2 ? 'Critical' : 'Low Stock');
        $totalValue = $item['unit_price'] * $item['quantity'];
        
        fputcsv($output, [
            $item['name'],
            $item['category_name'] ?? 'No Category',
            $item['supplier_name'] ?? 'No Supplier',
            $item['quantity'],
            '$' . number_format($item['unit_price'], 2),
            '$' . number_format($totalValue, 2),
            $status
        ]);
    }
    
    fclose($output);
}

function printStockTrends() {
    // Return HTML for printing
    header('Content-Type: text/html');
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Stock Trends Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            @media print { body { margin: 0; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Stock Trends Report</h1>
            <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Net Change</th>
                </tr>
            </thead>
            <tbody>';
    
    // Get data for last 7 days
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $displayDate = date('M j, Y', strtotime("-{$i} days"));
        
        $stockIn = getTotal("SELECT COALESCE(SUM(quantity_changed), 0) FROM stock_logs WHERE DATE(created_at) = '$date' AND change_type = 'in'");
        $stockOut = getTotal("SELECT COALESCE(SUM(quantity_changed), 0) FROM stock_logs WHERE DATE(created_at) = '$date' AND change_type = 'out'");
        $netChange = $stockIn - $stockOut;
        
        $html .= '<tr>';
        $html .= '<td>' . $displayDate . '</td>';
        $html .= '<td>' . number_format($stockIn) . '</td>';
        $html .= '<td>' . number_format($stockOut) . '</td>';
        $html .= '<td>' . ($netChange >= 0 ? '+' : '') . number_format($netChange) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Inventory Management System - Stock Trends Report</p>
        </div>
        
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>';
    
    echo $html;
}

function printCategoryDistribution() {
    // Similar print function for category distribution
    header('Content-Type: text/html');
    
    $categories = get("SELECT c.name, COUNT(i.id) as item_count 
                      FROM categories c 
                      LEFT JOIN items i ON c.id = i.category_id 
                      GROUP BY c.id, c.name 
                      ORDER BY item_count DESC");
    
    $totalItems = array_sum(array_column($categories, 'item_count'));
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Category Distribution Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            @media print { body { margin: 0; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Category Distribution Report</h1>
            <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Item Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($categories as $category) {
        $percentage = $totalItems > 0 ? round(($category['item_count'] / $totalItems) * 100, 2) : 0;
        
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($category['name']) . '</td>';
        $html .= '<td>' . number_format($category['item_count']) . '</td>';
        $html .= '<td>' . $percentage . '%</td>';
        $html .= '</tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Inventory Management System - Category Distribution Report</p>
        </div>
        
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>';
    
    echo $html;
}

function printDashboardSummary() {
    // Print function for dashboard summary
    header('Content-Type: text/html');
    
    $totalItems = getTotal('SELECT COUNT(*) FROM items');
    $lowStockItems = getTotal('SELECT COUNT(*) FROM items WHERE quantity < 5');
    $totalSuppliers = getTotal('SELECT COUNT(*) FROM suppliers');
    $totalCategories = getTotal('SELECT COUNT(*) FROM categories');
    $inventoryValue = getTotal('SELECT COALESCE(SUM(unit_price * quantity), 0) FROM items');
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Dashboard Summary Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            @media print { body { margin: 0; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Dashboard Summary Report</h1>
            <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Total Items</td><td>' . number_format($totalItems) . '</td></tr>
                <tr><td>Low Stock Items</td><td>' . number_format($lowStockItems) . '</td></tr>
                <tr><td>Total Suppliers</td><td>' . number_format($totalSuppliers) . '</td></tr>
                <tr><td>Total Categories</td><td>' . number_format($totalCategories) . '</td></tr>
                <tr><td>Inventory Value</td><td>$' . number_format($inventoryValue, 2) . '</td></tr>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Inventory Management System - Dashboard Summary Report</p>
        </div>
        
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>';
    
    echo $html;
}

function printLowStock() {
    // Print function for low stock report
    header('Content-Type: text/html');
    
    // Get low stock items
    $lowStockItems = get("SELECT i.*, c.name as category_name, s.name as supplier_name 
                         FROM items i 
                         LEFT JOIN categories c ON i.category_id = c.id 
                         LEFT JOIN suppliers s ON i.supplier_id = s.id 
                         WHERE i.quantity < 5 
                         ORDER BY i.quantity ASC, i.name ASC");
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Low Stock Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            .status-critical { color: #dc3545; font-weight: bold; }
            .status-warning { color: #ffc107; font-weight: bold; }
            .status-info { color: #17a2b8; font-weight: bold; }
            @media print { body { margin: 0; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Low Stock Report</h1>
            <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
            <p>Total Low Stock Items: ' . count($lowStockItems) . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Current Stock</th>
                    <th>Unit Price</th>
                    <th>Total Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($lowStockItems as $item) {
        $status = $item['quantity'] == 0 ? 'Out of Stock' : ($item['quantity'] <= 2 ? 'Critical' : 'Low Stock');
        $statusClass = $item['quantity'] == 0 ? 'status-critical' : ($item['quantity'] <= 2 ? 'status-warning' : 'status-info');
        $totalValue = $item['unit_price'] * $item['quantity'];
        
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($item['name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($item['category_name'] ?? 'No Category') . '</td>';
        $html .= '<td>' . htmlspecialchars($item['supplier_name'] ?? 'No Supplier') . '</td>';
        $html .= '<td>' . $item['quantity'] . ' units</td>';
        $html .= '<td>$' . number_format($item['unit_price'], 2) . '</td>';
        $html .= '<td>$' . number_format($totalValue, 2) . '</td>';
        $html .= '<td><span class="' . $statusClass . '">' . $status . '</span></td>';
        $html .= '</tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Inventory Management System - Low Stock Report</p>
            <p>Items with quantity less than 5 units are considered low stock</p>
        </div>
        
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>';
    
    echo $html;
}
?>
