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


public function update(Request $request, $id)
{
    // Vérifier si la marque existe
    $marque = DB::table('marques')->where('id', $id)->first();
    
    if (!$marque) {
        return response()->json([
            'success' => false,
            'message' => 'Marque non trouvée'
        ], 404);
    }

    $validatedData = $request->validate([
        'nom' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
        'site' => 'nullable|url',
        'logo' => 'nullable',
        'status' => 'sometimes|required|in:active,inactive'
    ]);

    try {
        $logoPath = $marque->logo;
        
        // Gestion du nouveau logo si fourni
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            
            if ($logoFile->isValid()) {
                $uploadPath = public_path('assets/marques');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Supprimer l'ancien logo s'il existe
                if ($logoPath && file_exists(public_path($logoPath))) {
                    unlink(public_path($logoPath));
                }
                
                $logoName = time() . '_' . str_replace(' ', '_', $logoFile->getClientOriginalName());
                $logoFile->move($uploadPath, $logoName);
                $logoPath = 'assets/marques/' . $logoName;
            }
        }

        // Préparer les données à mettre à jour
        $updateData = [];
        if ($request->has('nom')) {
            $updateData['nom'] = $validatedData['nom'];
        }
        if ($request->has('description')) {
            $updateData['description'] = $validatedData['description'];
        }
        if ($request->has('site')) {
            $updateData['site'] = $validatedData['site'];
        }
        $updateData['logo'] = $logoPath;
        if ($request->has('status')) {
            $updateData['status'] = $validatedData['status'];
        }

        // Mettre à jour la marque
        DB::table('marques')->where('id', $id)->update($updateData);

        // Récupérer la marque mise à jour
        $marqueUpdated = DB::table('marques')->where('id', $id)->first();
        
        $marqueWithUrl = (object) [
            'id' => $marqueUpdated->id,
            'nom' => $marqueUpdated->nom,
            'description' => $marqueUpdated->description,
            'site' => $marqueUpdated->site,
            'status' => $marqueUpdated->status,
            'logo' => $marqueUpdated->logo ? asset($marqueUpdated->logo) : null,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Marque mise à jour avec succès',
            'data' => $marqueWithUrl
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour de la marque: ' . $e->getMessage()
        ], 500);
    }
}


public function destroy($id)
{
    $marque = DB::table('marques')->where('id', $id)->first();

    if (!$marque) {
        return response()->json([
            'success' => false,
            'message' => 'Marque non trouvée'
        ], 404);
    }

    DB::table('marques')->where('id', $id)->delete();

    return response()->json([
        'success' => true,
        'message' => 'Marque supprimée avec succès'
    ], 200);
}



}