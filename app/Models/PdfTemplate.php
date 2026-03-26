<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'contract_type',
        'language',
        'file_path',
    ];

    public function mappings()
    {
        return $this->hasMany(TemplateMapping::class, 'template_id');
    }
}
