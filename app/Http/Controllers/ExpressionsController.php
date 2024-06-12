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

        $expressions = DB::table('expressions');

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

        $result = [];
        foreach ($expressions as $item) {
            $item = (array) $item;
            $key = $item['gene_name'] . '_' . $item['ensembl_transcript_id'];

            if (!isset($result[$key])) {
                $result[$key] = [
                    'gene_name' => $item['gene_name'],
                    'internal_tid' => $item['internal_tid'],
                    'ensembl_transcript_id' => $item['ensembl_transcript_id'],
                    'key' => $key,
                    'expressions' => []
                ];
            }

            unset($item['gene_name'], $item['internal_tid'], $item['ensembl_transcript_id']);
            $result[$key]['expressions'][] = $item;
        }

        $result = array_values($result);

        foreach ($result as &$item) {
            $uniqueTissues = array_unique(array_column($item['expressions'], 'tissue'));
            $item['unique_tissues_count'] = count(array_filter($uniqueTissues, fn($tissue) => $tissue !== 'NA'));

            $uniqueCellTypes = array_unique(array_column($item['expressions'], 'cell_type'));
            $item['unique_cell_type_count'] = count(array_filter($uniqueCellTypes, fn($cellType) => $cellType !== 'NA'));
        }
        unset($item);

        return response()->json($result, 200);
    }
}
