<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطای سرور — 500</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tahoma', 'Vazirmatn', 'IRANSans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 24px;
            color: #555;
            margin-bottom: 15px;
        }
        .error-message {
            font-size: 16px;
            color: #888;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        .btn-back {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .support-text {
            margin-top: 20px;
            font-size: 14px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <div class="error-code">500</div>
        <h1 class="error-title">خطای داخلی سرور</h1>
        <p class="error-message">
            متأسفانه مشکلی در سرور رخ داده است.<br>
            تیم فنی ما از این مشکل مطلع شده و در حال بررسی هستند.
        </p>
        <a href="{{ url('/') }}" class="btn-back">بازگشت به صفحه اصلی</a>
        <p class="support-text">اگر مشکل ادامه داشت، با پشتیبانی تماس بگیرید.</p>
    </div>
</body>
</html>
