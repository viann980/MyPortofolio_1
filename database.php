<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'computer_sales';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert order
    $stmt = $pdo->prepare('INSERT INTO orders (total_amount, order_date) VALUES (?, NOW())');
    $stmt->execute([$data['total']]);
    $orderId = $pdo->lastInsertId();
    
    // Insert order items
    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    foreach ($data['items'] as $item) {
        $stmt->execute([$orderId, $item['id'], 1, $item['price']]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId]);
} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
