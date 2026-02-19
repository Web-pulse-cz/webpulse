<?php

namespace App\Http\Controllers\Client\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Quiz\QuizResource;
use App\Http\Resources\Client\Quiz\QuizSimpleResource;
use App\Models\Quiz\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Quiz::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('status', 'public');

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where(function ($subQuery) use ($searchString) {
                    $subQuery->where('name', 'like', '%' . $searchString . '%')
                        ->orWhere('description', 'like', '%' . $searchString . '%')
                        ->orWhere('tags', 'like', '%' . $searchString . '%');
                });
            }
        }

        if ($request->has('filters') && is_array($request->get('filters')) && count($request->get('filters')) > 0) {
            $filters = $request->get('filters');
            foreach ($filters as $filter) {
                $query->where('tags', 'like', '%' . $filter . '%')
                    ->orWhere('tags', 'like', $filter);
            }
        }

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
    public function store(Request $request, int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400, 'Quiz ID is required');
        }

        $quiz = Quiz::with(['questions', 'questions.answers'])->find($id);
        if (!$quiz) {
            App::abort(404, 'Quiz not found');
        }

        $answers = [];

        $correctAnswers = 0;
        $incorrectAnswers = 0;
        foreach ($request['questions'] as $key => $question) {
            $answers[$key] = [
                'question' => array_key_exists('name', $question) ? $question['name'] : null,
                'image' => array_key_exists('image', $question) ? $question['image'] : null,
                'correctAnswer' => null,
                'userAnswer' => null,
                'isCorrect' => false,
            ];

            foreach ($question['answers'] as $answer) {
                $originalAnswer = $quiz->questions->find($question['id'])->answers->find($answer['id']);
                if ($originalAnswer) {
                    $answers[$key]['correctAnswer'] .= $originalAnswer->is_correct ? $originalAnswer->name : '';
                    if ($originalAnswer->is_correct && $answer['is_selected']) {
                        $answer['solved_correct'] = true;
                        $answers[$key]['userAnswer'] .= $originalAnswer->name;
                        $answers[$key]['isCorrect'] = true;
                        $correctAnswers++;
                    } else if (!$originalAnswer->is_correct && $answer['is_selected']) {
                        $answer['solved_correct'] = false;
                        $answers[$key]['userAnswer'] .= $originalAnswer->name;
                        $incorrectAnswers++;
                    }
                }
            }
        }

        $accuracy = $correctAnswers + $incorrectAnswers > 0 ? round(($correctAnswers / ($correctAnswers + $incorrectAnswers)) * 100) : 0;

        $quiz->attempts++;

        // average accuracy calculation
        if ($quiz->accuracy) {
            $quiz->accuracy = (($quiz->accuracy * ($quiz->attempts - 1)) + $accuracy) / $quiz->attempts;
        } else {
            $quiz->accuracy = $accuracy;
        }
        $quiz->save();

        return Response::json([
            'accuracy' => $accuracy,
            'quizAccuracy' => $quiz->accuracy,
            'correctAnswers' => $correctAnswers,
            'incorrectAnswers' => $incorrectAnswers,
            'answers' => $answers,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400, 'Quiz ID is required');
        }

        $quiz = Quiz::with(['questions' => function ($query) {
            $query->orderByRaw('RAND()');
        }, 'questions.answers' => function ($query) {
            $query->orderByRaw('RAND()');
        }])
            ->whereRelation('sites', 'site_id', $siteId)
            ->whereIn('status', ['public', 'private'])
            ->find($id);
        if (!$quiz) {
            App::abort(404, 'Quiz not found');
        }

        return Response::json(QuizResource::make($quiz));
    }

    public function filters(Request $request): JsonResponse
    {
        $query = Quiz::query()
            ->select('tags')
            ->where('status', '=', 'public')
            ->distinct()
            ->pluck('tags');

        $rawTags = [];
        foreach ($query as $tag) {
            $tags = explode(',', $tag);
            foreach ($tags as $item) {
                if (!array_key_exists($item, $rawTags)) {
                    $rawTags[ucfirst(trim($item))] = [
                        'name' => ucfirst(trim($item)),
                        'count' => Quiz::query()
                            ->where('status', '=', 'public')
                            ->where(function ($query) use ($item) {
                                $query->where('tags', 'like', '%' . $item . '%')
                                    ->orWhere('tags', 'like', $item);
                            })
                            ->count(),
                    ];
                }
            }
        }

        $tags = [];
        foreach ($rawTags as $item) {
            $tags[] = $item;
        }

        return Response::json($tags);
    }
}
