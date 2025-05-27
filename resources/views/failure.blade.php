<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distinguished</title>
    <link rel="stylesheet" href="{{ asset('assets/css/disting/style_lp2.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
<header class="navbar">
    <img alt="Website Logo" src="{{ asset('assets/website-logo.png') }}" class="logo">
    <button id="lang-toggle" class="lang-btn" onclick="toggleLanguage()">كردی</button>
</header>

<div class="content">
    <div class="form">
        <div class="welcome">
            <h1 id="welcome-text">
                مرحبًا بك في المتميزون
            </h1>

            <p id="welcome-info">العب بذكاء، اجمع أكثر واصعد للقمة</p>
            <p class="bold-text" id="welcome-description">كلما زادت نقاطك زادت فرصك بالفوز </p>
            <img class="image content-mobile"  alt="certificate"
                 src="{{ asset('assets/disting_images/image_lp2.png') }}">
        </div>

        <div class="box">
            <h2 id="failure-text">عذرًا لا يمكنك الاشتراك
                في الخدمة</h2>
            <div class="instructions">
                <p id="footer-text"> اهلا بك في مسابقة "بطل الجائزة الكبرى"</p>
                <p id="trial-text"> من أسياسيل للمشتركين الجدد أول ثلاث أيام مجانا ثم تكلفة الاشتراك 300 د.ج يوميا </p>
                <p id="cancel-text"> لإلغاء الاشتراك ارسل 0 مجانا إلى 4603 </p>
            </div>
        </div>
    </div>

    <img class="image content-desk" alt="certificate" src="{{ asset('assets/image_lp2.png') }}">
</div>
<script src="{{ asset('assets/js/disting/translation_lp2.js') }}"></script>
</body>
</html>
