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
        // Define the default take and page
        $page = config('config.default.page');
        $take = config('config.default.take');
        $total_count = $collection->count() > 0 ? $collection->count() : 1;


        // If the request has a take, and it's an integer, and it's greater than 1, set the take
        if ($request->has('take') && (int)$request->take && $request->take >= 1) {
            // If the take is greater than the total_count, set it to the total_count
            if ($request->take > $total_count) {
                $take = $total_count;
            } else {
                $take = $request->take;
            }
        }

        $total_pages = ceil($total_count / $take);

        // If the request has a page, and it's an integer, and it's greater than 1, set the page
        if ($request->has('page') && (int)$request->page && $request->page >= 1) {
            $page = $request->page;

            // If the page is too high, return the last page
            if ($page > $total_pages) {
                $page = $total_pages;
            }
        }

        $offset = ($page - 1) * $take;

        return [
            $collection->skip($offset)->take($take),
            $total_count,
            $total_pages,
            $page,
            $take,
        ];
    }
}
