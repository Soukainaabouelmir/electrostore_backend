<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ProduitsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        // Ligne d'exemple
        return [
            [
                'Laptop Dell XPS 15',
                'Ordinateur portable haute performance',
                12500,
                15000,
                'Dell',
                'https://example.com/image.jpg',
                'Intel Core i7-12700H',
                'NVIDIA RTX 3050',
                '16GB DDR5',
                '512GB SSD',
                'Haute',
                1,
                1,
                'Écran 15.6" Full HD, Windows 11 Pro',
                'Processeur Intel Core i7 12ème génération',
                'Ordinateurs',
                5,
                'Laptops',
                1,
                '2 ans'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'nom',
            'description',
            'prix',
            'prix_original',
            'marque',
            'image_url',
            'processeur',
            'carte_graphique',
            'ram',
            'stockage',
            'performance',
            'disponibilite',
            'promotion',
            'description_detail',
            'caracteristique_principale',
            'categorie',
            'quantity',
            'sous_categorie',
            'in_stock',
            'garantie'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style pour la ligne d'en-tête
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // nom
            'B' => 35,  // description
            'C' => 12,  // prix
            'D' => 15,  // prix_original
            'E' => 15,  // marque
            'F' => 30,  // image_url
            'G' => 25,  // processeur
            'H' => 25,  // carte_graphique
            'I' => 15,  // ram
            'J' => 15,  // stockage
            'K' => 15,  // performance
            'L' => 15,  // disponibilite
            'M' => 12,  // promotion
            'N' => 40,  // description_detail
            'O' => 40,  // caracteristique_principale
            'P' => 15,  // categorie
            'Q' => 12,  // quantity
            'R' => 20,  // sous_categorie
            'S' => 12,  // in_stock
            'T' => 15,  // garantie
        ];
    }
}