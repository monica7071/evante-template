<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'type',
        'project_id',
        'floor',
        'unit_type',
        'image_path',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
