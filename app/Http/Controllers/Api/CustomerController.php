<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\storeCustomerRequest;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): string
    {
        define("App\Http\Controllers\Api\DEFAULT_TAKE", 20);
        define("App\Http\Controllers\Api\DEFAULT_PAGE", 1);

        // If the page or take is not set, set them to default values
        $page = $request->page ?? DEFAULT_PAGE;
        $take = $request->take ?? DEFAULT_TAKE;

        // If the page is too low, set it to default value
        if ($page < DEFAULT_PAGE) {
            $page = DEFAULT_PAGE;
        }

        // If the take is too low or too high, set it to default value or the max
        if ($take < 1) {
            $take = DEFAULT_TAKE;
        } else if (Customer::count() < $take) {
            $take = Customer::count();
        }

        // If the page is too high, return the last page
        if ($page > ceil(Customer::count() / $take)) {
            $page = ceil(Customer::count() / $take);
        }

        $offset = ($page - 1) * $take;

        return Customer::skip($offset)->take($take)->get()->toJson();
    }

    /**
     * Store a newly created resource in storage.
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

        return Customer::findOrFail($customer->id)->toJson();
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): string
    {
        return Customer::findOrFail($customer->id)->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
