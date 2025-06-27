<?php
namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FyearWithBranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
         // If the user is an admin (assuming 'is_admin' field exists), or has no branch assigned, show all data
         if ($user->role_id<=2 || !$user->branch_id) {
            return;
        }
        
        if (Auth::check() && Auth::user()->branch_id) {
            $builder->where('branch_id', Auth::user()->branch_id);
        }

        if (Session::has('fyear')) {
            $builder->where('fyid', Session::get('fyear.id'));
        }
    }

    /**
     * Extend the scope to handle model creation.
     */
    public function extend(Builder $builder): void
    {
        $builder->getModel()->creating(function ($model) {
            if (Auth::check() && Auth::user()->branch_id) {
                $model->branch_id = Auth::user()->branch_id;
                $model->user_id = Auth::user()->id;
            }

            if (Session::has('fyear')) {
                $model->fyid = Session::get('fyear.id');
            }
        });
    }
}


?>