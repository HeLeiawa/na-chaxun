<?php
session_start();

// 简单的后台登录验证
$adminUsername = 'admin';
$adminPassword = '197350';

$brandsFile = 'brands.json';
$noticesFile = 'notices.json';
$statsFile = 'stats.json';
$friendsFile = 'friends.json';
$ordersFile = 'orders.json';

// 读取统计数据
$stats = ['visits' => 0];
if (file_exists($statsFile)) {
    $stats = json_decode(file_get_contents($statsFile), true) ?: ['visits' => 0];
}

// 登出
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['admin_logged_in']);
    header('Location: admin.php');
    exit;
}

// 处理登录
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $loginError = '用户名或密码错误！';
    }
}

// 处理添加品牌
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_brand']) && isset($_SESSION['admin_logged_in'])) {
    $brandName = $_POST['brand_name'];
    $brandWebsite = $_POST['brand_website'];
    $brandIntro = $_POST['brand_intro'];
    $brandStatus = $_POST['brand_status'];
    $hasLicense = $_POST['has_license'];
    $contact = $_POST['contact'];
    $licenseFile = isset($_FILES['license_file']) ? $_FILES['license_file'] : '';
    $logoFile = isset($_FILES['logo_file']) ? $_FILES['logo_file'] : '';

    if (!empty($brandName) && !empty($brandWebsite)) {
        $year = date('Y');
        $generatedCode = 'NY' . $year . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // 检查识别码是否已存在，如果存在则重新生成
        $codeExists = true;
        while ($codeExists) {
            $codeExists = false;
            foreach ($brands as $brand) {
                if ($brand['code'] === $generatedCode) {
                    $codeExists = true;
                    break;
                }
            }
            if ($codeExists) {
                $generatedCode = 'NY' . $year . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            }
        }

        $licensePath = '';
        if ($hasLicense == 'yes' && !empty($licenseFile['name'])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExt = pathinfo($licenseFile['name'], PATHINFO_EXTENSION);
            $fileName = $generatedCode . '_license.' . $fileExt;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($licenseFile['tmp_name'], $targetPath)) {
                $licensePath = $targetPath;
            }
        }

        $logoPath = '';
        if (!empty($logoFile['name'])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExt = pathinfo($logoFile['name'], PATHINFO_EXTENSION);
            $fileName = $generatedCode . '_logo.' . $fileExt;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($logoFile['tmp_name'], $targetPath)) {
                $logoPath = $targetPath;
            }
        }

        $brands = [];
        if (file_exists($brandsFile)) {
            $brands = json_decode(file_get_contents($brandsFile), true) ?: [];
        }

        $newBrand = [
            'code' => $generatedCode,
            'name' => $brandName,
            'website' => $brandWebsite,
            'intro' => $brandIntro,
            'status' => $brandStatus,
            'has_license' => $hasLicense,
            'license_file' => $licensePath,
            'logo' => $logoPath,
            'contact' => $contact,
            'date' => date('Y-m-d H:i:s')
        ];

        $brands[] = $newBrand;
        file_put_contents($brandsFile, json_encode($brands, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $addSuccess = '品牌添加成功！';
    } else {
        $addError = '请填写完整的品牌信息！';
    }
}

