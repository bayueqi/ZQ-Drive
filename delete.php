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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '仅支持 DELETE 或 POST 请求']);
    exit;
}

$files = [];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => '缺少文件 ID']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([intval($_GET['id'])]);
    $file = $stmt->fetch();
    if ($file) {
        $files[] = $file;
    }
} else {
    if (isset($_POST['tokens'])) {
        $tokens = explode(',', $_POST['tokens']);
        foreach ($tokens as $token) {
            $stmt = $pdo->prepare("SELECT * FROM files WHERE token = ?");
            $stmt->execute([$token]);
            $file = $stmt->fetch();
            if ($file) {
                $files[] = $file;
            }
        }
    } elseif (isset($_POST['ids'])) {
        $fileIds = array_map('intval', explode(',', $_POST['ids']));
        foreach ($fileIds as $id) {
            $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
            $stmt->execute([$id]);
            $file = $stmt->fetch();
            if ($file) {
                $files[] = $file;
            }
        }
    } elseif (isset($_POST['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([intval($_POST['id'])]);
        $file = $stmt->fetch();
        if ($file) {
            $files[] = $file;
        }
    } else {
        echo json_encode(['success' => false, 'message' => '缺少文件 ID 或 token']);
        exit;
    }
}

try {
    foreach ($files as $file) {
        if (file_exists($file['filepath'])) {
            unlink($file['filepath']);
        }

        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$file['id']]);
    }

    echo json_encode(['success' => true, 'message' => '文件删除成功']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
}
?>