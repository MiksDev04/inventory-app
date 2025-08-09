<?php
session_start();
include '../includes/config.php';

// Simple debug to test chart data endpoints
echo "<h2>Debug Chart Data</h2>";

try {
    // Test stock trends
    echo "<h3>Stock Trends Data:</h3>";
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime('-6 days'));
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('M j', strtotime("-{$i} days"));
        $dbDate = date('Y-m-d', strtotime("-{$i} days"));
        
        $stockIn = getTotal("SELECT COALESCE(SUM(quantity_changed), 0) FROM stock_logs WHERE DATE(created_at) = '$dbDate' AND change_type = 'in'");
        $stockOut = getTotal("SELECT COALESCE(SUM(quantity_changed), 0) FROM stock_logs WHERE DATE(created_at) = '$dbDate' AND change_type = 'out'");
        
        echo "$date: In=$stockIn, Out=$stockOut<br>";
    }
    
    echo "<h3>Category Distribution:</h3>";
    $categories = get("SELECT c.name, COUNT(i.id) as item_count 
                      FROM categories c 
                      LEFT JOIN items i ON c.id = i.category_id 
                      GROUP BY c.id, c.name 
                      ORDER BY item_count DESC 
                      LIMIT 10");
    
    foreach ($categories as $category) {
        echo $category['name'] . ": " . $category['item_count'] . " items<br>";
    }
    
    echo "<h3>Dashboard Stats:</h3>";
    $totalItems = getTotal('SELECT COUNT(*) FROM items');
    $lowStockItems = getTotal('SELECT COUNT(*) FROM items WHERE quantity < 5');
    $totalSuppliers = getTotal('SELECT COUNT(*) FROM suppliers');
    $totalCategories = getTotal('SELECT COUNT(*) FROM categories');
    $inventoryValue = getTotal('SELECT COALESCE(SUM(unit_price * quantity), 0) FROM items');
    
    echo "Total Items: $totalItems<br>";
    echo "Low Stock Items: $lowStockItems<br>";
    echo "Total Suppliers: $totalSuppliers<br>";
    echo "Total Categories: $totalCategories<br>";
    echo "Inventory Value: $inventoryValue<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
