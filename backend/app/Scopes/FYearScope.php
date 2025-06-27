<?php 
namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FYearScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();

           // If the user is an admin (assuming 'is_admin' field exists), or has no branch assigned, show all data
            if ($user->role_id<=2 || !$user->branch_id) {
                return;
            }

            // Apply branch filter for normal users
            $builder->where('fyid', session::get('fyear.id'));
        }
    }
}
