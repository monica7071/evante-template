<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Questionnaire extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'agent_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'age',
        'source',
        'source_other',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
