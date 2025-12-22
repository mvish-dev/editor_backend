<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleMaster extends Model
{
    protected $table = 'modules_masters';
    protected $fillable = ['name', 'group_id', 'status'];

    public function group()
    {
        return $this->belongsTo(ModuleGroup::class, 'group_id');
    }
}
