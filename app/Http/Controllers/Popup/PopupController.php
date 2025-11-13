<?php

namespace App\Http\Controllers\Popup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
class PopupController extends Controller

{



    public function getPopups(){
 try {
        $popups = DB::table('popups')->get();
        
        $popupsWithUrls = $popups->map(function ($popup) {
            return (object) [
                'id' => $popup->id,
                'titre' => $popup->titre,
                'description' => $popup->description,
                'lien' => $popup->lien,
                'is_active' => $popup->is_active,
                'delai' => $popup->delai,
                'image' => $popup->image ? asset($popup->image) : null,
             
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $popupsWithUrls
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des popups: ' . $e->getMessage()
        ], 500);
    }

    }


    public function store(Request $request)
{
    $validatedData = $request->validate([
        'titre' => 'required|string|max:255',
        'description' => 'nullable|string',
        'lien' => 'nullable|url',
        'image' => 'nullable',
        'is_active' => 'required|in:active,inactive',
        'delai' => 'nullable|integer',
    ]);

    try {
        $logoPath = null;
        if ($request->hasFile('image')) {
            $logoFile = $request->file('image');
            
            if ($logoFile->isValid()) {
                $uploadPath = public_path('assets/popups');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $logoName = time() . '_' . str_replace(' ', '_', $logoFile->getClientOriginalName());
                
                $logoFile->move($uploadPath, $logoName);
                
                $logoPath = 'assets/popups/' . $logoName;
            }
        }

        $popupId = DB::table('popups')->insertGetId([
            'titre' => $validatedData['titre'],
            'description' => $validatedData['description'] ?? null,
            'lien' => $validatedData['lien'] ?? null,
            'image' => $logoPath,
            'is_active' => $validatedData['is_active'],
            'delai' => $validatedData['delai'] ?? null,
       
        ]);

        $popup = DB::table('popups')->where('id', $popupId)->first();
        
        $popupWithUrl = (object) [
            'id' => $popup->id,
            'titre' => $popup->titre,
            'description' => $popup->description,
            'lien' => $popup->lien,
            'is_active' => $popup->is_active,
            'image' => $popup->image ? asset($popup->image) : null,
            'delai' => $popup->delai,
          
        ];

        return response()->json([
            'success' => true,
            'message' => 'popup créée avec succès',
            'data' => $popupWithUrl
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création de la popup: ' . $e->getMessage()
        ], 500);
    }
}


public function update(Request $request, $id)
{
    // Vérifier si la popup existe
    $popup = DB::table('popups')->where('id', $id)->first();
    
    if (!$popup) {
        return response()->json([
            'success' => false,
            'message' => 'popup non trouvée'
        ], 404);
    }

    $validatedData = $request->validate([
        'titre' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
        'lien' => 'nullable|url',
        'image' => 'nullable',
        'is_active' => 'sometimes|required|in:active,inactive',
        'delai' => 'nullable|integer',
    ]);

    try {
        $logoPath = $popup->image;
        
        // Gestion du nouveau logo si fourni
        if ($request->hasFile('image')) {
            $logoFile = $request->file('image');
            
            if ($logoFile->isValid()) {
                $uploadPath = public_path('assets/popups');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Supprimer l'ancien logo s'il existe
                if ($logoPath && file_exists(public_path($logoPath))) {
                    unlink(public_path($logoPath));
                }
                
                $logoName = time() . '_' . str_replace(' ', '_', $logoFile->getClientOriginalName());
                $logoFile->move($uploadPath, $logoName);
                $logoPath = 'assets/popups/' . $logoName;
            }
        }

        // Préparer les données à mettre à jour
        $updateData = [];
        if ($request->has('titre')) {
            $updateData['titre'] = $validatedData['titre'];
        }
        if ($request->has('description')) {
            $updateData['description'] = $validatedData['description'];
        }
        if ($request->has('lien')) {
            $updateData['lien'] = $validatedData['lien'];
        }
        $updateData['image'] = $logoPath;
        if ($request->has('is_active')) {
            $updateData['is_active'] = $validatedData['is_active'];
        }
 if ($request->has('delai')) {
            $updateData['delai'] = $validatedData['delai'];
        }
        // Mettre à jour la popup
        DB::table('popups')->where('id', $id)->update($updateData);

        // Récupérer la popup mise à jour
        $popupUpdated = DB::table('popups')->where('id', $id)->first();
        
        $popupWithUrl = (object) [
            'id' => $popupUpdated->id,
            'titre' => $popupUpdated->titre,
            'description' => $popupUpdated->description,
            'lien' => $popupUpdated->lien,
            'is_active' => $popupUpdated->is_active,
            'delai' => $popupUpdated->delai,
            'image' => $popupUpdated->image ? asset($popupUpdated->image) : null,
        ];

        return response()->json([
            'success' => true,
            'message' => 'popup mise à jour avec succès',
            'data' => $popupWithUrl
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour de la popup: ' . $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    $popup = DB::table('popups')->where('id', $id)->first();

    if (!$popup) {
        return response()->json([
            'success' => false,
            'message' => 'Popup non trouvée'
        ], 404);
    }

    DB::table('popups')->where('id', $id)->delete();

    return response()->json([
        'success' => true,
        'message' => 'Popup supprimée avec succès'
    ], 200);
}


}
