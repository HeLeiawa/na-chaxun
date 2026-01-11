<?php
$brandsFile = 'brands.json';
$brands = [];
if (file_exists($brandsFile)) {
    $brands = json_decode(file_get_contents($brandsFile), true) ?: [];
}

// 访问量统计
$statsFile = 'stats.json';
$stats = ['visits' => 0];
if (file_exists($statsFile)) {
    $stats = json_decode(file_get_contents($statsFile), true) ?: ['visits' => 0];
}
$stats['visits']++;
file_put_contents($statsFile, json_encode($stats));

$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$brand = null;
$errorMsg = '';
$successMsg = '';

// 根据code查找品牌
if (!empty($code)) {
    foreach ($brands as $b) {
        if ($b['code'] === $code) {
            $brand = $b;
            break;
        }
    }
}

if (!$brand) {
    $errorMsg = '未找到该识别码对应的品牌信息';
}

// 处理表单提交
$submitted = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $brand) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $sealType = isset($_POST['seal_type']) ? trim($_POST['seal_type']) : '';
    $paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    $paymentNumber = isset($_POST['payment_number']) ? trim($_POST['payment_number']) : '';

    if (empty($name)) {
        $errorMsg = '请填写收件人姓名';
    } elseif (empty($phone)) {
        $errorMsg = '请填写联系电话';
    } elseif (empty($address)) {
        $errorMsg = '请填写邮寄地址';
    } elseif (empty($sealType)) {
        $errorMsg = '请选择印章类型';
    } elseif (empty($paymentMethod)) {
        $errorMsg = '请选择支付方式';
    } elseif (empty($paymentNumber)) {
        $errorMsg = '请填写支付单号';
    } else {
        // 根据印章类型计算价格
        $amount = ($sealType === '实体印章') ? 25.9 : 19.9;

        // 保存订单信息
        $ordersFile = 'orders.json';
        $orders = [];
        if (file_exists($ordersFile)) {
            $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
        }

        $order = [
            'order_id' => 'ORD' . date('YmdHis') . rand(100, 999),
            'brand_code' => $code,
            'brand_name' => $brand['name'],
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'seal_type' => $sealType,
            'payment_method' => $paymentMethod,
            'payment_number' => $paymentNumber,
            'amount' => $amount,
            'status' => '待发货',
            'create_time' => date('Y-m-d H:i:s')
        ];

        $orders[] = $order;
        file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $successMsg = '订单提交成功！将在5个工作日内发出，请保持手机畅通，注意查收短信通知。';
        $submitted = true;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>领取实体证书 - 钠云查询</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            min-height: 100vh;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin-top: 30px;
            text-align: center;
        }

        .back-btn {
            display: inline-block;
            padding: 12px 28px;
            color: #333;
            background: #fff;
            border: 2px solid #e8e8e8;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 30px;
            font-size: 15px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .back-btn:hover {
            background: #f8f8f8;
            border-color: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-weight: 500;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
        }

        .form-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            text-align: left;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .brand-info {
            background: #f8f8f8;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 10px;
            text-align: left;
        }

        .brand-info p {
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
        }

        .brand-info strong {
            color: #333;
        }

        .price-info {
            background: linear-gradient(135deg, #333 0%, #555 100%);
            color: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 10px;
            text-align: center;
        }

        .price-info .price {
            font-size: 36px;
            font-weight: 700;
        }

        .price-info .label {
            font-size: 14px;
            opacity: 0.9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 15px;
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

        .payment-section {
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .payment-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
            text-align: center;
        }

        .payment-codes {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .payment-code-item {
            text-align: center;
        }

        .payment-code-item img {
            width: 200px;
            height: 200px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .payment-code-item p {
            margin-top: 10px;
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            color: #fff;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            border: none;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .footer {
            margin-top: auto;
            padding: 30px 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.05);
            width: 100%;
        }

        .footer-text {
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            h1 {
                font-size: 1.6em;
            }

            .form-box {
                padding: 20px;
            }

            .payment-codes {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .payment-code-item img {
                width: 180px;
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="certificate.php<?php echo !empty($code) ? '?code=' . urlencode($code) : ''; ?>" class="back-btn">← 返回证书下载</a>

        <h1>领取实体证书</h1>

        <?php if ($errorMsg): ?>
            <div class="error-msg"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <?php if ($successMsg): ?>
            <div class="success-msg"><?php echo $successMsg; ?></div>
        <?php endif; ?>

        <?php if ($brand && !$submitted): ?>
        <div class="form-box">
            <div class="brand-info">
                <p><strong>品牌名称：</strong><?php echo htmlspecialchars($brand['name']); ?></p>
                <p><strong>识别码：</strong><?php echo htmlspecialchars($brand['code']); ?></p>
            </div>

            <div class="price-info">
                <div class="price" id="priceDisplay">¥19.9</div>
                <div class="label">工本费 + 邮费</div>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label>收件人姓名 *</label>
                    <input type="text" name="name" placeholder="请输入收件人姓名" required>
                </div>

                <div class="form-group">
                    <label>联系电话 *</label>
                    <input type="tel" name="phone" placeholder="请输入联系电话" required>
                </div>

                <div class="form-group">
                    <label>邮寄地址 *</label>
                    <textarea name="address" placeholder="请输入详细的邮寄地址" required></textarea>
                </div>

                <div class="form-group">
                    <label>印章类型 *</label>
                    <select name="seal_type" id="sealType" required onchange="updatePrice()">
                        <option value="">请选择印章类型</option>
                        <option value="实体印章">实体印章</option>
                        <option value="印刷印章">印刷印章</option>
                    </select>
                </div>

                <div class="payment-section">
                    <h3>请扫描下方二维码支付</h3>
                    <div class="payment-codes">
                        <div class="payment-code-item">
                            <img src="zfb.jpg" alt="支付宝支付">
                            <p>支付宝</p>
                        </div>
                        <div class="payment-code-item">
                            <img src="wx.png" alt="微信支付">
                            <p>微信</p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>支付方式 *</label>
                    <select name="payment_method" required>
                        <option value="">请选择支付方式</option>
                        <option value="支付宝">支付宝</option>
                        <option value="微信">微信</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>支付单号 *</label>
                    <input type="text" name="payment_number" placeholder="请输入支付单号" required>
                </div>

                <button type="submit" class="submit-btn">提交订单</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div class="footer-text">注意：请确保填写正确的邮寄信息，我们将通过短信通知您发货状态</div>
    </div>

    <script>
        function updatePrice() {
            var sealType = document.getElementById('sealType').value;
            var priceDisplay = document.getElementById('priceDisplay');

            if (sealType === '实体印章') {
                priceDisplay.textContent = '¥25.9';
            } else if (sealType === '印刷印章') {
                priceDisplay.textContent = '¥19.9';
            } else {
                priceDisplay.textContent = '¥19.9';
            }
        }
    </script>
</body>
</html>
