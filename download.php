<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    die('缺少文件 ID');
}

$id = intval($_GET['id']);
$mode = $_GET['mode'] ?? 'download'; // download 或 inline

try {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$id]);
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