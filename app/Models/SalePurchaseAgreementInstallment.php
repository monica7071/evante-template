<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePurchaseAgreementInstallment extends Model
{
    protected $fillable = [
        'sale_purchase_agreement_id',
        'sequence',
        'amount_number',
        'amount_text',
        'due_date',
        'proof_image',
    ];

    protected $casts = [
        'amount_number' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(SalePurchaseAgreement::class, 'sale_purchase_agreement_id');
    }
}
