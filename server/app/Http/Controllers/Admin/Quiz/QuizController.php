<?php

namespace App\Http\Controllers\Admin\Quiz;

use App\Events\QuizSaved;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Quiz\QuizResource;
use App\Http\Resources\Admin\Quiz\QuizSimpleResource;
use App\Models\Quiz\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Quiz::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('name', 'like', '%' . $searchString . '%')
                    ->orWhere('description', 'like', '%' . $searchString . '%')
                    ->orWhere('tags', 'like', '%' . $searchString . '%');
            }
        }

        $query->where('user_id', $request->user()->id);

        if ($request->has('orderBy') && $request->has('orderWay')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $quizzes = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => QuizSimpleResource::collection($quizzes->items()),
                'total' => $quizzes->total(),
                'perPage' => $quizzes->perPage(),
                'currentPage' => $quizzes->currentPage(),
                'lastPage' => $quizzes->lastPage(),
            ]);
        }

        $quizzes = $query->get();
        return Response::json(QuizSimpleResource::collection($quizzes));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $quiz = Quiz::find($id);
            if (!$quiz) {
                App::abort(404);
            }
        } else {
            $quiz = new Quiz();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
            'questions' => 'required|array',
            //'questions.*.name' => 'required|string|max:255', //TODO: změnit na name
            'questions.*.answers' => 'required|array|min:2',
            'questions.*.answers.*.name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $quiz->fill($request->all());
            $quiz->slug = Str::slug($request->name);
            $quiz->user_id = $request->user()->id;
            $quiz->save();
            $quiz->questions()->delete();
            foreach ($request->questions as $questionData) {
                $question = $quiz->questions()->create([
                    'name' => $questionData['name'],
                    'image' => array_key_exists('image', $questionData) ? $questionData['image'] : null,
                ]);
                foreach ($questionData['answers'] as $answer) {
                    $question->answers()->create([
                        'name' => $answer['name'],
                        'is_correct' => (bool)$answer['is_correct'] ?? false, // Assuming is_correct is a boolean field
                    ]);
                }
            }

            QuizSaved::dispatch($quiz);

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['error' => 'An error occurred while saving the quiz.'], 500);
        }

        return Response::json(QuizResource::make($quiz));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $quiz = Quiz::with(['questions', 'questions.answers'])->find($id);
        if (!$quiz) {
            App::abort(404);
        }

        return Response::json(QuizResource::make($quiz));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $quiz = Quiz::find($id);
        if (!$quiz) {
            App::abort(404);
        }

        $quiz->delete();
        return Response::json();
    }
}
