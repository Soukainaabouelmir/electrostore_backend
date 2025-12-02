<?php

namespace App\Http\Controllers\ListeProduits\Produits;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProduitsImport;
use App\Exports\ProduitsTemplateExport;
use Exception;

class ProduitsController extends Controller
{
    public function getProducts(){
        $data = DB::table("produits")
            ->leftjoin('marques','produits.id_marque','=','marques.id')
            ->select(
                'produits.id as id',
                'marques.nom as marque',
                'produits.nom as nom',
                'produits.description as description',
                'produits.prix as prix',
                'produits.prix_original as prix_original',
                'produits.image_url as image_url',
                'produits.id_marque as id_marque',
                'produits.processeur as processeur',
                'produits.carte_graphique as carte_graphique',
                'produits.ram as ram',
                'produits.stockage as stockage',
                'produits.performance as performance',
                'produits.disponibilite as disponibilite',
                'produits.promotion as promotion',
                'produits.description_detail as description_detail',
                'produits.caracteristique_principale as caracteristique_principale',
                'produits.categorie as categorie',
                'produits.quantity as quantity',
                'produits.sous_categorie as sous_categorie',
                'produits.in_stock as in_stock',
                'produits.garantie as garantie',
                'produits.created_at as created_at',
            )
            ->get();
        return response()->json($data);
    }

    public function importProducts(Request $request)
    {
        try {
            // Validation du fichier
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier invalide',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            
            // Importer avec Laravel Excel
            $import = new ProduitsImport();
            Excel::import($import, $file);

            return response()->json([
                'success' => true,
                'message' => "{$import->getImportedCount()} produit(s) importé(s) avec succès",
                'imported' => $import->getImportedCount(),
                'errors' => $import->getErrors(),
                'total_rows' => $import->getTotalRows()
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'importation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            // Champs obligatoires
            'nom' => 'required|string|max:150',
            'prix' => 'required|numeric|min:0',
            'categorie' => 'required|string',
            'sous_categorie' => 'required|string',
            'id_marque' => 'required|integer|exists:marques,id',
            
            // Champs optionnels
            'description' => 'nullable|string',
            'description_detail' => 'nullable|string',
            'caracteristique_principale' => 'nullable|string',
            'prix_original' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string|max:255',
            'promotion' => 'boolean',
            'in_stock' => 'boolean',
            'quantity' => 'integer|min:0',
            'garantie' => 'nullable|string',
            'disponibilite' => 'nullable|string',
            
            // Champs spécifiques ordinateurs/PC
            'processeur' => 'nullable|string|max:100',
            'carte_graphique' => 'nullable|string|max:100',
            'ram' => 'nullable|string|max:50',
            'stockage' => 'nullable|string|max:50',
            'performance' => 'nullable|string|max:50',
            
            // Champs spécifiques composants
            'type_composant' => 'nullable|string|max:100',
            'socket' => 'nullable|string|max:50',
            'compatibilite_cpu' => 'nullable|string|max:50',
            'chipset' => 'nullable|string|max:50',
            'format_carte_mere' => 'nullable|string|max:50',
            'puissance_watts' => 'nullable|integer|min:0',
        ]);

        // Si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Insertion dans la base de données
            $produitId = DB::table('produits')->insertGetId([
                // Champs obligatoires
                'nom' => $request->nom,
                'prix' => $request->prix,
                'categorie' => $request->categorie,
                'sous_categorie' => $request->sous_categorie,
                'id_marque' => $request->id_marque,
                
                // Champs optionnels avec valeurs par défaut
                'description' => $request->description ?? null,
                'description_detail' => $request->description_detail ?? null,
                'caracteristique_principale' => $request->caracteristique_principale ?? null,
                'prix_original' => $request->prix_original ?? null,
                'image_url' => $request->image_url ?? null,
                'promotion' => $request->promotion ?? false,
                'in_stock' => $request->in_stock ?? true,
                'quantity' => $request->quantity ?? 0,
                'garantie' => $request->garantie ?? null,
                'disponibilite' => $request->disponibilite ?? 'stock',
                
                // Champs spécifiques PC/Ordinateurs
                'processeur' => $request->processeur ?? null,
                'carte_graphique' => $request->carte_graphique ?? null,
                'ram' => $request->ram ?? null,
                'stockage' => $request->stockage ?? null,
                'performance' => $request->performance ?? null,
                
                // Champs spécifiques Composants
                'type_composant' => $request->type_composant ?? null,
                'socket' => $request->socket ?? null,
                'compatibilite_cpu' => $request->compatibilite_cpu ?? null,
                'chipset' => $request->chipset ?? null,
                'format_carte_mere' => $request->format_carte_mere ?? null,
                'puissance_watts' => $request->puissance_watts ?? null,
                
                // Timestamp
                'created_at' => now(),
            ]);

            // Récupérer le produit créé avec ses relations
            $produit = DB::table('produits')
                ->leftJoin('marques', 'produits.id_marque', '=', 'marques.id')
                ->select(
                    'produits.*',
                    'marques.nom as marque_nom',
                    'marques.logo_url as marque_logo'
                )
                ->where('produits.id', $produitId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Produit créé avec succès',
                'data' => $produit
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du produit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProduitsTemplateExport(), 'template_produits_' . date('Y-m-d') . '.xlsx');
    }
}