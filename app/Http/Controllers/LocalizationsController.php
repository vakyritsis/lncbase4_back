<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalizationsController extends Controller
{
    public function show(Request $request)
    {
        // Validate inputs
        $validatedData = $request->validate([
            'geneNames' => 'nullable|string',
            'tissues' => 'nullable|string',
            'cellTypes' => 'nullable|string',
            'species' => 'nullable|string',
        ]);

        $geneList = $this->stringToList($validatedData['geneNames'] ?? '');
        $tissuesList = $this->stringToList($validatedData['tissues'] ?? '');
        $cellTypesList = $this->stringToList($validatedData['cellTypes'] ?? '');
        $speciesList = $this->stringToList($validatedData['species'] ?? '');

        $filters = [
            'gene_name' => $geneList,
            'tissue' => $tissuesList,
            'cell_type' => $cellTypesList,
            'species' => $speciesList,
        ];

        $localizations = DB::table('localizations')->whereNotNull('gene_name'); 

        foreach ($filters as $column => $values) {
            if (!empty($values)) {
                $localizations = $localizations->whereIn($column, $values);
            }
        }

        $localizations = $localizations->get();

        $result = $this->groupExpressions($localizations);

        return response()->json($result, 200);
    }
}
