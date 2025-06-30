<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class Branch extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function accounts()
    {
        return $this->belongsToMany(
            Account::class,
            'branches_accounts', // mapping Table Name
            'branch_id',
            'account_id'
        );
    }


    public function bdata(){
        return $this->hasMany(BranchData::class, 'branch_id');
    }


    public function saveBranch($data = [], $object_id = 0, $object = null)
    {
        if (!empty($object)) {
            //
        } elseif ($object_id > 0) {
            $object = $this->find($object_id);
        } else {
            $object = new Branch();
        }
        $object->fill($data);
        $object->save();

        return $object;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branches');
    }

}
