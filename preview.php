<?php
require_once 'config.php';

// 检查登录状态
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die('未授权访问');
}

if (!isset($_GET['id'])) {
    die('文件 ID 未指定');
}

$id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    
    if (!$file) {
        die('文件不存在');
    }
    
    $filepath = $file['filepath'];
    $filename = $file['filename'];
    $filetype = $file['filetype'];
    $filesize = $file['filesize'];
    
    if (!file_exists($filepath)) {
        die('文件不存在');
    }
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // 获取文件 URL（预览模式）
    $fileUrl = 'download.php?id=' . $id . '&mode=inline';
    
    // 获取下载 URL（下载按钮使用）
    $downloadUrl = 'download.php?id=' . $id;
    
    // 根据文件类型选择预览方式
    $previewType = 'unknown';
    
    // 图片文件
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'])) {
        $previewType = 'image';
    }
    // PDF 文件
    elseif ($extension === 'pdf') {
        $previewType = 'pdf';
    }
    // 文档文件（Word、Excel、PowerPoint）
    elseif (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf'])) {
        $previewType = 'google-docs';
    }
    // 文本文件
    elseif (in_array($extension, ['txt', 'md', 'json', 'xml', 'html', 'css', 'js', 'ts', 'jsx', 'tsx', 'vue', 'php', 'py', 'java', 'c', 'cpp', 'h', 'cs', 'go', 'rs', 'swift', 'kt', 'rb', 'sql', 'yaml', 'yml', 'ini', 'cfg', 'log'])) {
        $previewType = 'text';
    }
    // 视频文件
    elseif (in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'mkv', 'flv', 'webm', 'm4v', '3gp', 'ts'])) {
        $previewType = 'video';
    }
    // 音频文件
    elseif (in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma'])) {
        $previewType = 'audio';
    }
    // 压缩文件
    elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz'])) {
        $previewType = 'archive';
    }
    // 可执行文件
    elseif (in_array($extension, ['exe', 'msi', 'app', 'dmg', 'deb', 'rpm', 'apk', 'ipa'])) {
        $previewType = 'executable';
    }
    
} catch (PDOException $e) {
    die('数据库错误');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($filename); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            overflow: hidden;
        }
        
        .preview-container {
            width: 100%;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }
        
        .preview-frame {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .pdf-container {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #525652;
        }
        
        .pdf-embed {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 100vh;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }
        
        .text-preview {
            background: white;
            padding: 40px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            overflow: auto;
            height: 100vh;
            min-height: 200px;
        }
        
        .text-preview:empty::before {
            content: '正在加载文本内容...';
            color: #999;
            font-style: italic;
        }
        
        .video-preview, .audio-preview {
            width: 100%;
            max-width: 100%;
            display: block;
            margin: 0 auto;
        }
        
        .video-container, .audio-container {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }
        
        .no-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: #f5f5f5;
        }
        
        .no-preview h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .no-preview p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .no-preview a {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        
        .no-preview a:hover {
            background: #5568d3;
        }
        
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.3s;
        }
        
        .loading.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="loading" id="loading">
        <div class="loading-spinner"></div>
        <div class="loading-text">加载中...</div>
    </div>
    
    <div class="preview-container">
        <?php if ($previewType === 'image'): ?>
            <img src="<?php echo $fileUrl; ?>" alt="<?php echo htmlspecialchars($filename); ?>" class="image-preview" onload="hideLoading()" onerror="hideLoading()">
            
        <?php elseif ($previewType === 'pdf'): ?>
            <div class="pdf-container">
                <embed src="<?php echo $fileUrl; ?>" type="application/pdf" class="pdf-embed" onerror="handlePdfError()" onload="hideLoading()">
                <script>
                    function handlePdfError() {
                        hideLoading();
                        const container = document.querySelector('.pdf-container');
                        container.innerHTML = `
                            <div class="no-preview">
                                <h2>PDF 预览失败</h2>
                                <p>您的浏览器不支持在线预览 PDF 文件</p>
                                <a href="<?php echo $downloadUrl; ?>" target="_blank">在新窗口打开</a>
                            </div>
                        `;
                    }
                </script>
            </div>
            
        <?php elseif ($previewType === 'google-docs'): ?>
            <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $fileUrl); ?>&wdStartOn=1" class="preview-frame" onload="hideLoading()"></iframe>
            
        <?php elseif ($previewType === 'text'): ?>
            <div class="text-preview" id="textPreview"></div>
            <script>
                fetch('<?php echo $fileUrl; ?>')
                    .then(response => response.text())
                    .then(text => {
                        const preview = document.getElementById('textPreview');
                        preview.textContent = text;
                        hideLoading();
                    })
                    .catch(error => {
                        console.error('加载失败:', error);
                        const preview = document.getElementById('textPreview');
                        preview.innerHTML = '<p style="color: #ef4444;">加载失败，请<a href="<?php echo $fileUrl; ?>" style="color: #667eea;">下载文件</a>查看</p>';
                        hideLoading();
                    });
            </script>
            
        <?php elseif ($previewType === 'video'): ?>
            <div class="video-container">
                <video src="<?php echo $fileUrl; ?>" controls class="video-preview" onloadeddata="hideLoading()"></video>
            </div>
            
        <?php elseif ($previewType === 'audio'): ?>
            <div class="audio-container">
                <audio src="<?php echo $fileUrl; ?>" controls class="audio-preview" onloadeddata="hideLoading()"></audio>
            </div>
            
        <?php elseif ($previewType === 'archive'): ?>
            <div class="no-preview">
                <h2>📦 压缩文件</h2>
                <p>此文件为压缩包，无法在线预览</p>
                <a href="<?php echo $downloadUrl; ?>">下载文件</a>
            </div>
            
        <?php elseif ($previewType === 'executable'): ?>
            <div class="no-preview">
                <h2>⚙️ 可执行文件</h2>
                <p>此文件为可执行程序，无法在线预览</p>
                <a href="<?php echo $downloadUrl; ?>">下载文件</a>
            </div>
            
        <?php else: ?>
            <div class="no-preview">
                <h2>无法预览此文件</h2>
                <p>此文件类型不支持在线预览</p>
                <a href="<?php echo $downloadUrl; ?>">下载文件</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function hideLoading() {
            const loading = document.getElementById('loading');
            if (loading) {
                loading.classList.add('hidden');
                setTimeout(() => {
                    if (loading.parentNode) {
                        loading.style.display = 'none';
                    }
                }, 300);
            }
        }
        
        // 页面加载完成后，确保隐藏加载状态
        window.addEventListener('load', function() {
            setTimeout(hideLoading, 500);
        });
        
        // 如果预览失败，确保隐藏加载状态
        window.addEventListener('error', function() {
            setTimeout(hideLoading, 500);
        });
    </script>
</body>
</html>