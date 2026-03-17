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

    if (!isset($_FILES['fileChunk'])) {
        echo json_encode(['success' => false, 'message' => '未收到文件块']);
        exit;
    }

    $chunk = intval($_POST['chunk']);
    $totalChunks = intval($_POST['totalChunks']);
    $fileHash = $_POST['fileHash'];
    $fileName = $_POST['fileName'];
    
    $chunkFile = $_FILES['fileChunk'];
    
    if ($chunkFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => '文件块上传失败']);
        exit;
    }

    // 创建临时目录
    $tempDir = 'temp_chunks/' . $fileHash . '/';
    if (!is_dir($tempDir)) {
        // 确保temp_chunks目录存在
        if (!is_dir('temp_chunks')) {
            if (!mkdir('temp_chunks', 0755, true)) {
                echo json_encode(['success' => false, 'message' => '无法创建temp_chunks目录']);
                exit;
            }
        }
        if (!mkdir($tempDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => '无法创建临时目录']);
            exit;
        }
    }
    // 确保临时目录有写入权限
    if (!is_writable($tempDir)) {
        if (!chmod($tempDir, 0755)) {
            echo json_encode(['success' => false, 'message' => '无法设置临时目录权限']);
            exit;
        }
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
    
    // 检查临时目录是否有文件块
    $chunkFiles = glob($tempDir . '*');
    if (empty($chunkFiles)) {
        echo json_encode(['success' => false, 'message' => '临时目录中没有文件块']);
        exit;
    }

    // 检查是否已经存在uploads_开头的目录
    $uploadDirs = glob('uploads_*');
    if (!empty($uploadDirs)) {
        // 使用第一个找到的uploads_目录
        $uploadDir = $uploadDirs[0] . '/';
    } else {
        // 生成随机上传目录名称
        $randomDir = 'uploads_' . bin2hex(random_bytes(8));
        $uploadDir = $randomDir . '/';
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => '无法创建上传目录']);
            exit;
        }
    }
    // 确保上传目录有写入权限
    if (!is_writable($uploadDir)) {
        if (!chmod($uploadDir, 0755)) {
            echo json_encode(['success' => false, 'message' => '无法设置上传目录权限']);
            exit;
        }
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

        // 检查文件块数量
        if (empty($chunkFiles)) {
            fclose($finalFile);
            throw new Exception('临时目录中没有文件块');
        }

        $totalWritten = 0;
        foreach ($chunkFiles as $chunkFile) {
            // 检查文件块是否存在且可读
            if (!file_exists($chunkFile) || !is_readable($chunkFile)) {
                fclose($finalFile);
                throw new Exception('文件块不存在或不可读: ' . basename($chunkFile));
            }
            
            // 使用更高效的读取方式
            $chunkData = file_get_contents($chunkFile);
            if ($chunkData === false) {
                fclose($finalFile);
                throw new Exception('无法读取文件块: ' . basename($chunkFile));
            }
            
            // 写入文件块
            $written = fwrite($finalFile, $chunkData);
            if ($written === false) {
                fclose($finalFile);
                throw new Exception('无法写入文件块: ' . basename($chunkFile));
            }
            $totalWritten += $written;
            
            // 立即释放内存
            unset($chunkData);
        }

        fclose($finalFile);

        // 检查文件大小，如果为0，抛出异常
        $actualFileSize = filesize($finalPath);
        if ($actualFileSize === 0 || $totalWritten === 0) {
            unlink($finalPath);
            throw new Exception('文件合并失败，生成的文件为空');
        }

        cleanTempDir($tempDir);

        // 处理ZIP文件
        if ($fileType === 'application/zip') {
            // 创建解压目录
            $extractDir = 'temp_extract_' . uniqid() . '/';
            mkdir($extractDir, 0755, true);
            
            // 解压ZIP文件
            $zip = new ZipArchive();
            if ($zip->open($finalPath) === TRUE) {
                $zip->extractTo($extractDir);
                $zip->close();
                
                // 读取解压后的文件
                $extractedFiles = [];
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractDir));
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $extractedFiles[] = $file->getPathname();
                    }
                }
                
                // 保存解压后的文件
                foreach ($extractedFiles as $extractedFile) {
                    $relativePath = str_replace($extractDir, '', $extractedFile);
                    $extFileName = basename($extractedFile);
                    $extFileSize = filesize($extractedFile);
                    $extFileType = mime_content_type($extractedFile);
                    
                    // 生成新的文件名
                    $extExtension = pathinfo($extFileName, PATHINFO_EXTENSION);
                    $extNewFilename = uniqid() . '.' . $extExtension;
                    $extFinalPath = $uploadDir . $extNewFilename;
                    
                    // 移动文件
                    if (rename($extractedFile, $extFinalPath)) {
                        // 生成随机token
                        $extToken = bin2hex(random_bytes(16));
                        
                        // 保存文件信息到数据库
                        saveFileToDatabase($extFileName, $extFinalPath, $extFileSize, $extFileType, $description, $folderId, $extToken);
                    }
                }
                
                // 清理临时文件
                array_map('unlink', glob($extractDir . '*'));
                rmdir($extractDir);
                unlink($finalPath);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'ZIP文件上传并解压成功'
                ]);
            } else {
                unlink($finalPath);
                throw new Exception('无法解压ZIP文件');
            }
        } else {
            // 生成随机token
            $token = bin2hex(random_bytes(16));

            saveFileToDatabase($fileName, $finalPath, $actualFileSize, $fileType, $description, $folderId, $token);

            echo json_encode([
                'success' => true,
                'message' => '文件上传成功',
                'fileId' => $finalPath
            ]);
        }
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

function saveFileToDatabase($fileName, $finalPath, $fileSize, $fileType, $description, $folderId = 0, $token = null) {
    global $pdo;
    
    if (!$fileSize) {
        $fileSize = filesize($finalPath);
    }

    $stmt = $pdo->prepare("INSERT INTO files (filename, filepath, filesize, filetype, description, folder_id, token) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fileName, $finalPath, $fileSize, $fileType, $description, $folderId > 0 ? $folderId : null, $token]);
}
?>