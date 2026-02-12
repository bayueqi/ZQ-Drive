<?php
session_start();

$installed = file_exists('config.php') && file_exists('config.lock');

if (!$installed) {
    header('Location: setup.php');
    exit;
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = '请填写账号和密码';
    } elseif ($username === $admin_username && $password === $admin_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = '账号或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>登录 ZQ-Drive</title>
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
      max-width: 400px;
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
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🔐 登录</h1>
      <p>请输入管理员账号和密码</p>
    </div>

    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="username">账号</label>
        <input type="text" id="username" name="username" placeholder="输入管理员账号" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">密码</label>
        <input type="password" id="password" name="password" placeholder="输入密码" required>
      </div>

      <button type="submit" class="btn">登录</button>
    </form>
  </div>
</body>
</html>
