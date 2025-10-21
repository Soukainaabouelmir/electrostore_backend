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

    public function downloadTemplate()
    {
        return Excel::download(new ProduitsTemplateExport(), 'template_produits_' . date('Y-m-d') . '.xlsx');
    }
}