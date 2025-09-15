<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/database.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $newsId = $_POST['news_id'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    
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
    
    // Check if news exists
    $checkSql = "SELECT id, image FROM news WHERE id = ?";
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
    
    $existingNews = $result->fetch_assoc();
    $checkStmt->close();
    
    // Handle image upload
    $imageName = $existingNews['image']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $imageExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = 'news_' . time() . '.' . $imageExtension;
        $imagePath = $uploadDir . $imageName;
        
        // Check if image file is valid
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($imageExtension), $allowedTypes)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Only JPG, JPEG, PNG & GIF files are allowed!'
            ]);
            exit;
        }
        
        // Move uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error uploading image!'
            ]);
            exit;
        }
        
        // Delete old image if it exists
        if ($existingNews['image'] && file_exists($uploadDir . $existingNews['image'])) {
            unlink($uploadDir . $existingNews['image']);
        }
    }
    
    // Build SQL update query dynamically
    $updateFields = [];
    $params = [];
    $types = "";
    
    if (!empty($category)) {
        $updateFields[] = "category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    if (!empty($description)) {
        $updateFields[] = "description = ?";
        $params[] = $description;
        $types .= "s";
        
        // Also update title with first 50 characters of description
        $title = strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
        $updateFields[] = "title = ?";
        $params[] = $title;
        $types .= "s";
    }
    
    if (!empty($date)) {
        $updateFields[] = "date_created = ?";
        $params[] = $date;
        $types .= "s";
    }
    
    if ($imageName != $existingNews['image']) {
        $updateFields[] = "image = ?";
        $params[] = $imageName;
        $types .= "s";
    }
    
    if (empty($updateFields)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No fields to update!'
        ]);
        $conn->close();
        exit;
    }
    
    // Add news ID to parameters
    $params[] = $newsId;
    $types .= "i";
    
    // Prepare SQL statement
    $sql = "UPDATE news SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $conn->error
        ]);
        exit;
    }
    
    // Bind parameters
    $stmt->bind_param($types, ...$params);
    
    // Execute query
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'News updated successfully!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error updating news: ' . $stmt->error
        ]);
    }
    
    // Close connections
    $stmt->close();
    $conn->close();
    
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method!'
    ]);
}
?>