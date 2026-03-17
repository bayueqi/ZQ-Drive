<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    if ($action === 'tree') {
        // 获取所有文件夹和文件用于构建树形结构
        try {
            $stmt = $pdo->prepare("SELECT * FROM folders ORDER BY name ASC");
            $stmt->execute();
            $folders = $stmt->fetchAll();
            
            $stmt = $pdo->prepare("SELECT * FROM files ORDER BY upload_time DESC");
            $stmt->execute();
            $files = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'folders' => $folders, 'files' => $files]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => '数据库错误：' . $e->getMessage()]);
        }
    } else {
        $folderId = intval($_GET['folder_id'] ?? 0);
        
        // 获取文件
        try {
            // 确保使用正确的查询语句
            if ($folderId > 0) {
                // 查询指定文件夹的文件
                $stmt = $pdo->prepare("SELECT * FROM files WHERE folder_id = :folder_id ORDER BY upload_time DESC");
                $stmt->bindParam(':folder_id', $folderId, PDO::PARAM_INT);
            } else {
                // 查询根目录文件（folder_id 为 NULL）
                $stmt = $pdo->prepare("SELECT * FROM files WHERE folder_id IS NULL ORDER BY upload_time DESC");
            }
            
            $stmt->execute();
            $files = $stmt->fetchAll();
            
            // 获取文件夹
            if ($folderId > 0) {
                $stmt = $pdo->prepare("SELECT * FROM folders WHERE parent_id = :parent_id ORDER BY name ASC");
                $stmt->bindParam(':parent_id', $folderId, PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM folders WHERE parent_id IS NULL ORDER BY name ASC");
            }
            
            $stmt->execute();
            $folders = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'files' => $files, 'folders' => $folders]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => '数据库错误：' . $e->getMessage()]);
        }
    }
}
?>