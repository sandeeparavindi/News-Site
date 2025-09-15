<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

try {
    // Get news ID from URL parameter
    $newsId = $_GET['id'] ?? '';
    
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
    
    // Prepare SQL to get single news
    $sql = "SELECT id, category, title, description, image, date_created, created_at FROM news WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    // Bind parameter and execute
    $stmt->bind_param("i", $newsId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc();
        
        // Format the date
        $news['formatted_date'] = date('M d, Y', strtotime($news['date_created']));
        
        // Add image URL if image exists
        if (!empty($news['image'])) {
            $news['image_url'] = $news['image']; // Just return the filename, let frontend handle the full URL
        } else {
            $news['image_url'] = null;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $news
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'News not found!'
        ]);
    }
    
    // Close connections
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("Get single news error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>