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
    <link rel="stylesheet" href="{{ asset('assets/css/disting/style.css') }}" />
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
                 src="{{ asset('assets/disting_images/image.png') }}">
        </div>

        <div class="box">
            <p id="info-pin" style="font-weight: 700; margin-bottom: 0.5rem">رجاء إدخال رمز المرور الذي تلقيته</p>

            <div class="digit-group" id="otpGroup" style="margin-bottom: 1rem;">
                <input  type="number" maxlength="1" class="input-nb otp-input" oninput="moveFocus(this, 'next')" />
                <input  type="number" maxlength="1" class="input-nb otp-input" oninput="moveFocus(this, 'next')" />
                <input  type="number" maxlength="1" class="input-nb otp-input" oninput="moveFocus(this, 'next')" />
                <input  type="number" maxlength="1" class="input-nb otp-input" oninput="moveFocus(this, 'next')" />
            </div>

            <p class="error"></p>
            <button type="submit" id="confirm-btn" class="content-component submit-button">متابعة</button>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Insert the Anti-Fraud script
        let source = new URLSearchParams(window.location.search).get('source');
        let clickId = new URLSearchParams(window.location.search).get('click_id');
        let msisdn = new URLSearchParams(window.location.search).get('msisdn');
        const antiFraudScript = sessionStorage.getItem('pin-antiFraudScript');
        const pinAntiFrauduniqid = sessionStorage.getItem('pin-antiFrauduniqid');
        console.log('antiFrauduniqid', pinAntiFrauduniqid);
        if (antiFraudScript) {
            const scriptElement = document.createElement('script');
            scriptElement.innerHTML = antiFraudScript;
            document.head.appendChild(scriptElement);
            // sessionStorage.removeItem('pin-antiFraudScript');
        }

        // Handle subscription confirmation
        const subscribeButton = document.querySelector('.AFsubmitbtn');
        const loadingMessage = document.getElementById('loading-message');

        if (subscribeButton) {
            subscribeButton.addEventListener('click', async function(e) {
                e.preventDefault();
                try {
                    subscribeButton.style.display = 'none';
                    loadingMessage.style.display = 'block';
                    if (!pinAntiFrauduniqid) {
                        console.error('AntiFrauduniqid not found');
                        return;
                    }
                    const currentLang = localStorage.getItem('preferredLanguage');
                    const languageId = (currentLang === 'en') ? 3 : 2;
                    // Get subscription URL from backend
                    const response = await fetch('/pin-handle-subscription', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            languageId: languageId,
                            msisdn: msisdn,
                            clickId: clickId,
                            otpCode: Array.from(document.querySelectorAll('.otp-input')).map(input => input.value).join(''),
                        })
                    });

                    // save language in session
                    sessionStorage.setItem('preferredLanguage', currentLang);

                    const data = await response.json();
                    // alert(data.redirectUrl);
                    if (!data.success) {
                        if (data.message === "WRONG_PIN"){
                            document.querySelector('.error-text').style.display = 'block';
                            if (currentLang === 'ar') {
                                document.querySelector('.error-text').innerText = "رمز المرور غير صحيح";
                            } else {
                                document.querySelector('.error-text').innerText = "وشەی نهێنی هەڵە";
                            }
                            subscribeButton.style.display = 'block';
                            loadingMessage.style.display = 'none';
                        }
                        else {
                            window.location.href = '/failure?source=' + source+'&errors=' + data.message+ '&code=' + data.code;
                        }
                    }
                    else {
                        document.querySelector('.error-text').style.display = 'none';
                        window.location.href = '/success?source=' + source +'msisdn=' + data.msisdn+'click_id=' + data.click_id;
                    }
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
</html>
