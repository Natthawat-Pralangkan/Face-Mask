<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, access");
include "./servers/connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // First, check for and handle an uploaded file
    if (isset($_FILES['imageFile'])) {
        // The path where the file will be saved
        $targetDirectory = "imgage/"; // Ensure this directory exists and is writable
        $targetFile = $targetDirectory . basename($_FILES['imageFile']['name']);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Attempt to move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $targetFile)) {
            echo "The file " . htmlspecialchars(basename($_FILES['imageFile']['name'])) . " has been uploaded.";

            // After successful upload, you can also insert the file path into the database here if needed
            // $sql = "INSERT INTO face_recognition_data (image_path) VALUES (:image_path)";
            // $stmt = $db->prepare($sql);
            // $stmt->bindParam(':image_path', $targetFile); // Use $targetFile which contains the path
            // $stmt->execute();

        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "No file was uploaded.";
    }

    // Then, handle other POST data, like 'image_path'
    if (isset($_POST['image_path'])) {
        // Your existing code to handle 'image_path'
        echo "Image path received: " . $_POST['image_path'];

        // Example of inserting face data into the database
        $sql = "INSERT INTO face_recognition_data (image_path) VALUES (:image_path)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':image_path', $_POST['image_path']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert data into the database']);
        }
    } else {
        // If handling file uploads only, you might not need this part
        // echo "Image path is missing in the request";
    }
}
?>
