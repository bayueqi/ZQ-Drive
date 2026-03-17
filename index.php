<?php
session_start();

if (!file_exists('config.php') || !file_exists('config.lock')) {
    header('Location: setup.php');
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ZQ-Drive</title>
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
    }
    
    /* 侧边栏 */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: 240px;
      height: 100vh;
      background: #fff;
      border-right: 1px solid #e1e4e8;
      transition: transform 0.3s ease;
      z-index: 100;
      display: flex;
      flex-direction: column;
    }
    
    .sidebar.collapsed {
      transform: translateX(-240px);
      width: 0 !important;
    }
    
    .sidebar-resizer {
      position: absolute;
      right: 0;
      top: 0;
      width: 4px;
      height: 100vh;
      cursor: col-resize;
      background: transparent;
      transition: background 0.2s;
    }
    
    .sidebar-resizer:hover {
      background: #3498db;
    }
    
    .sidebar-resizer.dragging {
      background: #3498db;
    }
    
    .sidebar-header {
      padding: 16px;
      border-bottom: 1px solid #e1e4e8;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #f8f9fa;
    }
    
    .sidebar-title {
      font-size: 16px;
      font-weight: 600;
      color: #303133;
    }
    
    .sidebar-actions {
      display: flex;
      gap: 8px;
    }
    
    .sidebar-action-btn {
      width: 32px;
      height: 32px;
      border: none;
      background: none;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
      transition: background 0.2s;
    }
    
    .sidebar-action-btn:hover {
      background: #e1e4e8;
    }
    
    .sidebar-toggle {
      width: 32px;
      height: 32px;
      border: none;
      background: none;
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
      transition: background 0.2s;
    }
    
    .sidebar-toggle:hover {
      background: #e1e4e8;
    }
    
    .sidebar-content {
      flex: 1;
      overflow-y: auto;
      padding: 16px 0;
    }
    
    .sidebar-footer {
      padding: 16px;
      border-top: 1px solid #e1e4e8;
      margin-top: auto;
    }
    
    .github-link {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #606266;
      text-decoration: none;
      font-size: 14px;
      transition: color 0.2s;
    }
    
    .github-link:hover {
      color: #3498db;
    }
    
    .sidebar-toggle-btn {
      position: fixed;
      left: 240px;
      top: 50%;
      transform: translateY(-50%);
      width: 24px;
      height: 48px;
      border: none;
      background: #fff;
      border: 1px solid #e1e4e8;
      border-left: none;
      border-radius: 0 4px 4px 0;
      cursor: pointer;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: left 0.3s ease, transform 0.3s ease, background 0.2s;
      z-index: 99;
      box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    }
    
    .sidebar-toggle-btn:hover {
      background: #f5f7fa;
    }
    
    .sidebar-toggle-btn.collapsed {
      left: 0 !important;
      transform: translateY(-50%) rotate(180deg);
    }
    
    .tree-item {
      cursor: pointer;
      user-select: none;
    }
    
    .tree-item-content {
      display: flex;
      align-items: center;
      padding: 8px 16px;
      transition: background 0.2s;
    }
    
    .tree-item-content:hover {
      background: #f5f7fa;
    }
    
    .tree-item-content.active {
      background: #ecf6fd;
      color: #3498db;
    }
    
    .tree-item-toggle {
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 4px;
      font-size: 12px;
      transition: transform 0.2s;
      flex-shrink: 0;
    }
    
    .tree-item-toggle.expanded {
      transform: rotate(90deg);
    }
    
    .tree-item-toggle.hidden {
      visibility: hidden;
    }
    
    .hidden {
      display: none !important;
    }
    
    .tree-item-icon {
      margin-right: 8px;
      font-size: 16px;
      flex-shrink: 0;
    }
    
    .tree-item-name {
      flex: 1;
      font-size: 14px;
      color: #303133;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .tree-item-actions {
      display: none;
      gap: 4px;
      margin-left: 8px;
    }
    
    .tree-item-content:hover .tree-item-actions {
      display: flex;
    }
    
    .tree-item-action-btn {
      width: 20px;
      height: 20px;
      border: none;
      background: none;
      cursor: pointer;
      font-size: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 2px;
      transition: background 0.2s;
    }
    
    .tree-item-action-btn:hover {
      background: #e1e4e8;
    }
    
    .tree-children {
      display: none;
      padding-left: 20px;
      border-left: 2px solid #e1e4e8;
      margin-left: 20px;
    }
    
    .tree-children.expanded {
      display: block;
    }
    
    .tree-item-file {
      margin-top: 2px;
    }
    
    /* 主内容区 */
    .main-content {
      margin-left: 240px;
      padding: 0;
      transition: margin-left 0.3s ease;
    }
    
    .sidebar.collapsed ~ .main-content {
      margin-left: 0;
    }
    
    /* 批量操作区域 */
    .batch-actions {
      background: #f8f9fa;
      border-bottom: 1px solid #e1e4e8;
      padding: 12px 24px;
      display: none;
    }
    
    .batch-actions.show {
      display: block;
    }
    
    .batch-actions-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .batch-select-info {
      font-size: 14px;
      color: #303133;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    #selectAllCheckbox {
      width: 16px;
      height: 16px;
      cursor: pointer;
    }
    
    #selectAllCheckbox + label {
      cursor: pointer;
      user-select: none;
    }
    
    .batch-actions-buttons {
      display: flex;
      gap: 8px;
    }
    
    .btn.danger {
      background: #e74c3c;
      color: white;
    }
    
    .btn.danger:hover {
      background: #c0392b;
    }
    
    /* 文件列表区 */
    .files-container {
      padding: 24px;
    }
    
    .files-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 16px;
    }
    
    .file-card {
      background: #fff;
      border: 1px solid #e1e4e8;
      border-radius: 8px;
      padding: 16px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      position: relative;
    }
    
    .file-checkbox {
      position: absolute;
      bottom: 0px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    
    .file-card.selected {
      background: #ecf6fd;
      border-color: #3498db;
    }
    
    .file-card {
      flex-direction: column;
      align-items: center;
      text-align: center;
      position: relative;
    }
    
    .file-card:hover {
      border-color: #3498db;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .file-card.selected {
      border-color: #3498db;
      background: #ecf6fd;
    }
    
    .file-icon {
      font-size: 48px;
      margin-bottom: 12px;
    }
    
    .file-name {
      font-size: 13px;
      color: #303133;
      font-weight: 500;
      word-break: break-all;
      line-height: 1.4;
      margin-bottom: 4px;
    }
    
    .file-meta {
      font-size: 11px;
      color: #909399;
    }
    
    .file-description {
      font-size: 12px;
      color: #606266;
      margin-bottom: 4px;
      word-break: break-word;
      line-height: 1.4;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
    
    .file-actions {
      position: absolute;
      top: 0px;
      right: 8px;
      display: flex;
      gap: 4px;
      background: rgba(255, 255, 255, 0.95);
      padding: 4px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      width: calc(100% - 16px);
      justify-content: space-around;
    }
    
    .file-action-btn {
      flex: 1;
      height: 24px;
      border: none;
      background: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
      max-width: 36px;
    }
    
    .file-action-btn:hover {
      background: rgba(0, 0, 0, 0.05);
    }
    
    /* 上传区域 */
    .upload-area {
      border: 2px dashed #d1d5db;
      border-radius: 8px;
      padding: 40px 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;
      background: #fafbfc;
      margin-bottom: 24px;
    }
    
    .upload-area:hover {
      border-color: #3498db;
      background: #ecf6fd;
    }
    
    .upload-area.dragover {
      border-color: #3498db;
      background: #ecf6fd;
    }
    
    .upload-icon {
      font-size: 48px;
      margin-bottom: 12px;
    }
    
    .upload-text {
      color: #606266;
      font-size: 14px;
    }
    
    .upload-subtext {
      color: #909399;
      font-size: 12px;
      margin-top: 4px;
    }
    
    /* 模态框 */
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
    
    .modal.show {
      display: flex;
    }
    
    .modal-content {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 480px;
      max-height: 80vh;
      display: flex;
      flex-direction: column;
    }
    
    .modal-header {
      padding: 20px;
      border-bottom: 1px solid #e1e4e8;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-header h3 {
      font-size: 16px;
      font-weight: 600;
      color: #303133;
    }
    
    .modal-close {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #909399;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
      transition: all 0.2s;
    }
    
    .modal-close:hover {
      background: #f5f7fa;
    }
    
    .modal-body {
      padding: 24px;
      overflow-y: auto;
      flex: 1;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      color: #606266;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 8px;
    }
    
    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.2s;
      background: #fff;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #3498db;
    }
    
    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }
    
    .file-input-group {
      margin-bottom: 16px;
      padding: 12px;
      border: 1px solid #e4e7ed;
      border-radius: 8px;
      background-color: #f9f9f9;
    }
    
    .file-input-row {
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }
    
    .file-name-input,
    .file-desc-input {
      flex: 1;
      min-width: 0;
    }
    
    .file-name-input input {
      width: 100%;
      padding: 8px;
      border: 1px solid #dcdfe6;
      border-radius: 4px;
      font-size: 14px;
    }
    
    .file-desc-input textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #dcdfe6;
      border-radius: 4px;
      resize: vertical;
      min-height: 80px;
      font-size: 14px;
    }
    
    .file-name-input input:focus,
    .file-desc-input textarea:focus {
      outline: none;
      border-color: #3498db;
      box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }
    
    @media (max-width: 768px) {
      .file-input-row {
        flex-direction: column;
        gap: 8px;
      }
    }
    
    .modal-footer {
      padding: 16px 24px;
      border-top: 1px solid #e1e4e8;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }
    
    .btn {
      padding: 10px 20px;
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
    
    .btn.primary {
      background: #3498db;
      color: #fff;
    }
    
    .btn.secondary {
      background: #f5f7fa;
      color: #606266;
    }
    
    /* 进度条 */
    .upload-progress {
      margin-top: 16px;
      display: none;
    }
    
    .progress-bar {
      height: 4px;
      background: #e1e4e8;
      border-radius: 2px;
      overflow: hidden;
    }
    
    .progress-fill {
      height: 100%;
      background: #3498db;
      width: 0%;
      transition: width 0.3s;
    }
    
    .progress-text {
      text-align: center;
      margin-top: 8px;
      color: #909399;
      font-size: 13px;
    }
    
    /* 通知 */
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 12px 20px;
      border-radius: 6px;
      color: #fff;
      font-size: 14px;
      font-weight: 500;
      z-index: 1000;
      transform: translateX(400px);
      transition: transform 0.3s;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    .notification.show {
      transform: translateX(0);
    }
    
    .notification.success {
      background: #10b981;
    }
    
    .notification.error {
      background: #ef4444;
    }
    
    /* 空状态 */
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #909399;
    }
    
    .empty-icon {
      font-size: 64px;
      margin-bottom: 16px;
    }
    
    .empty-text {
      font-size: 14px;
      color: #606266;
    }
    
    /* 响应式 */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-240px);
        z-index: 1001;
      }
      
      .sidebar.collapsed {
        transform: translateX(-240px);
      }
      
      .sidebar.mobile-open {
        transform: translateX(0);
      }
      
      .sidebar-toggle-btn {
        left: 0;
        transform: translateY(-50%) rotate(180deg);
        z-index: 1002;
      }
      
      .sidebar-toggle-btn.mobile-open {
        left: 240px;
        transform: translateY(-50%) rotate(0deg);
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .sidebar.mobile-open ~ .main-content {
        margin-left: 0;
      }
      
      .files-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      }
      
      .file-card {
        padding: 12px;
      }
      
      .file-icon {
        font-size: 40px;
      }
      
      .file-name {
        font-size: 12px;
      }
      
      .file-description {
        font-size: 11px;
        -webkit-line-clamp: 1;
      }
      
      .file-meta {
        font-size: 10px;
      }
      
      .upload-area {
        padding: 30px 15px;
      }
      
      .upload-icon {
        font-size: 40px;
      }
      
      .upload-text {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>
  <button class="sidebar-toggle-btn" id="sidebarToggleBtn" onclick="toggleSidebar()">◀</button>

  <!-- 侧边栏 -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-title">
        <svg t="1770824389605" class="icon" viewBox="0 0 1408 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="5614" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="18" style="vertical-align: middle; margin-right: 8px;">
          <path d="M620.8 454.4h19.2c19.2 0 32-12.8 32-32s-12.8-32-32-32h-19.2c-44.8 0-76.8-25.6-76.8-57.6s32-57.6 76.8-57.6c12.8 0 19.2 0 32 6.4 19.2 6.4 38.4-6.4 44.8-25.6 0-19.2 25.6-32 51.2-32 32 0 51.2 19.2 51.2 38.4v6.4c-6.4 19.2 6.4 38.4 25.6 44.8 25.6 6.4 38.4 19.2 38.4 38.4s-25.6 38.4-51.2 38.4h-25.6c-19.2 0-32 12.8-32 32s12.8 32 32 32h25.6c64 0 115.2-44.8 115.2-102.4 0-38.4-25.6-76.8-64-89.6 0-57.6-57.6-102.4-115.2-102.4-44.8 0-83.2 19.2-102.4 57.6h-25.6c-76.8 0-140.8 51.2-140.8 121.6s64 115.2 140.8 115.2zM544 768h-320c-19.2 0-32 12.8-32 32s12.8 32 32 32h320c19.2 0 32-12.8 32-32s-12.8-32-32-32z" fill="#1296db" p-id="5615"></path>
          <path d="M1388.8 716.8v-19.2l-153.6-544C1216 64 1132.8 0 1056 0h-704C275.2 0 192 64 166.4 147.2L12.8 691.2v19.2c-6.4 32-12.8 57.6-12.8 89.6C0 921.6 102.4 1024 224 1024h960c121.6 0 224-102.4 224-224 0-32-6.4-57.6-19.2-83.2zM230.4 166.4C243.2 108.8 300.8 64 352 64h704c51.2 0 102.4 44.8 121.6 102.4l121.6 448c-32-25.6-70.4-38.4-115.2-38.4h-960c-44.8 0-83.2 12.8-115.2 32l121.6-441.6zM1184 960h-960C134.4 960 64 889.6 64 800c0-12.8 0-19.2 6.4-32v-6.4c12.8-70.4 76.8-121.6 153.6-121.6h960c76.8 0 140.8 51.2 153.6 128 0 12.8 6.4 19.2 6.4 32 0 89.6-70.4 160-160 160z" fill="#1296db" p-id="5616"></path>
          <path d="M1120 704c-51.2 0-96 44.8-96 96s44.8 96 96 96 96-44.8 96-96-44.8-96-96-96z m0 128c-19.2 0-32-12.8-32-32s12.8-32 32-32 32 12.8 32 32-12.8 32-32 32z" fill="#1296db" p-id="5617"></path>
        </svg>
        ZQ-Drive
      </div>
      <div class="sidebar-actions">
        <button class="sidebar-action-btn" onclick="showCreateFolderModal()" title="新建文件夹">➕</button>
        <button class="sidebar-action-btn" onclick="logout()" title="退出登录">🚪</button>
      </div>
    </div>
    <div class="sidebar-content" id="sidebarTree">
      <!-- 文件树将在这里动态生成 -->
    </div>
    <div class="sidebar-footer">
      <a href="https://github.com/bayueqi/ZQ-Drive" target="_blank" class="github-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-github"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
        GitHub
      </a>
    </div>
    <div class="sidebar-resizer" id="sidebarResizer"></div>
  </div>

  <!-- 主内容区 -->
  <div class="main-content">
    <!-- 批量操作区域 -->
    <div class="batch-actions" id="batchActions">
      <div class="batch-actions-content">
        <div class="batch-select-info">
          <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll()">
          <label for="selectAllCheckbox">全选</label>
          <span>已选择 <span id="selectedCount">0</span> 个文件</span>
        </div>
        <div class="batch-actions-buttons">
          <button class="btn secondary" onclick="clearSelection()">取消选择</button>
          <button class="btn danger" onclick="batchDeleteFiles()">批量删除</button>
        </div>
      </div>
    </div>
    
    <!-- 文件列表区 -->
    <div class="files-container">
      <!-- 上传区域 -->
      <div class="upload-area" id="uploadArea">
        <div class="upload-icon">☁️</div>
        <div class="upload-text">点击或拖拽文件到此处上传</div>
        <div class="upload-subtext">支持任意格式文件</div>
      </div>
      <input type="file" id="fileInput" multiple style="display: none;">
      
      <div class="upload-progress" id="uploadProgress">
        <div class="progress-bar">
          <div class="progress-fill" id="progressFill"></div>
        </div>
        <div class="progress-text" id="progressText">上传中... 0%</div>
      </div>

      <div class="files-grid" id="filesList"></div>
    </div>
  </div>

  <!-- 创建文件夹模态框 -->
  <div class="modal" id="createFolderModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>新建文件夹</h3>
        <button class="modal-close" onclick="closeCreateFolderModal()">×</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>文件夹名称</label>
          <input type="text" id="newFolderName" placeholder="请输入文件夹名称">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn secondary" onclick="closeCreateFolderModal()">取消</button>
        <button class="btn primary" onclick="confirmCreateFolder()">确定</button>
      </div>
    </div>
  </div>

  <!-- 上传选项模态框 -->
  <div class="modal" id="uploadModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>上传文件</h3>
        <button class="modal-close" onclick="closeUploadModal()">×</button>
      </div>
      <div class="modal-body">
        <div id="singleFileUpload">
          <div class="form-group">
            <label>文件名</label>
            <input type="text" id="modalFileName">
          </div>
          <div class="form-group">
            <label>描述</label>
            <textarea id="modalDescription" placeholder="文件描述（可选）"></textarea>
          </div>
        </div>
        <div id="multipleFileUpload" class="hidden">
          <div class="form-group">
            <label>文件列表</label>
            <div id="fileInputsContainer"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn secondary" onclick="closeUploadModal()">取消</button>
        <button class="btn primary" onclick="confirmUpload()">开始上传</button>
      </div>
    </div>
  </div>

  <!-- 编辑模态框 -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>编辑文件</h3>
        <button class="modal-close" onclick="closeEditModal()">×</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editFileId">
        <div class="form-group">
          <label>文件名</label>
          <input type="text" id="editFileName">
        </div>
        <div class="form-group">
          <label>描述</label>
          <textarea id="editDescription" placeholder="文件描述（可选）"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn secondary" onclick="closeEditModal()">取消</button>
        <button class="btn primary" onclick="saveEdit()">保存</button>
      </div>
    </div>
  </div>

  <div class="notification" id="notification"></div>

  <script>
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    const filesList = document.getElementById('filesList');
    const notification = document.getElementById('notification');
    const uploadModal = document.getElementById('uploadModal');
    const editModal = document.getElementById('editModal');
    const modalFileName = document.getElementById('modalFileName');
    const modalDescription = document.getElementById('modalDescription');
    const editFileId = document.getElementById('editFileId');
    const editFileName = document.getElementById('editFileName');
    const editDescription = document.getElementById('editDescription');
    const createFolderModal = document.getElementById('createFolderModal');
    const newFolderName = document.getElementById('newFolderName');
    
    let currentFile = null;
    let currentFiles = [];
    let currentFolderId = 0;
    let allFolders = [];
    let allFiles = [];
    let sidebarExpanded = true;
    let expandedFolders = new Set();
    let isResizing = false;
    let currentWidth = 240;
    let selectedFiles = new Set();

    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const toggleBtn = document.getElementById('sidebarToggleBtn');
      sidebarExpanded = !sidebarExpanded;
      
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('mobile-open');
        toggleBtn.classList.toggle('mobile-open');
      } else {
        sidebar.classList.toggle('collapsed');
        toggleBtn.classList.toggle('collapsed');
        
        if (sidebarExpanded) {
          // 展开时使用当前宽度
          sidebar.style.width = currentWidth + 'px';
          toggleBtn.style.left = currentWidth + 'px';
          const mainContent = document.querySelector('.main-content');
          if (mainContent) {
            mainContent.style.marginLeft = currentWidth + 'px';
          }
        } else {
          // 收起时隐藏侧边栏
          sidebar.style.width = '0';
          toggleBtn.style.left = '0';
          const mainContent = document.querySelector('.main-content');
          if (mainContent) {
            mainContent.style.marginLeft = '0';
          }
        }
      }
    }

    function logout() {
      if (confirm('确定要退出登录吗？')) {
        window.location.href = 'logout.php';
      }
    }

    async function loadFileTree() {
      try {
        const response = await fetch('api.php?action=tree');
        const data = await response.json();
        allFolders = data.folders || [];
        allFiles = data.files || [];
        renderFileTree();
      } catch (error) {
        console.error('加载文件树失败:', error);
      }
    }

    function renderFileTree() {
      const sidebarTree = document.getElementById('sidebarTree');
      const rootFolders = allFolders.filter(f => !f.parent_id);
      const rootFiles = allFiles.filter(f => f.folder_id === 0 || f.folder_id === null || f.folder_id === '');

      
      // 创建虚拟根目录
      const rootFolder = {
        id: 0,
        name: '根目录',
        parent_id: null
      };
      
      let html = '';
      // 渲染根目录
      const hasRootChildren = rootFolders.length > 0 || rootFiles.length > 0;
      const isRootExpanded = expandedFolders.has(0);
      
      html += `
        <div class="tree-item tree-item-folder" data-folder-id="0">
          <div class="tree-item-content ${currentFolderId === 0 ? 'active' : ''}" onclick="handleFolderClick(0, event)">
            <span class="tree-item-toggle ${hasRootChildren ? '' : 'hidden'}" onclick="toggleFolderTree(this, event)">▶</span>
            <span class="tree-item-icon">📁</span>
            <span class="tree-item-name">根目录</span>
          </div>
          <div class="tree-children ${isRootExpanded ? 'expanded' : ''}">
      `;
      
      // 渲染根目录下的文件
      rootFiles.forEach(file => {
        const icon = getFileIcon(file.filetype, file.filename);
        html += `
          <div class="tree-item tree-item-file" data-file-id="${file.id}">
            <div class="tree-item-content" onclick="handleFileClick(${file.id}, 0)">
              <span class="tree-item-toggle hidden"></span>
              <span class="tree-item-icon">${icon}</span>
              <span class="tree-item-name">${file.filename}</span>
            </div>
          </div>
        `;
      });
      
      // 渲染根目录下的文件夹
      rootFolders.forEach(folder => {
        html += renderFolderTree(folder, allFolders, allFiles);
      });
      
      html += `
          </div>
        </div>
      `;
      
      sidebarTree.innerHTML = html;
    }

    function renderFolderTree(folder, allFolders, allFiles, level = 0) {
      const childrenFolders = allFolders.filter(f => f.parent_id === folder.id);
      const childrenFiles = allFiles.filter(f => f.folder_id === folder.id);
      const hasChildren = childrenFolders.length > 0 || childrenFiles.length > 0;
      const isExpanded = expandedFolders.has(folder.id);
      
      let html = `
        <div class="tree-item tree-item-folder" data-folder-id="${folder.id}">
          <div class="tree-item-content ${currentFolderId === folder.id ? 'active' : ''}" onclick="handleFolderClick(${folder.id}, event)">
            <span class="tree-item-toggle ${hasChildren ? '' : 'hidden'}" onclick="toggleFolderTree(this, event)">▶</span>
            <span class="tree-item-icon">📁</span>
            <span class="tree-item-name">${folder.name}</span>
            <span class="tree-item-actions">
              <button class="tree-item-action-btn" onclick="event.stopPropagation(); renameFolder(${folder.id})" title="重命名文件夹">✏️</button>
              <button class="tree-item-action-btn" onclick="event.stopPropagation(); deleteFolder(${folder.id})" title="删除文件夹">🗑️</button>
            </span>
          </div>
          <div class="tree-children ${isExpanded ? 'expanded' : ''}">
      `;
      
      childrenFolders.forEach(childFolder => {
        html += renderFolderTree(childFolder, allFolders, allFiles, level + 1);
      });
      
      childrenFiles.forEach(file => {
        const icon = getFileIcon(file.filetype, file.filename);
        html += `
          <div class="tree-item tree-item-file" data-file-id="${file.id}">
            <div class="tree-item-content" onclick="handleFileClick(${file.id}, ${file.folder_id})">
              <span class="tree-item-toggle hidden"></span>
              <span class="tree-item-icon">${icon}</span>
              <span class="tree-item-name">${file.filename}</span>
            </div>
          </div>
        `;
      });
      
      html += `
          </div>
        </div>
      `;
      
      return html;
    }

    function toggleFolderTree(element, event) {
      event.stopPropagation();
      const treeItem = element.closest('.tree-item');
      const children = treeItem.querySelector('.tree-children');
      const folderId = treeItem.dataset.folderId;
      
      element.classList.toggle('expanded');
      children.classList.toggle('expanded');
      
      if (element.classList.contains('expanded')) {
        expandedFolders.add(parseInt(folderId));
      } else {
        expandedFolders.delete(parseInt(folderId));
      }
    }

    function handleFolderClick(folderId, event) {
      event.stopPropagation();
      const treeItem = event.target.closest('.tree-item');
      const children = treeItem.querySelector('.tree-children');
      const toggle = treeItem.querySelector('.tree-item-toggle');
      
      if (toggle && !toggle.classList.contains('hidden')) {
        if (!toggle.classList.contains('expanded')) {
          toggle.classList.add('expanded');
          children.classList.add('expanded');
          expandedFolders.add(folderId);
        }
      }
      
      navigateToFolder(folderId);
      updateActiveFolder(folderId);
    }

    function handleFileClick(fileId, folderId) {
      navigateToFolder(folderId);
      updateActiveFolder(folderId);
    }

    function updateActiveFolder(folderId) {
      document.querySelectorAll('.tree-item-content').forEach(el => {
        el.classList.remove('active');
      });
      
      const activeItem = document.querySelector(`.tree-item[data-folder-id="${folderId}"] .tree-item-content`);
      if (activeItem) {
        activeItem.classList.add('active');
      }
    }

    uploadArea.addEventListener('click', () => fileInput.click());
    
    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
      uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadArea.classList.remove('dragover');
      const files = Array.from(e.dataTransfer.files);
      if (files.length > 0) {
        handleFileUpload(files);
      }
    });
    
    fileInput.addEventListener('change', (e) => {
      if (e.target.files.length > 0) {
        handleFileUpload(Array.from(e.target.files));
      }
    });

    function handleFileUpload(files) {
      currentFiles = files;
      if (files.length === 1) {
        document.getElementById('singleFileUpload').classList.remove('hidden');
        document.getElementById('multipleFileUpload').classList.add('hidden');
        modalFileName.value = files[0].name;
        modalDescription.value = '';
      } else {
        document.getElementById('singleFileUpload').classList.add('hidden');
        document.getElementById('multipleFileUpload').classList.remove('hidden');
        const container = document.getElementById('fileInputsContainer');
        container.innerHTML = '';
        files.forEach((file, index) => {
          const fileInputGroup = document.createElement('div');
          fileInputGroup.className = 'file-input-group';
          fileInputGroup.innerHTML = `
            <div class="file-input-row">
              <div class="form-group file-name-input">
                <label>文件名 ${index + 1}</label>
                <input type="text" class="file-name-input" value="${file.name}" data-index="${index}">
              </div>
              <div class="form-group file-desc-input">
                <label>描述 ${index + 1}</label>
                <textarea class="file-desc-input" placeholder="文件描述（可选）" data-index="${index}"></textarea>
              </div>
            </div>
          `;
          container.appendChild(fileInputGroup);
        });
      }
      uploadModal.classList.add('show');
    }

    function closeUploadModal() {
      uploadModal.classList.remove('show');
      currentFile = null;
    }

    function confirmUpload() {
      if (!currentFiles || currentFiles.length === 0) return;
      
      uploadModal.classList.remove('show');
      uploadMultipleFiles(currentFiles);
      currentFiles = [];
    }

    async function uploadMultipleFiles(files) {
      let uploadedCount = 0;
      let failedCount = 0;
      
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        try {
          await uploadSingleFile(file, i);
          uploadedCount++;
        } catch (error) {
          failedCount++;
        }
      }
      
      uploadProgress.style.display = 'none';
      
      if (failedCount === 0) {
        showNotification(`成功上传 ${uploadedCount} 个文件`, 'success');
      } else {
        showNotification(`成功上传 ${uploadedCount} 个文件，失败 ${failedCount} 个`, 'error');
      }
      
      loadFiles();
    }

    function uploadSingleFile(file, index) {
      return new Promise((resolve, reject) => {
        let filename = file.name;
        let description = '';
        
        if (currentFiles.length === 1) {
          filename = modalFileName.value;
          description = modalDescription.value;
        } else {
          const nameInput = document.querySelector(`.file-name-input[data-index="${index}"]`);
          const descInput = document.querySelector(`.file-desc-input[data-index="${index}"]`);
          if (nameInput) filename = nameInput.value;
          if (descInput) description = descInput.value;
        }
        
        const uploadOptions = {
          filename: filename,
          description: description,
          fileSize: file.size,
          fileType: file.type
        };
        
        uploadFile(file, uploadOptions, resolve, reject);
      });
    }

    function calculateFileHash(file) {
      return new Promise((resolve) => {
        const hash = btoa(encodeURIComponent(file.name + '-' + file.size + '-' + Date.now()))
          .replace(/[^a-zA-Z0-9]/g, '')
          .substring(0, 16) + '_' + Date.now();
        resolve(hash);
      });
    }

    function uploadFile(file, options, resolve, reject) {
      uploadProgress.style.display = 'block';
      progressFill.style.width = '0%';
      progressText.textContent = `上传中... ${file.name}`;
      
      const chunkSize = 10 * 1024 * 1024;
      const totalChunks = Math.ceil(file.size / chunkSize);
      let currentChunk = 0;
      
      calculateFileHash(file).then(hash => {
        uploadChunk(file, hash, chunkSize, totalChunks, currentChunk, options, resolve, reject);
      });
    }

    function uploadChunk(file, hash, chunkSize, totalChunks, currentChunk, options, resolve, reject) {
      const start = currentChunk * chunkSize;
      const end = Math.min(file.size, start + chunkSize);
      const chunk = file.slice(start, end);
      
      const formData = new FormData();
      formData.append('chunk', chunk);
      formData.append('chunk', currentChunk);
      formData.append('totalChunks', totalChunks);
      formData.append('fileHash', hash);
      formData.append('fileName', options.filename);
      
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload.php', true);
      
      xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
          const totalProgress = Math.round(((currentChunk / totalChunks) + (e.loaded / e.total / totalChunks)) * 100);
          progressFill.style.width = totalProgress + '%';
          progressText.textContent = `上传中... ${file.name} ${totalProgress}%`;
        }
      };
      
      xhr.onload = () => {
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
              if (currentChunk < totalChunks - 1) {
                uploadChunk(file, hash, chunkSize, totalChunks, currentChunk + 1, options, resolve, reject);
              } else {
                mergeChunks(hash, options, resolve, reject);
              }
            } else {
              reject(new Error(response.message));
            }
          } catch (e) {
            reject(e);
          }
        } else {
          reject(new Error('上传失败'));
        }
      };
      
      xhr.onerror = () => {
        reject(new Error('上传失败'));
      };
      
      xhr.send(formData);
    }

    function mergeChunks(hash, options, resolve, reject) {
      progressText.textContent = `合并中... ${options.filename}`;
      
      const formData = new FormData();
      formData.append('action', 'merge');
      formData.append('fileHash', hash);
      formData.append('fileName', options.filename);
      formData.append('fileSize', options.fileSize);
      formData.append('fileType', options.fileType);
      formData.append('description', options.description || '');
      formData.append('folder_id', currentFolderId);
      
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload.php', true);
      
      xhr.onload = () => {
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
              resolve();
            } else {
              reject(new Error(response.message));
            }
          } catch (e) {
            reject(e);
          }
        } else {
          reject(new Error('合并失败'));
        }
      };
      
      xhr.onerror = () => {
        reject(new Error('合并失败'));
      };
      
      xhr.send(formData);
    }

    function showNotification(message, type) {
      notification.textContent = message;
      notification.className = 'notification ' + type;
      notification.classList.add('show');
      setTimeout(() => {
        notification.classList.remove('show');
      }, 3000);
    }

    async function loadFiles() {
      try {
        const timestamp = new Date().getTime();
        const url = currentFolderId > 0 ? `api.php?folder_id=${currentFolderId}&t=${timestamp}` : `api.php?t=${timestamp}`;
        const response = await fetch(url);
        const data = await response.json();
        renderFiles(data.files, data.folders);
        await loadFileTree();
        // 加载完成后更新选择UI
        setTimeout(() => {
          updateSelectionUI();
        }, 100);
      } catch (error) {
        showNotification('加载文件列表失败', 'error');
      }
    }

    function renderBreadcrumb() {
      // 面包屑导航功能（可根据需要实现）
    }

    pageLoad();

    async function pageLoad() {
      const toggleBtn = document.getElementById('sidebarToggleBtn');
      if (!sidebarExpanded) {
        toggleBtn.classList.add('collapsed');
      }
      await loadFiles();
      initSidebarResizer();
    }

    function initSidebarResizer() {
      const sidebar = document.getElementById('sidebar');
      const resizer = document.getElementById('sidebarResizer');
      const toggleBtn = document.getElementById('sidebarToggleBtn');
      
      if (!resizer) return;
      
      resizer.addEventListener('mousedown', startResizing);
      
      function startResizing(e) {
        isResizing = true;
        currentWidth = sidebar.offsetWidth;
        resizer.classList.add('dragging');
        document.body.style.cursor = 'col-resize';
        
        document.addEventListener('mousemove', resizeSidebar);
        document.addEventListener('mouseup', stopResizing);
      }
      
      function resizeSidebar(e) {
        if (!isResizing) return;
        
        const newWidth = e.clientX;
        if (newWidth >= 150 && newWidth <= 500) {
          currentWidth = newWidth;
          sidebar.style.width = currentWidth + 'px';
          toggleBtn.style.left = currentWidth + 'px';
          
          const mainContent = document.querySelector('.main-content');
          if (mainContent) {
            mainContent.style.marginLeft = currentWidth + 'px';
          }
        }
      }
      
      function stopResizing() {
        isResizing = false;
        resizer.classList.remove('dragging');
        document.body.style.cursor = '';
        document.removeEventListener('mousemove', resizeSidebar);
        document.removeEventListener('mouseup', stopResizing);
      }
    }

    function renderFiles(files, folders) {
      let html = '';
      
      if (folders && folders.length > 0) {
        html += folders.map(folder => `
          <div class="file-card" onclick="navigateToFolder(${folder.id})">
            <div class="file-icon">📁</div>
            <div class="file-name">${folder.name}</div>
            <div class="file-meta">${formatDate(folder.created_at)}</div>
          </div>
        `).join('');
      }
      
      if (files && files.length > 0) {
        html += files.map(file => {
          const icon = getFileIcon(file.filetype, file.filename);
          return `
          <div class="file-card">
            <input type="checkbox" class="file-checkbox" onclick="event.stopPropagation(); toggleFileSelection(${file.id})" data-file-id="${file.id}">
            <div class="file-actions">
              <button class="file-action-btn" onclick="event.stopPropagation(); previewFile(${file.id})" title="预览">👁️</button>
              <button class="file-action-btn" onclick="event.stopPropagation(); downloadFile(${file.id})" title="下载">⬇️</button>
              <button class="file-action-btn" onclick="event.stopPropagation(); showEditModal(${file.id})" title="编辑">✏️</button>
              <button class="file-action-btn" onclick="event.stopPropagation(); deleteFile(${file.id})" title="删除">🗑️</button>
            </div>
            <div class="file-icon">${icon}</div>
            <div class="file-name">${file.filename}</div>
            ${file.description ? `<div class="file-description">${file.description}</div>` : ''}
            <div class="file-meta">${formatSize(file.filesize)} · ${formatDate(file.upload_time)}</div>
          </div>
        `}).join('');
      }
      
      if (html === '') {
        html = `
          <div class="empty-state">
            <div class="empty-icon">📭</div>
            <div class="empty-text">暂无文件</div>
          </div>
        `;
      }
      
      filesList.innerHTML = html;
    }

    function getFileIcon(filetype, filename) {
      const ext = filename.split('.').pop().toLowerCase();
      
      if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'].includes(ext)) {
        return '🖼️';
      } else if (['mp4', 'avi', 'mov', 'wmv', 'mkv', 'flv', 'webm', 'm4v'].includes(ext)) {
        return '🎬';
      } else if (['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma'].includes(ext)) {
        return '🎵';
      } else if (['pdf'].includes(ext)) {
        return '📕';
      } else if (['doc', 'docx'].includes(ext)) {
        return '📘';
      } else if (['xls', 'xlsx', 'csv'].includes(ext)) {
        return '📗';
      } else if (['ppt', 'pptx'].includes(ext)) {
        return '📙';
      } else if (['txt', 'md', 'log', 'ini', 'cfg', 'json', 'xml', 'yaml', 'yml'].includes(ext)) {
        return '📝';
      } else if (['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz'].includes(ext)) {
        return '📦';
      } else if (['html', 'htm', 'css', 'js', 'ts', 'jsx', 'tsx', 'vue', 'php', 'py', 'java', 'c', 'cpp', 'h', 'cs', 'go', 'rs', 'swift', 'kt', 'rb'].includes(ext)) {
        return '💻';
      } else if (['exe', 'msi', 'app', 'dmg', 'deb', 'rpm'].includes(ext)) {
        return '⚙️';
      } else {
        return '📄';
      }
    }

    function formatSize(bytes) {
      if (bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    function formatDate(dateStr) {
      const date = new Date(dateStr);
      const now = new Date();
      const diff = now - date;
      
      if (diff < 60000) {
        return '刚刚';
      } else if (diff < 3600000) {
        return Math.floor(diff / 60000) + '分钟前';
      } else if (diff < 86400000) {
        return Math.floor(diff / 3600000) + '小时前';
      } else if (diff < 2592000000) {
        return Math.floor(diff / 86400000) + '天前';
      } else {
        return date.toLocaleDateString('zh-CN');
      }
    }

    function navigateToFolder(folderId) {
      if (folderId === currentFolderId) return;
      
      currentFolderId = folderId;
      loadFiles();
    }

    function showCreateFolderModal() {
      newFolderName.value = '';
      createFolderModal.classList.add('show');
    }

    function closeCreateFolderModal() {
      createFolderModal.classList.remove('show');
    }

    function confirmCreateFolder() {
      const name = newFolderName.value.trim();
      if (!name) {
        showNotification('请输入文件夹名称', 'error');
        return;
      }
      
      fetch('folder.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=create&name=${encodeURIComponent(name)}&parent_id=${currentFolderId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('文件夹创建成功', 'success');
          closeCreateFolderModal();
          loadFiles();
        } else {
          showNotification('创建失败：' + data.message, 'error');
        }
      });
    }

    async function deleteFolder(id) {
      if (!confirm('确定要删除这个文件夹吗？')) return;
      
      fetch('folder.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=delete&id=${id}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('文件夹删除成功', 'success');
          loadFiles();
        } else {
          showNotification('删除失败：' + data.message, 'error');
        }
      });
    }

    function renameFolder(id) {
      const newName = prompt('请输入新的文件夹名称：');
      if (!newName || newName.trim() === '') return;
      
      fetch('folder.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=rename&id=${id}&name=${encodeURIComponent(newName)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('文件夹重命名成功', 'success');
          loadFiles();
        } else {
          showNotification('重命名失败：' + data.message, 'error');
        }
      });
    }

    function downloadFile(id) {
      const link = document.createElement('a');
      link.href = 'download.php?id=' + id;
      link.target = '_blank';
      link.style.display = 'none';
      document.body.appendChild(link);
      link.click();
      setTimeout(() => {
        document.body.removeChild(link);
      }, 100);
    }

    function previewFile(id) {
      window.open('preview.php?id=' + id, '_blank');
    }

    function showEditModal(fileId) {
      fetch('edit.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=get&id=${fileId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const file = data.file;
          editFileId.value = file.id;
          editFileName.value = file.filename;
          editDescription.value = file.description || '';
          editModal.classList.add('show');
        } else {
          showNotification('获取文件信息失败', 'error');
        }
      });
    }

    function closeEditModal() {
      editModal.classList.remove('show');
    }

    function saveEdit() {
      const id = editFileId.value;
      const filename = editFileName.value;
      const description = editDescription.value;
      
      fetch('edit.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update&id=${id}&filename=${encodeURIComponent(filename)}&description=${encodeURIComponent(description)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('文件信息更新成功', 'success');
          closeEditModal();
          loadFiles();
        } else {
          showNotification('更新失败：' + data.message, 'error');
        }
      });
    }

    function deleteFile(id) {
      if (!confirm('确定要删除这个文件吗？')) return;
      
      fetch('delete.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${id}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('文件删除成功', 'success');
          loadFiles();
        } else {
          showNotification('删除失败：' + data.message, 'error');
        }
      });
    }

    function toggleFileSelection(fileId) {
      const fileCard = document.querySelector(`.file-checkbox[data-file-id="${fileId}"]`).closest('.file-card');
      const checkbox = document.querySelector(`.file-checkbox[data-file-id="${fileId}"]`);
      if (selectedFiles.has(fileId)) {
        selectedFiles.delete(fileId);
        fileCard.classList.remove('selected');
        if (checkbox) checkbox.checked = false;
      } else {
        selectedFiles.add(fileId);
        fileCard.classList.add('selected');
        if (checkbox) checkbox.checked = true;
      }
      updateSelectionUI();
    }

    function updateSelectionUI() {
      const selectedCount = selectedFiles.size;
      document.getElementById('selectedCount').textContent = selectedCount;
      const batchActions = document.getElementById('batchActions');
      const selectAllCheckbox = document.getElementById('selectAllCheckbox');
      const allFileCheckboxes = document.querySelectorAll('.file-checkbox');
      
      // 只有在有选中文件时，才显示批量操作区域
      if (selectedCount > 0) {
        batchActions.classList.add('show');
      } else {
        batchActions.classList.remove('show');
      }
      
      // 更新全选复选框状态
      if (selectAllCheckbox) {
        const totalFiles = allFileCheckboxes.length;
        selectAllCheckbox.checked = totalFiles > 0 && selectedCount === totalFiles;
      }
    }

    function clearSelection() {
      selectedFiles.forEach(fileId => {
        const fileCard = document.querySelector(`.file-checkbox[data-file-id="${fileId}"]`).closest('.file-card');
        const checkbox = document.querySelector(`.file-checkbox[data-file-id="${fileId}"]`);
        if (fileCard) {
          fileCard.classList.remove('selected');
        }
        if (checkbox) {
          checkbox.checked = false;
        }
      });
      // 同时取消全选复选框的勾选状态
      const selectAllCheckbox = document.getElementById('selectAllCheckbox');
      if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
      }
      selectedFiles.clear();
      updateSelectionUI();
    }

    function toggleSelectAll() {
      const selectAllCheckbox = document.getElementById('selectAllCheckbox');
      const allFileCheckboxes = document.querySelectorAll('.file-checkbox');
      
      if (selectAllCheckbox.checked) {
        // 全选
        allFileCheckboxes.forEach(checkbox => {
          const fileId = parseInt(checkbox.dataset.fileId);
          if (fileId) {
            selectedFiles.add(fileId);
            checkbox.checked = true;
            checkbox.closest('.file-card').classList.add('selected');
          }
        });
      } else {
        // 取消全选
        allFileCheckboxes.forEach(checkbox => {
          const fileId = parseInt(checkbox.dataset.fileId);
          if (fileId) {
            selectedFiles.delete(fileId);
            checkbox.checked = false;
            checkbox.closest('.file-card').classList.remove('selected');
          }
        });
        selectedFiles.clear();
      }
      
      updateSelectionUI();
    }

    function batchDeleteFiles() {
      if (selectedFiles.size === 0) return;
      
      if (!confirm(`确定要删除这 ${selectedFiles.size} 个文件吗？`)) return;
      
      const fileIds = Array.from(selectedFiles);
      
      fetch('delete.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `ids=${fileIds.join(',')}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(`成功删除 ${fileIds.length} 个文件`, 'success');
          selectedFiles.clear();
          updateSelectionUI();
          loadFiles();
        } else {
          showNotification('删除失败：' + data.message, 'error');
        }
      });
    }

    loadFiles();
  </script>
</body>
</html>