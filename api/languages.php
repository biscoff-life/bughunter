<?php
/**
 * BugHunter API - Languages Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$userId = getSessionUserId();

$sql = "SELECT l.*, 
        COUNT(DISTINCT c.id) as total_challenges,
        COUNT(DISTINCT CASE WHEN s.status = 'passed' AND s.user_id = ? THEN c.id END) as solved_challenges
        FROM languages l
        LEFT JOIN challenges c ON l.slug = c.language_slug
        LEFT JOIN submissions s ON c.id = s.challenge_id
        GROUP BY l.id, l.slug
        ORDER BY l.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$languages = $stmt->fetchAll();

echo json_encode([
    'languages' => array_map(function($l) {
        $total = (int)$l['total_challenges'];
        $solved = (int)$l['solved_challenges'];
        $progress = $total > 0 ? round(($solved / $total) * 100) : 0;
        return [
            'id' => (int)$l['id'],
            'slug' => $l['slug'],
            'name' => $l['name'],
            'icon' => $l['icon'],
            'color' => $l['color'],
            'description' => $l['description'],
            'total_challenges' => $total,
            'solved_challenges' => $solved,
            'progress' => $progress
        ];
    }, $languages)
]);
