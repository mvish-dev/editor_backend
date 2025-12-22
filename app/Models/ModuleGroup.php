<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleGroup extends Model
{
    protected $table = 'modules_groups';
    protected $fillable = ['name', 'status'];

    public function modules()
    {
        return $this->hasMany(ModuleMaster::class, 'group_id');
    }
}
