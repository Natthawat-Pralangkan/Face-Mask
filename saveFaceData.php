<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, access");

include "./connenct.php"; // แก้ไขชื่อไฟล์ตามชื่อที่เป็นไปตามความเหมาะสม

if (isset($_POST['function']) && $_POST['function'] == "insertimage_detec") {

    $success_check_flag = false;
    try {
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['imageFile']['tmp_name'];
            $name = $_FILES['imageFile']['name'];
            $path = "images/" . $name;
            move_uploaded_file($tmp, $path);
        } else {
            $name = "";
        }

        // เปิดไฟล์รูปภาพของ snapshot ด้วย imagecreatefrompng()
        $snapshot_path = "images/snapshot.png";
        $snapshot = imagecreatefrompng($snapshot_path);

        $getuser = $db->prepare("SELECT * FROM user");
        $getuser->execute();
        $rows = $getuser->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            // เปรียบเทียบภาพในฐานข้อมูลกับภาพในไฟล์ snapshot.png
            $user_image_path = "images/" . $row['images'];
            $user_image = imagecreatefromjpeg($user_image_path);

            // ใช้ imagesx() และ imagesy() เพื่อรับขนาดภาพของภาพที่เปิดไว้
            $is_matching = true;
            for ($x = 0; $x < imagesx($snapshot); $x++) {
                for ($y = 0; $y < imagesy($snapshot); $y++) {
                    $color_snapshot = imagecolorat($snapshot, $x, $y);
                    $color_user_image = imagecolorat($user_image, $x, $y);

                    if ($color_snapshot !== $color_user_image) {
                        $is_matching = false;
                        break 2; // ออกจากลูปทั้งหมด
                    }
                }
            }

            // หากตรงกัน $is_matching จะเป็น true
            if ($is_matching) {
                $check = $db->prepare("INSERT INTO check_detec (user_id) VALUES (?)");
                if ($check->execute([$row['user_id']])) {
                    $success_check_flag = true;
                }
            } else {
                // ใช้ error_get_last() เพื่อรับข้อความผิดพลาดล่าสุด
                echo json_encode(['status' => 400]); // แสดงข้อความผิดพลาดในรูปแบบ JSON
            }
        }
        if ($success_check_flag) {
            echo json_encode(['status' => 200]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 400, "msg" => $e->getMessage()]);
    }
}
