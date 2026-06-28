<?php
include 'config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'getById' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $koneksi->prepare("SELECT * FROM siswa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($result) {
        echo json_encode(['success' => true, 'data' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data not found']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>