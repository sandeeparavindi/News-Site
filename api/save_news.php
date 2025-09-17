<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method!'
    ]);
    exit;
}

try {
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    
    if (empty($category) || empty($description) || empty($date)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields (category, description, date) are required!'
        ]);
        exit;
    }
    
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/';
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create uploads directory!'
                ]);
                exit;
            }
        }
        
        $imageExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($imageExtension, $allowedTypes)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Only JPG, JPEG, PNG & GIF files are allowed!'
            ]);
            exit;
        }
        
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Image file size must be less than 5MB!'
            ]);
            exit;
        }
        
        $imageName = 'news_' . time() . '.' . $imageExtension;
        $imagePath = $uploadDir . $imageName;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error uploading image!'
            ]);
            exit;
        }
    }
    
    $title = strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
    
    $conn = getDatabaseConnection();
    
    $sql = "INSERT INTO news (category, title, description, image, date_created) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param("sssss", $category, $title, $description, $imageName, $date);
    
    if ($stmt->execute()) {
        $newNewsId = $conn->insert_id;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'News article saved successfully!',
            'news_id' => $newNewsId
        ]);
    } else {
        throw new Exception('Database execute error: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Save news error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>