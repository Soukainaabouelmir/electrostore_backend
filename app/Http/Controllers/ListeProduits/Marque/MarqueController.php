<?php

namespace App\Http\Controllers\ListeProduits\Marque;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class MarqueController extends Controller
{
    public function getMarque(){
 try {
        $marques = DB::table('marques')->get();
        
        $marquesWithUrls = $marques->map(function ($marque) {
            return (object) [
                'id' => $marque->id,
                'nom' => $marque->nom,
                'description' => $marque->description,
                'site' => $marque->site,
                'status' => $marque->status,
                'logo' => $marque->logo ? asset($marque->logo) : null,
             
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $marquesWithUrls
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des marques: ' . $e->getMessage()
        ], 500);
    }

    }


public function store(Request $request)
{
    $validatedData = $request->validate([
        'nom' => 'required|string|max:255',
        'description' => 'nullable|string',
        'site' => 'nullable|url',
        'logo' => 'nullable',
        'status' => 'required|in:active,inactive'
    ]);

    try {
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            
            if ($logoFile->isValid()) {
                $uploadPath = public_path('assets/marques');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $logoName = time() . '_' . str_replace(' ', '_', $logoFile->getClientOriginalName());
                
                $logoFile->move($uploadPath, $logoName);
                
                $logoPath = 'assets/marques/' . $logoName;
            }
        }

        $marqueId = DB::table('marques')->insertGetId([
            'nom' => $validatedData['nom'],
            'description' => $validatedData['description'] ?? null,
            'site' => $validatedData['site'] ?? null,
            'logo' => $logoPath,
            'status' => $validatedData['status'],
       
        ]);

        $marque = DB::table('marques')->where('id', $marqueId)->first();
        
        $marqueWithUrl = (object) [
            'id' => $marque->id,
            'nom' => $marque->nom,
            'description' => $marque->description,
            'site' => $marque->site,
            'status' => $marque->status,
            'logo' => $marque->logo ? asset($marque->logo) : null,
          
        ];

        return response()->json([
            'success' => true,
            'message' => 'Marque créée avec succès',
            'data' => $marqueWithUrl
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création de la marque: ' . $e->getMessage()
        ], 500);
    }
}




}