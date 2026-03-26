<?php
include('db_connect2.php');

$id = intval($_GET['id']);

if(isset($_POST['upload'])){
    $file = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];

    if(move_uploaded_file($tmp,"uploads/".$file)){
        $stmt = $conn->prepare("UPDATE waste_tracking SET photo=? WHERE id=?");
        $stmt->bind_param("si",$file,$id);
        $stmt->execute();
        header("Location: waste_tracking.php");
        exit;
    } else {
        echo "Upload failed.";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="photo" required>
    <button name="upload">Upload Correct Photo</button>
</form>