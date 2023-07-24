<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\storeCustomerRequest;
use App\Http\Requests\updateCustomerRequest;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JsonException;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @throws JsonException
     */
    public function index(Request $request): string
    {
        // Define the default take and page
        $page = config('config.default.page');
        $take = config('config.default.take');
        $total_count = Customer::count();

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

        $data = Customer::skip($offset)->take($take)->get();

        $content = [
            'data' => $data->toArray(),
            'total_elements' => $total_count,
            'total_pages' => $total_pages,
            'rendered_elements' => $data->count(),
            'page' => $page,
            'take' => $take,
            'previous_page' => $page > 1 ? ($request->url() . '?page=' . ($page - 1) . '&take=' . $take) : null,
            'next_page' => $page < $total_pages ? ($request->url() . '?page=' . ($page + 1) . '&take=' . $take) : null,
        ];

        return response(json_encode($content, JSON_THROW_ON_ERROR), 200)
            ->header('Content-type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Store a newly created resource in storage.
     * @throws JsonException
     */
    public function store(storeCustomerRequest $request): string
    {
        // Create the customer
        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'company_name' => $request->company_name ?? null,
            'email' => $request->email,
            'phone' => $request->phone ?? null,
        ]);

        if ($request->has('address')) {
            // Validate the address
            Validator::validate($request->address, [
                'street' => 'required|string',
                'street_complement' => 'nullable|string',
                'city' => 'required|string',
                'zip_code' => 'required|string',
                'country' => 'required|string',
            ]);
            // Create the address
            $address = Address::create([
                'id' => Str::uuid(),
                'street' => $request->address['street'] ?? null,
                'street_complement' => $request->address['street_complement'] ?? null,
                'city' => $request->address['city'] ?? null,
                'zip_code' => $request->address['zip_code'] ?? null,
                'country' => $request->address['country'] ?? null,
            ]);

            // Link the address to the customer
            $customer->address_id = $address->id;
            $customer->save();
        }

        $data = Customer::find($customer->id);

        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
        ];

        return response(json_encode($content, JSON_THROW_ON_ERROR), 201)
            ->header('Content-type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Display the specified resource.
     * @throws JsonException
     */
    public function show(Customer $customer): string
    {
        $data = Customer::findOrFail($customer->id);

        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
        ];

        return response(json_encode($content, JSON_THROW_ON_ERROR), 200)
            ->header('Content-type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Update the specified resource in storage.
     * @throws JsonException
     */
    public function update(updateCustomerRequest $request, Customer $customer) : string
    {
        // Update the customer
        $customer->update([
            'first_name' => $request->first_name ?? $customer->first_name,
            'last_name' => $request->last_name ?? $customer->last_name,
            'company_name' => $request->company_name ?? $customer->company_name,
            'email' => $request->email ?? $customer->email,
            'phone' => $request->phone ?? $customer->phone,
        ]);

        if ($request->has('address')) {
            // Validate the address
            Validator::validate($request->address, [
                'street' => 'required|string',
                'street_complement' => 'nullable|string',
                'city' => 'required|string',
                'zip_code' => 'required|string',
                'country' => 'required|string',
            ]);
            // Create the address
            $address = Address::create([
                'id' => Str::uuid(),
                'street' => $request->address['street'] ?? null,
                'street_complement' => $request->address['street_complement'] ?? null,
                'city' => $request->address['city'] ?? null,
                'zip_code' => $request->address['zip_code'] ?? null,
                'country' => $request->address['country'] ?? null,
            ]);
            $customer->address()->delete();

            // Link the address to the customer
            $customer->address_id = $address->id;
        }

        $customer->save();

        $data = Customer::findOrFail($customer->id);

        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
        ];

        return response(json_encode($content, JSON_THROW_ON_ERROR), 200)
            ->header('Content-type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'PUT')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
