<?php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$posts = [];

if (!empty($search)) {
    $searchTerm = "%$search%";

    $sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id";
    $conditions = ["(posts.title LIKE ? OR posts.brief LIKE ? OR posts.content LIKE ?)"];
    $params = [$searchTerm, $searchTerm, $searchTerm];

    if (isset($current_category) && $current_category !== 'home') {
        $conditions[] = "posts.category = ?";
        $params[] = $current_category;
    }

    $sql .= " WHERE " . implode(' AND ', $conditions);
    $sql .= " ORDER BY posts.created_at " . ($sort ?? 'DESC');

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}