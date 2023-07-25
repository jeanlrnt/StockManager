<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\storeCustomerRequest;
use App\Http\Requests\updateCustomerRequest;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JsonException;
use Notihnio\MultipartFormDataParser\MultipartFormDataParser;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @throws JsonException
     */
    public function index(Request $request): string
    {
        $user = Auth::user();
        // If the user is not logged in, or if the user is not authorized to view customers, return a 403 (Forbidden)
        if (!$user || $user->cannot('viewAny', Customer::class)) {
            return response(json_encode(['message' => 'Unauthorized action.'], JSON_THROW_ON_ERROR), 403)
                ->header('Content-type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
        }

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
        $user = Auth::user();
        // If the user is not logged in, or if the user is not authorized to create a customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('create', Customer::class)) {
            return response(json_encode(['message' => 'Unauthorized action.'], JSON_THROW_ON_ERROR), 403)
                ->header('Content-type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST')
                ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
        }

        // Create the customer with the parameters from the request if they exist or with null values
        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'company_name' => $request->company_name ?? null,
            'email' => $request->email,
            'phone' => $request->phone ?? null,
        ]);

        // If the request has an address, create it and link it to the customer if it's valid and if it exists
        if ($request->has('address')) {
            // Validate the address parameters if they exist and if they're valid
            Validator::validate($request->address, [
                'street' => 'required|string',
                'street_complement' => 'nullable|string',
                'city' => 'required|string',
                'zip_code' => 'required|string',
                'country' => 'required|string',
            ]);
            // Create the address and link it to the customer if the validation was successful
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

        // Get the created customer from the database and return it as JSON if the creation was successful (201)
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
     * @param string $customer
     * @return string
     * @throws JsonException
     */
    public function show(string $customer): string
    {
        $user = Auth::user();
        $realCustomer = Customer::find($customer);
        // If the user is not logged in, or if the user is not authorized to view the customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('view', $realCustomer)) {
            return response(json_encode(['message' => 'Unauthorized action.'], JSON_THROW_ON_ERROR), 403)
                ->header('Content-type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
        }

        // If the customer doesn't exist, return a 404 (Not found)
        if (!$realCustomer) {
            return response(json_encode(['message' => 'Ressource not found.'], JSON_THROW_ON_ERROR), 404)
                ->header('Content-type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
        }

        $content = [
            'data' => $realCustomer->toArray(),
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
    public function update(updateCustomerRequest $request, string $customer): string
    {
        $user = Auth::user();
        $realCustomer = Customer::find($customer);

        // If the user is not logged in, or if the user is not authorized to update the customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('update', $realCustomer)) {
            return response(json_encode(['message' => 'Unauthorized action.'], JSON_THROW_ON_ERROR), 403)
                ->header('Content-type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'PUT')
                ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
        }

        // If the customer doesn't exist, return a 404 (Not found)
        if (!$realCustomer) {
            return response(json_encode(['message' => 'Ressource not found.'], JSON_THROW_ON_ERROR), 404)
                ->header('Content-type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'PUT')
                ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
        }

        // Parse the request parameters and put them in an object
        $parameters = (object)MultipartFormDataParser::parse()->params;

        // Update the customer with the parameters from the request if they exist or with the customer's current values
        $realCustomer->update([
            'first_name' => $parameters->first_name ?? $realCustomer->first_name,
            'last_name' => $parameters->last_name ?? $realCustomer->last_name,
            'company_name' => $parameters->company_name ?? $realCustomer->company_name,
            'email' => $parameters->email ?? $realCustomer->email,
            'phone' => $parameters->phone ?? $realCustomer->phone,
        ]);

        // Parse the address parameters from the request and put them in an array to be validated
        $address = [];
        foreach ($parameters as $key => $parameter) {
            if (preg_match('/^address\[(.*)]$/', $key)) {
                preg_match('/^address\[(.*)]$/', $key, $matches);
                $address[$matches[1]] = $parameter;
                unset($parameters->$key);
            }
        }
        $parameters->address = $address;

        if ($address) {
            // Validate the address parameters if they exist
            Validator::validate($parameters->address, [
                'street' => 'nullable|string',
                'street_complement' => 'nullable|string',
                'city' => 'nullable|string',
                'zip_code' => 'nullable|string',
                'country' => 'nullable|string',
            ]);

            // Create the address if it doesn't exist, or update it if it does exist and link it to the customer in both cases
            Address::updateOrCreate(['id' => $realCustomer->address_id],
                [
                    'id' => $realCustomer->address_id ?? Str::uuid(),
                    'street' => $parameters->address['street'] ?? $realCustomer->address->street,
                    'street_complement' => $parameters->address['street_complement'] ?? $realCustomer->address->street_complement,
                    'city' => $parameters->address['city'] ?? $realCustomer->address->city,
                    'zip_code' => $parameters->address['zip_code'] ?? $realCustomer->address->zip_code,
                    'country' => $parameters->address['country'] ?? $realCustomer->address->country,
                ]);
        }

        // Get the updated customer from the database and return it as JSON if the update was successful (202)
        $data = Customer::findOrFail($realCustomer->id);
        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
        ];

        return response(json_encode($content, JSON_THROW_ON_ERROR), 202)
            ->header('Content-type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'PUT')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Remove the specified resource from storage.
     * @param string $customer
     * @return Application|\Illuminate\Foundation\Application|Response|ResponseFactory
     * @throws JsonException
     */
    public function destroy(string $customer)
    {
        $user = Auth::user();
        $realCustomer = Customer::find($customer);

        // If the user is not logged in, or if the user is not authorized to delete the customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('delete', $realCustomer)) {
            return response(json_encode(['message' => 'Unauthorized action.'], JSON_THROW_ON_ERROR), 403)
                ->header('Content-type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'DELETE')
                ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
        }

        // If the customer doesn't exist, return a 404 (Not found)
        if (!$realCustomer) {
            return response(json_encode(['message' => 'Ressource not found.'], JSON_THROW_ON_ERROR), 404)
                ->header('Content-type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'DELETE')
                ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
        }

        // Delete the customer and return a 204 (No content) if the deletion was successful
        $realCustomer->delete();

        // Return a 202 if the deletion was successful (Accepted)
        return response(json_encode(['message' => 'Ressource successfully deleted.'], JSON_THROW_ON_ERROR), 202)
            ->header('Content-type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'DELETE')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }
}
