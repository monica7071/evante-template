<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2A8B92;
            --cream: #F7EFE2;
            --text-dark: #2D3748;
            --text-light: #718096;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            background-color: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .thank-you-container {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 480px;
            padding: 3.5rem 2.5rem;
            text-align: center;
            position: relative;
        }

        /* Language Switcher */
        .lang-switcher {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            display: flex;
            border: 1.5px solid #E2E8F0;
            border-radius: 8px;
            overflow: hidden;
        }

        .lang-btn {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--white);
            color: #718096;
        }

        .lang-btn.active {
            background: var(--primary);
            color: var(--white);
        }

        .check-icon {
            width: 72px;
            height: 72px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .check-icon svg {
            width: 36px;
            height: 36px;
            stroke: var(--white);
            stroke-width: 3;
            fill: none;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="thank-you-container">
        <div class="lang-switcher">
            <button type="button" class="lang-btn active" data-lang="th">TH</button>
            <button type="button" class="lang-btn" data-lang="en">EN</button>
        </div>

        <div class="check-icon">
            <svg viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h1 data-th="ขอบคุณค่ะ!" data-en="Thank You!">ขอบคุณค่ะ!</h1>
        <p data-th="ส่งแบบสอบถามเรียบร้อยแล้ว ขอบคุณสำหรับเวลาของท่าน" data-en="Your response has been submitted successfully. We appreciate your time.">ส่งแบบสอบถามเรียบร้อยแล้ว<br>ขอบคุณสำหรับเวลาของท่าน</p>
    </div>

    <script>
        const langBtns = document.querySelectorAll('.lang-btn');
        langBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const lang = this.dataset.lang;
                langBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                document.querySelectorAll('[data-' + lang + ']').forEach(el => {
                    el.innerHTML = el.getAttribute('data-' + lang);
                });

                document.documentElement.lang = lang;
            });
        });
    </script>
</body>
</html>
