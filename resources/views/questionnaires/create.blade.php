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
            max-width: 840px;
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

        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 30px;
            flex: 1;
        }

        .form-group label {
            display: block;
            font-size: 20px;
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
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            color: var(--text-dark);
            transition: border-color 0.2s;
            outline: none;
            background: var(--white);
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary);
        }

        .form-group input.is-invalid,
        .form-group select.is-invalid {
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
            font-size: 20px;
            font-weight: 500;
            color: var(--text-mid);
            margin-bottom: 0.75rem;
        }

        .radio-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .radio-grid.single-col {
            grid-template-columns: 1fr;
        }

        .radio-option {
            position: relative;
        }

        .radio-option input[type="radio"],
        .radio-option input[type="checkbox"] {
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
            font-size: 16px;
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

        /* Checkbox style (square) */
        .radio-option input[type="checkbox"] + label::before {
            border-radius: 4px;
        }

        .radio-option input[type="radio"]:checked + label,
        .radio-option input[type="checkbox"]:checked + label {
            border-color: var(--primary);
            background-color: rgba(42, 139, 146, 0.05);
            color: var(--primary);
        }

        .radio-option input[type="radio"]:checked + label::before {
            border-color: var(--primary);
            background: var(--primary);
            box-shadow: inset 0 0 0 3px var(--white);
        }

        .radio-option input[type="checkbox"]:checked + label::before {
            border-color: var(--primary);
            background: var(--primary);
            box-shadow: inset 0 0 0 2px var(--white);
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

        .inline-input {
            display: inline-block;
            width: 60px;
            padding: 0.3rem 0.5rem;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            color: var(--text-dark);
            outline: none;
            text-align: center;
            transition: border-color 0.2s;
        }

        .inline-input:focus {
            border-color: var(--primary);
        }

        .children-input-wrapper {
            margin-top: 0.5rem;
            grid-column: 1 / -1;
            display: none;
            padding: 0.6rem 0.85rem;
            font-size: 14px;
            color: var(--text-mid);
        }

        .children-input-wrapper.show {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .address-grid .form-group {
            margin-bottom: 0;
        }

        .address-grid .full-width {
            grid-column: 1 / -1;
        }
        @media (max-width: 767px) {
            .source-section > label {
                font-size: 18px; 
            }
            .radio-option label {
                font-size: 16px;
            }
            .form-header {
                margin-top: 30px;
            }
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

            .address-grid {
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

            {{-- ===== ชื่อ-นามสกุล (ช่องเดียว) ===== --}}
            <div class="form-group">
                <label data-th="ชื่อ-นามสกุล" data-en="Name-Surname">ชื่อ-นามสกุล <span class="required">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" class="@error('full_name') is-invalid @enderror" data-placeholder-th="ชื่อ-นามสกุล" data-placeholder-en="Name-Surname" placeholder="ชื่อ-นามสกุล">
                @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ===== เพศ ===== --}}
            <div class="source-section">
                <label data-th="กรุณาระบุเพศของคุณ" data-en="Please specify your gender">กรุณาระบุเพศของคุณ<span class="required" style="color: var(--error);">*</span></label>
                @error('gender')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid">
                    @php
                        $genders = [
                            'male'   => ['th' => 'ชาย', 'en' => 'Male'],
                            'female' => ['th' => 'หญิง', 'en' => 'Female'],
                            'other'  => ['th' => 'อื่นๆ', 'en' => 'Other'],
                        ];
                    @endphp
                    @foreach ($genders as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="gender" id="gender_{{ $value }}" value="{{ $value }}" {{ old('gender') == $value ? 'checked' : '' }}>
                            <label for="gender_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                    <div class="other-input-wrapper {{ old('gender') == 'other' ? 'show' : '' }}" id="genderOtherWrapper">
                        <input type="text" name="gender_other" value="{{ old('gender_other') }}" data-placeholder-th="กรุณาระบุ..." data-placeholder-en="Please specify..." placeholder="กรุณาระบุ..." class="@error('gender_other') is-invalid @enderror">
                        @error('gender_other')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ===== ที่อยู่ปัจจุบัน ===== --}}
            <div class="source-section">
                <label class="section-title" data-th="ที่อยู่ปัจจุบัน" data-en="Current Address">ที่อยู่ปัจจุบัน</label>
                <div class="address-grid">
                    <div class="form-group">
                        <label data-th="บ้านเลขที่" data-en="House number">บ้านเลขที่</label>
                        <input type="text" name="address_house_no" value="{{ old('address_house_no') }}" data-placeholder-th="บ้านเลขที่" data-placeholder-en="House No." placeholder="บ้านเลขที่">
                    </div>
                    <div class="form-group">
                        <label data-th="ถนน" data-en="Street">ถนน</label>
                        <input type="text" name="address_street" value="{{ old('address_street') }}" data-placeholder-th="ถนน" data-placeholder-en="Street" placeholder="ถนน">
                    </div>
                    <div class="form-group">
                        <label data-th="แขวง/ตำบล" data-en="Subdistrict/Tambon">แขวง/ตำบล</label>
                        <input type="text" name="address_subdistrict" value="{{ old('address_subdistrict') }}" data-placeholder-th="แขวง/ตำบล" data-placeholder-en="Sub-district" placeholder="แขวง/ตำบล">
                    </div>
                    <div class="form-group">
                        <label data-th="เขต/อำเภอ" data-en="District/Amphoe">เขต/อำเภอ</label>
                        <input type="text" name="address_district" value="{{ old('address_district') }}" data-placeholder-th="เขต/อำเภอ" data-placeholder-en="District" placeholder="เขต/อำเภอ">
                    </div>
                    <div class="form-group">
                        <label data-th="จังหวัด" data-en="Province">จังหวัด</label>
                        <input type="text" name="address_province" value="{{ old('address_province') }}" data-placeholder-th="จังหวัด" data-placeholder-en="Province" placeholder="จังหวัด">
                    </div>
                    <div class="form-group">
                        <label data-th="รหัสไปรษณีย์" data-en="Postal Code">รหัสไปรษณีย์</label>
                        <input type="text" name="address_postal_code" value="{{ old('address_postal_code') }}" data-placeholder-th="รหัสไปรษณีย์" data-placeholder-en="Postal Code" placeholder="รหัสไปรษณีย์">
                    </div>
                    <div class="form-group full-width">
                        <label data-th="ประเทศ (กรณีชาวต่างชาติ)" data-en="Country (for foreign nationals)">ประเทศ (กรณีชาวต่างชาติ)</label>
                        <input type="text" name="address_country" value="{{ old('address_country') }}" data-placeholder-th="ประเทศ" data-placeholder-en="Country" placeholder="ประเทศ">
                    </div>
                </div>
            </div>

            {{-- ===== เบอร์โทร & อีเมล ===== --}}
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

            {{-- ===== อายุ (radio) ===== --}}
            <div class="source-section">
                <label data-th="อายุ" data-en="Age">อายุ <span class="required" style="color: var(--error);">*</span></label>
                @error('age_range')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid">
                    @php
                        $ageRanges = [
                            'under_25' => ['th' => 'ไม่เกิน 25 ปี', 'en' => 'Less than 25 years old'],
                            '26_30'    => ['th' => '26-30 ปี',       'en' => '26-30 years old'],
                            '31_35'    => ['th' => '31-35 ปี',       'en' => '31-35 years old'],
                            '36_40'    => ['th' => '36-40 ปี',       'en' => '36-40 years old'],
                            '41_45'    => ['th' => '41-45 ปี',       'en' => '41-45 years old'],
                            '46_50'    => ['th' => '46-50 ปี',       'en' => '46-50 years old'],
                            '51_55'    => ['th' => '51-55 ปี',       'en' => '51-55 years old'],
                            '56_60'    => ['th' => '56-60 ปี',       'en' => '56-60 years old'],
                            'over_61'  => ['th' => '61 ปีขึ้นไป',   'en' => '61 years and older'],
                        ];
                    @endphp
                    @foreach ($ageRanges as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="age_range" id="age_{{ $value }}" value="{{ $value }}" {{ old('age_range') == $value ? 'checked' : '' }}>
                            <label for="age_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== สถานภาพสมรส ===== --}}
            <div class="source-section">
                <label data-th="ขอทราบสถานภาพสมรสของคุณในปัจจุบัน" data-en="Please tell me your current marital status ">ขอทราบสถานภาพสมรสของคุณในปัจจุบัน <span class="required" style="color: var(--error);">*</span></label>
                @error('marital_status')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid">
                    @php
                        $maritalStatuses = [
                            'single'  => ['th' => 'โสด',  'en' => 'Single'],
                            'married' => ['th' => 'สมรส', 'en' => 'Married'],
                        ];
                    @endphp
                    @foreach ($maritalStatuses as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="marital_status" id="marital_{{ $value }}" value="{{ $value }}" {{ old('marital_status') == $value ? 'checked' : '' }}>
                            <label for="marital_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                    <div class="children-input-wrapper {{ old('marital_status') == 'married' ? 'show' : '' }}" id="childrenWrapper">
                        <span data-th="จำนวนบุตร" data-en="Number of children">จำนวนบุตร</span>
                        <input type="number" name="children_count" value="{{ old('children_count', 0) }}" min="0" max="20" class="inline-input">
                        <span data-th="คน" data-en="person(s)">คน</span>
                    </div>
                </div>
            </div>

            {{-- ===== รายได้ครอบครัวต่อเดือน ===== --}}
            <div class="source-section">
                <label data-th="กรุณาระบุรายได้ครอบครัวต่อเดือน (รวมรายได้สมาชิกทุกคนในบ้าน)" data-en="Monthly household income (combined income of all members)">กรุณาระบุรายได้ครอบครัวต่อเดือน (รวมรายได้สมาชิกทุกคนในบ้าน) <span class="required" style="color: var(--error);">*</span></label>
                @error('household_income')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid single-col">
                    @php
                        $incomeRanges = [
                            'under_40k'   => ['th' => 'น้อยกว่า 40,000 บาท',          'en' => 'Less than 40,000 baht'],
                            '40k_60k'     => ['th' => '40,000–60,000 บาท',         'en' => '40,000–60,000 baht'],
                            '60k_80k'     => ['th' => '60,001–80,000 บาท',         'en' => '60,001–80,000 baht'],
                            '80k_100k'    => ['th' => '80,001 - 100,000 บาท',        'en' => '80,001 - 100,000 baht'],
                            '100k_120k'   => ['th' => '100,001 - 120,000 บาท',       'en' => '100,001 - 120,000 baht'],
                            '120k_140k'   => ['th' => '120,001 - 140,000 บาท',          'en' => '120,001 - 140,000 baht'],
                            '140k_180k'   => ['th' => '140,001 - 180,000 บาท',          'en' => '140,001 - 180,000 baht'],
                            '180k_200k'   => ['th' => '180,001 - 200,000 บาท',          'en' => '180,001 - 200,000 baht'],
                            '200k_220k'   => ['th' => '200,001 - 220,000 บาท',          'en' => '200,001 - 220,000 baht'],
                            'over_220k'   => ['th' => 'มากกว่า 220,000 บาท ขึ้นไป',          'en' => 'More than 220,000 baht'],
                        ];
                    @endphp
                    @foreach ($incomeRanges as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="household_income" id="income_{{ $value }}" value="{{ $value }}" {{ old('household_income') == $value ? 'checked' : '' }}>
                            <label for="income_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== เหตุผลหลักที่เข้าชมโครงการ (เลือกได้หลายข้อ) ===== --}}
            <div class="source-section">
                <label data-th="เหตุผลหลักที่ท่านตัดสินใจเข้าชมโครงการในวันนี้ (ระบุได้มากกว่า 1 ข้อ)" data-en="What are the main reasons you decided to visit the project today? (You can select more than one answer)">เหตุผลหลักที่ท่านตัดสินใจเข้าชมโครงการในวันนี้ (ระบุได้มากกว่า 1 ข้อ) <span class="required" style="color: var(--error);">*</span></label>
                @error('visit_reasons')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid single-col">
                    @php
                        $visitReasons = [
                            'price'       => ['th' => 'ช่วงราคาของห้องชุด/บ้าน',          'en' => 'Price range of condominium/house'],
                            'promotion'   => ['th' => 'Promotionของโครงการ ในช่วงนี้',                'en' => 'Promotion for the project during this period'],
                            'design_room'      => ['th' => 'Design รูปแบบห้องชุด/บ้าน',   'en' => 'Condominium/house design'],
                            'design_entrance'   => ['th' => 'Design ทางเข้าโครงการ',   'en' => 'Project entrance design'],
                            'location'    => ['th' => 'ทำเลที่ตั้งโครงการ',               'en' => 'Project location'],
                            'other'       => ['th' => 'อื่นๆ',                          'en' => 'Other'],
                        ];
                    @endphp
                    @foreach ($visitReasons as $value => $labels)
                        <div class="radio-option">
                            <input type="checkbox" name="visit_reasons[]" id="visit_{{ $value }}" value="{{ $value }}" {{ is_array(old('visit_reasons')) && in_array($value, old('visit_reasons')) ? 'checked' : '' }}>
                            <label for="visit_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                    <div class="other-input-wrapper {{ is_array(old('visit_reasons')) && in_array('other', old('visit_reasons')) ? 'show' : '' }}" id="visitOtherWrapper">
                        <input type="text" name="visit_reasons_other" value="{{ old('visit_reasons_other') }}" data-placeholder-th="กรุณาระบุ..." data-placeholder-en="Please specify..." placeholder="กรุณาระบุ..." class="@error('visit_reasons_other') is-invalid @enderror">
                        @error('visit_reasons_other')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ===== รายการ Promotion ที่ต้องการ (เลือกได้หลายข้อ) ===== --}}
            <div class="source-section">
                <label data-th="รายการ Promotion ที่ท่านต้องการ (ระบุได้มากกว่า 1 ข้อ)" data-en="List of promotions you are interested in (You can select more than one answer)">รายการ Promotion ที่ท่านต้องการ (ระบุได้มากกว่า 1 ข้อ)</label>
                @error('promotions')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid single-col">
                    @php
                        $promotions = [
                            'special_financial_conditions'    => ['th' => 'เงื่อนไขพิเศษทางการเงิน',            'en' => 'Special financial conditions'],
                            'free_appliance'   => ['th' => 'ฟรีเครื่องใช้ไฟฟ้า',     'en' => 'Free electrical appliances'],
                            'free_aircon' => ['th' => 'ฟรีแอร์',            'en' => 'Free air conditioning'],
                            'free_curtain' => ['th' => 'ฟรีผ้าม่าน/วอลเปเปอร์',            'en' => 'Free curtains/wallpaper'],
                            'discount' => ['th' => 'บัตรกำนัลส่วนลด',            'en' => 'Discount voucher'],
                            'free_furniture' => ['th' => 'เฟอร์นิเจอร์ครบชุด (Fully furnished)',            'en' => 'Fully furnished furniture'],
                            'free_fitted' => ['th' => 'เฟอร์นิเจอร์ลอยตัว (Fully fitted)',            'en' => 'Fully fitted furniture'],
                            'other'       => ['th' => 'อื่นๆ',                  'en' => 'Other'],
                        ];
                    @endphp
                    @foreach ($promotions as $value => $labels)
                        <div class="radio-option">
                            <input type="checkbox" name="promotions[]" id="promo_{{ $value }}" value="{{ $value }}" {{ is_array(old('promotions')) && in_array($value, old('promotions')) ? 'checked' : '' }}>
                            <label for="promo_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                    <div class="other-input-wrapper {{ is_array(old('promotions')) && in_array('other', old('promotions')) ? 'show' : '' }}" id="promoOtherWrapper">
                        <input type="text" name="promotions_other" value="{{ old('promotions_other') }}" data-placeholder-th="กรุณาระบุ..." data-placeholder-en="Please specify..." placeholder="กรุณาระบุ..." class="@error('promotions_other') is-invalid @enderror">
                        @error('promotions_other')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ===== ทราบข่าวโครงการจากช่องทางใด ===== --}}
            <div class="source-section">
                <label data-th="ท่านทราบข่าวโครงการจากแหล่งใด (ระบุได้มากกว่า 1 ข้อ)" data-en="How did you hear about the project? (You can select more than one answer)">ท่านทราบข่าวโครงการจากแหล่งใด (ระบุได้มากกว่า 1 ข้อ) <span class="required" style="color: var(--error);">*</span></label>
                @error('source')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid">
                    @php
                        $sources = [
                            'pass_by'      => ['th' => 'ผ่านหน้าโครงการ',                   'en' => 'Passing by the project site / Site Signboard'],
                            'edm'          => ['th' => 'อีเมล์',                             'en' => 'Electronic Direct Mail'],
                            'sms'          => ['th' => 'SMS / ข้อความโทรศัพท์',               'en' => 'Short Message Service'],
                            'friend'       => ['th' => 'เพื่อน หรือญาติแนะนำ',                'en' => 'Friends or Relatives recommended'],
                            'website'      => ['th' => 'เว็บไซต์',                            'en' => 'Website'],
                            'billboard'    => ['th' => 'ป้ายโฆษณา โปรดระบุสถานที่',            'en' => 'Billboard (please specify location)'],
                            'online_media' => ['th' => 'สื่อออนไลน์ โปรดระบุช่องทาง',          'en' => 'Online Media (please specify channel)'],
                            'other'        => ['th' => 'อื่นๆ',                              'en' => 'Other'],
                        ];
                    @endphp
                    @foreach ($sources as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="source" id="source_{{ $value }}" value="{{ $value }}" {{ old('source') == $value ? 'checked' : '' }}>
                            <label for="source_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                    <div class="other-input-wrapper {{ old('source') == 'billboard' ? 'show' : '' }}" id="sourceBillboardWrapper">
                        <input type="text" name="source_billboard_detail" value="{{ old('source_billboard_detail') }}" data-placeholder-th="ระบุสถานที่ป้ายโฆษณา..." data-placeholder-en="Specify billboard location..." placeholder="ระบุสถานที่ป้ายโฆษณา..." class="@error('source_billboard_detail') is-invalid @enderror">
                        @error('source_billboard_detail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="other-input-wrapper {{ old('source') == 'online_media' ? 'show' : '' }}" id="sourceOnlineMediaWrapper">
                        <input type="text" name="source_online_media_detail" value="{{ old('source_online_media_detail') }}" data-placeholder-th="ระบุช่องทางสื่อออนไลน์..." data-placeholder-en="Specify online media channel..." placeholder="ระบุช่องทางสื่อออนไลน์..." class="@error('source_online_media_detail') is-invalid @enderror">
                        @error('source_online_media_detail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="other-input-wrapper {{ old('source') == 'other' ? 'show' : '' }}" id="sourceOtherWrapper">
                        <input type="text" name="source_other" value="{{ old('source_other') }}" data-placeholder-th="กรุณาระบุ..." data-placeholder-en="Please specify..." placeholder="กรุณาระบุ..." class="@error('source_other') is-invalid @enderror">
                        @error('source_other')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ===== งบประมาณ (ล้านบาท) ===== --}}
            <div class="source-section">
                <label data-th="งบประมาณสำหรับการซื้อที่อยู่อาศัยใหม่ (ล้านบาท)" data-en="Budget for purchasing a new home (in million baht)">งบประมาณสำหรับการซื้อที่อยู่อาศัยใหม่ (ล้านบาท) <span class="required" style="color: var(--error);">*</span></label>
                @error('budget')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid single-col">
                    @php
                        $budgets = [
                            'under_1m'  => ['th' => 'น้อยกว่า 1 ล้านบาท',       'en' => 'Less than 1 million baht'],
                            '1m_2m'     => ['th' => '1.1-2 ล้านบาท',          'en' => '1.1-2 million baht'],
                            '2m_3m'     => ['th' => '2.1-3 ล้านบาท',          'en' => '2.1-3 million baht'],
                            '3m_4m'    => ['th' => '3.1-4 ล้านบาท',         'en' => '3.1-4 million baht'],
                            '4m_5m'    => ['th' => '4.1-5 ล้านบาท',         'en' => '4.1-5 million baht'],
                            '5m_6m'    => ['th' => '5.1-6 ล้านบาท',         'en' => '5.1-6 million baht'],
                            'over_6m'  => ['th' => 'มากกว่า 6 ล้านบาท',      'en' => 'More than 6 million baht'],
                        ];
                    @endphp
                    @foreach ($budgets as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="budget" id="budget_{{ $value }}" value="{{ $value }}" {{ old('budget') == $value ? 'checked' : '' }}>
                            <label for="budget_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== วัตถุประสงค์ซื้อ ===== --}}
            <div class="source-section">
                <label data-th="วัตถุประสงค์ของคุณในการซื้อห้องชุด/บ้านที่โครงการนี้คืออะไร" data-en="What is your purpose in purchasing a condo/house at this project?">วัตถุประสงค์ของคุณในการซื้อห้องชุด/บ้านที่โครงการนี้คืออะไร <span class="required" style="color: var(--error);">*</span></label>
                @error('purchase_purpose')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid single-col">
                    @php
                        $purposes = [
                            'self_use'   => ['th' => 'ซื้อเพื่ออยู่เอง',    'en' => 'Bought for personal use'],
                            'investment' => ['th' => 'ซื้อเพื่อลงทุน',     'en' => 'Purchased for investment purposes'],
                            'other'      => ['th' => 'อื่นๆ',             'en' => 'Other'],
                        ];
                    @endphp
                    @foreach ($purposes as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="purchase_purpose" id="purpose_{{ $value }}" value="{{ $value }}" {{ old('purchase_purpose') == $value ? 'checked' : '' }}>
                            <label for="purpose_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                    <div class="other-input-wrapper {{ old('purchase_purpose') == 'other' ? 'show' : '' }}" id="purposeOtherWrapper">
                        <input type="text" name="purchase_purpose_other" value="{{ old('purchase_purpose_other') }}" data-placeholder-th="กรุณาระบุ..." data-placeholder-en="Please specify..." placeholder="กรุณาระบุ..." class="@error('purchase_purpose_other') is-invalid @enderror">
                        @error('purchase_purpose_other')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ===== วางแผนทางการเงิน ===== --}}
            <div class="source-section">
                <label data-th="ท่านวางแผนทางการเงินในการซื้อห้องชุด/บ้านที่โครงการนี้ด้วยวิธีใด" data-en="How do you plan to finance your purchase of a unit/house in this project?">ท่านวางแผนทางการเงินในการซื้อห้องชุด/บ้านที่โครงการนี้ด้วยวิธีใด <span class="required" style="color: var(--error);">*</span></label>
                @error('finance_plan')
                    <div class="invalid-feedback" style="margin-bottom: 0.5rem;">{{ $message }}</div>
                @enderror
                <div class="radio-grid single-col">
                    @php
                        $financePlans = [
                            'cash'       => ['th' => 'เงินสด เป็นเงินเก็บที่มีอยู่แล้ว',                    'en' => 'Purchased with cash, using existing savings'],
                            'bank_loan'  => ['th' => 'ขอสินเชื่อจากธนาคาร โดยดำเนินการด้วยตนเอง',              'en' => 'Apply for a loan from the bank by doing it yourself'],
                            'installment'=> ['th' => 'ขอสินเชื่อจากธนาคาร โดยให้โครงการดำเนินการให้',              'en' => 'Apply for a loan from the bank, with the project handling the process'],
                        ];
                    @endphp
                    @foreach ($financePlans as $value => $labels)
                        <div class="radio-option">
                            <input type="radio" name="finance_plan" id="finance_{{ $value }}" value="{{ $value }}" {{ old('finance_plan') == $value ? 'checked' : '' }}>
                            <label for="finance_{{ $value }}" data-th="{{ $labels['th'] }}" data-en="{{ $labels['en'] }}">{{ $labels['th'] }}</label>
                        </div>
                    @endforeach
                    <div class="other-input-wrapper {{ old('finance_plan') == 'other' ? 'show' : '' }}" id="financeOtherWrapper">
                        <input type="text" name="finance_plan_other" value="{{ old('finance_plan_other') }}" data-placeholder-th="กรุณาระบุ..." data-placeholder-en="Please specify..." placeholder="กรุณาระบุ..." class="@error('finance_plan_other') is-invalid @enderror">
                        @error('finance_plan_other')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn" data-th="ส่งแบบสอบถาม" data-en="Submit">ส่งแบบสอบถาม</button>
        </form>
    </div>

    <script>
        // Helper: toggle "other" input for radio groups
        function setupRadioOther(radioName, wrapperId) {
            document.querySelectorAll('input[name="' + radioName + '"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const wrapper = document.getElementById(wrapperId);
                    if (this.value === 'other') {
                        wrapper.classList.add('show');
                        wrapper.querySelector('input').focus();
                    } else {
                        wrapper.classList.remove('show');
                    }
                });
            });
        }

        // Helper: toggle "other" input for checkbox groups
        function setupCheckboxOther(checkboxName, wrapperId) {
            document.querySelectorAll('input[name="' + checkboxName + '"]').forEach(cb => {
                cb.addEventListener('change', function() {
                    const wrapper = document.getElementById(wrapperId);
                    const otherCb = document.getElementById(this.id);
                    if (this.value === 'other') {
                        if (this.checked) {
                            wrapper.classList.add('show');
                            wrapper.querySelector('input').focus();
                        } else {
                            wrapper.classList.remove('show');
                        }
                    }
                });
            });
        }

        // Source radio: toggle billboard / online_media / other input
        document.querySelectorAll('input[name="source"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('sourceBillboardWrapper').classList.toggle('show', this.value === 'billboard');
                document.getElementById('sourceOnlineMediaWrapper').classList.toggle('show', this.value === 'online_media');
                document.getElementById('sourceOtherWrapper').classList.toggle('show', this.value === 'other');
                const activeWrapper = document.querySelector('#sourceBillboardWrapper.show, #sourceOnlineMediaWrapper.show, #sourceOtherWrapper.show');
                if (activeWrapper) activeWrapper.querySelector('input').focus();
            });
        });

        // Setup all "other" toggles
        setupRadioOther('gender', 'genderOtherWrapper');
        setupRadioOther('purchase_purpose', 'purposeOtherWrapper');
        setupRadioOther('finance_plan', 'financeOtherWrapper');
        setupCheckboxOther('visit_reasons[]', 'visitOtherWrapper');
        setupCheckboxOther('promotions[]', 'promoOtherWrapper');

        // Marital status → show children count
        document.querySelectorAll('input[name="marital_status"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const wrapper = document.getElementById('childrenWrapper');
                if (this.value === 'married') {
                    wrapper.classList.add('show');
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
