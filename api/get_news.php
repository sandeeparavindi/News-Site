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
    // Connect to database
    $conn = getDatabaseConnection();
    
    // Check if specific news ID is requested
    $newsId = $_GET['id'] ?? '';
    
    if (!empty($newsId)) {
        // Get single news by ID
        $sql = "SELECT id, category, title, description, image, date_created, created_at FROM news WHERE id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $newsId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $news = $result->fetch_assoc();
            
            // Format the date
            $news['formatted_date'] = date('M d, Y', strtotime($news['date_created']));
            
            // Add full image URL if image exists
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
        
        $stmt->close();
        
    } else {
        // Get all news
        $sql = "SELECT id, category, title, description, image, date_created, created_at FROM news ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        if ($result === false) {
            throw new Exception('Database query error: ' . $conn->error);
        }
        
        $news = array();
        
        if ($result->num_rows > 0) {
            // Fetch all news
            while($row = $result->fetch_assoc()) {
                // Format the date
                $row['formatted_date'] = date('M d, Y', strtotime($row['date_created']));
                
                // Add image URL if image exists
                if (!empty($row['image'])) {
                    $row['image_url'] = $row['image']; // Just return the filename
                } else {
                    $row['image_url'] = null;
                }
                
                $news[] = $row;
            }
        }
        
        // Return JSON response
        echo json_encode([
            'status' => 'success',
            'data' => $news,
            'total' => count($news)
        ]);
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("Get news error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>