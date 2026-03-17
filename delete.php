<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '仅支持 DELETE 或 POST 请求']);
    exit;
}

$fileIds = [];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => '缺少文件 ID']);
        exit;
    }
    $fileIds = [intval($_GET['id'])];
} else {
    if (isset($_POST['ids'])) {
        $fileIds = array_map('intval', explode(',', $_POST['ids']));
    } elseif (isset($_POST['id'])) {
        $fileIds = [intval($_POST['id'])];
    } else {
        echo json_encode(['success' => false, 'message' => '缺少文件 ID']);
        exit;
    }
}

try {
    foreach ($fileIds as $id) {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if ($file) {
            if (file_exists($file['filepath'])) {
                unlink($file['filepath']);
            }

            $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$id]);
        }
    }

    echo json_encode(['success' => true, 'message' => '删除成功']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '数据库错误: ' . $e->getMessage()]);
}
?>