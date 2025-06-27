<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transporter extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function saveTransporter($data=[],$object_id=0,$object = null){
        if(!empty($object)){
            //
        }
        elseif($object_id > 0){
            $object = $this->find($object_id);
        }
        else{
            $object = new Transporter();
        }
        $object->fill($data);
        $object->save();
       
        return $object;
  }
}


