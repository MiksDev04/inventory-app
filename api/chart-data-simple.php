<?php
session_start();
include '../includes/config.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

try {
    switch ($type) {
        case 'stock-trends':
            // Get real stock movements from database
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
                
                $stockData['stockIn'][] = (int)$stockIn;
                $stockData['stockOut'][] = (int)$stockOut;
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
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'Stock Out',
                        'data' => $stockData['stockOut'],
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderWidth' => 3,
                        'tension' => 0.4
                    ]
                ]
            ]);
            break;
            
        case 'category-distribution':
            // Get real categories from database
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
            
            // If no categories found, use default ones
            if (empty($labels)) {
                $labels = ['No Categories'];
                $data = [0];
            }
            
            echo json_encode([
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 2
                ]]
            ]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid chart type']);
    }
    
} catch (Exception $e) {
    // If database fails, return sample data
    switch ($type) {
        case 'stock-trends':
            echo json_encode([
                'labels' => ['Aug 2', 'Aug 3', 'Aug 4', 'Aug 5', 'Aug 6', 'Aug 7', 'Aug 8'],
                'datasets' => [
                    [
                        'label' => 'Stock In (Sample)',
                        'data' => [45, 68, 82, 95, 115, 135, 155],
                        'borderColor' => '#22c55e',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'borderWidth' => 3,
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'Stock Out (Sample)',
                        'data' => [38, 52, 68, 75, 88, 105, 125],
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderWidth' => 3,
                        'tension' => 0.4
                    ]
                ]
            ]);
            break;
            
        case 'category-distribution':
            echo json_encode([
                'labels' => ['Electronics (Sample)', 'Clothing (Sample)', 'Food (Sample)', 'Books (Sample)'],
                'datasets' => [[
                    'data' => [45, 32, 28, 15],
                    'backgroundColor' => ['#3b82f6', '#22c55e', '#f59e0b', '#06b6d4'],
                    'borderWidth' => 2
                ]]
            ]);
            break;
            
        default:
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
