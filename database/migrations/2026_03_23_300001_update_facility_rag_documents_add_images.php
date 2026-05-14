<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $facilityContents = [
        'สิ่งอำนวยความสะดวก Evante Condo Thonglor' => <<<TEXT
สิ่งอำนวยความสะดวกของโครงการ Evante Condo Thonglor (ทองหล่อ)

![ล็อบบี้](https://picsum.photos/seed/lobby/800/600)
![สระว่ายน้ำ](https://picsum.photos/seed/pool/800/600)
![ฟิตเนส](https://picsum.photos/seed/fitness/800/600)

**ชั้น 1 — Lobby & Arrival**
- Grand Lobby ตกแต่งด้วยหินอ่อน พื้นที่ต้อนรับ 24 ชั่วโมง
  ![ล็อบบี้](https://picsum.photos/seed/lobby/800/600)
- ที่จอดรถอัตโนมัติ 200 คัน (1 คันต่อยูนิต)
  ![ที่จอดรถ](https://picsum.photos/seed/parking/800/600)
- ห้องรับพัสดุ Smart Locker

**ชั้น 6 — Lifestyle Deck**
- สระว่ายน้ำยาว 50 เมตร (Lap Pool) + สระเด็ก
  ![สระว่ายน้ำ](https://picsum.photos/seed/pool/800/600)
- ฟิตเนสเซ็นเตอร์ครบครัน เปิด 06:00–22:00 น.
  ![ฟิตเนส](https://picsum.photos/seed/fitness/800/600)
- สวนหย่อมกลางแจ้ง Rooftop Garden
  ![สวนหย่อม](https://picsum.photos/seed/garden/800/600)
- ห้อง Sauna และ Steam Room
  ![Sauna](https://picsum.photos/seed/sauna/800/600)
- Kids Zone พื้นที่เล่นสำหรับเด็ก
  ![Kids Zone](https://picsum.photos/seed/kidszone/800/600)

**ชั้น 7 — Work & Social**
- Co-working Space 30 ที่นั่ง พร้อม High-Speed Wi-Fi
  ![Co-working](https://picsum.photos/seed/coworking/800/600)
- ห้องประชุม Meeting Room 2 ห้อง (จองล่วงหน้าได้)
  ![ห้องประชุม](https://picsum.photos/seed/meeting/800/600)
- Sky Lounge บรรยากาศชั้น 32
  ![Sky Lounge](https://picsum.photos/seed/skylounge/800/600)

**บริการ**
- Security 24 ชั่วโมง CCTV ทั่วอาคาร
- Shuttle Bus ไป BTS ทองหล่อ (รอบเช้า-เย็น)
- Concierge Service
TEXT,
        'สิ่งอำนวยความสะดวก Evante Villa Sukhumvit' => <<<TEXT
สิ่งอำนวยความสะดวกของโครงการ Evante Villa Sukhumvit (สุขุมวิท 71)

![ล็อบบี้](https://picsum.photos/seed/lobby/800/600)
![Infinity Pool](https://picsum.photos/seed/pool/800/600)
![Sky Lounge](https://picsum.photos/seed/skylounge/800/600)

**ชั้น 1 — Entrance & Parking**
- Private Lobby บรรยากาศรีสอร์ท
  ![ล็อบบี้](https://picsum.photos/seed/lobby/800/600)
- ที่จอดรถ 80 คัน (1 คันต่อยูนิต + 20 คันสำหรับแขก)
  ![ที่จอดรถ](https://picsum.photos/seed/parking/800/600)
- Smart Home Automation ทุกยูนิต

**ชั้น Rooftop — Sky Amenity**
- Infinity Pool วิวเมือง 360 องศา
  ![Infinity Pool](https://picsum.photos/seed/pool/800/600)
- ฟิตเนสเซ็นเตอร์ พร้อมอุปกรณ์ระดับ Premium
  ![ฟิตเนส](https://picsum.photos/seed/fitness/800/600)
- Sky Lounge & Bar Terrace
  ![Sky Lounge](https://picsum.photos/seed/skylounge/800/600)
- BBQ Area สำหรับปาร์ตี้ส่วนตัว

**พื้นที่ส่วนกลาง**
- Tropical Garden 1,200 ตร.ม.
  ![สวนหย่อม](https://picsum.photos/seed/garden/800/600)
- ห้องสมุดและ Reading Lounge
- ห้องซักรีด Laundry Room

**บริการ**
- Security 24 ชั่วโมง
- Property Management Service
- Pet-Friendly Building (บางประเภท)
TEXT,
        'สิ่งอำนวยความสะดวก Evante Residence Ratchada' => <<<TEXT
สิ่งอำนวยความสะดวกของโครงการ Evante Residence Ratchada (รัชดาภิเษก)

![ล็อบบี้](https://picsum.photos/seed/lobby/800/600)
![สระว่ายน้ำ](https://picsum.photos/seed/pool/800/600)
![Sky Lounge](https://picsum.photos/seed/skylounge/800/600)

**ชั้น G — Lobby**
- Modern Lobby ดีไซน์ร่วมสมัย
  ![ล็อบบี้](https://picsum.photos/seed/lobby/800/600)
- ที่จอดรถ 150 คัน
  ![ที่จอดรถ](https://picsum.photos/seed/parking/800/600)

**ชั้น 5 — Amenity Floor**
- สระว่ายน้ำยาว 35 เมตร
  ![สระว่ายน้ำ](https://picsum.photos/seed/pool/800/600)
- ฟิตเนสเซ็นเตอร์พร้อมอุปกรณ์ครบ
  ![ฟิตเนส](https://picsum.photos/seed/fitness/800/600)
- สวนหย่อม Garden Deck
  ![สวนหย่อม](https://picsum.photos/seed/garden/800/600)
- Sauna & Jacuzzi
  ![Sauna](https://picsum.photos/seed/sauna/800/600)

**ชั้น 25 — Sky Facilities**
- Sky Pool Rooftop
  ![สระว่ายน้ำ Sky](https://picsum.photos/seed/pool/800/600)
- Sky Lounge พร้อม Co-working Space
  ![Sky Lounge](https://picsum.photos/seed/skylounge/800/600)
  ![Co-working](https://picsum.photos/seed/coworking/800/600)

**บริการ**
- Security 24 ชั่วโมง
- CCTV ทั่วอาคาร
- ใกล้ MRT รัชดาภิเษก เดิน 3 นาที
TEXT,
    ];

    public function up(): void
    {
        foreach ($this->facilityContents as $title => $content) {
            DB::table('rag_documents')
                ->where('title', $title)
                ->where('category', 'facility')
                ->update([
                    'content'    => $content,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // No rollback — original content without images is acceptable on rollback
    }
};
