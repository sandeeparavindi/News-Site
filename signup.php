<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!empty($_FILES['profile']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetFile = $targetDir . basename($_FILES["profile"]["name"]);
        move_uploaded_file($_FILES["profile"]["tmp_name"], $targetFile);
    }

    echo "<h2>Registration Successful</h2>";
    echo "Email: $email <br>";
    echo "Username: $username <br>";
    echo "Profile Picture: <img src='$targetFile' width='100'>";
}
?>
