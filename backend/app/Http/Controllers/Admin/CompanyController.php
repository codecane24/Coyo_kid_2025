<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class CompanyController extends WebController
 {
    public function create()
 {
        $company = Company::first();
        if ( $company ) {
            return redirect()->route( 'admin.company.edit' );
        }

        $permissions = Permission::all();
        return view( 'admin.company.create', compact( 'permissions' ) );
    }

    public function store( Request $request )
 {
        $validated = $request->validate( [
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'max_customers' => 'nullable|integer',
            'max_suppliers' => 'nullable|integer',
            'validity_start' => 'nullable|date',
            'validity_end' => 'nullable|date|after_or_equal:validity_start',
        ] );

        // Handle logo upload
        if ( $request->hasFile( 'logo' ) ) {
            $file = $request->file( 'logo' );
            $folder = 'logo';
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move( public_path( $folder ), $filename );
            $validated[ 'logo' ] = $folder . '/' . $filename;
        }

        // Create company
        $company = Company::create( $validated );

        // Assign permissions if provided
        if ( $request->has( 'permissions' ) ) {
            // Assuming Company model uses spatie/permission and has syncPermissions method
            $company->syncPermissions( $request->permissions );
        }

        return redirect()->route( 'admin.company.create' )->with( 'success', 'Company created and permissions assigned successfully.' );
    }

    public function edit()
 {
        $company = Company::firstOrFail();
        $permissions = Permission::all();
        $companyPermissions = $company->permissions->pluck( 'name' )->toArray();

        return view( 'admin.company.edit', compact( 'company', 'permissions', 'companyPermissions' ) );
    }

    public function update( Request $request )
 {
        $company = Company::firstOrFail();

        $validated = $request->validate( [
            'name' => 'required|string',
            'permissions' => 'nullable|array',
            'max_customers' => 'nullable',
            'max_suppliers'=> 'nullable',
            'validity_start' => 'nullable',
            'validity_end' => 'nullable'
        ] );

        if ( $request->hasFile( 'logo' ) ) {
            $file = $request->file( 'logo' );
            // Define folder path inside 'public'
            $folder = 'logo';

            // Generate a unique file name with extension
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            // Move the file to the public/logo folder
            $file->move( public_path( $folder ), $filename );

            // Delete old logo file if exists
            if ( $company->logo && file_exists( public_path( $company->logo ) ) ) {
                unlink( public_path( $company->logo ) );
            }

            // Store relative path to DB, e.g. 'logo/filename.jpg'
            $company->logo = $folder . '/' . $filename;

            $company->save();
        }

        $company->update( $validated );

        if ( $request->permissions ) {
            $company->syncPermissions( $request->permissions );
        } else {
            $company->syncPermissions( [] );
        }

        return redirect()->route( 'admin.company.edit' )->with( 'success', 'Company updated successfully' );
    }

    public function destroy()
 {
        $company = Company::firstOrFail();

        if ( $company->logo ) {
            Storage::disk( 'public' )->delete( $company->logo );
        }

        $company->delete();

        return redirect()->route( 'company.create' )->with( 'success', 'Company deleted successfully' );
    }
}
