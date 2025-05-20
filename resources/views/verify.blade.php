@php
    $projectSource = request()->query('source'); // Get source from URL
    $source = \App\Models\ProjectSource::where('uuid', $projectSource)->first()->source->name;
    $isGoogleAds = in_array($source, ['Google-Ads']);
@endphp
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Distinguished</title>
    <link rel="shortcut icon" href="{{ asset('assets/disting_images/icon.png') }}" sizes="32x32" type="image/svg">
    <link rel="shortcut icon" href="{{ asset('assets/disting_images/icon.png') }}" sizes="16x16" type="image/svg">
    <link rel="shortcut icon" href="{{ asset('assets/disting_images/icon.png') }}" sizes="72x72" type="image/svg">
    <link rel="stylesheet" href="{{ asset('assets/css/disting/style.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{--    @if($isGoogleAds)--}}
    {{--        <!-- Google Tag Manager -->--}}
    {{--        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':--}}
    {{--                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],--}}
    {{--                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=--}}
    {{--                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);--}}
    {{--            })(window,document,'script','dataLayer','GTM-N7FQGPP7');</script>--}}
    {{--        <!-- End Google Tag Manager -->--}}
    {{--    @endif--}}
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
                 src="{{ asset('assets/disting_images/image.png') }}">
        </div>

        <div class="box">
            <button type="button" id="confirm-btn" class="content-component submit-button verify_btn AFsubmitbtn">تأكيد الاشتراك</button>
            {{--            <p id="loading-message" style="display: none; text-align: center; margin-top: 10px;">الرجاء الانتظار ...</p>--}}
            <div class="instructions">
                <p id="footer-text"> اهلا بك في مسابقة "بطل الجائزة الكبرى"</p>
                <p id="trial-text"> من أسياسيل للمشتركين الجدد أول ثلاث أيام مجانا ثم تكلفة الاشتراك 300 د.ج يوميا </p>
                <p id="cancel-text"> لإلغاء الاشتراك ارسل 0 مجانا إلى 4603 </p>
            </div>
        </div>
    </div>

    <img class="image content-desk" alt="certificate" src="{{ asset('assets/image.png') }}">
</div>
<script src="{{ asset('assets/js/disting/translation.js') }}"></script>

</body>
</html>
