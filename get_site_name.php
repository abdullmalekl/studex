<?php
include 'includes/db_connection.php';

function getSiteName() {
    global $conn;
    $result = $conn->query("SELECT site_name FROM Site_name ORDER BY id DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['site_name'];
    }
    return 'المعهد العالي للعلوم والتقنية غريان'; // القيمة الافتراضية
}
?>

