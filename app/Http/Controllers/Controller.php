<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
    public function stringToList($input)
    {
        return empty($input) ? [] : explode(',', $input);
    }

    public function groupExpressions($expressions) 
    { 

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

        return $result;

    }
}
