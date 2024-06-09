<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class InitialCallController extends Controller
{
    // Will return all available lncRnas and miRNAs 
    // and the value for all the filters
    public function getAll(Request $request)
    {

        $mirna_name_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('mirna_name')
            ->distinct()
            ->pluck('mirna_name');

        $gene_name_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('gene_name')
            ->distinct()
            ->pluck('gene_name');

        $tissue_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('tissue')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return ['name' => $item->tissue];
            });

        $cell_type_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('cell_type')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return ['name' => $item->cell_type];
            });

        $experiment_name_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('experiment')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return ['name' => $item->experiment];
            });


        $type_of_experiment_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('type_of_experiment')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return ['name' => $item->type_of_experiment];
            });
        
        $type_of_interaction_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('type_of_interaction')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return ['name' => $item->type_of_interaction];
            });

        $mature_confidence_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('mature_confidence')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return ['name' => $item->mature_confidence];
            });

        $gene_biotype_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('gene_biotype')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return ['name' => $item->gene_biotype];
            });

        $species_unique = DB::table('norm_inter')
            ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
            ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
            ->select('species')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return ['name' => $item->species];
            });

        
        // $sources_unique = DB::table('norm_inter')
        //     ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
        //     ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
        //     ->select('sources')
        //     ->distinct()
        // ->get()
        // ->map(function ($item) {
        //     return ['name' => $item->sources];
        // });

        $tpm_unique = [
            ['name' => 'Low'],
            ['name' => 'Medium'],
            ['name' => 'High']
        ];


        $response_object = [
            'mirna_name_unique' => $mirna_name_unique,
            'gene_name_unique' => $gene_name_unique,
            'tissue_unique' => $tissue_unique,
            'cell_type_unique' => $cell_type_unique,
            'experiment_name_unique' => $experiment_name_unique,
            'type_of_experiment_unique' => $type_of_experiment_unique,
            'type_of_interaction_unique' => $type_of_interaction_unique,
            'mature_confidence_unique' => $mature_confidence_unique,
            'gene_biotype_unique' => $gene_biotype_unique,
            'species_unique' => $species_unique,
            // 'sources_unique' => $sources_unique
            'tpm_unique' => $tpm_unique
        ];
        return response()->json($response_object, 200);
    }
}
