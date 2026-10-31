<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$post_id = intval($input['post_id'] ?? 0);
$action = $input['action'] ?? '';

if ($post_id <= 0 || !in_array($action, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$user_id = $_SESSION['user_id'];

$check = $pdo->prepare("SELECT * FROM reactions WHERE post_id = ? AND user_id = ?");
$check->execute([$post_id, $user_id]);
$existing = $check->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    if ($existing['type'] === $action) {
        $del = $pdo->prepare("DELETE FROM reactions WHERE id = ?");
        $del->execute([$existing['id']]);
    } else {
        $upd = $pdo->prepare("UPDATE reactions SET type = ? WHERE id = ?");
        $upd->execute([$action, $existing['id']]);
    }
} else {
    $ins = $pdo->prepare("INSERT INTO reactions (post_id, user_id, type) VALUES (?, ?, ?)");
    $ins->execute([$post_id, $user_id, $action]);
}

$likes_stmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ? AND type = 'like'");
$likes_stmt->execute([$post_id]);
$likes_count = $likes_stmt->fetchColumn();

$dislikes_stmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ? AND type = 'dislike'");
$dislikes_stmt->execute([$post_id]);
$dislikes_count = $dislikes_stmt->fetchColumn();

$ur_stmt = $pdo->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
$ur_stmt->execute([$post_id, $user_id]);
$current_reaction = $ur_stmt->fetchColumn() ?: null;

echo json_encode([
    'success' => true,
    'likes_count' => $likes_count,
    'dislikes_count' => $dislikes_count,
    'total_reactions' => $likes_count + $dislikes_count,
    'user_reaction' => $current_reaction
]);