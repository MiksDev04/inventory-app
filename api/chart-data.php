<?php
session_start();
include '../includes/config.php';

// Check if user is logged in
if (!isLogIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

try {
    switch ($type) {
        case 'stock-trends':
            // Get stock movements for the last 7 days
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-6 days'));
            
            $stockData = [];
            $labels = [];
            
            // Generate labels for the last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = date('M j', strtotime("-{$i} days"));
                $labels[] = $date;
                $dbDate = date('Y-m-d', strtotime("-{$i} days"));
                
                // Get stock in for this date
                $stockIn = getTotal("SELECT COALESCE(SUM(quantity_changed), 0) FROM stock_logs WHERE DATE(created_at) = '$dbDate' AND change_type = 'in'");
                
                // Get stock out for this date  
                $stockOut = getTotal("SELECT COALESCE(SUM(quantity_changed), 0) FROM stock_logs WHERE DATE(created_at) = '$dbDate' AND change_type = 'out'");
                
                // Get current total stock at end of that day
                $currentStock = getTotal("SELECT COALESCE(SUM(quantity), 0) FROM items");
                
                $stockData['stockIn'][] = (int)$stockIn;
                $stockData['stockOut'][] = (int)$stockOut;
                $stockData['currentStock'][] = (int)$currentStock;
            }
            
            echo json_encode([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Stock In',
                        'data' => $stockData['stockIn'],
                        'borderColor' => '#22c55e',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'borderWidth' => 3,
                        'tension' => 0.4,
                        'pointBackgroundColor' => '#22c55e',
                        'pointBorderColor' => '#ffffff',
                        'pointBorderWidth' => 2,
                        'pointRadius' => 6,
                        'pointHoverRadius' => 8
                    ],
                    [
                        'label' => 'Stock Out',
                        'data' => $stockData['stockOut'],
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderWidth' => 3,
                        'tension' => 0.4,
                        'pointBackgroundColor' => '#ef4444',
                        'pointBorderColor' => '#ffffff',
                        'pointBorderWidth' => 2,
                        'pointRadius' => 6,
                        'pointHoverRadius' => 8
                    ]
                ]
            ]);
            break;
            
        case 'category-distribution':
            // Get items count by category
            $categories = get("SELECT c.name, COUNT(i.id) as item_count 
                              FROM categories c 
                              LEFT JOIN items i ON c.id = i.category_id 
                              GROUP BY c.id, c.name 
                              ORDER BY item_count DESC 
                              LIMIT 10");
            
            $labels = [];
            $data = [];
            $colors = [
                '#3b82f6', '#22c55e', '#f59e0b', '#06b6d4', '#ef4444',
                '#8b5cf6', '#f97316', '#ec4899', '#10b981', '#6366f1'
            ];
            
            foreach ($categories as $index => $category) {
                $labels[] = $category['name'];
                $data[] = (int)$category['item_count'];
            }
            
            echo json_encode([
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => array_slice($colors, 0, count($data)),
                        'borderWidth' => 2,
                        'hoverOffset' => 4
                    ]
                ]
            ]);
            break;
            
        case 'low-stock-items':
            // Get items with low stock (quantity < 10)
            $lowStockItems = get("SELECT i.name, i.quantity, c.name as category_name 
                                 FROM items i 
                                 LEFT JOIN categories c ON i.category_id = c.id 
                                 WHERE i.quantity < 10 
                                 ORDER BY i.quantity ASC 
                                 LIMIT 10");
            
            echo json_encode($lowStockItems);
            break;
            
        case 'recent-transactions':
            // Get recent transactions for the chart
            $transactions = get("SELECT DATE(date) as transaction_date, 
                                COUNT(*) as transaction_count,
                                SUM(total) as daily_total,
                                type
                                FROM transactions 
                                WHERE date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                GROUP BY DATE(date), type
                                ORDER BY transaction_date DESC");
            
            echo json_encode($transactions);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid chart type']);
    }
    
} catch (Exception $e) {
    error_log("Chart data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
