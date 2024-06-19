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

        /* Interactions Filters and unique values of miRNAs and lncRNAS */

        $interactions = DB::table('norm_inter')
            ->select(['gene_name', 'mirna_name', 'type_of_experiment', 'type_of_interaction', 'gene_biotype', 'species'])
            ->get();


        $tissues = DB::table('tissues')->select(['tissue', 'cell_type'])->get();

        $experiment_name_unique = DB::table('methods')->select('method_name')->get()->filter()
        ->map(function($tissue) {
            return ['name' => $tissue->method_name];
        });;

        $mirna_name_unique = $interactions->pluck('mirna_name')->unique()->values();
        $gene_name_unique = $interactions->pluck('gene_name')->unique()->values();
        

        $tissue_unique = $tissues->pluck('tissue')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });
    
        $cell_type_unique = $tissues->pluck('cell_type')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });

        // $type_of_experiment_unique = $interactions->pluck('type_of_experiment')->filter()->unique()->values()
        // ->map(function($tissue) {
        //     return ['name' => $tissue];
        // });

        // $type_of_interaction_unique = $interactions->pluck('type_of_interaction')->filter()->unique()->values()
        // ->map(function($tissue) {
        //     return ['name' => $tissue];
        // });

        $mature_confidence_unique = [
            ['name' => 'low'],
            ['name' => 'high'],
            ['name' => 'unknown']
        ];

        $type_of_experiment_unique = [
            ['name' => 'DIRECT'],
            ['name' => 'INDIRECT']
        ];
        $type_of_interaction_unique = [
            ['name' => 'POSITIVE'],
            ['name' => 'NEGATIVE'],
        ];


        $gene_biotype_unique = $interactions->pluck('gene_biotype')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });

        $species_unique = $interactions->pluck('species')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });


        // $sources_unique = $interactions->pluck('sources')->filter()->unique()->values()
        // ->map(function($tissue) {
        //     return ['name' => $tissue];
        // });

        /*Expressions Filters */
        $expressions = DB::table('expressions');
        
        $gene_name_unique_expressions = $expressions->pluck('gene_name')->unique()->values();

        $tissue_unique_expressions = $expressions->pluck('tissue')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });


        $cell_type_unique_expressions = $expressions->pluck('cell_type')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });

        $species_unique_expressions = $expressions->pluck('species')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });

        $tpm_unique = [
            ['name' => 'Low'],
            ['name' => 'Medium'],
            ['name' => 'High']
        ];

        /*Localizations Filters */
        $localizations = DB::table('localizations');

        $gene_name_unique_localizations = $localizations->pluck('gene_name')->unique()->values();


        $tissue_unique_localizations = $localizations->pluck('tissue')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });

        $cell_type_unique_localizations = $localizations->pluck('cell_type')->filter()->unique()->values()
        ->map(function($tissue) {
            return ['name' => $tissue];
        });

        $response_object = [
            'interactions_info' => [
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
                'tpm_unique' => $tpm_unique,
            ],
            'expressions_info' => [
                'gene_name_unique' => $gene_name_unique_expressions,
                'tissue_unique' => $tissue_unique_expressions,
                'cell_type_unique' => $cell_type_unique_expressions,
                'species_unique' => $species_unique_expressions,
                'tpm_unique' => $tpm_unique,

            ],
            'localizations_info' => [
                'gene_name_unique' => $gene_name_unique_localizations,
                'tissue_unique' => $tissue_unique_localizations,
                'cell_type_unique' => $cell_type_unique_localizations,
            ],
        ];
        return response()->json($response_object, 200);
    }
}
