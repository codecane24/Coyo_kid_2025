<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\ClassMaster;
use App\Models\ModuleGroup;
use App\Models\House;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /**
     * Display a listing of the classes.
     *
     * @return \Illuminate\Http\Response
     */
    //=== API to encrypt provided id  ===


    public function encryptId($id)
    {
        if (empty($id)) {
            return response()->json(['error' => 'ID is required'], 400);
        }
        try {
            $encryptedId = Crypt::encrypt($id);
            return response()->json(['encrypted_id' => $encryptedId]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Encryption failed'], 500);
        }
    }

    public function getSerialNo($type)
    {
        return getNewSerialNo($type);
    }

    public function companydata($companyEncruptedId)
    {   
        // Decrypt the company ID && not tempered encrypted ID
        if (empty($companyEncruptedId)) {
            return response()->json(['error' => 'Company ID is required'], 400);
        } 
        try {
            $companyEncryptedId = Crypt::decrypt($companyEncruptedId);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid company ID'], 400);
        }   
        // Assuming you have a Company model and the necessary logic to decrypt the ID
        $company = Company::findOrFail(decrypt($companyEncryptedId));
        return response()->json($company);
    }
    
    public function showBranch($branchEncryptedId)
    {
        // Decrypt the branch ID && not tempered encrypted ID
        if (empty($branchEncryptedId)) {
            return response()->json(['error' => 'Branch ID is required'], 400);
        } 
        try {
            $branchEncryptedId = Crypt::decrypt($branchEncryptedId);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid branch ID'], 400);
        }   
        
        // Assuming you have a Branch model and the necessary logic to decrypt the ID
        $branch = Branch::findOrFail(decrypt($branchEncryptedId));
        return response()->json($branch);
    }

    public function companyBranchesList($companyEncruptedId)
    {
        // Decrypt the company ID && not tempered encrypted ID
        if (empty($companyEncruptedId)) {
            return response()->json(['error' => 'Company ID is required'], 400);
        }
        try {
            $companyEncryptedId = Crypt::decrypt($companyEncruptedId);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid company ID'], 400);
        }
        // Assuming you have a Company model and the necessary logic to decrypt the ID
        $companyId = decrypt($companyEncryptedId); 
        $branches = Branch::where('company_id', $companyId)
            ->select('id', 'code', 'name', 'status')
            ->get();
        return response()->json($branches);
    }   
      

    public function branchList()
    {
        $list = Branch::select('id','code','name','status')->get();
        return response()->json($list);
    }

    public function classmasterList()
    {
        $list = ClassMaster::select('id','code','name','status')->get();
        return response()->json($list);
    }

    public function ModuleGroupList()
    {
        $list = ModuleGroup::select('id','name')->get();
        return response()->json($list);
    }

    public function HouseList()
    {
        $list = House::select('id','code','name','status')->get();
        return response()->json($list);
    }


    public function sectionList()
    {
        $sections = [];
        
        // Generate A-Z sections
        foreach (range('A', 'Z') as $letter) {
            $sections[] = [
                'id' => $letter,
                'name' => $letter 
            ];
        }

        return response()->json($sections);
    }

   
}