<?php
// 禁止输出警告信息，确保返回纯JSON
error_reporting(0);
ini_set('display_errors', 0);

// 查找 config 文件
$configFiles = glob('config_*.php');
if (empty($configFiles)) {
    // 如果没有找到随机名称的 config 文件，尝试加载默认的 config.php
    if (file_exists('config.php')) {
        require_once 'config.php';
    } else {
        die('配置文件不存在');
    }
} else {
    // 加载第一个找到的 config 文件
    require_once $configFiles[0];
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '仅支持 POST 请求']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'get') {
    // 获取文件信息
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => '无效的文件 ID']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();
        
        if (!$file) {
            echo json_encode(['success' => false, 'message' => '文件不存在']);
            exit;
        }
        
        echo json_encode(['success' => true, 'file' => $file]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '数据库错误']);
    }
} elseif ($action === 'update') {
    // 更新文件信息
    $id = intval($_POST['id'] ?? 0);
    $filename = $_POST['filename'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (!$id || !$filename) {
        echo json_encode(['success' => false, 'message' => '无效的参数']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE files SET filename = ?, description = ? WHERE id = ?");
        $stmt->execute([$filename, $description, $id]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => '文件不存在或未修改']);
            exit;
        }
        
        echo json_encode(['success' => true, 'message' => '文件信息更新成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '数据库错误']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '无效的操作']);
}
?>