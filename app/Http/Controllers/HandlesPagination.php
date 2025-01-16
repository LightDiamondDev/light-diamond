<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait HandlesPagination
{
    use ApiJsonResponseTrait;

    protected function validatePagination(Request $request, array $rules = []): \Illuminate\Validation\Validator
    {
        $defaultRules = [
            'page'       => ['integer'],
            'per_page'   => ['integer'],
            'sort_field' => ['string'],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ];

        return Validator::make($request->all(), array_merge($defaultRules, $rules));
    }

    protected function getPaginationParameters(Request $request, string $defaultSortField = 'created_at', int $defaultSortOrder = -1): array
    {
        $perPage   = $request->integer('per_page', 10);
        $sortOrder = $request->integer('sort_order', $defaultSortOrder);
        $sortField = $sortOrder === 0
            ? $defaultSortField
            : $request->string('sort_field', $defaultSortField);

        $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';

        return compact('perPage', 'sortField', 'sortDirection');
    }

    protected function paginateResponse($paginator): JsonResponse
    {
        return $this->successJsonResponse([
            'records'    => $paginator->items(),
            'pagination' => [
                'total_records' => $paginator->total(),
                'current_page'  => $paginator->currentPage(),
                'total_pages'   => $paginator->lastPage(),
            ],
        ]);
    }
}
