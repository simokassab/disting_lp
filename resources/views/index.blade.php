
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
            <button type="submit" id="continue" class="content-component submit-button">متابعة</button>
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

</body>

<script>
    document.addEventListener('DOMContentLoaded', async function () {
        // Generate clickId on page load and store it
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

        let headersResponse =  await fetch('get-request-headers', {
            method: 'GET',
        });
        let  {headersBase64, msisdn} = await headersResponse.json();

        if (!msisdn) {
            console.error('MSISDN not found in headers');
            // window.location.href = '/failure';
            // return;
        }

        document.getElementById('loading-message').style.display = 'block';
        document.querySelector('.submit-button').style.display = 'none';

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
            document.getElementById('loading-message').style.display = 'none';
            document.querySelector('.submit-button').style.display = 'block';
        }

        // alert('antiFrauduniqid: ' + antifraudData.antiFrauduniqid);
        sessionStorage.setItem('MCPuniqid', antifraudData.mcp_uniq_id);


        document.querySelector('.submit-button').addEventListener('click', async function (e) {
            e.preventDefault();
            // hide the button
            document.querySelector('.submit-button').style.display = 'none';
            // show a loading message
            document.getElementById('loading-message').style.display = 'block';
            try {


                // get the source from the url params
                let source = new URLSearchParams(window.location.search).get('source');

                // First store the tracking data
                const trackingResponse = await fetch('store-tracking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        click_id: clickId,
                        msisdn: msisdn,
                        user_headers: headersBase64
                    })
                });

                const trackingData = await trackingResponse.json();
                if (!trackingData.success) {
                    throw new Error('Failed to store tracking data');
                }

                // Then get anti-fraud script

                // Redirect to verify page with clickId
                window.location.href = `verify?click_id=${clickId}&source=${source}&msisdn=${msisdn}&uniqid=${sessionStorage.getItem('MCPuniqid')}`;
            } catch (error) {
                console.error('Error:', error);
                // window.location.href = '/failure';
            }
        });
    });
</script>
</html>
