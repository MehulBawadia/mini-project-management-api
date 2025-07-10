<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'status', 'project_id', 'due_date',
    ];

    /**
     * A tawsk belongs to a single project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Project, Task>
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
