<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Notihnio\MultipartFormDataParser\MultipartFormDataParser;
use Symfony\Component\HttpFoundation\Response;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $user = Auth::user();

        // If the user is not logged in, or if the user is not authorized to view customers, return a 403 (Forbidden)
        if (!$user || $user->cannot('viewAny', Provider::class)) {
            return response()->json([
                'message' => 'You are not authorized to view providers.',
            ], Response::HTTP_FORBIDDEN);
        }

        [$data, $links, $meta] = paginate(Provider::all(), $request);

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No providers found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $content = [
            'data' => $data->toArray(),
            'links' => $links,
            'meta' => $meta,
            'message' => 'Providers retrieved successfully.',
        ];

        return response()->json($content, Response::HTTP_OK)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreProviderRequest $request
     * @return JsonResponse
     */
    public function store(StoreProviderRequest $request) : JsonResponse
    {
        $user = Auth::user();

        // If the user is not logged in, or if the user is not authorized to create customers, return a 403 (Forbidden)
        if (!$user || $user->cannot('create', Provider::class)) {
            return response()->json([
                'message' => 'You are not authorized to create a provider.',
            ], Response::HTTP_FORBIDDEN);
        }

        $provider = Provider::create($request->validated());

        if ($request->has('address')) {
            // Validate the address parameters if they exist and if they're valid
            $validated_address = Validator::validate($request->address, [
                'street' => 'required|string',
                'street_complement' => 'nullable|string',
                'city' => 'required|string',
                'zip_code' => 'required|string',
                'country' => 'required|string',
            ]);

            $provider->address()->create($validated_address);
        }

        $data = Provider::find($provider->id);
        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
            'message' => 'Provider created successfully',
        ];

        return response()->json($content, Response::HTTP_CREATED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Display the specified resource.
     * @param string $provider
     * @return JsonResponse
     */
    public function show(string $provider) : JsonResponse
    {
        $user = Auth::user();
        $real_provider = Provider::find($provider);
        // If the article doesn't exist, return a 404 (Not Found)
        if (!$real_provider) {
            return response()->json([
                'message' => 'Provider not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to view the provider, return a 403 (Forbidden)
        if (!$user || $user->cannot('view', $real_provider)) {
            return response()->json([
                'message' => 'You are not authorized to view this provider.',
            ], Response::HTTP_FORBIDDEN);
        }

        $content = [
            'data' => $real_provider->toArray(),
            'rendered_elements' => 1,
            'message' => 'Provider retrieved successfully',
        ];

        return response()->json($content, Response::HTTP_OK)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateProviderRequest $request
     * @param string $provider
     * @return JsonResponse
     */
    public function update(UpdateProviderRequest $request, string $provider) : JsonResponse
    {
        $user = Auth::user();
        $real_provider = Provider::find($provider);

        // If the provider doesn't exist, return a 404 (Not Found)
        if (!$real_provider) {
            return response()->json([
                'message' => 'Provider not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to update the provider, return a 403 (Forbidden)
        if (!$user || $user->cannot('update', $real_provider)) {
            return response()->json([
                'message' => 'You are not authorized to update this provider.',
            ], Response::HTTP_FORBIDDEN);
        }

        // If the request is multipart/form-data, parse the parameters from the request
        if ($request->all() === []) {
            $parameters = (object)MultipartFormDataParser::parse()?->params;
        } else {
            $parameters = (object)$request->all();
        }

        $real_provider->update([
            'name' => $parameters->name ?? $real_provider->name,
            'email' => $parameters->email ?? $real_provider->email,
            'phone' => $parameters->phone ?? $real_provider->phone,
        ]);

        // Parse the address parameters from the request and put them in an array to be validated
        $address = [];
        foreach ($parameters as $key => $parameter) {
            if (preg_match('/^address\[(.*)]$/', $key, $matches)) {
                $address[$matches[1]] = $parameter;
                unset($parameters->$key);
            }
        }
        $parameters->address = $address;

        if ($address) {
            // Validate the address parameters if they exist
            $validated_address = Validator::validate($parameters->address, [
                'street' => 'nullable|string',
                'street_complement' => 'nullable|string',
                'city' => 'nullable|string',
                'zip_code' => 'nullable|string',
                'country' => 'nullable|string',
            ]);

            // If the provider doesn't have an address, create one
            if (!$real_provider->address) {
                $real_provider->address()->create($validated_address);
            } else {
                // Otherwise, update the existing address
                $real_provider->address->update($validated_address);
            }
        }

        $data = Provider::find($real_provider->id);
        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
            'message' => 'Provider updated successfully',
        ];

        return response()->json($content, Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'PUT')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Remove the specified resource from storage.
     * @param string $provider
     * @return JsonResponse
     */
    public function destroy(string $provider): JsonResponse
    {
        $user = Auth::user();
        $real_provider = Provider::find($provider);

        // If the provider doesn't exist, return a 404 (Not Found)
        if (!$real_provider) {
            return response()->json([
                'message' => 'Provider not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to delete the provider, return a 403 (Forbidden)
        if (!$user || $user->cannot('delete', $real_provider)) {
            return response()->json([
                'message' => 'You are not authorized to delete this provider.',
            ], Response::HTTP_FORBIDDEN);
        }

        $real_provider->delete();

        return response()->json([
            'message' => 'Provider deleted successfully',
        ], Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'DELETE')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Force delete the specified resource from storage.
     * @param string $provider
     * @return JsonResponse
     */
    public function forceDelete(string $provider): JsonResponse
    {
        $user = Auth::user();
        $real_provider = Provider::onlyTrashed()->find($provider);

        // If the provider doesn't exist, return a 404 (Not Found)
        if (!$real_provider) {
            return response()->json([
                'message' => 'The provider was not found in the trash.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to force delete the provider, return a 403 (Forbidden)
        if (!$user || $user->cannot('forceDelete', $real_provider)) {
            return response()->json([
                'message' => 'You are not authorized to force delete this provider.',
            ], Response::HTTP_FORBIDDEN);
        }

        $real_provider->forceDelete();

        return response()->json([
            'message' => 'Provider force deleted successfully',
        ], Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'DELETE')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Restore the specified resource from storage.
     * @param string $provider
     * @return JsonResponse
     */
    public function restore(string $provider) : JsonResponse
    {
        $user = Auth::user();
        $real_provider = Provider::onlyTrashed()->find($provider);

        // If the provider doesn't exist, return a 404 (Not Found)
        if (!$real_provider) {
            return response()->json([
                'message' => 'The provider was not found in the trash.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to restore the provider, return a 403 (Forbidden)
        if (!$user || $user->cannot('restore', $real_provider)) {
            return response()->json([
                'message' => 'You are not authorized to restore this provider.',
            ], Response::HTTP_FORBIDDEN);
        }

        $real_provider->restore();

        $content = [
            'data' => $real_provider->toArray(),
            'rendered_elements' => 1,
            'message' => 'Provider restored successfully',
        ];

        return response()->json($content, Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }
}
