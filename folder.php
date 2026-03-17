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

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
}

if ($action === 'create') {
    // 创建文件夹
    $name = $_POST['name'] ?? '';
    $parentId = intval($_POST['parent_id'] ?? 0);
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => '文件夹名称不能为空']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO folders (name, parent_id) VALUES (?, ?)");
        $stmt->execute([$name, $parentId > 0 ? $parentId : null]);
        
        echo json_encode(['success' => true, 'message' => '文件夹创建成功', 'folder_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
        
        // 检查是否是表不存在的错误
        if (strpos($errorMsg, "Table") !== false && strpos($errorMsg, "doesn't exist") !== false) {
            echo json_encode(['success' => false, 'message' => '数据库未更新，请先访问 update_folders.php']);
        } else {
            echo json_encode(['success' => false, 'message' => '创建失败：' . $errorMsg]);
        }
    }
} elseif ($action === 'delete') {
    // 删除文件夹
    $id = intval($_POST['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => '无效的文件夹 ID']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => '文件夹删除成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '删除失败：' . $e->getMessage()]);
    }
} elseif ($action === 'rename') {
    // 重命名文件夹
    $id = intval($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    
    if (!$id || empty($name)) {
        echo json_encode(['success' => false, 'message' => '无效的参数']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        
        echo json_encode(['success' => true, 'message' => '文件夹重命名成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '重命名失败：' . $e->getMessage()]);
    }
} elseif ($action === 'list') {
    // 获取文件夹列表
    $parentId = intval($_GET['parent_id'] ?? 0);
    
    try {
        if ($parentId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM folders WHERE parent_id = ? ORDER BY name ASC");
            $stmt->execute([$parentId]);
        } else {
            $stmt = $pdo->query("SELECT * FROM folders WHERE parent_id IS NULL ORDER BY name ASC");
        }
        
        $folders = $stmt->fetchAll();
        echo json_encode(['success' => true, 'folders' => $folders]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '获取失败：' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => '无效的操作']);
}
?>