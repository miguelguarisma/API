<?php
session_start();
include('../db_connect2.php');

header('Content-Type: application/json');

// Verify admin session
if (empty($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// --- CARD COUNTS ---
$totalReports = $conn->query("SELECT COUNT(*) FROM community_reports")->fetch_row()[0];
$pendingReports = $conn->query("SELECT COUNT(*) FROM community_reports WHERE status='pending'")->fetch_row()[0];
$resolvedCases = $conn->query("SELECT COUNT(*) FROM community_reports WHERE status='resolved'")->fetch_row()[0];
$criticalHazards = $conn->query("SELECT COUNT(*) FROM community_reports WHERE status='critical'")->fetch_row()[0];

// --- RECENT REPORTS ---
$recentReports = [];
$res = $conn->query("SELECT id, created_at, location, waste_type, status 
                     FROM community_reports 
                     ORDER BY created_at DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    $recentReports[] = $row;
}

// --- MONTHLY REPORTS FOR LINE CHART ---
$monthlyReports = [];
$res = $conn->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') AS month, COUNT(*) AS cnt
    FROM blockchain_ledger
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY YEAR(created_at), MONTH(created_at)
");
while($row = $res->fetch_assoc()) {
    $monthlyReports[] = $row;
}

// --- WASTE TYPES FOR BAR CHART ---
$wasteTypes = [];
$res = $conn->query("SELECT waste_type, COUNT(*) AS cnt FROM community_reports GROUP BY waste_type");
while($row = $res->fetch_assoc()) {
    $wasteTypes[] = $row;
}

// --- STATUS COUNTS FOR PIE CHART ---
$statusCounts = ['pending'=>0, 'resolved'=>0, 'critical'=>0];
$res = $conn->query("SELECT status, COUNT(*) AS cnt FROM community_reports GROUP BY status");
while($row = $res->fetch_assoc()) {
    $statusCounts[$row['status']] = (int)$row['cnt'];
}

// --- OUTPUT JSON ---
echo json_encode([
    'totalReports' => (int)$totalReports,
    'pendingReports' => (int)$pendingReports,
    'resolvedCases' => (int)$resolvedCases,
    'criticalHazards' => (int)$criticalHazards,
    'recentReports' => $recentReports,
    'monthlyReports' => $monthlyReports,
    'wasteTypes' => $wasteTypes,
    'statusCounts' => $statusCounts
]);