// 处理修改品牌信息
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_brand']) && isset($_SESSION['admin_logged_in'])) {
    $brandCode = $_POST['brand_code'];
    $brandName = $_POST['brand_name'];
    $brandWebsite = $_POST['brand_website'];
    $brandIntro = $_POST['brand_intro'];
    $brandStatus = $_POST['brand_status'];
    $hasLicense = $_POST['has_license'];
    $contact = $_POST['contact'];
    $licenseFile = isset($_FILES['license_file']) ? $_FILES['license_file'] : '';
    $logoFile = isset($_FILES['logo_file']) ? $_FILES['logo_file'] : '';

    if (file_exists($brandsFile)) {
        $brands = json_decode(file_get_contents($brandsFile), true) ?: [];

        foreach ($brands as &$brand) {
            if ($brand['code'] === $brandCode) {
                $brand['name'] = $brandName;
                $brand['website'] = $brandWebsite;
                $brand['intro'] = $brandIntro;
                $brand['status'] = $brandStatus;
                $brand['has_license'] = $hasLicense;
                $brand['contact'] = $contact;

                // 如果上传了新营业执照文件
                if (!empty($licenseFile['name'])) {
                    $uploadDir = 'uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileExt = pathinfo($licenseFile['name'], PATHINFO_EXTENSION);
                    $fileName = $brandCode . '_license.' . $fileExt;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($licenseFile['tmp_name'], $targetPath)) {
                        $brand['license_file'] = $targetPath;
                    }
                }

                // 如果上传了新logo文件
                if (!empty($logoFile['name'])) {
                    $uploadDir = 'uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileExt = pathinfo($logoFile['name'], PATHINFO_EXTENSION);
                    $fileName = $brandCode . '_logo.' . $fileExt;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($logoFile['tmp_name'], $targetPath)) {
                        $brand['logo'] = $targetPath;
                    }
                }

                break;
            }
        }

        file_put_contents($brandsFile, json_encode($brands, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $updateSuccess = '品牌信息更新成功！';
    }
}

