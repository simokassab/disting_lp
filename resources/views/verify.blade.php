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
    <link rel="stylesheet" href="{{ asset('assets/css/disting/style_lp2.css') }}" />
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
                 src="{{ asset('assets/disting_images/image_lp2.png') }}">
        </div>

        <div class="box">
            <button type="button" id="confirm-btn" class="content-component submit-button verify_btn AFsubmitbtn">تأكيد الاشتراك</button>
            <p id="loading-message" style="display: none; text-align: center; margin-top: 10px;">الرجاء الانتظار ...</p>
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
<script>
    document.addEventListener('DOMContentLoaded', async function () {


        let clickId = new URLSearchParams(window.location.search).get('click_id') ||
            new URLSearchParams(window.location.search).get('clickId') ||
            new URLSearchParams(window.location.search).get('gclid') ||
            new URLSearchParams(window.location.search).get('ttclid') ||
            new URLSearchParams(window.location.search).get('wbraid') ||
            new URLSearchParams(window.location.search).get('gbraid') ||
            new URLSearchParams(window.location.search).get('fbclid');

        let headersResponse =  await fetch('get-request-headers', {
            method: 'GET',
        });
        document.getElementById('loading-message').style.display = 'block';
        document.querySelector('.submit-button').style.display = 'none';
        let  {headersBase64, msisdn} = await headersResponse.json();

        let antifraudResponse = await fetch('get-antifraud-script', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                user_headers: headersBase64,
                msisdn: msisdn,
                user_ip: "{{ base64_encode(Request::ip()) }}",
                click_id: clickId,
                source: new URLSearchParams(window.location.search).get('source'),
                save_antifraud: '1', // dont save the antifrauduniqid in db
                page: 2

            })
        });
        const antifraudData = await antifraudResponse.json();
        console.log(antifraudData);
        if (!antifraudData.success) {
            window.location.href = 'failure?msisdn=' + msisdn + '&error=' + antifraudData.message;
        }

        // Store both the script and the AntiFrauduniqid
        sessionStorage.setItem('antiFraudScript', antifraudData.script);
        const antiFraudScript = sessionStorage.getItem('antiFraudScript');
        if (antiFraudScript) {
            const scriptElement = document.createElement('script');
            scriptElement.innerHTML = antiFraudScript;
            document.head.appendChild(scriptElement);
            sessionStorage.removeItem('antiFraudScript');
            document.getElementById('loading-message').style.display = 'none';
            document.querySelector('.submit-button').style.display = 'block';
            sessionStorage.setItem('antiFrauduniqid', antifraudData.antiFrauduniqid);
        }


        let antiFrauduniqid = sessionStorage.getItem('antiFrauduniqid');
        if (!antiFrauduniqid) {
            console.error('AntiFrauduniqid not found');
            return;
        }
        // Handle subscription confirmation
        const subscribeButton = document.querySelector('.submit-button');

        if (subscribeButton) {
            subscribeButton.addEventListener('click', async function (e) {

                e.preventDefault();
                document.querySelector('.submit-button').style.display = 'none';
                // show a loading message
                document.getElementById('loading-message').style.display = 'block';
                try {
                    const currentLang = localStorage.getItem('preferredLanguage');
                    const languageId = (currentLang === 'en') ? 3 : 2;
                    // Get subscription URL from backend
                    const response = await fetch('handle-subscription', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            antiFrauduniqid: antiFrauduniqid,
                            languageId: languageId,
                            msisdn: msisdn,
                        })
                    });

                    // save language in session
                    sessionStorage.setItem('preferredLanguage', currentLang);

                    const data = await response.json();
                    // alert(data.redirectUrl);
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to get subscription URL');
                    }

                    // Redirect to the subscription URL
                    window.location.href = data.redirectUrl;

                    // Clean up
                    sessionStorage.removeItem('antiFrauduniqid');
                } catch (error) {
                    console.error('Error:', error);
                    // window.location.href = '/failure';
                }
            });
        }
    });
</script>
</body>
</html>
