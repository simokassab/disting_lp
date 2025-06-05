const translations = {
    ar: {
        direction: 'rtl',
        languageBtn: 'عربی',
        welcomeText: `</span>مرحبًا بك في <span class='bold-text'>المتميزون`,
        welcomeInfo: `أثبت ذكائك، اكسب لقب "المتميزون"، <span class='bold-text'>واربح جوائز</span>`,
        welcomeDesc: `كلما زادت نقاطك زادت فرصك بالفوز`,
        subscribe: "إشترك",
        infoPhone: 'أدخل رقم جوالك لتتلقى رمز المرور',
        infoPin: 'رجاء إدخال رمز المرور الذي تلقيته',
        confirmBtn: 'تأكيد الاشتراك',
        continueText: 'متابعة',
        footerText: '• "مرحبًا بك في مسابقة المتميزون',
        trialText: '• من آسياسيل للمشتركين الجدد اول ثلاث ايام مجاناً, ثم تكلفة الاشتراك 300 د.ع يومياً',
        cancelText: '• لالغاء الاشتراك ارسل 0 مجاناً الى 2296.',
        failureText: "عذرًا لا يمكنك الاشتراك في الخدمة",
        successText: "شكرًا لاشتراكك في الخدمة"
    },
    ku: {
        direction: 'rtl',
        languageBtn: 'كوردی',
        welcomeText: `بەخێربێن بۆ پێشبڕکێی <span class='bold-text'> نایابەکان</span>`,
        welcomeInfo: `زیرەکی خۆت بسەلمێنە، نازناوی نایابەکان بەدەستبهێنە، و پاداشت وەربگرە`,
        welcomeDesc: `خاڵی زیاترکۆبکەرەوە و چانسی زیاتر بۆ بردنەوە بەدەستبهێنە`,
        subscribe: "بەشداربە",
        infoPhone: 'ژمارەی مۆبایلەکەت داخڵ بکە بۆ وەرگرتنی PIN کۆد',
        infoPin: 'تکایە ئەو پاسکۆدەی کە وەرتگرتووە دابنێ',
        confirmBtn: 'پشتڕاستکردنەوەی بەشداریکردن',
        continueText: 'بەدواداچوون',
        footerText: 'بەخێربێیت بۆ پێشبڕکێی "نایابەکان"',
        trialText: ' لە ئاسیاسیڵ بۆ بەشداربووانی نوێ، سێ ڕۆژی یەکەم بە بێبەرامبەر، پاشان نرخی بەشداریکردن 300 د.ع/ ڕۆژانە .',
        cancelText: 'بۆ ڕاگرتنی بەشداریکردن، 0 بە بێبەرامبەر بنێرە بۆ 2296.',
        failureText: "ببورن ناتوانن سەبسکرایبی خزمەتگوزارییەکە بکەن",
        successText: "سوپاس بۆ بەشداریکردنتان لە خزمەتگوزارییەکە"
    },
    en: {
        direction: 'ltr',
        languageBtn: 'En',
        welcomeText: `Welcome to The <span class='bold-text'>Distinguished</span>`,
        welcomeInfo: `Prove Your Intelligence, Earn the Title of The Distinguished, and <span class='bold-text'>Get Rewarded</span>`,
        welcomeDesc: 'Higher points = Higher chance of winning',
        subscribe: "Subscribe",
        infoPhone: "Enter your mobile to receive a PIN code",
        infoPin: "Enter PIN code",
        continueText: 'Continue',
        confirmBtn: "Confirm Subscription",
        footerText: '• Welcome to "The Distinguished" competition! ',
        trialText: '• First 3 days are a free trial for new users then subscription is 300 IQD/day.',
        cancelText: '• You can unsubscribe at any time for free by sending 0 to 2296.',
        failureText: "Sorry, you cannot subscribe to the service.",
        successText: "Thank you for subscribing to the service."
    }
};

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    // Load saved language or default to Arabic
    const savedLang = localStorage.getItem('preferredLanguage') || 'ar';
    changeLanguage(savedLang);
});

// Toggle between languages
function toggleLanguage() {
    const currentLang = localStorage.getItem('preferredLanguage') || 'ar';
    const newLang = currentLang === 'ar' ? 'ku' : currentLang === 'ku' ? 'en' : 'ar';
    changeLanguage(newLang);
}

// Change language function
function changeLanguage(lang) {
    // Save to localStorage
    localStorage.setItem('preferredLanguage', lang);

    // Set direction
    document.dir = translations[lang].direction;

    // Helper function to safely update element content
    function safelyUpdateElement(id, content, isHTML = false) {
        const element = document.getElementById(id);
        if (element) {
            if (isHTML) {
                element.innerHTML = content;
            }
            else {
                element.textContent = content;
            }
        }
    }

    function updateImageSrc(id, newSrc) {
        const imageElement = document.getElementById(id);
        if (imageElement) {
            imageElement.src = newSrc;
        }
    }

    // Update language toggle button
    safelyUpdateElement('lang-toggle', translations[lang].languageBtn);
    // Update all text elements
    safelyUpdateElement('success-text', translations[lang].successText, true);
    updateImageSrc('certificate', translations[lang].imageURL);
    safelyUpdateElement('failure-text', translations[lang].failureText, true);
    safelyUpdateElement('confirm-btn', translations[lang].confirmBtn);
    safelyUpdateElement('welcome-text', translations[lang].welcomeText, true);
    safelyUpdateElement('welcome-info', translations[lang].welcomeInfo,true);
    safelyUpdateElement('welcome-description', translations[lang].welcomeDesc,true);
    safelyUpdateElement('subscribe', translations[lang].subscribe);
    safelyUpdateElement('continue', translations[lang].continueText);
    safelyUpdateElement('footer-text', translations[lang].footerText);
    safelyUpdateElement('trial-text', translations[lang].trialText);
    safelyUpdateElement('cancel-text', translations[lang].cancelText);
    safelyUpdateElement('info-phone', translations[lang].infoPhone);
    safelyUpdateElement('info-pin', translations[lang].infoPin);
}


/*** Function: OTP ***/
function moveFocus(current, direction) {
    if (direction === 'next') {
        if (current.value.length >= 1) {
            const nextInput = current.nextElementSibling;
            if (nextInput) {
                nextInput.focus();
            }
        }
    }

    // No more than 4 digits
    if (current === document.querySelector('.otp-input:last-of-type') && current.value.length >= 1) {
        current.value = current.value.charAt(0); // Keep only the first character
    }
}
// let otp = document.getElementById('subscribe')

// if(otp)
//     otp.addEventListener('click', function() {
//         const inputs = document.querySelectorAll('.otp-input');
//         let otp = '';
//         inputs.forEach(input => {
//             otp += input.value;
//         });
//         alert(`Entered OTP: ${otp}`);
//     });
