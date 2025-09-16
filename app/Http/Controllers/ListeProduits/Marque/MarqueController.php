<?php

namespace App\Http\Controllers\ListeProduits\Marque;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class MarqueController extends Controller
{
    public function getMarque(){

        $data=DB::table('marques')
             ->get();
        return response()->json($data);
    }
}
