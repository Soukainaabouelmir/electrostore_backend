<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use DB;
use Exception;

class ProduitsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private $imported = 0;
    private $errors = [];
    private $totalRows = 0;

    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count();

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 car commence à 0 et on a l'en-tête
                
                try {
                    // Validation minimale
                    if (empty($row['nom'])) {
                        $this->errors[] = "Ligne {$rowNumber}: Le nom du produit est requis";
                        continue;
                    }

                    if (empty($row['prix']) || $row['prix'] <= 0) {
                        $this->errors[] = "Ligne {$rowNumber}: Le prix doit être supérieur à 0";
                        continue;
                    }

                    if (empty($row['categorie'])) {
                        $this->errors[] = "Ligne {$rowNumber}: La catégorie est requise";
                        continue;
                    }

                    // Préparer les données du produit
                    $productData = [
                        'nom' => trim($row['nom']),
                        'description' => trim($row['description'] ?? ''),
                        'prix' => floatval($row['prix']),
                        'prix_original' => floatval($row['prix_original'] ?? $row['prix']),
                        'image_url' => trim($row['image_url'] ?? ''),
                        'processeur' => trim($row['processeur'] ?? ''),
                        'carte_graphique' => trim($row['carte_graphique'] ?? ''),
                        'ram' => trim($row['ram'] ?? ''),
                        'stockage' => trim($row['stockage'] ?? ''),
                        'performance' => trim($row['performance'] ?? ''),
                        'disponibilite' => isset($row['disponibilite']) ? (int)$row['disponibilite'] : 1,
                        'promotion' => isset($row['promotion']) ? (int)$row['promotion'] : 0,
                        'description_detail' => trim($row['description_detail'] ?? ''),
                        'caracteristique_principale' => trim($row['caracteristique_principale'] ?? ''),
                        'categorie' => trim($row['categorie']),
                        'quantity' => isset($row['quantity']) ? (int)$row['quantity'] : 0,
                        'sous_categorie' => trim($row['sous_categorie'] ?? ''),
                        'in_stock' => isset($row['in_stock']) ? (int)$row['in_stock'] : 1,
                        'garantie' => trim($row['garantie'] ?? ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Gérer la marque
                    if (!empty($row['marque'])) {
                        $marqueName = trim($row['marque']);
                        $marque = DB::table('marques')
                            ->where('nom', $marqueName)
                            ->first();
                        
                        if ($marque) {
                            $productData['id_marque'] = $marque->id;
                        } else {
                            // Créer la marque si elle n'existe pas
                            $marqueId = DB::table('marques')->insertGetId([
                                'nom' => $marqueName,
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $productData['id_marque'] = $marqueId;
                        }
                    }

                    // Insérer le produit
                    DB::table('produits')->insert($productData);
                    $this->imported++;

                } catch (Exception $e) {
                    $this->errors[] = "Ligne {$rowNumber}: " . $e->getMessage();
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getImportedCount()
    {
        return $this->imported;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getTotalRows()
    {
        return $this->totalRows;
    }
}