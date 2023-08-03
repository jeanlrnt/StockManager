<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Notihnio\MultipartFormDataParser\MultipartFormDataParser;
use Str;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $user = Auth::user();

        // If the user is not logged in, or if the user is not authorized to view articles, return a 403 (Forbidden)
        if (!$user || !$user->can('viewAny', Article::class)) {
            return response()->json([
                'message' => 'You are not authorized to view articles.'
            ], Response::HTTP_FORBIDDEN);
        }

        [$data, $links, $meta] = paginate(Article::all(), $request);

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No articles found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $content = [
            'data' => $data->toArray(),
            'links' => $links,
            'meta' => $meta,
            'message' => 'Articles retrieved successfully.'
        ];

        return response()->json($content, Response::HTTP_OK)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreArticleRequest $request
     * @return JsonResponse
     */
    public function store(StoreArticleRequest $request) : JsonResponse
    {
        $user = Auth::user();

        // If the user is not logged in, or if the user is not authorized to create articles, return a 403 (Forbidden)
        if (!$user || !$user->can('create', Article::class)) {
            return response()->json([
                'message' => 'You are not authorized to create articles.'
            ], Response::HTTP_FORBIDDEN);
        }

        $article = Article::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'provider' => $request->provider_id,
        ]);

        $data = Article::find($article->id);
        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
            'message' => 'Article created successfully.'
        ];

        return response()->json($content, Response::HTTP_CREATED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Display the specified resource.
     * @param string $article
     * @return JsonResponse
     */
    public function show(string $article): JsonResponse
    {
        $user = Auth::user();
        $real_article = Article::find($article);
        // If the article was not found, return a 404 (Not Found)
        if (!$real_article) {
            return response()->json([
                'message' => 'Article not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to view articles, return a 403 (Forbidden)
        if (!$user || $user->cannot('view', $real_article)) {
            return response()->json([
                'message' => 'You are not authorized to view this article.'
            ], Response::HTTP_FORBIDDEN);
        }


        $content = [
            'data' => $real_article->toArray(),
            'rendered_elements' => 1,
            'message' => 'Article retrieved successfully.'
        ];

        return response()->json($content, Response::HTTP_OK)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateArticleRequest $request
     * @param string $article
     * @return JsonResponse
     */
    public function update(UpdateArticleRequest $request, string $article): JsonResponse
    {
        $user = Auth::user();
        $real_article = Article::find($article);

        // If the article was not found, return a 404 (Not Found)
        if (!$real_article) {
            return response()->json([
                'message' => 'Article not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to update articles, return a 403 (Forbidden)
        if (!$user || $user->cannot('update', $real_article)) {
            return response()->json([
                'message' => 'You are not authorized to update this article.'
            ], Response::HTTP_FORBIDDEN);
        }

        // If the request is multipart/form-data, parse the parameters from the request
        if ($request->all() === []) {
            $parameters = (object)MultipartFormDataParser::parse()?->params;
        } else {
            $parameters = (object)$request->all();
        }

        $real_article->update([
            'title' => $parameters->title ?? $real_article->title,
            'slug' => Str::slug($parameters->title ?? $real_article->title),
            'provider' => $parameters->provider_id ?? $real_article->provider,
        ]);

        $real_article->save();

        $data = Article::find($real_article->id);
        $content = [
            'data' => $data->toArray(),
            'rendered_elements' => 1,
            'message' => 'Article updated successfully.'
        ];

        return response()->json($content, Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'PUT')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Remove the specified resource from storage.
     * @param string $article
     * @return JsonResponse
     */
    public function destroy(string $article): JsonResponse
    {
        $user = Auth::user();
        $real_article = Article::find($article);

        // If the article was not found, return a 404 (Not Found)
        if (!$real_article) {
            return response()->json([
                'message' => 'Article not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to delete articles, return a 403 (Forbidden)
        if (!$user || $user->cannot('delete', $real_article)) {
            return response()->json([
                'message' => 'You are not authorized to delete this article.'
            ], Response::HTTP_FORBIDDEN);
        }

        $real_article->delete();

        $content = [
            'message' => 'Article deleted successfully.'
        ];

        return response()->json($content, Response::HTTP_ACCEPTED)
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
        $real_article = Article::onlyTrashed()->find($provider);

        // If the article was not found, return a 404 (Not Found)
        if (!$real_article) {
            return response()->json([
                'message' => 'The article was not found in the trash.'
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to delete articles, return a 403 (Forbidden)
        if (!$user || $user->cannot('forceDelete', $real_article)) {
            return response()->json([
                'message' => 'You are not authorized to delete this article.'
            ], Response::HTTP_FORBIDDEN);
        }

        $real_article->forceDelete();

        $content = [
            'message' => 'Article deleted successfully.'
        ];

        return response()->json($content, Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'DELETE')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }

    /**
     * Restore the specified resource from storage.
     * @param string $provider
     * @return JsonResponse
     */
    public function restore(string $provider): JsonResponse
    {
        $user = Auth::user();
        $real_article = Article::onlyTrashed()->find($provider);

        // If the article was not found, return a 404 (Not Found)
        if (!$real_article) {
            return response()->json([
                'message' => 'The article was not found in the trash.'
            ], Response::HTTP_NOT_FOUND);
        }

        // If the user is not logged in, or if the user is not authorized to delete articles, return a 403 (Forbidden)
        if (!$user || $user->cannot('restore', $real_article)) {
            return response()->json([
                'message' => 'You are not authorized to delete this article.'
            ], Response::HTTP_FORBIDDEN);
        }

        $real_article->restore();

        $content = [
            'data' => $real_article->toArray(),
            'rendered_elements' => 1,
            'message' => 'Article restored successfully.'
        ];

        return response()->json($content, Response::HTTP_ACCEPTED)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST')
            ->header('Access-Control-Allow-Headers', 'Authorization, Accept');
    }
}
