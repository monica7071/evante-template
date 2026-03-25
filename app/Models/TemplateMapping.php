<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class TemplateMapping extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'template_id',
        'db_field',
        'field_type',
        'x_position',
        'y_position',
        'page_number',
        'img_width',
    ];

    public function template()
    {
        return $this->belongsTo(PdfTemplate::class, 'template_id');
    }
}
