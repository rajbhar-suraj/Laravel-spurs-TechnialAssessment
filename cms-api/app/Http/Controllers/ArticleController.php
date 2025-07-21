<?php
namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\LLMService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['categories', 'author'])->latest();

        if ($request->has('category_id') && $request->input('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->input('category_id'));
            });
        }

        // Status filter
        if ($request->has('status') && $request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        // Date range filter
        if ($request->has('start_date') && $request->input('start_date')) {
            $query->whereDate('published_at', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date') && $request->input('end_date')) {
            $query->whereDate('published_at', '<=', $request->input('end_date'));
        }

        return $query->paginate(10);
    }

    protected $llmService;

    public function __construct(LLMService $llmService)
    {
        $this->llmService = $llmService;
    }

    protected function generateSlug(string $title): string
    {

        try {
            $prompt  = "Generate a SEO-friendly URL slug for: \"{$title}\". Return only the slug, no other text. Use lowercase with hyphens.";
            $llmSlug = $this->llmService->generateText(
                $prompt,
                30,                      // max tokens
                'gryphe/mythomax-l2-13b' // Free model
            );

            $slug = $llmSlug ? Str::slug($llmSlug) : Str::slug($title);

            // Ensure uniqueness
            $count = Article::where('slug', 'LIKE', "{$slug}%")->count();
            return $count ? "{$slug}-{$count}" : $slug;

        } catch (\Exception $e) {
            Log::warning('LLM slug generation failed', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);
            return Str::slug($title);
        }
    }

    protected function generateSummary(string $content): string
    {
        try {
            $prompt  = "Create a concise 1-sentence summary (max 150 characters) for this article content:\n\n{$content}\n\nSummary:";
            $summary = $this->llmService->generateText(
                $prompt,
                150,                     // max tokens
                'gryphe/mythomax-l2-13b' // Free model
            );

            return $summary ?: Str::limit(strip_tags($content), 150);

        } catch (\Exception $e) {
            Log::warning('LLM summary generation failed', [
                'error'   => $e->getMessage(),
                'content' => Str::limit($content, 50),
            ]);
            return Str::limit(strip_tags($content), 150);
        }
    }

    public function store(Request $request)
    {

        if ($request->wantsJson()) {
            $validated = $request->validate([
                'title'        => 'required',
                'content'      => 'required',
                'published_at' => 'nullable|date',
                'status'       => 'required|in:draft,published,archived',
                'categories'   => 'required|array|min:1',
                'categories.*' => 'exists:categories,id',
            ]);

            if ($validated['status'] === 'published' && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }
            try {
                $validated['slug']    = $this->generateSlug($validated['title']);
                $validated['summary'] = $this->generateSummary($validated['content']);
                $validated['user_id'] = auth()->id();

                $article = Article::create($validated);
                if (! empty($validated['categories'])) {
                    $article->categories()->sync($validated['categories']);
                }

                return response()->json([
                    'message' => 'Article created successfully',
                    'data'    => $article->load('categories'),
                ], 201);

            } catch (\Exception $e) {
                \Log::error('Article creation failed:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'message' => 'Failed to create article',
                    'error'   => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }
        }

        abort(400, 'JSON requests only');
    }

    public function show($id)
    {
        $article = Article::with(['author', 'categories'])->find($id);
        if (! $article) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        return response()->json([
            'data'    => $article,
            'message' => 'Article retrieved successfully',
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'content'      => 'sometimes|string',
            'published_at' => 'sometimes|date',
            'status'       => 'sometimes|in:draft,published,archived',
            'categories'   => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $article = Article::find($id);
         if (! $article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        // Update fields
        $article->fill($validated);

        // Regenerate derived fields if needed
        if ($request->has('title')) {
            $article->slug = Str::slug($validated['title']);
        }

        if ($request->has('content')) {
            $article->summary = $this->generateSummary($validated['content']);
        }

        $article->save();

        // Update relationships
        if ($request->has('categories')) {
            $article->categories()->sync($validated['categories']);
        }

        return response()->json([
            'data'    => $article->fresh(['author', 'categories']),
            'message' => 'Article updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $article = Article::find($id);
        if (! $article) {
            return response()->json(['message' => 'Article not found'], 404);
        }
        $article->categories()->detach();
        $article->delete();

        return response()->json(null, 204);
    }

    // ... (generateSummary method from above)
}
