<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Questionnaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2A8B92;
            --primary-dark: #1E6B71;
            --cream: #F7EFE2;
            --cream-dark: #EDE3D2;
            --text-dark: #2D3748;
            --text-mid: #4A5568;
            --text-light: #718096;
            --white: #FFFFFF;
            --error: #E53E3E;
            --border: #E2E8F0;
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

        .form-container {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 560px;
            padding: 3rem 2.5rem;
            position: relative;
        }

        /* Language Switcher */
        .lang-switcher {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            display: flex;
            border: 1.5px solid var(--border);
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
            color: var(--text-light);
        }

        .lang-btn.active {
            background: var(--primary);
            color: var(--white);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
            flex: 1;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-mid);
            margin-bottom: 0.4rem;
        }

        .form-group label .required {
            color: var(--error);
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            color: var(--text-dark);
            transition: border-color 0.2s;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--primary);
        }

        .form-group input.is-invalid {
            border-color: var(--error);
        }

        .invalid-feedback {
            color: var(--error);
            font-size: 0.8rem;
            margin-top: 0.3rem;
        }

        .source-section {
            margin-bottom: 1.5rem;
        }

        .source-section > label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-mid);
            margin-bottom: 0.75rem;
        }

        .radio-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .radio-option {
            position: relative;
        }

        .radio-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .radio-option label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 0.85rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            color: var(--text-mid);
            transition: all 0.2s;
            user-select: none;
        }

        .radio-option label::before {
            content: '';
            width: 18px;
            height: 18px;
            min-width: 18px;
            border: 2px solid var(--border);
            border-radius: 50%;
            transition: all 0.2s;
        }

        .radio-option input[type="radio"]:checked + label {
            border-color: var(--primary);
            background-color: rgba(42, 139, 146, 0.05);
            color: var(--primary);
        }

        .radio-option input[type="radio"]:checked + label::before {
            border-color: var(--primary);
            background: var(--primary);
            box-shadow: inset 0 0 0 3px var(--white);
        }

        .other-input-wrapper {
            margin-top: 0.5rem;
            grid-column: 1 / -1;
            display: none;
        }

        .other-input-wrapper.show {
            display: block;
        }

        .other-input-wrapper input {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s;
        }

        .other-input-wrapper input:focus {
            border-color: var(--primary);
        }

        .submit-btn {
            width: 100%;
            padding: 0.85rem;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem 0.75rem;
                align-items: flex-start;
            }

            .form-container {
                padding: 2rem 1.5rem;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .radio-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="lang-switcher">
            <button type="button" class="lang-btn active" data-lang="th">TH</button>
            <button type="button" class="lang-btn" data-lang="en">EN</button>
        </div>

        <div class="form-header">
            <h1 data-th="แบบสอบถาม" data-en="Questionnaire">แบบสอบถาม</h1>
            <p data-th="กรุณากรอกข้อมูลด้านล่าง" data-en="Please fill out the form below">กรุณากรอกข้อมูลด้านล่าง</p>
        </div>

        <form method="POST" action="{{ route('questionnaire.store') }}" id="questionnaireForm">
            @csrf
            @if(!empty($agentId))
                <input type="hidden" name="agent_id" value="{{ $agentId }}">
            @endif

            <div class="form-row">
                <div class="form-group">
                    <label data-th="ชื่อ" data-en="First Name">ชื่อ <span class="required">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="@error('first_name') is-invalid @enderror" data-placeholder-th="ชื่อ" data-placeholder-en="First Name" placeholder="ชื่อ">
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label data-th="นามสกุล" data-en="Last Name">นามสกุล <span class="required">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="@error('last_name') is-invalid @enderror" data-placeholder-th="นามสกุล" data-placeholder-en="Last Name" placeholder="นามสกุล">
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label data-th="เบอร์โทรศัพท์" data-en="Phone">เบอร์โทรศัพท์ <span class="required">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="@error('phone') is-invalid @enderror" data-placeholder-th="เบอร์โทรศัพท์" data-placeholder-en="Phone Number" placeholder="เบอร์โทรศัพท์">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label data-th="อีเมล" data-en="Email">อีเมล</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="@error('email') is-invalid @enderror" data-placeholder-th="อีเมล" data-placeholder-en="Email" placeholder="อีเมล">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group" style="max-width: 120px;">
                <label data-th="อายุ" data-en="Age">อายุ</label>
                <input type="number" name="age" value="{{ old('age') }}" class="@error('age') is-invalid @enderror" data-placeholder-th="อายุ" data-placeholder-en="Age" placeholder="อายุ" min="1" max="150">
                @error('age')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="source-section">
                <label data-th="ท่านทราบข่าวโครงการจากช่องทางใด?" data-en="How did you hear about us?">ท่านทราบข่าวโครงการจากช่องทางใด? <span class="required" style="color: var(--error);">*</span></label>
                @error('source')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid">
                    @php
                        $sources = [
                            'facebook'  => ['th' => 'Facebook',       'en' => 'Facebook'],
                            'google'    => ['th' => 'Google',         'en' => 'Google'],
                            'website'   => ['th' => 'เว็บไซต์',         'en' => 'Website'],
                            'line'      => ['th' => 'LINE',           'en' => 'LINE'],
                            'agent'     => ['th' => 'ตัวแทน/นายหน้า',   'en' => 'Agent'],
                            'friend'    => ['th' => 'เพื่อน/คนรู้จัก',    'en' => 'Friend'],
                            'billboard' => ['th' => 'ป้ายโฆษณา',       'en' => 'Billboard'],
                            'event'     => ['th' => 'งานอีเวนต์',       'en' => 'Event'],
                            'other'     => ['th' => 'อื่นๆ',            'en' => 'Other'],
                        ];
                    @endphp
                    @foreach ($sources as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="source" id="source_{{ $value }}" value="{{ $value }}" {{ old('source') == $value ? 'checked' : '' }}>
                            <label for="source_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                    <div class="other-input-wrapper {{ old('source') == 'other' ? 'show' : '' }}" id="otherInputWrapper">
                        <input type="text" name="source_other" value="{{ old('source_other') }}" data-placeholder-th="กรุณาระบุ..." data-placeholder-en="Please specify..." placeholder="กรุณาระบุ..." class="@error('source_other') is-invalid @enderror">
                        @error('source_other')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn" data-th="ส่งแบบสอบถาม" data-en="Submit">ส่งแบบสอบถาม</button>
        </form>
    </div>

    <script>
        // Source "other" toggle
        document.querySelectorAll('input[name="source"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const wrapper = document.getElementById('otherInputWrapper');
                if (this.value === 'other') {
                    wrapper.classList.add('show');
                    wrapper.querySelector('input').focus();
                } else {
                    wrapper.classList.remove('show');
                }
            });
        });

        // Language switcher
        const langBtns = document.querySelectorAll('.lang-btn');
        langBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const lang = this.dataset.lang;
                langBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Update all [data-th] / [data-en] text elements
                document.querySelectorAll('[data-' + lang + ']').forEach(el => {
                    // For labels with required asterisk, preserve the <span>
                    const requiredSpan = el.querySelector('.required');
                    const text = el.getAttribute('data-' + lang);
                    if (requiredSpan) {
                        el.textContent = text + ' ';
                        el.appendChild(requiredSpan);
                    } else {
                        el.textContent = text;
                    }
                });

                // Update placeholders
                document.querySelectorAll('[data-placeholder-' + lang + ']').forEach(el => {
                    el.placeholder = el.getAttribute('data-placeholder-' + lang);
                });

                document.documentElement.lang = lang;
            });
        });
    </script>
</body>
</html>
