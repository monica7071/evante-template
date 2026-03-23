<?php

namespace App\Models;

use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;

class TemplateMapping extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(function ($model) {
            if (auth()->check() && !auth()->user()->isSuperAdmin() && empty($model->organization_id)) {
                $model->organization_id = auth()->user()->organization_id;
            }
        });
    }

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
