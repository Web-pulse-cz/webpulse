<?php

namespace App\Http\Controllers\Admin\PersonalGrowth;

use App\Http\Resources\Admin\PersonalGrowth\BookResource;
use App\Models\PersonalGrowth\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class BookController extends PersonalGrowthController
{
    public function index(Request $request): JsonResponse
    {
        $query = Book::query()
            ->where('user_id', $request->user()->id)
            ->withCount(['tasks', 'doneTasks']);

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('author', 'like', '%'.$search.'%');
                $this->keywordsLike($q, $search);
            });
        }

        $read = $request->get('read');
        if ($read === 'read' || $read === 'unread') {
            $query->where('is_read', $read === 'read');
        }

        $this->applyOrder($query, $request, ['name', 'author', 'is_read', 'created_at', 'updated_at'], 'created_at');

        if ($request->has('paginate')) {
            return Response::json($this->paginated($request, $query, BookResource::class));
        }

        return Response::json(BookResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $book = $this->findOwned(Book::class, $request, $id);
        $book->load('tasks');

        return Response::json(BookResource::make($book));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        $book = $id ? $this->findOwned(Book::class, $request, $id) : new Book;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'keywords' => 'nullable|array',
            'keywords.*' => 'nullable|string|max:100',
            'is_read' => 'boolean',
            'image' => 'nullable|string|max:255',
            'tasks' => 'nullable|array',
            'tasks.*.id' => 'nullable|integer',
            'tasks.*.title' => 'nullable|string|max:500',
            'tasks.*.is_done' => 'boolean',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $book->fill($request->only(['name', 'author', 'notes', 'is_read']));
            $book->keywords = $this->normalizeKeywords($request->get('keywords'));
            $book->user_id = $request->user()->id;
            $book->save();

            $book->saveImages($book, $request->get('image'));
            $book->syncTasks($request->get('tasks', []) ?? []);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return Response::json(['message' => 'An error occurred while saving book.'], 500);
        }

        $book->load('tasks');

        return Response::json(BookResource::make($book));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $book = $this->findOwned(Book::class, $request, $id);
        $book->delete();

        return Response::json();
    }
}
