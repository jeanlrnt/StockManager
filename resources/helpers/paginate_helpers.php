<?php

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

if (!function_exists('paginate')) {
    /**
     * Paginate a collection.
     *
     * @param Collection $collection
     * @param Request $request
     * @return array
     */
    function paginate(Collection $collection, Request $request): array
    {
        // Define the default limit and page
        $page = config('config.default.page');
        $limit = config('config.default.limit');
        $total_count = $collection->count() > 0 ? $collection->count() : 1;


        // If the request has a limit, and it's an integer, and it's greater than 1, set the limit
        if ($request->has('limit') && (int)$request->limit && $request->limit >= 1) {
            // If the limit is greater than the total_count, set it to the total_count
            if ($request->limit > $total_count) {
                $limit = $total_count;
            } else {
                $limit = $request->limit;
            }
        }

        $total_pages = ceil($total_count / $limit);

        // If the request has a page, and it's an integer, and it's greater than 1, set the page
        if ($request->has('page') && (int)$request->page && $request->page >= 1) {
            $page = $request->page;

            // If the page is too high, return the last page
            if ($page > $total_pages) {
                $page = $total_pages;
            }
        }

        $offset = ($page - 1) * $limit;

        $data = $collection->slice($offset, $limit);
        return [
            $data,
            [
                'first' => $request->fullUrlWithQuery(['page' => 1, 'limit' => $limit]),
                'last' => $request->fullUrlWithQuery(['page' => $total_pages, 'limit' => $limit]),
                'self' => $request->fullUrlWithQuery(['page' => $page, 'limit' => $limit]),
                'prev' => $page > 1 ? $request->fullUrlWithQuery(['page' => $page - 1, 'limit' => $limit]) : null,
                'next' => $page < $total_pages ? $request->fullUrlWithQuery(['page' => $page + 1, 'limit' => $limit]) : null,
            ],
            [
                'current_page' => (int)$page,
                'last_page' => (int)$total_pages,
                'from' => $offset + 1,
                'to' => $offset + $data->count(),
                'per_page' => (int)$limit,
                'total' => $total_count,
                'request' => $request->getRequestUri(),
            ],
        ];
    }
}
