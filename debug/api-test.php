<?php
// Simple test to check if API is working
echo "<h2>API Test</h2>";

// Test if we can access the chart-data.php file
$url = "http://localhost/inventory-app/api/chart-data.php?type=stock-trends";

echo "<h3>Testing URL: $url</h3>";

// Use cURL to test the endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "<h4>HTTP Code: $httpCode</h4>";
if ($error) {
    echo "<h4>cURL Error: $error</h4>";
}

echo "<h4>Response:</h4>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Also test direct inclusion
echo "<hr><h3>Direct Test:</h3>";
try {
    $_GET['type'] = 'stock-trends';
    ob_start();
    include '../api/chart-data.php';
    $output = ob_get_clean();
    echo "<h4>Direct output:</h4>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
} catch (Exception $e) {
    echo "Direct test error: " . $e->getMessage();
}
?>
