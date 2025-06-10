<?php
$serverName = "DESKTOP-F68QS4T";
$connectionOptions = ["Database" => "LeaseManagementDB", "Uid" => "", "PWD" => ""];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) die(print_r(sqlsrv_errors(), true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $pageLink = "units.php?apartment=" . urlencode($name);

    $imagePath = null;
    if (!empty($_FILES["image"]["name"])) {
        $targetDir = "images/";
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    $sql = "INSERT INTO Apartments (name, address, page_link, image_path) VALUES (?, ?, ?, ?)";
    $params = [$name, $address, $pageLink, $imagePath];
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if (sqlsrv_execute($stmt)) {
        header("Location: index.php");
        exit();
    } else {
        die(print_r(sqlsrv_errors(), true));
    }
}
?>

