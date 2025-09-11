<?php

namespace App\Http\Controllers\ListeProduits\Categories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class CategoriesController extends Controller
{
    public function getCategory()
    {
        try {
            $categories = DB::table('categories')
                ->select([
                    'id',
                    'nom', 
                    'parent_id',
                    'status',
                  
                ])
                ->orderBy('parent_id', 'ASC') 
                ->orderBy('nom', 'ASC')
                ->get();

          
            $transformedCategories = $categories->map(function ($category) use ($categories) {
                $parentName = null;
                if ($category->parent_id) {
                    $parent = $categories->firstWhere('id', $category->parent_id);
                    $parentName = $parent ? $parent->nom : null;
                }

                return [
                    'id' => $category->id,
                    'nom' => $category->nom,
                    'parent_id' => $category->parent_id,
                    'parent_name' => $parentName,
                    'is_main_category' => is_null($category->parent_id),
                    'status' => $category->status ?? 'active',
                  
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedCategories,
                'message' => 'Catégories récupérées avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des catégories',
                'error' => $e->getMessage()
            ], 500);
        }
    }



 

 public function store(Request $request)
{
    try {
        $request->validate([
            'nom' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'in:active,inactive',
            'subCategories' => 'nullable|array',
            'subCategories.*' => 'string|max:255',
            'subCategoryData' => 'nullable|array'
        ]);

        DB::beginTransaction();

        // Créer la catégorie principale
        $categoryId = DB::table('categories')->insertGetId([
            'nom' => $request->nom,
            'parent_id' => $request->parent_id,
            'status' => $request->status ?? 'active',
        
        ]);

        $category = DB::table('categories')->where('id', $categoryId)->first();
        $createdSubCategories = [];
        $createdSubSubCategories = [];

        // Si on a des sous-catégories et que c'est une catégorie principale (pas de parent)
        if ($request->has('subCategories') && is_array($request->subCategories) && !$request->parent_id) {
            
            foreach ($request->subCategories as $subCategoryName) {
                if (!empty(trim($subCategoryName))) {
                    // Vérifier que la sous-catégorie n'existe pas déjà
                    $existingSubCategory = DB::table('categories')
                        ->where('nom', trim($subCategoryName))
                        ->where('parent_id', $categoryId)
                        ->first();
                    
                    if (!$existingSubCategory) {
                        // Créer la sous-catégorie
                        $subCategoryId = DB::table('categories')->insertGetId([
                            'nom' => trim($subCategoryName),
                            'parent_id' => $categoryId,
                            'status' => 'active',
                         
                        ]);
                        
                        $createdSubCategories[] = [
                            'id' => $subCategoryId,
                            'nom' => trim($subCategoryName),
                            'parent_id' => $categoryId,
                            'status' => 'active'
                        ];

                        // Créer les sous-sous-catégories si elles existent
                        if ($request->has('subCategoryData') && 
                            isset($request->subCategoryData[trim($subCategoryName)]) &&
                            is_array($request->subCategoryData[trim($subCategoryName)])) {
                            
                            $subSubCategories = [];
                            
                            foreach ($request->subCategoryData[trim($subCategoryName)] as $subSubCategoryName) {
                                if (!empty(trim($subSubCategoryName))) {
                                    // Vérifier que la sous-sous-catégorie n'existe pas déjà
                                    $existingSubSubCategory = DB::table('categories')
                                        ->where('nom', trim($subSubCategoryName))
                                        ->where('parent_id', $subCategoryId)
                                        ->first();
                                    
                                    if (!$existingSubSubCategory) {
                                        $subSubCategoryId = DB::table('categories')->insertGetId([
                                            'nom' => trim($subSubCategoryName),
                                            'parent_id' => $subCategoryId,
                                            'status' => 'active',
                                         
                                        ]);
                                        
                                        $subSubCategories[] = [
                                            'id' => $subSubCategoryId,
                                            'nom' => trim($subSubCategoryName),
                                            'parent_id' => $subCategoryId,
                                            'status' => 'active'
                                        ];
                                    }
                                }
                            }
                            
                            if (!empty($subSubCategories)) {
                                $createdSubSubCategories[trim($subCategoryName)] = $subSubCategories;
                            }
                        }
                    }
                }
            }
        }

        DB::commit();

        // Compter le total des catégories créées
        $totalSubCategories = count($createdSubCategories);
        $totalSubSubCategories = array_sum(array_map('count', $createdSubSubCategories));
        
        $message = 'Catégorie créée avec succès';
        if ($totalSubCategories > 0) {
            $message .= ' avec ' . $totalSubCategories . ' sous-catégorie(s)';
            if ($totalSubSubCategories > 0) {
                $message .= ' et ' . $totalSubSubCategories . ' sous-sous-catégorie(s)';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'subCategories' => $createdSubCategories,
                'subSubCategories' => $createdSubSubCategories
            ],
            'message' => $message
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création de la catégorie',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'status' => 'in:active,inactive'
            ]);

            // Vérifier que la catégorie ne devient pas son propre parent
            if ($request->parent_id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une catégorie ne peut pas être son propre parent'
                ], 422);
            }

            // Vérifier les références circulaires
            if ($this->wouldCreateCircularReference($id, $request->parent_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette action créerait une référence circulaire'
                ], 422);
            }

            $updated = DB::table('categories')
                ->where('id', $id)
                ->update([
                    'nom' => $request->nom,
                    'parent_id' => $request->parent_id,
                    'status' => $request->status ?? 'active',
                  
                ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catégorie non trouvée'
                ], 404);
            }

            $category = DB::table('categories')->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Catégorie mise à jour avec succès'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la catégorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $hasChildren = DB::table('categories')
                ->where('parent_id', $id)
                ->exists();

            if ($hasChildren) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer une catégorie qui a des sous-catégories'
                ], 422);
            }

            $deleted = DB::table('categories')->where('id', $id)->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catégorie non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Catégorie supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la catégorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
   
   
  
}