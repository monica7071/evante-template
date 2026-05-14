<?php

namespace App\Services\AI;

class MockAgentService
{
    private function contains(string $text, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }
        return false;
    }

    public function respond(string $message, ?string $imageUrl = null): array
    {
        usleep(rand(900000, 1700000)); // 0.9–1.7s delay

        if ($imageUrl) {
            return $this->imageResponse();
        }

        $lower = mb_strtolower($message);

        if ($this->contains($lower, ['สวัสดี', 'hello', 'hi', 'หวัดดี', 'hey', 'ดีครับ', 'ดีค่ะ', 'ยินดี'])) {
            return $this->greetingResponse();
        }

        if ($this->contains($lower, ['ห้อง', 'unit', 'room', 'ยูนิต', 'คอนโด', 'ห้องชุด', 'ห้องว่าง', 'พื้นที่'])) {
            return $this->roomResponse();
        }

        if ($this->contains($lower, ['ราคา', 'price', 'เท่าไหร่', 'เท่าไร', 'cost', 'budget', 'งบ', 'แพง', 'ถูก', 'มูลค่า'])) {
            return $this->priceResponse();
        }

        if ($this->contains($lower, ['นัด', 'appointment', 'ชม', 'visit', 'ดูห้อง', 'schedule', 'เวลา', 'จอง'])) {
            return $this->appointmentResponse();
        }

        if ($this->contains($lower, ['คำนวณ', 'สินเชื่อ', 'ผ่อน', 'loan', 'กู้', 'mortgage', 'ดาวน์', 'เงินกู้', 'ธนาคาร'])) {
            return $this->loanResponse($message);
        }

        if ($this->contains($lower, ['โปรโมชั่น', 'promotion', 'ส่วนลด', 'discount', 'offer', 'deal', 'พิเศษ', 'สิทธิ์'])) {
            return $this->promotionResponse();
        }

        if ($this->contains($lower, ['ที่ตั้ง', 'location', 'ทำเล', 'bts', 'mrt', 'ใกล้', 'ห่าง', 'เดินทาง', 'รถไฟฟ้า'])) {
            return $this->locationResponse();
        }

        if ($this->contains($lower, ['สิ่งอำนวย', 'facility', 'amenity', 'สระ', 'pool', 'ฟิตเนส', 'gym', 'สวน', 'parking', 'จอดรถ'])) {
            return $this->facilityResponse();
        }

        return $this->defaultResponse();
    }

    private function imageResponse(): array
    {
        return [
            'text' => "ขอบคุณสำหรับรูปภาพครับ 🖼️\n\nผมได้รับรูปภาพของคุณแล้ว สามารถช่วยประเมินหรืออธิบายเพิ่มเติมได้ครับ\n\nต้องการให้ช่วยเรื่องอะไรต่อไปครับ?",
            'quick_replies' => ['ประเมินราคา', 'ดูห้องที่คล้ายกัน', 'ติดต่อเจ้าหน้าที่'],
        ];
    }

    private function greetingResponse(): array
    {
        return [
            'text' => "สวัสดีครับ! 👋 ยินดีต้อนรับสู่ **Evante Property Assistant**\n\nผมเป็น AI ผู้ช่วยด้านอสังหาริมทรัพย์ พร้อมช่วยคุณในทุกเรื่อง:\n\n- 🏠 **ค้นหาห้อง** — ขนาด, ชั้น, วิว, ประเภท\n- 💰 **ราคาและโปรโมชั่น** — ราคา, ส่วนลด, ข้อเสนอพิเศษ\n- 📅 **นัดชมห้อง** — จองเวลาพบเจ้าหน้าที่\n- 🏦 **คำนวณสินเชื่อ** — ยอดผ่อน, เงินดาวน์\n- 📍 **ทำเลและสิ่งอำนวยความสะดวก**\n\nวันนี้ต้องการให้ช่วยเรื่องอะไรครับ?",
            'quick_replies' => ['ดูห้องว่าง', 'โปรโมชั่น', 'นัดชมห้อง', 'คำนวณสินเชื่อ'],
        ];
    }

    private function roomResponse(): array
    {
        return [
            'text' => "มีห้องว่างหลายแบบให้เลือกครับ 🏢\n\n**ห้องที่น่าสนใจขณะนี้:**\n\n| รหัสห้อง | ขนาด | ชั้น | ราคา | สถานะ |\n|---------|------|------|------|-------|\n| A-1205 | 35 ตร.ม. | 12 | ฿3,200,000 | ว่าง |\n| B-0803 | 48 ตร.ม. | 8 | ฿4,500,000 | ว่าง |\n| C-1501 | 65 ตร.ม. | 15 | ฿6,800,000 | ว่าง |\n| D-0210 | 28 ตร.ม. | 2 | ฿2,400,000 | จอง |\n\n**ประเภทห้องทั้งหมด:**\n- 🏠 **Studio** (25–30 ตร.ม.) — เริ่ม 2.2 ล้าน\n- 🛏️ **1 Bedroom** (35–45 ตร.ม.) — เริ่ม 3.0 ล้าน\n- 🛏️🛏️ **2 Bedrooms** (55–70 ตร.ม.) — เริ่ม 5.5 ล้าน\n\nต้องการดูห้องแบบไหนครับ?",
            'quick_replies' => ['Studio', '1 ห้องนอน', '2 ห้องนอน', 'นัดชมห้อง'],
        ];
    }

    private function priceResponse(): array
    {
        return [
            'text' => "ข้อมูลราคาห้องชุดครับ 💰\n\n**ช่วงราคาตามขนาด:**\n\n🏠 **Studio** (25–30 ตร.ม.)\nราคา **2.2 – 2.8 ล้านบาท** (~฿88,000/ตร.ม.)\n\n🛏️ **1 Bedroom** (35–45 ตร.ม.)\nราคา **3.0 – 4.5 ล้านบาท** (~฿90,000/ตร.ม.)\n\n🛏️🛏️ **2 Bedrooms** (55–70 ตร.ม.)\nราคา **5.5 – 7.5 ล้านบาท** (~฿95,000/ตร.ม.)\n\n> ⚡ ชั้นสูงและวิวดีราคาจะสูงขึ้น 5–15%\n> 💡 ราคาพิเศษสำหรับการจองในเดือนนี้!\n\nสนใจงบประมาณเท่าไหร่ครับ?",
            'quick_replies' => ['งบไม่เกิน 3 ล้าน', 'งบ 3–5 ล้าน', 'งบ 5 ล้านขึ้นไป', 'คำนวณสินเชื่อ'],
        ];
    }

    private function appointmentResponse(): array
    {
        return [
            'text' => "ยินดีจัดการนัดชมห้องให้ครับ 📅\n\n**ขั้นตอนการนัดชม:**\n\n1️⃣ แจ้งห้องที่สนใจ (รหัส/ประเภท/ชั้น)\n2️⃣ เลือกวันและเวลาที่สะดวก\n3️⃣ เจ้าหน้าที่ยืนยันนัดหมายทาง SMS/Line\n4️⃣ พบกัน ณ สำนักงานขาย โครงการ Evante\n\n**เวลาทำการ:**\n🕘 จันทร์ – ศุกร์: 09:00 – 18:00 น.\n🕘 เสาร์ – อาทิตย์: 10:00 – 17:00 น.\n\n**ที่อยู่:** ซอยสุขุมวิท 24 กรุงเทพฯ\n📞 โทร: 02-XXX-XXXX\n\nต้องการนัดชมวันไหนครับ?",
            'quick_replies' => ['วันนี้', 'พรุ่งนี้', 'เสาร์-อาทิตย์', 'เลือกวันเอง'],
        ];
    }

    private function loanResponse(string $message): array
    {
        preg_match('/(\d[\d,.]*)(\s*)(ล้าน|ลบ\.?|บาท)?/u', $message, $m);
        $price = 3500000;
        if (!empty($m[1])) {
            $num = (float) str_replace(',', '', $m[1]);
            if (!empty($m[3]) && str_contains($m[3], 'ล้าน')) {
                $num *= 1000000;
            }
            if ($num >= 500000) {
                $price = (int) $num;
            }
        }

        $down10 = $price * 0.1;
        $down20 = $price * 0.2;
        $loan10 = $price - $down10;
        $loan20 = $price - $down20;

        $rate = 0.065 / 12;
        $n30 = 360;
        $n20 = 240;

        $monthly30 = $loan10 * $rate * pow(1 + $rate, $n30) / (pow(1 + $rate, $n30) - 1);
        $monthly20 = $loan20 * $rate * pow(1 + $rate, $n20) / (pow(1 + $rate, $n20) - 1);

        return [
            'text' => sprintf(
                "คำนวณสินเชื่อเบื้องต้นครับ 🏦\n\n**ราคาห้อง:** ฿%s\n\n**ตัวอย่าง Option 1 — ดาวน์ 10%%**\n| รายการ | จำนวน |\n|--------|-------|\n| เงินดาวน์ | ฿%s |\n| ยอดกู้ | ฿%s |\n| ผ่อน 30 ปี (6.5%%) | ฿%s/เดือน |\n\n**ตัวอย่าง Option 2 — ดาวน์ 20%%**\n| รายการ | จำนวน |\n|--------|-------|\n| เงินดาวน์ | ฿%s |\n| ยอดกู้ | ฿%s |\n| ผ่อน 20 ปี (6.5%%) | ฿%s/เดือน |\n\n> ⚠️ ตัวเลขเป็นการประมาณการเบื้องต้น อาจแตกต่างตามเงื่อนไขธนาคารและ Credit Score\n\nต้องการให้เจ้าหน้าที่ประเมินสินเชื่อจริงได้เลยครับ!",
                number_format($price),
                number_format($down10),
                number_format($loan10),
                number_format($monthly30),
                number_format($down20),
                number_format($loan20),
                number_format($monthly20)
            ),
            'quick_replies' => ['คำนวณใหม่', 'ติดต่อธนาคาร', 'นัดปรึกษาเจ้าหน้าที่'],
        ];
    }

    private function promotionResponse(): array
    {
        return [
            'text' => "โปรโมชั่นพิเศษประจำเดือนนี้ 🎉\n\n**🔥 Early Bird Special**\n- ลดสูงสุด **5%** สำหรับ 10 ยูนิตแรก\n- ฟรีชุดเฟอร์นิเจอร์ Built-in (มูลค่า 250,000 บาท)\n\n**🎁 Package Complete**\n- ฟรีที่จอดรถ 1 คัน (มูลค่า 400,000 บาท)\n- ฟรีค่าส่วนกลาง 2 ปี (มูลค่า 60,000 บาท)\n\n**👥 Referral Program**\n- แนะนำเพื่อน รับ Cash Back **฿50,000**\n- ไม่จำกัดจำนวนครั้ง\n\n**💳 Easy Payment**\n- ผ่อน 0% 24 เดือนสำหรับเงินดาวน์\n- ฟรีค่าโอนและค่าจดจำนอง\n\n⏰ **โปรสิ้นสุด:** 31 มีนาคม 2026\n\nสนใจโปรไหนครับ?",
            'quick_replies' => ['Early Bird', 'Package Complete', 'Referral', 'ดูเงื่อนไข'],
        ];
    }

    private function locationResponse(): array
    {
        return [
            'text' => "ทำเลที่ตั้งโครงการครับ 📍\n\n**Evante Residence Sukhumvit**\n📍 ซอยสุขุมวิท 24 แขวงคลองตัน กรุงเทพฯ\n\n**การเดินทาง:**\n🚇 BTS อโศก: **8 นาที** (เดิน)\n🚇 MRT สุขุมวิท: **10 นาที** (เดิน)\n🚗 ทางด่วนศรีรัช: **5 นาที** (รถยนต์)\n🚕 Grab จากสุวรรณภูมิ: **35 นาที**\n\n**สิ่งอำนวยความสะดวกใกล้เคียง:**\n- 🛍️ Terminal 21 (500 ม.)\n- 🏥 โรงพยาบาลกรุงเทพ (1.2 กม.)\n- 🎓 NIST International School (800 ม.)\n- 🌳 สวนสาธารณะ Benchakitti (1.5 กม.)\n- 🏪 Tops Market (200 ม.)\n- 🍽️ ร้านอาหารนานาชาติ (50 ม.)",
            'quick_replies' => ['นัดชมห้อง', 'ดูห้องว่าง', 'สิ่งอำนวยความสะดวก'],
        ];
    }

    private function facilityResponse(): array
    {
        return [
            'text' => "สิ่งอำนวยความสะดวกในโครงการครับ 🏊\n\n**ชั้น 5 — Recreation Floor:**\n- 🏊 สระว่ายน้ำ Infinity (25 ม. + Jacuzzi)\n- 💪 Fitness Center (เปิด 24 ชม.)\n- 🧘 Yoga & Meditation Room\n- 🎮 Game & Lounge Room\n\n**ชั้น G — Ground Floor:**\n- 🚗 ที่จอดรถ 1 คัน/ยูนิต + EV Charger\n- 📦 Parcel Locker 24 ชม.\n- ☕ Co-Working Space\n- 🛡️ รปภ. 24 ชม. + CCTV ทั่วโครงการ\n\n**ชั้น R — Rooftop:**\n- 🌇 Sky Garden & BBQ Area\n- 🔭 360° City View\n\n**บริการเสริม:**\n- 🏠 Housekeeping (เสริม)\n- 🚐 รถรับส่ง BTS (ฟรี 7–22 น.)",
            'quick_replies' => ['ดูห้องว่าง', 'นัดชมโครงการ', 'สอบถามเพิ่มเติม'],
        ];
    }

    private function defaultResponse(): array
    {
        $responses = [
            [
                'text' => "ขอบคุณสำหรับคำถามครับ 😊\n\nผมยังไม่เข้าใจคำถามนี้ แต่พร้อมช่วยเรื่อง:\n\n- 🏠 **ข้อมูลห้อง** — ขนาด, ชั้น, วิว, ประเภท\n- 💰 **ราคาและโปรโมชั่น** — ราคา, ส่วนลด\n- 📅 **นัดชมห้อง** — จองเวลาพบเจ้าหน้าที่\n- 🏦 **คำนวณสินเชื่อ** — ยอดผ่อน, เงินดาวน์\n- 📍 **ทำเลและสิ่งอำนวยความสะดวก**\n\nลองถามใหม่ หรือเลือกหัวข้อด้านล่างได้เลยครับ!",
                'quick_replies' => ['ดูห้องว่าง', 'ราคาห้อง', 'โปรโมชั่น', 'นัดชมห้อง'],
            ],
            [
                'text' => "ขออภัยครับ คำถามนี้อยู่นอกเหนือขอบเขตที่ผมช่วยได้ 🙏\n\nหากต้องการความช่วยเหลือเพิ่มเติม สามารถ:\n- โทรหาเจ้าหน้าที่: **02-XXX-XXXX**\n- Line: **@evante**\n- Email: **info@evante.th**\n\nหรือเลือกหัวข้อด้านล่างครับ!",
                'quick_replies' => ['ดูห้องว่าง', 'คำนวณสินเชื่อ', 'โปรโมชั่น', 'ติดต่อเจ้าหน้าที่'],
            ],
        ];

        return $responses[array_rand($responses)];
    }
}
