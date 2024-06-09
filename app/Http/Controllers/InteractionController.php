<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InteractionController extends Controller
{
    //
    private function stringToList($input) {
        if (empty($input)) {
            return [];
        } else {
            return explode(',', $input);
        }
    }

    public function show(Request $request)
    {

        $mirNames = $request->input('mirNames');
        $geneNames = $request->input('geneNames');
        $tissues = $request->input('tissues');
        $cellTypes = $request->input('cellTypes');
        $methods = $request->input('methods');
        $validatedAs = $request->input('validatedAs');
        $validationType = $request->input('validationType');
        $confLevel = $request->input('confLevel');
        $biotypes = $request->input('biotypes');
        $species = $request->input('species');
        $sources = $request->input('sources');
        $variants = $request->input('variants');


        $mirList =  $this->stringToList($mirNames);
        $geneList =  $this->stringToList($geneNames);
        $tissuesList =  $this->stringToList($tissues);
        $cellTypesList =  $this->stringToList($cellTypes);
        $methodsList =  $this->stringToList($methods);
        $validatedAsList =  $this->stringToList($validatedAs);
        $validationTypeList =  $this->stringToList($validationType);
        $confLevelList =  $this->stringToList($confLevel);
        $biotypesList =  $this->stringToList($biotypes);
        $speciesList =  $this->stringToList($species);
        $sourcesList =  $this->stringToList($sources);
        $variantsList =  $this->stringToList($variants);

        
        $filters = [
            'mirna_name' => $mirList,
            'gene_name' => $geneList,
            'tissue' => $tissuesList,
            'cell_type' => $cellTypesList,
            'experiment' => $methodsList,
            'type_of_experiment' => $validatedAsList,
            'type_of_interaction' => $validationTypeList,
            'mature_confidence' => $confLevelList,
            'gene_biotype' => $biotypesList,
            'species' => $speciesList,
            // 'sources' => $sourcesList,
            // 'variants' => $variantsList
        ];


        $interactions = DB::table('norm_inter')
                ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
                ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id');


        foreach ($filters as $column => $values) {
            if (!empty($values)) {
                $interactions = $interactions->whereIn($column, $values);
            }
        }
        $interactions = $interactions->get();

        
        // Initialize the result array
        $result = [];
        // Process the input array to group by mirna_name and gene_name
        foreach ($interactions as $item) {
            // Convert object to associative array
            $item = (array) $item;
            
            $key = $item['mirna_name'] . '_' . $item['gene_name'];
            
            if (!isset($result[$key])) {
                $result[$key] = [
                    "mirna_name" => $item['mirna_name'],
                    "mimat" => $item['mimat'],
                    "mature_confidence" => $item['mature_confidence'],
                    "precursor_accession" => $item['precursor_accession'],
                    "precursor_name" => $item['precursor_name'],
                    "precursor_confidence" => $item['precursor_confidence'],
                    "gene_name" => $item['gene_name'],
                    "ensembl_gene_id" => $item['ensembl_gene_id'],
                    "ensembl_transcript_id" => $item['ensembl_transcript_id'],
                    "key" => $key,
                    "publications" => []
                ];
            }
            
            // Remove mirna_name and gene_name from the current item
            unset(  $item['mirna_name'],
                    $item['mimat'],
                    $item['mature_confidence'],
                    $item['precursor_accession'],
                    $item['precursor_name'],
                    $item['precursor_confidence'],
                    $item['gene_name'],
                    $item['ensembl_gene_id'],
                    $item['ensembl_transcript_id']
            );
            
            // Add the rest of the fields to the entries list
            $result[$key]['publications'][] = $item;

            // Log::channel('errorlog')->info(($item));

            // Log::channel('errorlog')->info("****************");

        }
        
        // Reset array keys to ensure the result is indexed numerically
        $result = array_values($result);

        // Process the input array to group by title, tissue, and category
        foreach ($result as &$items) {
            $uniqueExperiments = array_unique(array_column($items["publications"], 'experiment'));
            $items["unique_experiments_count"] = count($uniqueExperiments);
            $groupedPublications = [];

            foreach ($items["publications"] as $item) {
                $item = (array) $item;

                $key = $item['pmid']
                . '_' . $item['tissue'] . '_' . $item['cell_type']. '_' . $item['category']
                . '_' . $item['cell_line'] . '_' . $item['experimental_condition']. '_' . $item['experiment']

                ;
  
                if (!isset($groupedPublications[$key])) {
                    $groupedPublications[$key] = [
                        "author_name" => $item['author_name'],
                        "pub_year" => $item['pub_year'],
                        "title" => $item["title"] ,
                        "journal" => $item["journal"] ,
                        "pmid" => $item["pmid"] ,
                        "email_contact" => $item["email_contact"] ,

                        "tissue" => $item['tissue'],
                        "cell_type" => $item['cell_type'],
                        "category" => $item['category'],
                        "cell_line" => $item['cell_line'],
                        "experimental_condition" => $item['experimental_condition'],
                        "experiment" => $item['experiment'],
                        "key" => $key,
                        "binding_sites" => []

                    ];
                }
                unset(
                    $item['author_name'],
                    $item['pub_year'],
                    $item["title"],
                    $item["journal"],
                    $item["pmid"],
                    $item["email_contact"],

                    $item['tissue'],
                    $item['cell_type'],
                    $item['category'],
                    $item['cell_line'],
                    $item['experimental_condition'],
                    // $item['experiment']
                );
                $groupedPublications[$key]['binding_sites'][] = $item;
            }

            
            $items["publications"] = array_values($groupedPublications);


            // Compute unique tissues count
            $uniqueTissues = array_unique(array_column($items["publications"], 'tissue'));
            $items["unique_tissues_count"] = count($uniqueTissues);

            $uniqueCellTypes = array_unique(array_column($items["publications"], 'cell_type'));
            $items["unique_cell_type_count"] = count($uniqueCellTypes);

            $uniquePublications = array_unique(array_column($items["publications"], 'pmid'));
            $items["unique_publications_count"] = count($uniquePublications);
        }



        // // Assuming you want to return JSON of the first interaction, if exists
        if (!empty($result)) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 200);
        }
    }
    

}
