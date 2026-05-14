<?php

namespace App\Services\AI\Tools;

class FacilitiesTool extends AbstractTool
{
    public function name(): string
    {
        return 'get_facilities';
    }

    public function description(): string
    {
        return 'ดึงข้อมูลสิ่งอำนวยความสะดวกของโครงการพร้อมรูปภาพ '
            . 'Use this when the customer asks about facilities, amenities, swimming pool, fitness, '
            . 'parking, lobby, co-working, sky lounge, sauna, kids zone, or common areas of any project. '
            . 'Returns structured facility list with image URLs for each amenity.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'project' => [
                    'type'        => 'string',
                    'description' => 'ชื่อโครงการที่ต้องการ เช่น "Thonglor", "Sukhumvit", "Ratchada" หรือ "all" เพื่อดูทุกโครงการ',
                ],
            ],
            'required' => ['project'],
        ];
    }

    public function execute(array $input, int $organizationId): array
    {
        $project = strtolower(trim($input['project'] ?? 'all'));

        $all = $this->getFacilitiesData();

        if ($project === 'all') {
            return $this->success($all, 'พบข้อมูลสิ่งอำนวยความสะดวกทั้งหมด ' . count($all) . ' โครงการ');
        }

        $filtered = [];
        foreach ($all as $name => $data) {
            if (str_contains(strtolower($name), $project)) {
                $filtered[$name] = $data;
            }
        }

        if (empty($filtered)) {
            return $this->notFound("ไม่พบข้อมูลสิ่งอำนวยความสะดวกของโครงการ: {$input['project']}");
        }

        return $this->success($filtered, 'พบข้อมูลสิ่งอำนวยความสะดวก ' . count($filtered) . ' โครงการ');
    }

    private function getFacilitiesData(): array
    {
        return [
            'Evante Condo Thonglor' => [
                'location'   => 'สุขุมวิท 55 (ทองหล่อ) เขตวัฒนา กรุงเทพฯ',
                'facilities' => [
                    [
                        'name'        => 'Grand Lobby',
                        'description' => 'ล็อบบี้ตกแต่งด้วยหินอ่อน ต้อนรับ 24 ชั่วโมง ชั้น 1',
                        'image'       => 'https://picsum.photos/seed/lobby/800/600',
                    ],
                    [
                        'name'        => 'ที่จอดรถอัตโนมัติ',
                        'description' => '200 คัน (1 คันต่อยูนิต) ชั้น 1',
                        'image'       => 'https://picsum.photos/seed/parking/800/600',
                    ],
                    [
                        'name'        => 'สระว่ายน้ำ (Lap Pool)',
                        'description' => 'ยาว 50 เมตร พร้อมสระเด็ก ชั้น 6',
                        'image'       => 'https://picsum.photos/seed/pool/800/600',
                    ],
                    [
                        'name'        => 'ฟิตเนสเซ็นเตอร์',
                        'description' => 'เปิด 06:00–22:00 น. ชั้น 6',
                        'image'       => 'https://picsum.photos/seed/fitness/800/600',
                    ],
                    [
                        'name'        => 'สวนหย่อม (Rooftop Garden)',
                        'description' => 'สวนกลางแจ้ง ชั้น 6',
                        'image'       => 'https://picsum.photos/seed/garden/800/600',
                    ],
                    [
                        'name'        => 'Sauna & Steam Room',
                        'description' => 'ห้องซาวน่าและห้องอบไอน้ำ ชั้น 6',
                        'image'       => 'https://picsum.photos/seed/sauna/800/600',
                    ],
                    [
                        'name'        => 'Kids Zone',
                        'description' => 'พื้นที่เล่นสำหรับเด็ก ชั้น 6',
                        'image'       => 'https://picsum.photos/seed/kidszone/800/600',
                    ],
                    [
                        'name'        => 'Co-working Space',
                        'description' => '30 ที่นั่ง พร้อม High-Speed Wi-Fi ชั้น 7',
                        'image'       => 'https://picsum.photos/seed/coworking/800/600',
                    ],
                    [
                        'name'        => 'ห้องประชุม (Meeting Room)',
                        'description' => '2 ห้อง จองล่วงหน้าได้ ชั้น 7',
                        'image'       => 'https://picsum.photos/seed/meeting/800/600',
                    ],
                    [
                        'name'        => 'Sky Lounge',
                        'description' => 'ชั้น 32 วิวเมืองแบบ 360 องศา',
                        'image'       => 'https://picsum.photos/seed/skylounge/800/600',
                    ],
                ],
            ],
            'Evante Villa Sukhumvit' => [
                'location'   => 'สุขุมวิท 71 (พร้อมพงษ์) เขตวัฒนา กรุงเทพฯ',
                'facilities' => [
                    [
                        'name'        => 'Private Lobby',
                        'description' => 'บรรยากาศรีสอร์ท ชั้น 1',
                        'image'       => 'https://picsum.photos/seed/lobby/800/600',
                    ],
                    [
                        'name'        => 'ที่จอดรถ',
                        'description' => '80 คัน + 20 คันสำหรับแขก ชั้น 1',
                        'image'       => 'https://picsum.photos/seed/parking/800/600',
                    ],
                    [
                        'name'        => 'Infinity Pool',
                        'description' => 'วิวเมือง 360 องศา ชั้น Rooftop',
                        'image'       => 'https://picsum.photos/seed/pool/800/600',
                    ],
                    [
                        'name'        => 'ฟิตเนสเซ็นเตอร์',
                        'description' => 'อุปกรณ์ระดับ Premium ชั้น Rooftop',
                        'image'       => 'https://picsum.photos/seed/fitness/800/600',
                    ],
                    [
                        'name'        => 'Sky Lounge & Bar Terrace',
                        'description' => 'ชั้น Rooftop',
                        'image'       => 'https://picsum.photos/seed/skylounge/800/600',
                    ],
                    [
                        'name'        => 'Tropical Garden',
                        'description' => '1,200 ตร.ม. พื้นที่สีเขียวขนาดใหญ่',
                        'image'       => 'https://picsum.photos/seed/garden/800/600',
                    ],
                ],
            ],
            'Evante Residence Ratchada' => [
                'location'   => 'รัชดาภิเษก 32 เขตห้วยขวาง กรุงเทพฯ',
                'facilities' => [
                    [
                        'name'        => 'Modern Lobby',
                        'description' => 'ดีไซน์ร่วมสมัย ชั้น G',
                        'image'       => 'https://picsum.photos/seed/lobby/800/600',
                    ],
                    [
                        'name'        => 'ที่จอดรถ',
                        'description' => '150 คัน ชั้น G',
                        'image'       => 'https://picsum.photos/seed/parking/800/600',
                    ],
                    [
                        'name'        => 'สระว่ายน้ำ',
                        'description' => 'ยาว 35 เมตร ชั้น 5',
                        'image'       => 'https://picsum.photos/seed/pool/800/600',
                    ],
                    [
                        'name'        => 'ฟิตเนสเซ็นเตอร์',
                        'description' => 'อุปกรณ์ครบครัน ชั้น 5',
                        'image'       => 'https://picsum.photos/seed/fitness/800/600',
                    ],
                    [
                        'name'        => 'สวนหย่อม (Garden Deck)',
                        'description' => 'ชั้น 5',
                        'image'       => 'https://picsum.photos/seed/garden/800/600',
                    ],
                    [
                        'name'        => 'Sauna & Jacuzzi',
                        'description' => 'ชั้น 5',
                        'image'       => 'https://picsum.photos/seed/sauna/800/600',
                    ],
                    [
                        'name'        => 'Sky Pool',
                        'description' => 'Rooftop ชั้น 25',
                        'image'       => 'https://picsum.photos/seed/pool/800/600',
                    ],
                    [
                        'name'        => 'Sky Lounge & Co-working Space',
                        'description' => 'ชั้น 25',
                        'image'       => 'https://picsum.photos/seed/skylounge/800/600',
                    ],
                ],
            ],
        ];
    }
}
