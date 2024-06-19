<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InteractionController extends Controller
{
    public function showByKeyword(Request $request)
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
                ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
                ->join('methods', 'norm_inter.method_id', '=', 'methods.id')
                ->leftjoin('mir_info', 'norm_inter.mir_info_id', '=', 'mir_info.id');

        foreach ($filters as $column => $values) {
            if (!empty($values)) {
                $interactions = $interactions->whereIn($column, $values);
            }
        }
        $interactions = $interactions->get();

        $response_object = $this->groupInteractions($interactions);

        if (!empty($result)) {
            return response()->json($response_object, 200);
        } else {
            return response()->json($response_object, 200);
        }
    }
    
    public function showByLocation(Request $request)
    {

        $chrName = $request->input('chrName');
        $coordStart = $request->input('coordStart');
        $coordEnd = $request->input('coordEnd');
        //use this as well
        $searchType = $request->input('searchType');

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

            'tissue' => $tissuesList,
            'cell_type' => $cellTypesList,
            'method_name' => $methodsList,
            'type_of_experiment' => $validatedAsList,
            'type_of_interaction' => $validationTypeList,
            'mature_confidence' => $confLevelList,
            'gene_biotype' => $biotypesList,
            'species' => $speciesList,
            // 'sources' => $sourcesList,
            // 'variants' => $variantsList
        ];

        $start = intval($coordStart);
        $end = intval($coordEnd);

        $interactions = DB::table('norm_inter')
                ->join('publications', 'norm_inter.publication_id', '=', 'publications.id')
                ->join('tissues', 'norm_inter.tissue_id', '=', 'tissues.id')
                ->join('methods', 'norm_inter.method_id', '=', 'methods.id')
                ->join('mir_info', 'norm_inter.mir_info_id', '=', 'mir_info.id')
                ->where('coordinates', 'like', "$chrName%")
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('coordinates_start', [$start, $end])
                          ->orWhereBetween('coordinates_end', [$start, $end])
                          ->orWhere(function ($query) use ($start, $end) {
                              $query->where('coordinates_start', '<', $start)
                                    ->where('coordinates_end', '>', $end);
                          });
                });

        foreach ($filters as $column => $values) {
            if (!empty($values)) {
                $interactions = $interactions->whereIn($column, $values);
            }
        }
        $interactions = $interactions->get();

        $response_object = $this->groupInteractions($interactions);

        
        if (!empty($result)) {
            return response()->json($response_object, 200);
        } else {
            return response()->json($response_object, 200);
        }
    }


    private function groupInteractions($interactions) {
        $uniqueExperiments = $interactions->unique('method_name');

        $num_of_experiments = $interactions->unique('method_name')->count();
        $num_of_exp_low =  $uniqueExperiments->where('highthroughput', 'f')->count();
        $num_of_exp_high = $uniqueExperiments->where('highthroughput', 't')->count();
        $num_of_cell_lines = $interactions->unique('cell_type')->count();
        $num_of_publication = $interactions->unique('pmid')->count();
        $num_of_tissues = $interactions->reject(
            function ($value) {
                return $value->tissue === 'NA';
            })
        ->unique('tissue')->count();

        $result = [];

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
            
            $result[$key]['publications'][] = $item;

        }
        
        $result = array_values($result);

        foreach ($result as &$items) {
            $uniqueExperiments = array_unique(array_column($items["publications"], 'method_name'));
            $items["unique_experiments_count"] = count($uniqueExperiments);
            $groupedPublications = [];

            foreach ($items["publications"] as $item) {
                $item = (array) $item;

                $key = $item['pmid']
                . '_' . $item['tissue'] . '_' . $item['cell_type']. '_' . $item['category']
                . '_' . $item['cell_line'] . '_' . $item['experimental_condition']. '_' . $item['method_name']

                ;
  
                if (!isset($groupedPublications[$key])) {
                    $groupedPublications[$key] = [
                        "author_name" => $item['author_name'],
                        "pub_year" => $item['pub_year'],
                        "title" => $item["title"] ,
                        "journal" => $item["journal"] ,
                        "pmid" => $item["pmid"] ,
                        "email_contact" => $item["email_contact"] ,
                        "abr" => $item["abr"], 
                        "highthroughput" => $item["highthroughput"],
                        "tissue" => $item['tissue'],
                        "cell_type" => $item['cell_type'],
                        "category" => $item['category'],
                        "cell_line" => $item['cell_line'],
                        "experimental_condition" => $item['experimental_condition'],
                        "method_name" => $item['method_name'],
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
                    $item["abr"], 
                    $item["highthroughput"],
                    $item['tissue'],
                    $item['cell_type'],
                    $item['category'],
                    $item['cell_line'],
                    $item['experimental_condition'],
                );
                $groupedPublications[$key]['binding_sites'][] = $item;
            }

            
            $items["publications"] = array_values($groupedPublications);

            // Compute unique 
            $uniqueTissues = array_unique(array_column($items["publications"], 'tissue'));
            $items["unique_tissues_count"] = count($uniqueTissues);

            $uniqueCellTypes = array_unique(array_column($items["publications"], 'cell_type'));
            $items["unique_cell_type_count"] = count($uniqueCellTypes);

            $uniquePublications = array_unique(array_column($items["publications"], 'pmid'));
            $items["unique_publications_count"] = count($uniquePublications);
        }


        $response_object = [
            'interactions' => $result,
            'num_of_interactions' => count($result),
            'num_of_experiments' => $num_of_experiments,
            'num_of_exp_low' => $num_of_exp_low,
            'num_of_exp_high' => $num_of_exp_high,
            'num_of_cell_lines' => $num_of_cell_lines,
            'num_of_tissues' => $num_of_tissues,
            'num_of_publication' => $num_of_publication,
        ];

        return $response_object;
    }

}
