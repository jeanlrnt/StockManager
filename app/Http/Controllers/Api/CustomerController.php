<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\storeCustomerRequest;
use App\Http\Requests\updateCustomerRequest;
use App\Models\Address;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Notihnio\MultipartFormDataParser\MultipartFormDataParser;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // If the user is not logged in, or if the user is not authorized to view customers, return a 403 (Forbidden)
        if (!$user || $user->cannot('viewAny', Customer::class)) {
            return response()->json([
                'message' => 'You are not authorized to view customers.',
            ], Response::HTTP_FORBIDDEN);
        }

        [$data, $links, $meta] = paginate(Customer::all(), $request);

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No customers found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $content = [
            'data' => $data->toArray(),
            'links' => $links,
            'meta' => $meta,
            'message' => 'Customers retrieved successfully',
        ];

        return response()->json($content, Response::HTTP_OK)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Store a newly created resource in storage.
     * @param storeCustomerRequest $request
     * @return JsonResponse
     */
    public function store(storeCustomerRequest $request): JsonResponse
    {
        $user = Auth::user();
        // If the user is not logged in, or if the user is not authorized to create a customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('create', Customer::class)) {
            return response()->json([
                'message' => 'You are not authorized to create a customer'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($request->has('first_name')) {
            $customerable = new Person([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'date_of_birth' => $request->date_of_birth ?? null,
                'gender' => $request->gender ?? null,
            ]);
        } else {
            $customerable = new Company([
                'name' => $request->name,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'website' => $request->website ?? null,
                'industry' => $request->industry ?? null,
                'number_of_employees' => $request->number_of_employees ?? null,
                'annual_revenue' => $request->annual_revenue ?? null,
                'description' => $request->description ?? null,
            ]);
        }
        // Create the customer with the parameters from the request if they exist or with null values
        $customer = Customer::create();
        $customer->customerable()->associate($customerable);

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
                'street' => $request->address['street'] ?? null,
                'street_complement' => $request->address['street_complement'] ?? null,
                'city' => $request->address['city'] ?? null,
                'zip_code' => $request->address['zip_code'] ?? null,
                'country' => $request->address['country'] ?? null,
                'addressable_id' => $customer->id,
                'addressable_type' => Customer::class,
            ]);

            $customer->customerable()->address()->save($address);
        }

        // Get the created customer from the database and return it as JSON if the creation was successful (201)
        $data = Customer::find($customer->id);
        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
            'message' => 'Customer created successfully',
        ];

        return response()->json($content, Response::HTTP_CREATED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Display the specified resource.
     * @param string $customer
     * @return JsonResponse
     */
    public function show(string $customer): JsonResponse
    {
        $user = Auth::user();
        $realCustomer = Customer::find($customer);
        // If the customer doesn't exist, return a 404 (Not found)
        if (!$realCustomer) {
            return response()->json([
                'message' => 'The customer was not found',
            ], Response::HTTP_NOT_FOUND);
        }
        // If the user is not logged in, or if the user is not authorized to view the customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('view', $realCustomer)) {
            return response()->json([
                'message' => 'You are not authorized to view this customer',
            ], Response::HTTP_FORBIDDEN);
        }

        $content = [
            'data' => $realCustomer->toArray(),
            'rendered_elements' => 1,
            'message' => 'Customer retrieved successfully',
        ];

        return response()->json($content, Response::HTTP_OK)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Update the specified resource in storage.
     * @param updateCustomerRequest $request
     * @param string $customer
     * @return JsonResponse
     */
    public function update(updateCustomerRequest $request, string $customer): JsonResponse
    {
        $user = Auth::user();
        $realCustomer = Customer::find($customer);

        // If the customer doesn't exist, return a 404 (Not found)
        if (!$realCustomer) {
            return response()->json([
                'message' => 'The customer you are trying to update does not exist.',
            ], Response::HTTP_NOT_FOUND);
        }
        // If the user is not logged in, or if the user is not authorized to update the customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('update', $realCustomer)) {
            return response()->json([
                'message' => 'You are not authorized to update this customer.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($request->all() === [] && $request->getContent() === '') {
            return response()->json([
                'message' => 'No parameters were provided.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // If the request is a multipart/form-data request, parse the parameters from the request
        if ($request->all() === []) {
            $parameters = (object)MultipartFormDataParser::parse()?->params;
        } else {
            $parameters = (object)$request->all();
        }

        // Update the customer with the parameters from the request if they exist or with the customer's current values
        if ($realCustomer->customerable_type == 'App\Models\Person') {
            $realCustomer->customerable()->update([
                'first_name' => $parameters->first_name ?? $realCustomer->customerable->first_name,
                'last_name' => $parameters->last_name ?? $realCustomer->customerable->last_name,
                'email' => $parameters->email ?? $realCustomer->customerable->email,
                'phone' => $parameters->phone ?? $realCustomer->customerable->phone,
            ]);
        } else if ($realCustomer->customerable_type == 'App\Models\Company') {
            $realCustomer->customerable()->update([
                'name' => $parameters->name ?? $realCustomer->customerable->name,
                'email' => $parameters->email ?? $realCustomer->customerable->email,
                'phone' => $parameters->phone ?? $realCustomer->customerable->phone,
            ]);
        }

        $parameters->address = parse_address($parameters);

        if ($parameters->address) {
            // Validate the address parameters if they exist
            $validated_address = Validator::validate($parameters->address, [
                'street' => 'nullable|string',
                'street_complement' => 'nullable|string',
                'city' => 'nullable|string',
                'zip_code' => 'nullable|string',
                'country' => 'nullable|string',
            ]);

            // If the customer doesn't have an address, create it and link it to the customer
            if (!$realCustomer->customerable()->address()) {
                $realCustomer->customerable()->address()->create($validated_address);
            } else {
                // If the customer already has an address, update it with the validated address parameters
                $realCustomer->customerable()->address()->update($validated_address);
            }
        }

        // Get the updated customer from the database and return it as JSON if the update was successful (202)
        $data = Customer::findOrFail($realCustomer->id);
        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
            'message' => 'Customer updated successfully',
        ];

        return response()->json($content, Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Remove the specified resource from storage.
     * @param string $customer
     * @return JsonResponse
     */
    public function destroy(string $customer): JsonResponse
    {
        $user = Auth::user();
        $realCustomer = Customer::find($customer);

        // If the customer doesn't exist, return a 404 (Not found)
        if (!$realCustomer) {
            return response()->json([
                'message' => 'The customer you are trying to delete does not exist.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to delete the customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('delete', $realCustomer)) {
            return response()->json([
                'message' => 'You are not authorized to delete this customer.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Delete the customer and return a 204 (No content) if the deletion was successful
        $realCustomer->delete();

        // Return a 202 if the deletion was successful (Accepted)
        return response()->json([
            'message' => 'Customer successfully deleted.',
        ], Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'DELETE')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Restore the specified resource from storage.
     * @param string $customer
     * @return JsonResponse
     */
    public function restore(string $customer): JsonResponse
    {
        $user = Auth::user();
        $realCustomer = Customer::onlyTrashed()->find($customer);

        // If the customer doesn't exist, return a 404 (Not found)
        if (!$realCustomer) {
            return response()->json([
                'message' => 'The customer was not found in the trash.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to restore the customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('restore', $realCustomer)) {
            return response()->json([
                'message' => 'You are not authorized to restore this customer.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Restore the customer and return a 202 (Accepted) if the restoration was successful
        $realCustomer->restore();

        $content = [
            'data' => $realCustomer->toArray(),
            'rendered_elements' => 1,
            'message' => 'Customer successfully restored.',
        ];

        return response()->json($content, Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Permanently remove the specified resource from storage.
     * @param string $customer
     * @return JsonResponse
     */
    public function forceDelete(string $customer): JsonResponse
    {
        $user = Auth::user();
        $realCustomer = Customer::onlyTrashed()->find($customer);

        // If the customer doesn't exist, return a 404 (Not found)
        if (!$realCustomer) {
            return response()->json([
                'message' => 'The customer was not found in the trash.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to delete the customer, return a 403 (Forbidden)
        if (!$user || $user->cannot('forceDelete', $realCustomer)) {
            return response()->json([
                'message' => 'You are not authorized to force-delete this customer.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Delete the customer and return a 202 (No content) if the deletion was successful
        $realCustomer->forceDelete();

        // Return a 202 if the deletion was successful (Accepted)
        return response()->json([
            'message' => 'Customer successfully force deleted.',
        ], Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'DELETE')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }
}
