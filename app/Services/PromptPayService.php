<?php

namespace App\Services;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;

class PromptPayService
{
    /**
     * Generate PromptPay EMVCo QR payload string (Thai standard).
     *
     * @param  string  $promptpayId  Phone number (10 digits) or Tax ID (13 digits)
     * @param  float   $amount       Amount in THB
     */
    public function generatePayload(string $promptpayId, float $amount): string
    {
        $guid    = 'A000000677010111';                        // PromptPay global UID
        $account = $this->formatId($promptpayId);

        $merchantInfo = $this->tlv('00', $guid) . $this->tlv('01', $account);

        $payload = $this->tlv('00', '01')                    // Payload Format Indicator
            . $this->tlv('01', '12')                         // Dynamic QR (one-time)
            . $this->tlv('29', $merchantInfo)                // PromptPay merchant info
            . $this->tlv('53', '764')                        // Currency: THB
            . $this->tlv('54', number_format($amount, 2, '.', ''))  // Amount
            . $this->tlv('58', 'TH')                         // Country: Thailand
            . '6304';                                        // CRC placeholder

        return $payload . $this->crc16($payload);
    }

    /**
     * Generate QR code as PNG base64 string (data URI).
     */
    public function generateQrBase64(string $promptpayId, float $amount): string
    {
        $payload = $this->generatePayload($promptpayId, $amount);

        $options = new QROptions([
            'version'     => 6,
            'outputType'  => QROutputInterface::GDIMAGE_PNG,
            'eccLevel'    => EccLevel::Q,
            'scale'       => 6,
            'imageBase64' => true,
        ]);

        return (new QRCode($options))->render($payload);
    }

    // -----------------------------------------------------------------------

    private function tlv(string $tag, string $value): string
    {
        return $tag . str_pad(strlen($value), 2, '0', STR_PAD_LEFT) . $value;
    }

    private function formatId(string $id): string
    {
        $clean = preg_replace('/\D/', '', $id);

        // Phone (10 digits) → 0066 + last 9 digits
        if (strlen($clean) === 10) {
            return '0066' . substr($clean, 1);
        }

        // Tax ID / National ID (13 digits) → use as-is
        return $clean;
    }

    /**
     * CRC-16/CCITT-FALSE (poly 0x1021, init 0xFFFF).
     */
    private function crc16(string $data): string
    {
        $crc = 0xFFFF;

        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000)
                    ? (($crc << 1) ^ 0x1021) & 0xFFFF
                    : ($crc << 1) & 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
