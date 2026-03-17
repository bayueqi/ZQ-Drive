<?php
session_start();

$installed = file_exists('config.php') && file_exists('config.lock');

if ($installed) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

$step = $_GET['step'] ?? '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === '1') {
        $host = $_POST['host'] ?? '';
        $dbname = $_POST['dbname'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($host) || empty($dbname) || empty($username) || empty($password)) {
            $error = '请填写所有字段';
        } else {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = "
CREATE TABLE IF NOT EXISTS files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL,
  filepath VARCHAR(500) NOT NULL,
  filesize BIGINT NOT NULL,
  filetype VARCHAR(100),
  description TEXT,
  folder_id INT DEFAULT NULL,
  upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS folders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  parent_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_upload_time ON files(upload_time DESC);
CREATE INDEX idx_folder_id ON files(folder_id);
CREATE INDEX idx_parent_id ON folders(parent_id);
";
                $pdo->exec($sql);

                $_SESSION['setup_db'] = [
                    'host' => $host,
                    'dbname' => $dbname,
                    'username' => $username,
                    'password' => $password
                ];

                header('Location: setup.php?step=2');
                exit;
            } catch (PDOException $e) {
                $error = '数据库连接或创建表失败: ' . $e->getMessage();
            }
        }
    } elseif ($step === '2') {
        $adminUsername = $_POST['admin_username'] ?? '';
        $adminPassword = $_POST['admin_password'] ?? '';
        $adminPasswordConfirm = $_POST['admin_password_confirm'] ?? '';

        if (empty($adminUsername) || empty($adminPassword)) {
            $error = '请填写管理员账号和密码';
        } elseif ($adminPassword !== $adminPasswordConfirm) {
            $error = '两次输入的密码不一致';
        } elseif (strlen($adminPassword) < 6) {
            $error = '密码长度至少为6位';
        } else {
            $db = $_SESSION['setup_db'] ?? null;
            if (!$db) {
                $error = '会话已过期，请重新开始安装';
            } else {
                $configContent = "<?php
\$host = '{$db['host']}';
\$dbname = '{$db['dbname']}';
\$username = '{$db['username']}';
\$password = '{$db['password']}';
\$admin_username = '{$adminUsername}';
\$admin_password = '{$adminPassword}';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    die('数据库连接失败: ' . \$e->getMessage());
}
?>";

                file_put_contents('config.php', $configContent);
                file_put_contents('config.lock', date('Y-m-d H:i:s'));

                unset($_SESSION['setup_db']);

                $success = '安装成功！正在跳转...';
                header('refresh:2;url=login.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>安装 ZQ-Drive</title>
  <link rel="icon" href="data:image/svg+xml,%3Csvg t='1770824389605' class='icon' viewBox='0 0 1408 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='5614' xmlns:xlink='http://www.w3.org/1999/xlink' width='32' height='32'%3E%3Cpath d='M620.8 454.4h19.2c19.2 0 32-12.8 32-32s-12.8-32-32-32h-19.2c-44.8 0-76.8-25.6-76.8-57.6s32-57.6 76.8-57.6c12.8 0 19.2 0 32 6.4 19.2 6.4 38.4-6.4 44.8-25.6 0-19.2 25.6-32 51.2-32 32 0 51.2 19.2 51.2 38.4v6.4c-6.4 19.2 6.4 38.4 25.6 44.8 25.6 6.4 38.4 19.2 38.4 38.4s-25.6 38.4-51.2 38.4h-25.6c-19.2 0-32 12.8-32 32s12.8 32 32 32h25.6c64 0 115.2-44.8 115.2-102.4 0-38.4-25.6-76.8-64-89.6 0-57.6-57.6-102.4-115.2-102.4-44.8 0-83.2 19.2-102.4 57.6h-25.6c-76.8 0-140.8 51.2-140.8 121.6s64 115.2 140.8 115.2zM544 768h-320c-19.2 0-32 12.8-32 32s12.8 32 32 32h320c19.2 0 32-12.8 32-32s-12.8-32-32-32z' fill='%231296db' p-id='5615'%3E%3C/path%3E%3Cpath d='M1388.8 716.8v-19.2l-153.6-544C1216 64 1132.8 0 1056 0h-704C275.2 0 192 64 166.4 147.2L12.8 691.2v19.2c-6.4 32-12.8 57.6-12.8 89.6C0 921.6 102.4 1024 224 1024h960c121.6 0 224-102.4 224-224 0-32-6.4-57.6-19.2-83.2zM230.4 166.4C243.2 108.8 300.8 64 352 64h704c51.2 0 102.4 44.8 121.6 102.4l121.6 448c-32-25.6-70.4-38.4-115.2-38.4h-960c-44.8 0-83.2 12.8-115.2 32l121.6-441.6zM1184 960h-960C134.4 960 64 889.6 64 800c0-12.8 0-19.2 6.4-32v-6.4c12.8-70.4 76.8-121.6 153.6-121.6h960c76.8 0 140.8 51.2 153.6 128 0 12.8 6.4 19.2 6.4 32 0 89.6-70.4 160-160 160z' fill='%231296db' p-id='5616'%3E%3C/path%3E%3Cpath d='M1120 704c-51.2 0-96 44.8-96 96s44.8 96 96 96 96-44.8 96-96-44.8-96-96-96z m0 128c-19.2 0-32-12.8-32-32s12.8-32 32-32 32 12.8 32 32-12.8 32-32 32z' fill='%231296db' p-id='5617'%3E%3C/path%3E%3C/svg%3E" type="image/svg+xml">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: #f5f7fa;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .container {
      background: #fff;
      border: 1px solid #e1e4e8;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 40px;
      max-width: 500px;
      width: 100%;
    }
    .header {
      text-align: center;
      margin-bottom: 30px;
    }
    .header h1 {
      color: #303133;
      font-size: 24px;
      margin-bottom: 10px;
    }
    .header p {
      color: #909399;
      font-size: 14px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      color: #606266;
      font-weight: 500;
      margin-bottom: 8px;
      font-size: 14px;
    }
    .form-group input {
      width: 100%;
      padding: 10px 16px;
      border: 1px solid #e1e4e8;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.2s;
    }
    .form-group input:focus {
      outline: none;
      border-color: #3498db;
    }
    .form-group input::placeholder {
      color: #c0c4cc;
    }
    .btn {
      width: 100%;
      padding: 10px 20px;
      background: #3498db;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn:hover {
      opacity: 0.9;
    }
    .error {
      background: #fef0f0;
      color: #f56c6c;
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 14px;
      border: 1px solid #fde2e2;
    }
    .success {
      background: #f0f9ff;
      color: #67c23a;
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 14px;
      border: 1px solid #e1f3fe;
    }
    .info {
      background: #f4f4f5;
      color: #909399;
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 13px;
      line-height: 1.6;
      border: 1px solid #e9e9eb;
    }
    .info code {
      background: rgba(0, 0, 0, 0.1);
      padding: 2px 6px;
      border-radius: 4px;
      font-family: monospace;
    }
  </style>
</head>
<body>
  <div class="container">
    <?php if ($step === '1'): ?>
      <div class="header">
        <h1>🚀 安装向导 (1/2)</h1>
        <p>配置数据库信息以完成安装</p>
      </div>

      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="host">数据库主机</label>
          <input type="text" id="host" name="host" placeholder="输入数据库主机地址" value="<?php echo htmlspecialchars($_POST['host'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="dbname">数据库名</label>
          <input type="text" id="dbname" name="dbname" placeholder="输入数据库名称" value="<?php echo htmlspecialchars($_POST['dbname'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="username">数据库用户名</label>
          <input type="text" id="username" name="username" placeholder="输入数据库用户名" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="password">数据库密码</label>
          <input type="password" id="password" name="password" placeholder="输入数据库密码" required>
        </div>

        <button type="submit" class="btn">下一步</button>
      </form>
    <?php elseif ($step === '2'): ?>
      <div class="header">
        <h1>🔐 设置管理员账号 (2/2)</h1>
        <p>设置管理员账号和密码以保护您的网站</p>
      </div>

      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <div class="info">
        <strong>提示：</strong>请设置一个安全的密码，密码长度至少为6位。
      </div>

      <form method="POST">
        <div class="form-group">
          <label for="admin_username">管理员账号</label>
          <input type="text" id="admin_username" name="admin_username" placeholder="输入管理员账号" value="<?php echo htmlspecialchars($_POST['admin_username'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="admin_password">管理员密码</label>
          <input type="password" id="admin_password" name="admin_password" placeholder="输入管理员密码（至少6位）" required>
        </div>

        <div class="form-group">
          <label for="admin_password_confirm">确认密码</label>
          <input type="password" id="admin_password_confirm" name="admin_password_confirm" placeholder="再次输入管理员密码" required>
        </div>

        <button type="submit" class="btn">完成安装</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
