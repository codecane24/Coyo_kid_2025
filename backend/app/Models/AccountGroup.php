<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountGroup extends Model
{
    protected $table = 'tbl_account_group';
    protected $hidden = ['created_at', 'updated_at'];

    public function account(){
    		return $this->hasMany(Account::class,'acGroup');
    }

    
    public function parent(){
        return $this->belongsTo(AccountGroup::class, 'parent_id');
     }

    public function child(){
        return $this->hasMany(AccountGroup::class, 'parent_id');
    }



}
