<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExpressionsController extends Controller
{
    public function show(Request $request)
    {
        $validatedData = $request->validate([
            'geneNames' => 'nullable|string',
            'tissues' => 'nullable|string',
            'cellTypes' => 'nullable|string',
            'species' => 'nullable|string',
            'tpmLevels' => 'nullable|string'
        ]);

        $geneList = $this->stringToList($validatedData['geneNames'] ?? '');
        $tissuesList = $this->stringToList($validatedData['tissues'] ?? '');
        $cellTypesList = $this->stringToList($validatedData['cellTypes'] ?? '');
        $speciesList = $this->stringToList($validatedData['species'] ?? '');
        $tpmList = $this->stringToList($validatedData['tpmLevels'] ?? '');

        $filters = [
            'gene_name' => $geneList,
            'tissue' => $tissuesList,
            'cell_type' => $cellTypesList,
            'species' => $speciesList
        ];

        $expressions = DB::table('expressions')->whereNotNull('gene_name'); 
        
        foreach ($filters as $column => $values) {
            if (!empty($values)) {
                $expressions = $expressions->whereIn($column, $values);
            }
        }

        if (!empty($tpmList)) {
            $expressions->where(function ($query) use ($tpmList) {
                foreach ($tpmList as $tpm) {
                    if ($tpm === 'Low') {
                        $query->orWhereBetween('tpm', [1, 10]);
                    } elseif ($tpm === 'Medium') {
                        $query->orWhereBetween('tpm', [11, 600]);
                    } elseif ($tpm === 'High') {
                        $query->orWhere('tpm', '>', 600);
                    }
                }
            });
        }

        $expressions = $expressions->get();

        $result = $this->groupExpressions($expressions);

        return response()->json($result, 200);
    }
}
