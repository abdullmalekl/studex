

<?php
function getLatestAnnouncements($conn, $limit = 5) {
    $announcements = [];
    $sql = "SELECT title, content FROM announcements ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    return $announcements;
}
