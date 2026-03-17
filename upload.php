<?php
// 查找 config 文件
$configFiles = glob('config_*.php');
if (empty($configFiles)) {
    die('配置文件不存在');
}

// 加载第一个找到的 config 文件
require_once $configFiles[0];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '仅支持 POST 请求']);
    exit;
}

if (isset($_POST['chunk']) && isset($_POST['totalChunks']) && isset($_POST['fileHash']) && isset($_POST['fileName'])) {
    handleChunkUpload();
} elseif (isset($_POST['action']) && $_POST['action'] === 'merge' && isset($_POST['fileHash']) && isset($_POST['fileName'])) {
    handleMergeRequest();
} else {
    echo json_encode(['success' => false, 'message' => '无效的请求参数']);
    exit;
}

function handleChunkUpload() {
    // 检查登录状态
    session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => '未授权访问']);
        exit;
    }

    if (!isset($_FILES['chunk'])) {
        echo json_encode(['success' => false, 'message' => '未收到文件块']);
        exit;
    }

    $chunk = intval($_POST['chunk']);
    $totalChunks = intval($_POST['totalChunks']);
    $fileHash = $_POST['fileHash'];
    $fileName = $_POST['fileName'];
    
    $chunkFile = $_FILES['chunk'];
    
    if ($chunkFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => '文件块上传失败']);
        exit;
    }

    // 创建临时目录
    $tempDir = 'temp_chunks/' . $fileHash . '/';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $chunkPath = $tempDir . $chunk;
    if (!move_uploaded_file($chunkFile['tmp_name'], $chunkPath)) {
        echo json_encode(['success' => false, 'message' => '文件块保存失败']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => '文件块上传成功', 'chunk' => $chunk]);
}

function handleMergeRequest() {
    $fileHash = $_POST['fileHash'];
    $fileName = $_POST['fileName'];
    $fileSize = intval($_POST['fileSize'] ?? 0);
    $fileType = $_POST['fileType'] ?? '';
    $description = $_POST['description'] ?? '';
    $folderId = intval($_POST['folder_id'] ?? 0);

    // 检查登录状态
    session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => '未授权访问']);
        exit;
    }

    $tempDir = 'temp_chunks/' . $fileHash . '/';
    if (!is_dir($tempDir)) {
        echo json_encode(['success' => false, 'message' => '临时文件目录不存在']);
        exit;
    }

    // 生成随机上传目录名称
    $randomDir = 'upload_' . bin2hex(random_bytes(8));
    $uploadDir = $randomDir . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFilename = uniqid() . '.' . $extension;
    $finalPath = $uploadDir . $newFilename;

    try {
        // 使用更高效的文件合并方式
        $finalFile = fopen($finalPath, 'wb');
        if (!$finalFile) {
            throw new Exception('无法创建目标文件');
        }

        $chunkFiles = glob($tempDir . '*');
        natsort($chunkFiles); // 使用自然排序，确保块按顺序合并

        foreach ($chunkFiles as $chunkFile) {
            // 使用更高效的读取方式
            $chunkData = file_get_contents($chunkFile);
            if ($chunkData === false) {
                throw new Exception('无法读取文件块');
            }
            fwrite($finalFile, $chunkData);
            // 立即释放内存
            unset($chunkData);
        }

        fclose($finalFile);

        cleanTempDir($tempDir);

        saveFileToDatabase($fileName, $finalPath, $fileSize, $fileType, $description, $folderId);

        echo json_encode([
            'success' => true,
            'message' => '文件上传成功',
            'fileId' => $finalPath
        ]);
    } catch (Exception $e) {
        if (file_exists($finalPath)) {
            unlink($finalPath);
        }
        cleanTempDir($tempDir);
        echo json_encode(['success' => false, 'message' => '文件合并失败: ' . $e->getMessage()]);
    }
}

function cleanTempDir($tempDir) {
    $files = glob($tempDir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    rmdir($tempDir);
}

function saveFileToDatabase($fileName, $finalPath, $fileSize, $fileType, $description, $folderId = 0) {
    global $pdo;
    
    if (!$fileSize) {
        $fileSize = filesize($finalPath);
    }

    $stmt = $pdo->prepare("INSERT INTO files (filename, filepath, filesize, filetype, description, folder_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fileName, $finalPath, $fileSize, $fileType, $description, $folderId > 0 ? $folderId : null]);
}
?>