// 处理删除品牌
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_brand']) && isset($_SESSION['admin_logged_in'])) {
    $brandCode = $_POST['brand_code'];

    if (file_exists($brandsFile)) {
        $brands = json_decode(file_get_contents($brandsFile), true) ?: [];
        
        $brands = array_filter($brands, function($brand) use ($brandCode) {
            return $brand['code'] !== $brandCode;
        });

        file_put_contents($brandsFile, json_encode(array_values($brands), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $deleteSuccess = '品牌删除成功！';
    }
}

// 处理发布公告
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_notice']) && isset($_SESSION['admin_logged_in'])) {
    $noticeTitle = $_POST['notice_title'];
    $noticeTag = $_POST['notice_tag'];
    $noticeContent = $_POST['notice_content'];

    if (!empty($noticeTitle) && !empty($noticeContent)) {
        $notices = [];
        if (file_exists($noticesFile)) {
            $notices = json_decode(file_get_contents($noticesFile), true) ?: [];
        }

        $maxId = 0;
        foreach ($notices as $notice) {
            if ($notice['id'] > $maxId) {
                $maxId = $notice['id'];
            }
        }

        $newNotice = [
            'id' => $maxId + 1,
            'title' => $noticeTitle,
            'tag' => $noticeTag,
            'date' => date('Y-m-d'),
            'content' => $noticeContent
        ];

        array_unshift($notices, $newNotice);
        file_put_contents($noticesFile, json_encode($notices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $addNoticeSuccess = '公告发布成功！';
    } else {
        $addNoticeError = '请填写完整的公告信息！';
    }
}


// 处理删除公告
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_notice']) && isset($_SESSION['admin_logged_in'])) {
    $noticeId = (int) $_POST['notice_id'];

    if (file_exists($noticesFile)) {
        $notices = json_decode(file_get_contents($noticesFile), true) ?: [];

        $notices = array_filter($notices, function($notice) use ($noticeId) {
            return $notice['id'] !== $noticeId;
        });

        file_put_contents($noticesFile, json_encode(array_values($notices), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $deleteNoticeSuccess = '公告删除成功！';
    }
}

// 处理添加友链
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_friend']) && isset($_SESSION['admin_logged_in'])) {
    $friendName = $_POST['friend_name'];
    $friendDesc = $_POST['friend_desc'];
    $friendLogo = $_POST['friend_logo'];
    $friendUrl = $_POST['friend_url'];

    if (!empty($friendName) && !empty($friendUrl)) {
        $friends = [];
        if (file_exists($friendsFile)) {
            $friends = json_decode(file_get_contents($friendsFile), true) ?: [];
        }

        $maxId = 0;
        foreach ($friends as $friend) {
            if ($friend['id'] > $maxId) {
                $maxId = $friend['id'];
            }
        }

        $newFriend = [
            'id' => $maxId + 1,
            'name' => $friendName,
            'description' => $friendDesc,
            'logo' => $friendLogo,
            'url' => $friendUrl
        ];

        $friends[] = $newFriend;
        file_put_contents($friendsFile, json_encode($friends, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $addFriendSuccess = '友链添加成功！';
    } else {
        $addFriendError = '请填写完整的友链信息！';
    }
}

// 处理删除友链
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_friend']) && isset($_SESSION['admin_logged_in'])) {
    $friendId = $_POST['friend_id'];

    if (file_exists($friendsFile)) {
        $friends = json_decode(file_get_contents($friendsFile), true) ?: [];

        $friends = array_filter($friends, function($friend) use ($friendId) {
            return $friend['id'] !== $friendId;
        });

        file_put_contents($friendsFile, json_encode(array_values($friends), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $deleteFriendSuccess = '友链删除成功！';
    }
}

// 处理更新订单状态
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_status']) && isset($_SESSION['admin_logged_in'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];

    if (file_exists($ordersFile)) {
        $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
        
        $updated = false;
        foreach ($orders as &$order) {
            if ($order['order_id'] === $orderId) {
                $order['status'] = $newStatus;
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $updateOrderSuccess = '订单状态更新成功！';
        }
    }
}

// 处理删除订单
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order']) && isset($_SESSION['admin_logged_in'])) {
    $orderId = $_POST['order_id'];

    if (file_exists($ordersFile)) {
        $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
        
        $filteredOrders = array_filter($orders, function($order) use ($orderId) {
            return $order['order_id'] !== $orderId;
        });
        
        if (count($filteredOrders) !== count($orders)) {
            file_put_contents($ordersFile, json_encode(array_values($filteredOrders), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $deleteOrderSuccess = '订单删除成功！';
        }
    }
}

// 读取品牌数据
$brands = [];
if (file_exists($brandsFile)) {
    $brands = json_decode(file_get_contents($brandsFile), true) ?: [];
}

// 读取公告数据
$notices = [];
if (file_exists($noticesFile)) {
    $notices = json_decode(file_get_contents($noticesFile), true) ?: [];
}

// 读取友链数据
$friends = [];
if (file_exists($friendsFile)) {
    $friends = json_decode(file_get_contents($friendsFile), true) ?: [];
}

// 读取订单数据
$orders = [];
if (file_exists($ordersFile)) {
    $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 钠云查询</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            min-height: 100vh;
            background-image: url('https://t.alcy.cc/ycy/');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            padding: 20px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 30px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 1.5em;
        }

        .header-links a {
            color: #333;
            text-decoration: none;
            margin-left: 20px;
            font-size: 14px;
        }

        .header-links a:hover {
            text-decoration: underline;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            max-width: 400px;
            margin: 100px auto;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .login-box h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            border: 2px solid #e8e8e8;
            outline: none;
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #333;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(51, 51, 51, 0.1);
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            font-size: 15px;
            color: #fff;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            border: none;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 14px 18px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 10px;
            font-weight: 500;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 14px 18px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 10px;
            font-weight: 500;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .main-content {
            display: flex;
            gap: 20px;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            flex: 0 0 350px;
        }

        .stats-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .stats-box h2 {
            color: #333;
            margin-bottom: 18px;
            font-size: 1.2em;
            font-weight: 600;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e8e8e8;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .stat-value {
            color: #333;
            font-weight: 600;
            font-size: 15px;
        }

        .add-brand-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .add-brand-box h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.2em;
            font-weight: 600;
        }

        .add-notice-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .add-notice-box h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.2em;
            font-weight: 600;
        }

        .notice-list-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .notice-list-box h2 {
            color: #333;
            margin-bottom: 18px;
            font-size: 1.2em;
            font-weight: 600;
        }

        .notice-item-small {
            padding: 15px;
            border: 1px solid #e8e8e8;
            margin-bottom: 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .notice-item-small:hover {
            border-color: #d0d0d0;
            transform: translateX(5px);
        }

        .notice-item-small h4 {
            color: #333;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .notice-item-small .notice-meta {
            color: #999;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .notice-item-small p {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }

        .brands-table {
            background: rgba(255, 255, 255, 0.95);
            flex: 1;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .table-header {
            background: linear-gradient(135deg, #333 0%, #555 100%);
            color: #fff;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e8e8e8;
        }

        th {
            background: #f8f8f8;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            color: #666;
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: bold;
            color: #333;
            border: none;
        }

        .status-经营 {
            background: #c8f7dc;
            border-radius: 15px;
        }

        .status-歇业 {
            background: #ffe4cc;
            border-radius: 12px;
        }

        .status-停业 {
            background: #ffcccc;
            border-radius: 12px;
        }

        .status-跑路 {
            background: #ffd0d5;
            border-radius: 12px;
        }

        .action-btn {
            padding: 7px 14px;
            margin-right: 5px;
            font-size: 12px;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background: linear-gradient(135deg, #333 0%, #555 100%);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .edit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .delete-btn {
            background: #dc3545;
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.15);
        }

        .delete-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.2);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            padding: 35px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-header h3 {
            color: #333;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: all 0.3s ease;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: #333;
            background: #f5f5f5;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn-group .submit-btn {
            flex: 1;
        }

        .cancel-btn {
            flex: 1;
            padding: 14px;
            font-size: 15px;
            color: #333;
            background: #e8e8e8;
            border: none;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .cancel-btn:hover {
            background: #d0d0d0;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        @media (max-width: 1024px) {
            .main-content {
                flex-direction: column;
            }

            .sidebar {
                flex: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']): ?>
            <!-- 登录页面 -->
            <div class="login-box">
                <h2>后台登录</h2>
                <?php if (isset($loginError)): ?>
                    <div class="error-msg"><?php echo $loginError; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>用户名</label>
                        <input type="text" name="username" placeholder="请输入用户名" required>
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input type="password" name="password" placeholder="请输入密码" required>
                    </div>
                    <button type="submit" name="login" class="submit-btn">登录</button>
                </form>
                <div style="margin-top: 20px; text-align: center;">
                    <a href="index.php" style="color: #333; text-decoration: none;">返回首页</a>
                </div>
            </div>
        <?php else: ?>
            <!-- 后台管理页面 -->
            <div class="header">
                <h1>钠云查询 - 后台管理</h1>
                <div class="header-links">
                    <span style="color: #666;">欢迎，管理员</span>
                    <a href="?action=logout">退出登录</a>
                    <a href="index.php">返回首页</a>
                </div>
            </div>

            <?php if (isset($addSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 0 auto 20px;"><?php echo $addSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($addError)): ?>
                <div class="error-msg" style="max-width: 1400px; margin: 0 auto 20px;"><?php echo $addError; ?></div>
            <?php endif; ?>

            <?php if (isset($addNoticeSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 0 auto 20px;"><?php echo $addNoticeSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($addNoticeError)): ?>
                <div class="error-msg" style="max-width: 1400px; margin: 0 auto 20px;"><?php echo $addNoticeError; ?></div>
            <?php endif; ?>

            <div class="main-content">
                <div class="sidebar">
                    <!-- 统计信息 -->
                    <div class="stats-box">
                        <h2>统计信息</h2>
                        <div class="stat-item">
                            <span class="stat-label">网站访问量</span>
                            <span class="stat-value"><?php echo number_format($stats['visits']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">品牌数量</span>
                            <span class="stat-value"><?php echo count($brands); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">公告数量</span>
                            <span class="stat-value"><?php echo count($notices); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">订单数量</span>
                            <span class="stat-value"><?php echo count($orders); ?></span>
                        </div>
                    </div>

                    <!-- 发布公告 -->
                    <div class="add-notice-box">
                        <h2>发布公告</h2>
                        <?php if (isset($addNoticeError)): ?>
                            <div class="error-msg"><?php echo $addNoticeError; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label>公告标题</label>
                                <input type="text" name="notice_title" placeholder="请输入公告标题" required>
                            </div>
                            <div class="form-group">
                                <label>公告标签</label>
                                <select name="notice_tag" required>
                                    <option value="重要">重要</option>
                                    <option value="更新">更新</option>
                                    <option value="提示">提示</option>
                                    <option value="通知">通知</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>公告内容</label>
                                <textarea name="notice_content" placeholder="请输入公告内容" required></textarea>
                            </div>
                            <button type="submit" name="add_notice" class="submit-btn">发布公告</button>
                        </form>
                    </div>

                    <!-- 公告列表 -->
                    <div class="notice-list-box">
                        <h2>公告列表</h2>
                        <?php if (!empty($notices)): ?>
                            <?php foreach (array_slice($notices, 0, 5) as $notice): ?>
                                <div class="notice-item-small">
                                    <h4><?php echo htmlspecialchars($notice['title']); ?></h4>
                                    <div class="notice-meta">
                                        <?php echo htmlspecialchars($notice['tag']); ?> · <?php echo htmlspecialchars($notice['date']); ?>
                                    </div>
                                    <p><?php echo mb_substr(htmlspecialchars($notice['content']), 0, 50); ?>...</p>
                                    <form method="POST" style="margin-top: 5px;">
                                        <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                                        <button type="submit" name="delete_notice" class="delete-btn">删除</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #999; text-align: center; padding: 20px;">暂无公告</p>
                        <?php endif; ?>
                    </div>

                    <!-- 添加友链 -->
                    <div class="add-notice-box">
                        <h2>添加友链</h2>
                        <?php if (isset($addFriendError)): ?>
                            <div class="error-msg"><?php echo $addFriendError; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label>友链名称</label>
                                <input type="text" name="friend_name" placeholder="请输入友链名称" required>
                            </div>
                            <div class="form-group">
                                <label>友链描述</label>
                                <input type="text" name="friend_desc" placeholder="请输入友链描述">
                            </div>
                            <div class="form-group">
                                <label>友链Logo</label>
                                <input type="url" name="friend_logo" placeholder="请输入Logo图片链接" required>
                            </div>
                            <div class="form-group">
                                <label>友链链接</label>
                                <input type="url" name="friend_url" placeholder="请输入友链链接" required>
                            </div>
                            <button type="submit" name="add_friend" class="submit-btn">添加友链</button>
                        </form>
                    </div>

                    <!-- 友链列表 -->
                    <div class="notice-list-box">
                        <h2>友链列表</h2>
                        <?php if (!empty($friends)): ?>
                            <?php foreach ($friends as $friend): ?>
                                <div class="notice-item-small">
                                    <h4><?php echo htmlspecialchars($friend['name']); ?></h4>
                                    <div class="notice-meta">
                                        <?php echo htmlspecialchars($friend['description']); ?>
                                    </div>
                                    <form method="POST" style="margin-top: 5px;">
                                        <input type="hidden" name="friend_id" value="<?php echo $friend['id']; ?>">
                                        <button type="submit" name="delete_friend" class="delete-btn">删除</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #999; text-align: center; padding: 20px;">暂无友链</p>
                        <?php endif; ?>
                    </div>

                    <!-- 订单列表 -->
                    <div class="notice-list-box">
                        <h2>订单列表</h2>
                        <?php if (!empty($orders)): ?>
                            <?php foreach (array_reverse($orders) as $order): ?>
                                <div class="notice-item-small">
                                    <h4>订单号：<?php echo htmlspecialchars($order['order_id']); ?></h4>
                                    <div class="notice-meta">
                                        <?php echo htmlspecialchars($order['brand_name']); ?> · <?php echo htmlspecialchars($order['create_time']); ?>
                                    </div>
                                    <p style="color: #666; font-size: 13px; line-height: 1.6;">
                                        <strong>收件人：</strong><?php echo htmlspecialchars($order['name']); ?><br>
                                        <strong>电话：</strong><?php echo htmlspecialchars($order['phone']); ?><br>
                                        <strong>地址：</strong><?php echo htmlspecialchars($order['address']); ?><br>
                                        <strong>印章：</strong><?php echo htmlspecialchars($order['seal_type']); ?><br>
                                        <strong>支付：</strong><?php echo htmlspecialchars($order['payment_method']); ?> · <?php echo htmlspecialchars($order['payment_number']); ?><br>
                                        <strong>金额：</strong>¥<?php echo htmlspecialchars($order['amount']); ?>
                                    </p>
                                    <form method="POST" style="margin-top: 8px; display: inline-block;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 5px 10px; border-radius: 5px; border: 1px solid #ddd; font-size: 12px; font-weight: 600;
                                            <?php if ($order['status'] === '待发货'): ?>
                                                background: #fff3cd; color: #856404;
                                            <?php elseif ($order['status'] === '已发货'): ?>
                                                background: #d4edda; color: #155724;
                                            <?php else: ?>
                                                background: #f8d7da; color: #721c24;
                                            <?php endif; ?>">
                                            <option value="待发货" <?php if ($order['status'] === '待发货') echo 'selected'; ?>>待发货</option>
                                            <option value="已发货" <?php if ($order['status'] === '已发货') echo 'selected'; ?>>已发货</option>
                                            <option value="无法发货" <?php if ($order['status'] === '无法发货') echo 'selected'; ?>>无法发货</option>
                                        </select>
                                        <input type="hidden" name="update_order_status" value="1">
                                    </form>
                                    <form method="POST" style="margin-top: 8px; display: inline-block; margin-left: 8px;" onsubmit="return confirm('确定要删除这个订单吗？');">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                        <button type="submit" name="delete_order" class="delete-btn">删除订单</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #999; text-align: center; padding: 20px;">暂无订单</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 品牌列表 -->
                <div class="brands-table">
                    <div class="table-header">
                        <span>品牌列表（共 <?php echo count($brands); ?> 个）</span>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>识别码</th>
                                    <th>Logo</th>
                                    <th>品牌名称</th>
                                    <th>官网</th>
                                    <th>状态</th>
                                    <th>营业执照</th>
                                    <th>联系方式</th>
                                    <th>提交时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($brands)): ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state">
                                                暂无品牌数据
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($brands as $brand): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($brand['code']); ?></td>
                                            <td>
                                                <?php if (!empty($brand['logo'])): ?>
                                                    <img src="<?php echo htmlspecialchars($brand['logo']); ?>" alt="Logo" style="max-width: 50px; max-height: 50px; object-fit: contain;">
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 12px;">无</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($brand['name']); ?></td>
                                            <td><?php echo htmlspecialchars($brand['website']); ?></td>
                                            <td><span class="status-badge status-<?php echo htmlspecialchars($brand['status']); ?>"><?php echo htmlspecialchars($brand['status']); ?></span></td>
                                            <td>
                                                <?php if ($brand['has_license'] == 'yes' && !empty($brand['license_file'])): ?>
                                                    <a href="<?php echo htmlspecialchars($brand['license_file']); ?>" target="_blank" style="color: #333; text-decoration: underline; font-size: 12px;">查看</a>
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 12px;">无</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($brand['contact']); ?></td>
                                            <td><?php echo htmlspecialchars($brand['date']); ?></td>
                                            <td>
                                                <button class="action-btn edit-btn" onclick="editBrand('<?php echo htmlspecialchars($brand['code']); ?>', '<?php echo htmlspecialchars($brand['name']); ?>', '<?php echo htmlspecialchars($brand['website']); ?>', '<?php echo htmlspecialchars($brand['intro']); ?>', '<?php echo htmlspecialchars($brand['status']); ?>', '<?php echo htmlspecialchars($brand['has_license']); ?>', '<?php echo htmlspecialchars($brand['contact']); ?>', '<?php echo isset($brand['logo']) ? addslashes(htmlspecialchars($brand['logo'])) : ''; ?>', '<?php echo isset($brand['license_file']) ? addslashes(htmlspecialchars($brand['license_file'])) : ''; ?>')">编辑</button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('确定要删除这个品牌吗？');">
                                                    <input type="hidden" name="brand_code" value="<?php echo htmlspecialchars($brand['code']); ?>">
                                                    <button type="submit" name="delete_brand" class="action-btn delete-btn">删除</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (isset($updateSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 20px auto 0;"><?php echo $updateSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($deleteSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 20px auto 0;"><?php echo $deleteSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($deleteNoticeSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 20px auto 0;"><?php echo $deleteNoticeSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($addFriendSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 20px auto 0;"><?php echo $addFriendSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($deleteFriendSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 20px auto 0;"><?php echo $deleteFriendSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($updateOrderSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 20px auto 0;"><?php echo $updateOrderSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($deleteOrderSuccess)): ?>
                <div class="success-msg" style="max-width: 1400px; margin: 20px auto 0;"><?php echo $deleteOrderSuccess; ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- 编辑模态框 -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>编辑品牌信息</h3>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <form method="POST" id="editForm" enctype="multipart/form-data" action="admin.php">
                <input type="hidden" name="brand_code" id="editCode">
                <div class="form-group">
                    <label>品牌名称</label>
                    <input type="text" name="brand_name" id="editName" required>
                </div>
                <div class="form-group">
                    <label>品牌官网</label>
                    <input type="url" name="brand_website" id="editWebsite" required>
                </div>
                <div class="form-group">
                    <label>品牌介绍</label>
                    <textarea name="brand_intro" id="editIntro" style="width: 100%; padding: 10px 15px; font-size: 14px; border: 1px solid #ddd; outline: none; min-height: 80px; resize: vertical;" required></textarea>
                </div>
                <div class="form-group">
                    <label>品牌状态</label>
                    <select name="brand_status" id="editStatus" style="width: 100%; padding: 10px 15px; font-size: 14px; border: 1px solid #ddd; outline: none;" required>
                        <option value="经营">经营</option>
                        <option value="歇业">歇业</option>
                        <option value="停业">停业</option>
                        <option value="跑路">跑路</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>营业执照</label>
                    <select name="has_license" id="editLicense" style="width: 100%; padding: 10px 15px; font-size: 14px; border: 1px solid #ddd; outline: none;" required>
                        <option value="yes">有</option>
                        <option value="no">无</option>
                    </select>
                </div>
                <div class="form-group" id="licensePreview" style="display: none;">
                    <label>当前营业执照</label>
                    <img id="licensePreviewImg" src="" alt="营业执照预览" style="max-width: 100%; max-height: 200px; object-fit: contain; border: 1px solid #ddd; padding: 5px;">
                </div>
                <div class="form-group">
                    <label>联系方式</label>
                    <input type="text" name="contact" id="editContact" required>
                </div>
                <div class="form-group">
                    <label>品牌Logo</label>
                    <input type="file" name="logo_file" accept="image/*">
                </div>
                <div class="form-group" id="logoPreview" style="display: none;">
                    <label>当前Logo</label>
                    <img id="logoPreviewImg" src="" alt="Logo预览" style="max-width: 100px; max-height: 100px; object-fit: contain; border: 1px solid #ddd; padding: 5px;">
                </div>
                <div class="btn-group">
                    <button type="button" class="cancel-btn" onclick="closeModal()">取消</button>
                    <button type="submit" name="update_brand" class="submit-btn">保存</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editBrand(code, name, website, intro, status, hasLicense, contact, logo, licenseFile) {
            document.getElementById('editCode').value = code;
            document.getElementById('editName').value = name;
            document.getElementById('editWebsite').value = website;
            document.getElementById('editIntro').value = intro;
            document.getElementById('editStatus').value = status;
            document.getElementById('editLicense').value = hasLicense;
            document.getElementById('editContact').value = contact;

            // 显示Logo预览
            const logoPreviewDiv = document.getElementById('logoPreview');
            const logoPreviewImg = document.getElementById('logoPreviewImg');
            if (logo) {
                logoPreviewDiv.style.display = 'block';
                logoPreviewImg.src = logo;
            } else {
                logoPreviewDiv.style.display = 'none';
            }

            // 显示营业执照预览
            const licensePreviewDiv = document.getElementById('licensePreview');
            const licensePreviewImg = document.getElementById('licensePreviewImg');
            if (licenseFile && hasLicense === 'yes') {
                licensePreviewDiv.style.display = 'block';
                licensePreviewImg.src = licenseFile;
            } else {
                licensePreviewDiv.style.display = 'none';
            }

            document.getElementById('editModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
