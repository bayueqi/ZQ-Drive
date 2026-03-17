<?php
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

// 无需登录即可下载文件

// 处理批量压缩下载
if (isset($_GET['action']) && $_GET['action'] === 'zip') {
    if (!isset($_GET['tokens'])) {
        die('缺少文件token');
    }
    
    $tokens = explode(',', $_GET['tokens']);
    
    // 创建临时目录
    $tempDir = 'temp_zip_' . uniqid() . '/';
    mkdir($tempDir, 0755, true);
    
    // 准备压缩文件
    $zipFilename = 'download_' . date('YmdHis') . '.zip';
    $zipPath = $tempDir . $zipFilename;
    
    // 创建ZIP文件
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
        die('无法创建压缩文件');
    }
    
    // 添加文件到ZIP
    foreach ($tokens as $token) {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE token = ?");
        $stmt->execute([$token]);
        $file = $stmt->fetch();
        
        if ($file && file_exists($file['filepath'])) {
            $zip->addFile($file['filepath'], $file['filename']);
        }
    }
    
    $zip->close();
    
    // 发送压缩文件
    if (file_exists($zipPath)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $zipFilename);
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        
        // 清理临时文件
        unlink($zipPath);
        rmdir($tempDir);
    } else {
        die('压缩文件创建失败');
    }
    exit;
}

if (!isset($_GET['token'])) {
    die('缺少文件 token');
}

$token = $_GET['token'];
$mode = $_GET['mode'] ?? 'download'; // download 或 inline

try {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE token = ?");
    $stmt->execute([$token]);
    $file = $stmt->fetch();

    if (!$file) {
        die('文件不存在');
    }

    if (!file_exists($file['filepath'])) {
        die('文件已丢失');
    }

    $filepath = $file['filepath'];
    $filename = $file['filename'];
    $filetype = $file['filetype'];
    $filesize = filesize($filepath);
    
    // 支持断点续传
    $range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;
    
    if ($range) {
        // 解析Range头
        list($unit, $range) = explode('=', $range, 2);
        if ($unit === 'bytes') {
            list($start, $end) = explode('-', $range, 2);
            $start = intval($start);
            $end = $end === '' ? $filesize - 1 : intval($end);
            $length = $end - $start + 1;
            
            // 设置断点续传响应头
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $filesize);
            header('Content-Length: ' . $length);
        }
    } else {
        // 完整下载
        header('HTTP/1.1 200 OK');
        header('Content-Length: ' . $filesize);
    }
    
    // 告诉客户端支持断点续传
    header('Accept-Ranges: bytes');
    
    // 添加更多响应头，确保下载管理器能够弹出
    header('Content-Transfer-Encoding: binary');
    header('Connection: keep-alive');
    
    // 对文件名进行RFC 5987编码，确保正确显示中文文件名
    $encodedFilename = rawurlencode($filename);
    $dispositionFilename = $encodedFilename !== $filename ? "filename*=UTF-8''$encodedFilename; filename=\"$filename\"" : "filename=\"$filename\"";
    
    header('Content-Type: ' . $filetype);
    
    if ($mode === 'inline') {
        // 预览模式：在浏览器中显示
        header('Content-Disposition: inline; ' . $dispositionFilename);
        header('X-Content-Type-Options: nosniff');
    } else {
        // 下载模式：强制下载
        header('Content-Disposition: attachment; ' . $dispositionFilename);
    }
    
    header('Cache-Control: public, max-age=86400');
    header('Pragma: public');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
    
    // 使用流式输出，提高大文件下载速度
    $handle = fopen($filepath, 'rb');
    if ($handle) {
        if ($range && isset($start)) {
            // 断点续传：跳转到指定位置
            fseek($handle, $start);
            while (!feof($handle) && ftell($handle) <= $end) {
                echo fread($handle, 8192);
                ob_flush();
                flush();
            }
        } else {
            // 完整下载
            while (!feof($handle)) {
                echo fread($handle, 8192);
                ob_flush();
                flush();
            }
        }
        fclose($handle);
    } else {
        readfile($filepath);
    }
} catch (PDOException $e) {
    die('数据库错误: ' . $e->getMessage());
}
?>