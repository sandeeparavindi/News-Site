<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    
    $newsId = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $newsId = $_POST['news_id'] ?? '';
    } else {
        $newsId = $_GET['id'] ?? '';
    }
    
    // Basic validation
    if (empty($newsId)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'News ID is required!'
        ]);
        exit;
    }
    
    // Connect to database
    $conn = getDatabaseConnection();
    
    $checkSql = "SELECT image FROM news WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $newsId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'News not found!'
        ]);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    
    $newsData = $result->fetch_assoc();
    $checkStmt->close();
    
    // Prepare delete SQL statement
    $sql = "DELETE FROM news WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $conn->error
        ]);
        exit;
    }
    
    $stmt->bind_param("i", $newsId);
    
    if ($stmt->execute()) {
        if ($newsData['image'] && file_exists('../uploads/' . $newsData['image'])) {
            unlink('../uploads/' . $newsData['image']);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'News deleted successfully!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error deleting news: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method!'
    ]);
}
?>