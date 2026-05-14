<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        $org = DB::table('organizations')->first();
        $orgId = $org?->id ?? 1;

        $this->command->info('=== MockDataSeeder: เพิ่ม mock data สำหรับทดสอบ AI Chat ===');

        $this->seedListings($orgId);
        $this->seedPromotions($orgId);
        $this->seedRagDocuments($orgId);
        $this->seedSalesAppointments($orgId);
        $this->seedChatSessionsAndMessages($orgId);

        $this->command->info('=== เสร็จสิ้น ===');
    }

    // -------------------------------------------------------------------------
    // Listings — เพิ่มให้ครบ 15 ห้อง (ปัจจุบันมี 7)
    // -------------------------------------------------------------------------
    private function seedListings(int $orgId): void
    {
        $existing = DB::table('listings')->where('organization_id', $orgId)->count();
        if ($existing >= 15) {
            $this->command->info("  [skip] listings มีอยู่แล้ว {$existing} รายการ");
            return;
        }

        $proj1 = DB::table('projects')->where('organization_id', $orgId)->where('name', 'like', '%Thonglor%')->value('id');
        $proj2 = DB::table('projects')->where('organization_id', $orgId)->where('name', 'like', '%Sukhumvit%')->value('id');
        $loc1  = DB::table('locations')->where('organization_id', $orgId)->where('project_name', 'like', '%Thonglor%')->value('id');
        $loc2  = DB::table('locations')->where('organization_id', $orgId)->where('project_name', 'like', '%Sukhumvit%')->value('id');

        // สร้างโครงการที่ 3 — Evante Residence Ratchada
        $loc3 = DB::table('locations')->insertGetId([
            'project_name'    => 'Evante Residence Ratchada',
            'province'        => 'กรุงเทพมหานคร',
            'district'        => 'ห้วยขวาง',
            'subdistrict'     => 'ห้วยขวาง',
            'address'         => 'รัชดาภิเษก 32 กรุงเทพฯ',
            'organization_id' => $orgId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $proj3 = DB::table('projects')->insertGetId([
            'location_id'     => $loc3,
            'name'            => 'Evante Residence Ratchada',
            'total_floors'    => 25,
            'total_units'     => 200,
            'organization_id' => $orgId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $newListings = [
            // --- Evante Condo Thonglor เพิ่มเติม ---
            [
                'location_id'     => $loc1,
                'project_id'      => $proj1,
                'project_name'    => 'Evante Condo Thonglor',
                'building'        => 'A',
                'unit_code'       => 'ECT-3201',
                'room_number'     => '3201',
                'floor'           => 32,
                'unit_type'       => 'Penthouse',
                'bedrooms'        => 4,
                'area'            => 185.00,
                'price_per_room'  => 29600000,
                'price_per_sqm'   => 160000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/ect3201fp/800/600',
                'room_layout_image' => 'https://picsum.photos/seed/ect3201rl/800/600',
            ],
            [
                'location_id'     => $loc1,
                'project_id'      => $proj1,
                'project_name'    => 'Evante Condo Thonglor',
                'building'        => 'B',
                'unit_code'       => 'ECT-2510',
                'room_number'     => '2510',
                'floor'           => 25,
                'unit_type'       => '3 Bedroom',
                'bedrooms'        => 3,
                'area'            => 105.00,
                'price_per_room'  => 15750000,
                'price_per_sqm'   => 150000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/ect2510fp/800/600',
                'room_layout_image' => 'https://picsum.photos/seed/ect2510rl/800/600',
            ],
            [
                'location_id'     => $loc1,
                'project_id'      => $proj1,
                'project_name'    => 'Evante Condo Thonglor',
                'building'        => 'A',
                'unit_code'       => 'ECT-0512',
                'room_number'     => '0512',
                'floor'           => 5,
                'unit_type'       => '1 Bedroom',
                'bedrooms'        => 1,
                'area'            => 36.00,
                'price_per_room'  => 3960000,
                'price_per_sqm'   => 110000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/ect0512fp/800/600',
                'room_layout_image' => 'https://picsum.photos/seed/ect0512rl/800/600',
            ],
            // --- Evante Villa Sukhumvit เพิ่มเติม ---
            [
                'location_id'     => $loc2,
                'project_id'      => $proj2,
                'project_name'    => 'Evante Villa Sukhumvit',
                'building'        => 'Main',
                'unit_code'       => 'EVS-101',
                'room_number'     => '101',
                'floor'           => 1,
                'unit_type'       => 'Studio',
                'bedrooms'        => 0,
                'area'            => 30.00,
                'price_per_room'  => 3300000,
                'price_per_sqm'   => 110000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/evs101fp/800/600',
                'room_layout_image' => 'https://picsum.photos/seed/evs101rl/800/600',
            ],
            [
                'location_id'     => $loc2,
                'project_id'      => $proj2,
                'project_name'    => 'Evante Villa Sukhumvit',
                'building'        => 'Main',
                'unit_code'       => 'EVS-801',
                'room_number'     => '801',
                'floor'           => 8,
                'unit_type'       => 'Penthouse',
                'bedrooms'        => 4,
                'area'            => 220.00,
                'price_per_room'  => 44000000,
                'price_per_sqm'   => 200000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/evs801fp/800/600',
                'room_layout_image' => 'https://picsum.photos/seed/evs801rl/800/600',
            ],
            // --- Evante Residence Ratchada ---
            [
                'location_id'     => $loc3,
                'project_id'      => $proj3,
                'project_name'    => 'Evante Residence Ratchada',
                'building'        => 'A',
                'unit_code'       => 'ERR-0801',
                'room_number'     => '0801',
                'floor'           => 8,
                'unit_type'       => 'Studio',
                'bedrooms'        => 0,
                'area'            => 27.00,
                'price_per_room'  => 2430000,
                'price_per_sqm'   => 90000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/err0801fp/800/600',
                'room_layout_image' => 'https://picsum.photos/seed/err0801rl/800/600',
            ],
            [
                'location_id'     => $loc3,
                'project_id'      => $proj3,
                'project_name'    => 'Evante Residence Ratchada',
                'building'        => 'A',
                'unit_code'       => 'ERR-1205',
                'room_number'     => '1205',
                'floor'           => 12,
                'unit_type'       => '1 Bedroom',
                'bedrooms'        => 1,
                'area'            => 40.00,
                'price_per_room'  => 4400000,
                'price_per_sqm'   => 110000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/err1205fp/800/600',
                'room_layout_image' => 'https://picsum.photos/seed/err1205rl/800/600',
            ],
            [
                'location_id'     => $loc3,
                'project_id'      => $proj3,
                'project_name'    => 'Evante Residence Ratchada',
                'building'        => 'B',
                'unit_code'       => 'ERR-1803',
                'room_number'     => '1803',
                'floor'           => 18,
                'unit_type'       => '2 Bedroom',
                'bedrooms'        => 2,
                'area'            => 62.00,
                'price_per_room'  => 7440000,
                'price_per_sqm'   => 120000,
                'status'          => 'reserved',
                'floor_plan_image'  => 'https://picsum.photos/seed/err1803fp/800/600',
                'room_layout_image' => 'https://picsum.photos/seed/err1803rl/800/600',
            ],
        ];

        foreach ($newListings as $listing) {
            DB::table('listings')->insert(array_merge($listing, [
                'organization_id' => $orgId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]));
        }

        $total = DB::table('listings')->where('organization_id', $orgId)->count();
        $this->command->info("  [done] listings รวม {$total} รายการ");
    }

    // -------------------------------------------------------------------------
    // Promotions
    // -------------------------------------------------------------------------
    private function seedPromotions(int $orgId): void
    {
        if (DB::table('promotions')->where('organization_id', $orgId)->count() > 0) {
            $this->command->info('  [skip] promotions มีข้อมูลแล้ว');
            return;
        }

        $promotions = [
            [
                'title'          => 'Early Bird พิเศษ ลด 5%',
                'description'    => 'จองล่วงหน้าก่อนใคร รับส่วนลดพิเศษ 5% จากราคาห้อง',
                'discount_type'  => 'percent',
                'discount_value' => 5.00,
                'start_date'     => '2026-03-01',
                'end_date'       => '2026-04-30',
                'conditions'     => 'สำหรับยูนิต available เท่านั้น • ชำระมัดจำ 50,000 บาทภายใน 7 วัน • ไม่สามารถใช้ร่วมกับโปรโมชั่นอื่นได้',
                'is_active'      => true,
            ],
            [
                'title'          => 'ฟรีค่าโอน',
                'description'    => 'รับสิทธิ์ฟรีค่าธรรมเนียมการโอนกรรมสิทธิ์ มูลค่าสูงถึง 150,000 บาท',
                'discount_type'  => 'freebie',
                'discount_value' => 150000.00,
                'start_date'     => '2026-03-01',
                'end_date'       => '2026-05-31',
                'conditions'     => 'สำหรับการโอนกรรมสิทธิ์ในปี 2026 • เฉพาะโครงการ Evante Condo Thonglor และ Evante Villa Sukhumvit',
                'is_active'      => true,
            ],
            [
                'title'          => 'แถมเฟอร์นิเจอร์ Built-in ครบชุด',
                'description'    => 'รับเฟอร์นิเจอร์ built-in ครบชุด มูลค่า 300,000 บาท สำหรับห้อง 2 ห้องนอนขึ้นไป',
                'discount_type'  => 'freebie',
                'discount_value' => 300000.00,
                'start_date'     => '2026-03-15',
                'end_date'       => '2026-06-30',
                'conditions'     => 'เฉพาะห้อง 2 ห้องนอนและ 3 ห้องนอน • ออกแบบและติดตั้งโดยทีมงานโครงการ • ระยะเวลาติดตั้ง 60 วันก่อนโอน',
                'is_active'      => true,
            ],
            [
                'title'          => 'ผ่อนดาวน์ 0% นาน 24 เดือน',
                'description'    => 'ผ่อนชำระดาวน์โดยไม่มีดอกเบี้ยนาน 24 เดือน เงินดาวน์ 15% ของราคาห้อง',
                'discount_type'  => 'freebie',
                'discount_value' => 0,
                'start_date'     => '2026-01-01',
                'end_date'       => '2026-12-31',
                'conditions'     => 'ดาวน์ขั้นต่ำ 15% ของราคาห้อง • แบ่งชำระ 24 งวด ไม่มีดอกเบี้ย • เงื่อนไขการกู้สินเชื่อเป็นไปตามธนาคาร',
                'is_active'      => true,
            ],
            [
                'title'          => 'Referral Reward รับ 30,000 บาท',
                'description'    => 'แนะนำเพื่อนซื้อห้อง รับเงินสด 30,000 บาท เมื่อเพื่อนโอนกรรมสิทธิ์สำเร็จ',
                'discount_type'  => 'amount',
                'discount_value' => 30000.00,
                'start_date'     => '2026-01-01',
                'end_date'       => '2026-12-31',
                'conditions'     => 'ผู้แนะนำต้องเป็นเจ้าของห้องในโครงการ Evante • จ่ายเงินรางวัลหลังจากผู้ถูกแนะนำโอนกรรมสิทธิ์แล้ว 30 วัน',
                'is_active'      => true,
            ],
        ];

        foreach ($promotions as $promo) {
            DB::table('promotions')->insert(array_merge($promo, [
                'organization_id' => $orgId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]));
        }

        $this->command->info('  [done] promotions 5 รายการ');
    }

    // -------------------------------------------------------------------------
    // RAG Documents — FAQ, Facilities, Promotions, Policy
    // -------------------------------------------------------------------------
    private function seedRagDocuments(int $orgId): void
    {
        if (DB::table('rag_documents')->where('organization_id', $orgId)->count() > 0) {
            $this->command->info('  [skip] rag_documents มีข้อมูลแล้ว');
            return;
        }

        $docs = [
            // --- Facilities ---
            [
                'title'    => 'สิ่งอำนวยความสะดวก Evante Condo Thonglor',
                'category' => 'facility',
                'content'  => <<<TEXT
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
            ],
            [
                'title'    => 'สิ่งอำนวยความสะดวก Evante Villa Sukhumvit',
                'category' => 'facility',
                'content'  => <<<TEXT
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
            ],
            [
                'title'    => 'สิ่งอำนวยความสะดวก Evante Residence Ratchada',
                'category' => 'facility',
                'content'  => <<<TEXT
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
            ],
            // --- Promotions ---
            [
                'title'    => 'โปรโมชั่นพิเศษ ปี 2026',
                'category' => 'promotion',
                'content'  => <<<TEXT
โปรโมชั่นพิเศษจาก Evante Property ปี 2026

1. **Early Bird ลด 5%** (มี.ค.–เม.ย. 2026)
   จองล่วงหน้ารับส่วนลดทันที 5% จากราคาห้อง
   เงื่อนไข: ชำระมัดจำ 50,000 บาท ภายใน 7 วัน

2. **ฟรีค่าโอน** มูลค่าสูงถึง 150,000 บาท (มี.ค.–พ.ค. 2026)
   รับสิทธิ์ฟรีค่าธรรมเนียมการโอนกรรมสิทธิ์
   เฉพาะ Evante Condo Thonglor และ Evante Villa Sukhumvit

3. **แถมเฟอร์นิเจอร์ Built-in** มูลค่า 300,000 บาท (มี.ค.–มิ.ย. 2026)
   เฉพาะห้อง 2 ห้องนอนขึ้นไป ออกแบบและติดตั้งโดยทีมงาน

4. **ผ่อนดาวน์ 0% นาน 24 เดือน** (ตลอดปี 2026)
   ดาวน์ 15% แบ่งชำระ 24 งวด ไม่มีดอกเบี้ย

5. **Referral Reward 30,000 บาท** (ตลอดปี 2026)
   แนะนำเพื่อน รับเงินสดหลังโอนกรรมสิทธิ์
TEXT,
            ],
            // --- FAQ ---
            [
                'title'    => 'FAQ การจองห้องและนัดชม',
                'category' => 'faq',
                'content'  => <<<TEXT
คำถามพบบ่อย — การจองห้องและนัดชม

**Q: ขั้นตอนการจองห้องมีอะไรบ้าง?**
A: 1) เลือกห้องที่สนใจ → 2) นัดชมห้องกับทีมเซลล์ → 3) ชำระเงินจอง 50,000 บาท → 4) ทำสัญญาจะซื้อจะขายภายใน 30 วัน → 5) ผ่อนดาวน์และยื่นกู้ → 6) โอนกรรมสิทธิ์

**Q: การนัดชมห้องทำได้อย่างไร?**
A: แจ้งชื่อ เบอร์โทรศัพท์ วันที่และเวลาที่สะดวก ทีมงานจะติดต่อยืนยันนัดหมายภายใน 24 ชั่วโมง

**Q: วันไหนและเวลาไหนสามารถเข้าชมได้?**
A: เปิดทุกวัน 9:00–17:00 น. ไม่เว้นวันหยุด

**Q: ต้องเสียค่าใช้จ่ายในการเข้าชมไหม?**
A: ไม่มีค่าใช้จ่ายในการเข้าชม ฟรีทุกครั้ง

**Q: ยกเลิกการจองได้ไหม?**
A: สามารถยกเลิกได้ภายใน 3 วันทำการ โดยจะคืนเงินมัดจำ 50% หลังจาก 3 วัน จะไม่คืนเงินมัดจำ
TEXT,
            ],
            [
                'title'    => 'FAQ การกู้สินเชื่อและการชำระเงิน',
                'category' => 'faq',
                'content'  => <<<TEXT
คำถามพบบ่อย — สินเชื่อและการชำระเงิน

**Q: กู้ได้กี่เปอร์เซ็นต์ของราคาห้อง?**
A: โดยทั่วไปธนาคารอนุมัติ 80–90% ขึ้นอยู่กับเครดิตบูโรและรายได้

**Q: ดอกเบี้ยสินเชื่อประมาณเท่าไหร่?**
A: อัตราดอกเบี้ยปัจจุบัน ประมาณ 3.00–4.50% ต่อปี ขึ้นอยู่กับธนาคารและโปรโมชั่น

**Q: ผ่อนนานได้แค่ไหน?**
A: สูงสุด 30 ปี ขึ้นอยู่กับอายุผู้กู้ (อายุผู้กู้ + ระยะเวลากู้ไม่เกิน 70 ปี)

**Q: ค่าใช้จ่ายวันโอนมีอะไรบ้าง?**
A: ค่าโอน 2% + ค่าอากร 0.5% + ภาษีธุรกิจเฉพาะ 3.3% (ถ้าถือน้อยกว่า 5 ปี) + ค่าประกันอัคคีภัย + ค่ากองทุนส่วนกลาง (Sinking Fund)

**Q: ค่าส่วนกลางต่อปีเท่าไหร่?**
A: Evante Condo Thonglor: 70 บาท/ตร.ม./เดือน, Evante Villa Sukhumvit: 80 บาท/ตร.ม./เดือน, Evante Residence Ratchada: 65 บาท/ตร.ม./เดือน
TEXT,
            ],
            [
                'title'    => 'FAQ ข้อมูลโครงการและที่ตั้ง',
                'category' => 'faq',
                'content'  => <<<TEXT
คำถามพบบ่อย — ข้อมูลโครงการ

**Evante Condo Thonglor**
- ที่ตั้ง: สุขุมวิท 55 (ทองหล่อ) เขตวัฒนา กรุงเทพฯ
- การเดินทาง: BTS ทองหล่อ เดิน 5 นาที
- จำนวน 32 ชั้น 280 ยูนิต
- คาดว่าจะก่อสร้างแล้วเสร็จ Q4/2027

**Evante Villa Sukhumvit**
- ที่ตั้ง: สุขุมวิท 71 (พร้อมพงษ์) เขตวัฒนา กรุงเทพฯ
- การเดินทาง: BTS พร้อมพงษ์ เดิน 8 นาที
- จำนวน 8 ชั้น 120 ยูนิต (Low-Rise Luxury)
- คาดว่าจะก่อสร้างแล้วเสร็จ Q2/2027

**Evante Residence Ratchada**
- ที่ตั้ง: รัชดาภิเษก 32 เขตห้วยขวาง กรุงเทพฯ
- การเดินทาง: MRT รัชดาภิเษก เดิน 3 นาที
- จำนวน 25 ชั้น 200 ยูนิต
- คาดว่าจะก่อสร้างแล้วเสร็จ Q1/2028
TEXT,
            ],
        ];

        foreach ($docs as $doc) {
            DB::table('rag_documents')->insert(array_merge($doc, [
                'is_active'       => true,
                'organization_id' => $orgId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]));
        }

        $this->command->info('  [done] rag_documents ' . count($docs) . ' รายการ');
    }

    // -------------------------------------------------------------------------
    // Sales (Appointments)
    // -------------------------------------------------------------------------
    private function seedSalesAppointments(int $orgId): void
    {
        if (DB::table('sales')->where('organization_id', $orgId)->count() > 0) {
            $this->command->info('  [skip] sales มีข้อมูลแล้ว');
            return;
        }

        $listings = DB::table('listings')
            ->where('organization_id', $orgId)
            ->whereIn('status', ['available', 'reserved'])
            ->limit(3)
            ->get();

        $appointments = [
            [
                'appointment_name'  => 'สมชาย ใจดี',
                'appointment_phone' => '081-234-5678',
                'appointment_date'  => Carbon::now()->addDays(2)->format('Y-m-d'),
                'appointment_time'  => '10:00',
                'remark_appointment' => 'ต้องการดูห้องพร้อมแผน floor plan',
                'status'            => 'appointment',
            ],
            [
                'appointment_name'  => 'วิภาวดี สวัสดี',
                'appointment_phone' => '089-876-5432',
                'appointment_date'  => Carbon::now()->addDays(3)->format('Y-m-d'),
                'appointment_time'  => '14:00',
                'remark_appointment' => 'มากับครอบครัว สนใจห้อง 2 ห้องนอน',
                'status'            => 'appointment',
            ],
            [
                'appointment_name'  => 'ธนกร มีสุข',
                'appointment_phone' => '062-345-6789',
                'appointment_date'  => Carbon::now()->addDays(5)->format('Y-m-d'),
                'appointment_time'  => '11:00',
                'remark_appointment' => 'ต้องการข้อมูลสินเชื่อธนาคาร',
                'status'            => 'appointment',
            ],
        ];

        foreach ($appointments as $i => $appt) {
            $listing = $listings[$i] ?? $listings[0];
            $today   = now()->format('Ymd');
            $count   = DB::table('sales')->count() + $i + 1;

            DB::table('sales')->insert([
                'listing_id'         => $listing->id,
                'sale_number'        => 'SL-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
                'status'             => $appt['status'],
                'appointment_name'   => $appt['appointment_name'],
                'appointment_phone'  => $appt['appointment_phone'],
                'appointment_date'   => $appt['appointment_date'],
                'appointment_time'   => $appt['appointment_time'],
                'remark_appointment' => $appt['remark_appointment'],
                'organization_id'    => $orgId,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        $this->command->info('  [done] sales (appointments) 3 รายการ');
    }

    // -------------------------------------------------------------------------
    // Chat Sessions & Messages
    // -------------------------------------------------------------------------
    private function seedChatSessionsAndMessages(int $orgId): void
    {
        if (DB::table('chat_sessions')->where('organization_id', $orgId)->count() > 0) {
            $this->command->info('  [skip] chat_sessions มีข้อมูลแล้ว');
            return;
        }

        $session1 = DB::table('chat_sessions')->insertGetId([
            'session_token'   => Str::random(64),
            'customer_name'   => 'สมชาย ใจดี',
            'channel'         => 'web',
            'status'          => 'resolved',
            'handled_by'      => 'ai',
            'organization_id' => $orgId,
            'last_message_at' => Carbon::now()->subHours(3),
            'created_at'      => Carbon::now()->subHours(3),
            'updated_at'      => Carbon::now()->subHours(1),
        ]);

        $session2 = DB::table('chat_sessions')->insertGetId([
            'session_token'   => Str::random(64),
            'customer_name'   => 'วิภาวดี สวัสดี',
            'channel'         => 'line',
            'status'          => 'active',
            'handled_by'      => 'ai',
            'organization_id' => $orgId,
            'last_message_at' => Carbon::now()->subMinutes(30),
            'created_at'      => Carbon::now()->subHours(1),
            'updated_at'      => Carbon::now()->subMinutes(30),
        ]);

        $session3 = DB::table('chat_sessions')->insertGetId([
            'session_token'   => Str::random(64),
            'customer_name'   => null,
            'channel'         => 'web',
            'status'          => 'active',
            'handled_by'      => 'ai',
            'organization_id' => $orgId,
            'last_message_at' => Carbon::now()->subMinutes(5),
            'created_at'      => Carbon::now()->subMinutes(10),
            'updated_at'      => Carbon::now()->subMinutes(5),
        ]);

        $messages = [
            // Session 1 — สอบถามห้อง 1BR
            ['session_id' => $session1, 'sender_type' => 'user', 'content' => 'สวัสดีครับ มีห้อง 1 ห้องนอนว่างไหมครับ ราคาไม่เกิน 5 ล้าน', 'created_at' => Carbon::now()->subHours(3)],
            ['session_id' => $session1, 'sender_type' => 'ai',   'content' => 'สวัสดีครับ! มียูนิต 1 ห้องนอนที่ตรงกับงบประมาณของคุณอยู่ 2 ห้องครับ ได้แก่ ECT-0512 ชั้น 5 ขนาด 36 ตร.ม. ราคา 3.96 ล้านบาท และ ECT-2005 ชั้น 20 ขนาด 42 ตร.ม. ราคา 5.46 ล้านบาท สนใจห้องไหนเป็นพิเศษไหมครับ?', 'created_at' => Carbon::now()->subHours(3)->addMinutes(1)],
            ['session_id' => $session1, 'sender_type' => 'user', 'content' => 'สนใจห้อง ECT-0512 ครับ ขอดูรูปห้องได้ไหม', 'created_at' => Carbon::now()->subHours(2)->addMinutes(30)],
            ['session_id' => $session1, 'sender_type' => 'ai',   'content' => "ห้อง ECT-0512 ชั้น 5 อาคาร A โครงการ Evante Condo Thonglor\n- ขนาด: 36 ตร.ม. | 1 ห้องนอน\n- ราคา: 3,960,000 บาท\n- ราคาต่อตร.ม.: 110,000 บาท\n\nแผนผังห้อง:\n![ECT-0512 Floor Plan](https://picsum.photos/seed/ect0512fp/800/600)\n\nต้องการนัดชมห้องจริงไหมครับ?", 'created_at' => Carbon::now()->subHours(2)->addMinutes(31)],
            ['session_id' => $session1, 'sender_type' => 'user', 'content' => 'สนใจครับ ขอนัดดูห้องวันพฤหัสฯ ได้ไหม', 'created_at' => Carbon::now()->subHours(1)->addMinutes(45)],
            ['session_id' => $session1, 'sender_type' => 'ai',   'content' => 'ได้เลยครับ! ขอทราบชื่อและเบอร์โทรเพื่อยืนยันนัดหมายได้เลยครับ', 'created_at' => Carbon::now()->subHours(1)->addMinutes(46)],

            // Session 2 — คำนวณสินเชื่อ
            ['session_id' => $session2, 'sender_type' => 'user', 'content' => 'ถ้าซื้อห้อง 2 ห้องนอน ราคา 7.5 ล้าน ผ่อนเดือนละเท่าไหร่คะ', 'created_at' => Carbon::now()->subMinutes(60)],
            ['session_id' => $session2, 'sender_type' => 'ai',   'content' => "คำนวณสินเชื่อเบื้องต้นให้เลยค่ะ:\n\nราคาห้อง: 7,500,000 บาท\nกู้ 80%: 6,000,000 บาท\nดอกเบี้ย 4% ต่อปี ผ่อน 30 ปี\n\n**ผ่อนประมาณ 28,643 บาท/เดือน**\n\nถ้าผ่อน 20 ปี จะอยู่ที่ประมาณ **36,348 บาท/เดือน** ค่ะ\nสนใจดูห้อง 2 ห้องนอนที่มีอยู่ไหมคะ?", 'created_at' => Carbon::now()->subMinutes(59)],
            ['session_id' => $session2, 'sender_type' => 'user', 'content' => 'มีโปรโมชั่นอะไรบ้างคะตอนนี้', 'created_at' => Carbon::now()->subMinutes(30)],
            ['session_id' => $session2, 'sender_type' => 'ai',   'content' => "โปรโมชั่นปัจจุบันของ Evante ค่ะ:\n\n🎉 **Early Bird ลด 5%** — จองก่อนสิ้นเมษายน 2026\n🏠 **ฟรีค่าโอน** มูลค่าถึง 150,000 บาท (ถึงพฤษภาคม 2026)\n🛋️ **แถมเฟอร์นิเจอร์ Built-in** 300,000 บาท สำหรับห้อง 2BR+ \n💳 **ผ่อนดาวน์ 0%** นาน 24 เดือน\n\nโปรโมชั่นเฟอร์นิเจอร์น่าสนใจมากสำหรับห้อง 2 ห้องนอนนะคะ ต้องการรายละเอียดเพิ่มเติมไหมคะ?", 'created_at' => Carbon::now()->subMinutes(29)],

            // Session 3 — สอบถามโครงการ
            ['session_id' => $session3, 'sender_type' => 'user', 'content' => 'มีโครงการที่ไหนบ้างครับ', 'created_at' => Carbon::now()->subMinutes(10)],
            ['session_id' => $session3, 'sender_type' => 'ai',   'content' => "Evante Property มี 3 โครงการครับ:\n\n1. **Evante Condo Thonglor** — สุขุมวิท 55 ใกล้ BTS ทองหล่อ\n   32 ชั้น | 280 ยูนิต | Studio–Penthouse\n\n2. **Evante Villa Sukhumvit** — สุขุมวิท 71 ใกล้ BTS พร้อมพงษ์\n   8 ชั้น | 120 ยูนิต | Luxury Low-Rise\n\n3. **Evante Residence Ratchada** — รัชดาภิเษก 32 ใกล้ MRT รัชดา\n   25 ชั้น | 200 ยูนิต | Urban Living\n\nสนใจโครงการไหนเป็นพิเศษไหมครับ?", 'created_at' => Carbon::now()->subMinutes(9)],
        ];

        foreach ($messages as $msg) {
            DB::table('chat_messages')->insert([
                'session_id'  => $msg['session_id'],
                'sender_type' => $msg['sender_type'],
                'content'     => $msg['content'],
                'metadata'    => null,
                'created_at'  => $msg['created_at'],
                'updated_at'  => $msg['created_at'],
            ]);
        }

        $this->command->info('  [done] chat_sessions 3 รายการ, chat_messages ' . count($messages) . ' รายการ');
    }
}
