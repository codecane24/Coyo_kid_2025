<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasPermissions;

class Company extends Model
 {

    use HasPermissions;
    protected $guard_name = 'web';
    protected $fillable = [ 'name', 'logo','max_customers','max_suppliers','validity_start','validity_end','status' ];

    public function permissions()
    {
        return $this->belongsToMany( Permission::class, 'company_permissions','company_id', 'permission_id');
    }
}
