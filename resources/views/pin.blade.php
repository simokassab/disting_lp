{{--@php--}}
{{--    $projectSource = request()->query('source'); // Get source from URL--}}
{{--    $source = \App\Models\ProjectSource::where('uuid', $projectSource)->first()->source->name;--}}
{{--    $isGoogleAds = in_array($source, ['Google-Ads']);--}}
{{--@endphp--}}
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
            <p id="info-phone" style="font-weight: 700; margin-bottom: 0.5rem">أدخل رقم جوالك لتتلقى رمز المرور</p>

            <div class="content-component" style="margin-bottom: 1rem;">
                <p>+964</p>
                <input id='phone-number' type="text" title="phone" >
            </div>
            <button type="submit" id="continue" class="content-component submit-button" style="display: none">متابعة</button>
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
<img class="bottom-image"
     src="{{ asset('assets/disting_images/White PNG Transparent.svg') }}">
<script src="{{ asset('assets/js/disting/translation_lp2.js') }}"></script>

</body>
<script>
    document.addEventListener('DOMContentLoaded', async function () {

        const phoneInput = document.getElementById('phone-number');
        const continueButton = document.getElementById('continue');
        const loadingMessage = document.getElementById('loading-message');
        const errorDiv = document.getElementById('error');
        let full_number = '';
        let anti_fraut_id = '';
        phoneInput.addEventListener('input', function () {
            // Remove all non-numeric characters
            this.value = this.value.replace(/\D/g, '');

            full_number = '964' + this.value;

            // Validation
            if (this.value.length < 7) {
                continueButton.style.display = 'none';
            } else if (full_number.startsWith('96477') || full_number.startsWith('964077')) {
                // remove the 0 after the 964
                full_number = full_number.replace(/^9640/, '964');
                console.log(full_number);
                // Check if the number is valid
                if (full_number.length === 13) {
                    // If valid, show the continue button
                    continueButton.style.display = 'inline-block';
                } else {
                    // If not valid, hide the continue button
                    continueButton.style.display = 'none';
                }
            } else {
                continueButton.style.display = 'none';
            }
        });

        let source = new URLSearchParams(window.location.search).get('source');
        let clickId = new URLSearchParams(window.location.search).get('click_id') ||
            new URLSearchParams(window.location.search).get('clickId') ||
            new URLSearchParams(window.location.search).get('gclid') ||
            new URLSearchParams(window.location.search).get('ttclid') ||
            new URLSearchParams(window.location.search).get('wbraid') ||
            new URLSearchParams(window.location.search).get('gbraid') ||
            new URLSearchParams(window.location.search).get('fbclid');

        currentLanguage = localStorage.getItem('language') || 'AR';
        fetch('save-preferred-language', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({language: currentLanguage})
        });

        const headersResponse = await fetch('get-request-headers', {
            method: 'GET',
        });
        const {headersBase64, msisdn1} = await headersResponse.json();

        let antifraudResponse = await fetch('pin-get-antifraud-script', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                user_headers: headersBase64,
                msisdn: '',
                user_ip: "{{ base64_encode(Request::ip()) }}",
                click_id: clickId,
                source: new URLSearchParams(window.location.search).get('source'),
                save_antifraud: '0', // dont save the antifrauduniqid in db
                page: 1

            })
        });

        const antifraudData =   await antifraudResponse.json();
        console.log(antifraudData);
        if (!antifraudData.success) {
            throw new Error('Failed to get anti-fraud script');
        }

        // Store both the script and the AntiFrauduniqid
        sessionStorage.setItem('antiFraudScript', antifraudData.script);
        const antiFraudScript = sessionStorage.getItem('antiFraudScript');
        if (antiFraudScript) {
            const scriptElement = document.createElement('script');
            scriptElement.innerHTML = antiFraudScript;
            document.head.appendChild(scriptElement);
            sessionStorage.removeItem('antiFraudScript');
            anti_fraut_id = antifraudData.antiFrauduniqid;
            // document.getElementById('loading-message').style.display = 'none';
            // document.querySelector('.submit-button').style.display = 'block';
        }
        // alert('antiFrauduniqid: ' + antifraudData.antiFrauduniqid);
        sessionStorage.setItem('MCPuniqid', antifraudData.mcp_uniq_id);


        // click button
        document.querySelector('.submit-button').addEventListener('click', async function (e) {
            e.preventDefault();
            continueButton.style.display = 'none';
            loadingMessage.style.display = 'block';
            try {
                const trackingResponse = await fetch('pin-store-tracking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        click_id: clickId,
                        msisdn: full_number
                    })
                });

                const trackingData = await trackingResponse.json();
                console.log(trackingData);
                if (!trackingData.success) {
                    throw new Error('Failed to store tracking data');
                }

                const getPinResponse = await fetch('/get-pin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        click_id: anti_fraut_id,
                        msisdn: full_number,
                        languageId: currentLanguage === 'AR' ? 2 : 3,
                    })
                });

                const getPinData = await getPinResponse.json();
                if (!getPinData.success) {
                    loadingMessage.style.display = 'block';
                    loadingMessage.innerHTML = getPinData.message;
                    // window.location.href = '/failure?code='+getPinData.code+'&message='+getPinData.message+'&msisdn='+full_number;
                } else {

                    window.location.href = `otp?gclid=${clickId}&source=${source}&msisdn=${full_number}&uniqid=${sessionStorage.getItem('MCPuniqid')}`;
                }
            } catch (error) {
                console.error('Error:', error);
                // window.location.href = '/failure';
            }
        });
    });
</script>
</html>
