<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'user_id',
    ];

    /**
     * The project belongs to a single user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Project>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A projet has multiple tasks attached to it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Task, Project>
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
