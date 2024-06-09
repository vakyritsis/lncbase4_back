<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalizationsController extends Controller
{

    private function stringToList($input) {
        if (empty($input)) {
            return [];
        } else {
            return explode(',', $input);
        }
    }
    
    public function show(Request $request)
    {

        $geneNames = $request->input('geneNames');
        $tissues = $request->input('tissues');
        $cellTypes = $request->input('cellTypes');
        $species = $request->input('species');

        $geneList =  $this->stringToList($geneNames);
        $tissuesList =  $this->stringToList($tissues);
        $cellTypesList =  $this->stringToList($cellTypes);
        $speciesList =  $this->stringToList($species);

        $filters = [
            'gene_name' => $geneList,
            'tissue' => $tissuesList,
            'cell_type' => $cellTypesList,
            'species' => $speciesList,
        ];

        $expressions = DB::table('localizations');

        foreach ($filters as $column => $values) {
            if (!empty($values)) {
                $expressions = $expressions->whereIn($column, $values);
            }
        }

        $expressions = $expressions->get();

        // Initialize the result array
        $result = [];
        // Process the input array to group by mirna_name and gene_name
        foreach ($expressions as $item) {
            // Convert object to associative array
            $item = (array) $item;
            
            $key = $item['gene_name']. '_' . $item['ensembl_transcript_id'];

            if (!isset($result[$key])) {
                $result[$key] = [
                    "gene_name" => $item['gene_name'],
                    "internal_tid" => $item['internal_tid'],
                    "ensembl_transcript_id" => $item['ensembl_transcript_id'],
                    "key" => $key,
                    "expressions" => []
                ];
            }
            
            // Remove mirna_name and gene_name from the current item
            unset(  $item['gene_name'],
                    $item['internal_tid'],
                    $item['ensembl_transcript_id'],
            );
            
            // Add the rest of the fields to the entries list
            $result[$key]['expressions'][] = $item;
        }
        $result = array_values($result);


        foreach($result as &$item) {
            $uniqueTissues = array_unique(array_column($item["expressions"], 'tissue'));
            // Remove 'NA' from the unique tissues list if it exists
            if (($key = array_search('NA', $uniqueTissues)) !== false) {
                unset($uniqueTissues[$key]);
            }
            // Re-index the array after removing 'NA'
            $uniqueTissues = array_values($uniqueTissues);
            $item["unique_tissues_count"] = count($uniqueTissues);


            // Extract unique cell types from the expressions array
            $uniqueCellTypes = array_unique(array_column($item["expressions"], 'cell_type'));

            // Remove 'NA' from the unique cell types list if it exists
            if (($key = array_search('NA', $uniqueCellTypes)) !== false) {
                unset($uniqueCellTypes[$key]);
            }

            // Re-index the array after removing 'NA'
            $uniqueCellTypes = array_values($uniqueCellTypes);

            // Count unique cell types excluding 'NA'
            $item["unique_cell_type_count"] = count($uniqueCellTypes);

        }
        unset($item);
        // Reset array keys to ensure the result is indexed numerically


        // // Assuming you want to return JSON of the first interaction, if exists
        if (!empty($result)) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 200);
        }
    }
}
