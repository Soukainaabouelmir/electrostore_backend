<?php

namespace App\Http\Controllers\ListeProduits\Produits;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class ProduitsController extends Controller
{
    public function getProducts(){

        $data=DB::table("produits")
        ->leftjoin('marques','produits.id_marque','=','marques.id')
        ->select(
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
            'produits.sous_categorie as sous_categorie',
            'produits.in_stock as in_stock',
            'produits.garantie as garantie',
            'produits.created_at as created_at',
            
        )
        ->get();
        return response()->json($data);
    }
}
