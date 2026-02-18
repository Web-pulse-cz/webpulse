<?php

namespace App\Http\Controllers;

use App\Models\Activity\Activity;
use App\Models\Activity\UserActivity;
use App\Models\Cashflow\CashflowCategory;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactPhase;
use App\Models\Site\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function handleLanguage(?string $lang = null): string
    {
        if (!$lang) {
            $lang = App::getLocale();
        }
        App::setLocale($lang);

        return $lang;
    }

    public function handleSite(?string $hash = null): int
    {
        if (!$hash) {
            App::abort(404);
        }
        $site = Site::query()->where('hash', $hash)->first();
        if (!$site) {
            App::abort(404);
        }

        return $site->id;
    }

    public function dashboard(Request $request): JsonResponse
    {
        $lastAddedContacts = Contact::without(['phase', 'source', 'tasks'])
            ->orderBy('created_at', 'desc')
            ->where('user_id', $request->user()->id)
            ->limit(10)
            ->get();

        $contactsToCall = Contact::without(['phase', 'source', 'tasks'])
            ->whereDate('next_contact', now()->toDateString())
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $comingEvents = Contact::without(['phase', 'source', 'tasks'])
            ->whereDate('next_meeting', '>=', now()->toDateString())
            ->orderBy('next_meeting')
            ->where('user_id', $request->user()->id)
            ->get();

        return Response::json([
            'lastAddedContacts' => [
                'data' => $lastAddedContacts
            ],
            'contactsToCall' => [
                'data' => $contactsToCall
            ],
            'comingEvents' => [
                'data' => $comingEvents
            ]
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $businessGrowthActivityIds = [1, 6, 7, 8, 9, 10, 11, 12, 21, 22, 24];
        $personalGrowthActivityIds = [2, 3, 4, 5, 16, 17, 18, 27, 28, 29, 30];

        $daysMonths = now()->daysInMonth;
        if ($request->has('filter')) {
            if ($request->get('filter') == 'month' && $request->has('year')) {
                $daysMonths = Carbon::createFromDate($request->year, $request->month)->daysInMonth;
            } else if ($request->get('filter') == 'year') {
                $daysMonths = 12;
            }
        }

        $contactPhasesQuery = ContactPhase::query()
            ->where('user_id', $request->user()->id)
            ->where('show_in_statistics', true)
            ->withCount(['contacts' => function ($query) use ($request) {
                if ($request->has('from')) {
                    $query->whereDate('created_at', '>=', Carbon::parse($request->from));
                }
                if ($request->has('to')) {
                    $query->whereDate('created_at', '<=', Carbon::parse($request->to));
                }
            }])
            ->orderBy('position', 'asc')
            ->get();

        $businessActivitiesQuery = UserActivity::with('activity')
            ->where('user_id', $request->user()->id)
            ->whereIn('activity_id', $businessGrowthActivityIds)
            ->groupBy('activity_id', 'day');

        $personalActivitiesQuery = UserActivity::with('activity')
            ->where('user_id', $request->user()->id)
            ->whereIn('activity_id', $personalGrowthActivityIds)
            ->groupBy('activity_id', 'day');

        $cashflowQuery = CashflowCategory::with([
            'cashflows' => function ($query) use ($request) {
                if ($request->has('filter')) {
                    if ($request->get('filter') == 'month') {
                        if ($request->has('month') && $request->has('year')) {
                            $query->whereMonth('date', $request->month)
                                ->whereYear('date', $request->year)
                                ->whereMonth('date', $request->month)
                                ->whereYear('date', $request->year);
                        } else {
                            $query->whereMonth('date', now()->month)
                                ->whereYear('date', now()->year)
                                ->whereMonth('date', now()->month)
                                ->whereYear('date', now()->year);
                        }
                    } else if ($request->get('filter') == 'year') {
                        if ($request->has('year')) {
                            $query->whereYear('date', $request->year)
                                ->whereYear('date', $request->year);
                        } else {
                            $query->whereYear('date', now()->year)
                                ->whereYear('date', now()->year);
                        }
                    }
                }
            },
            'budgets' => function ($query) use ($request) {
                if ($request->has('filter')) {
                    if ($request->get('filter') == 'month') {
                        if ($request->has('month') && $request->has('year')) {
                            $query->whereMonth('start_date', $request->month)
                                ->whereYear('start_date', $request->year)
                                ->whereMonth('end_date', $request->month)
                                ->whereYear('end_date', $request->year);
                        } else {
                            $query->whereMonth('start_date', now()->month)
                                ->whereYear('start_date', now()->year)
                                ->whereMonth('end_date', now()->month)
                                ->whereYear('end_date', now()->year);
                        }
                    } else if ($request->get('filter') == 'year') {
                        if ($request->has('year')) {
                            $query->whereYear('start_date', $request->year)
                                ->whereYear('start_date', $request->year)
                                ->whereYear('end_date', $request->year)
                                ->whereYear('end_date', $request->year);
                        } else {
                            $query->whereYear('start_date', now()->year)
                                ->whereYear('start_date', now()->year)
                                ->whereYear('end_date', now()->year)
                                ->whereYear('end_date', now()->year);
                        }
                    }
                }
            }
        ])
            ->where('user_id', $request->user()->id);

        if ($request->has('filter')) {
            if ($request->get('filter') == 'month') {
                if ($request->has('month') && $request->has('year')) {
                    $businessActivitiesQuery->whereMonth('date', (int)$request->month)
                        ->whereYear('date', $request->year);
                    $personalActivitiesQuery->whereMonth('date', (int)$request->month)
                        ->whereYear('date', $request->year);
                } else {
                    $businessActivitiesQuery->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year);
                    $personalActivitiesQuery->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year);
                }
                $businessActivitiesQuery->selectRaw('activity_id, COUNT(*) as count, DATE_FORMAT(date, "%e. %c.") as day');
                $personalActivitiesQuery->selectRaw('activity_id, COUNT(*) as count, DATE_FORMAT(date, "%e. %c.") as day');
            } else if ($request->get('filter') == 'year') {
                if ($request->has('year')) {
                    $businessActivitiesQuery->whereYear('date', $request->year);
                    $personalActivitiesQuery->whereYear('date', $request->year);
                } else {
                    $businessActivitiesQuery->whereYear('date', now()->year);
                    $personalActivitiesQuery->whereYear('date', now()->year);
                }
                $businessActivitiesQuery->selectRaw('activity_id, COUNT(*) as count, DATE_FORMAT(date, "%c.") as day');
                $personalActivitiesQuery->selectRaw('activity_id, COUNT(*) as count, DATE_FORMAT(date, "%c.") as day');
            }
        }

        $businessActivities = $businessActivitiesQuery->get();
        $personalActivities = $personalActivitiesQuery->get();
        $cashflow = $cashflowQuery->get();

        $rawBusinessActivitiesArr = [];
        $rawPersonalActivitiesArr = [];
        $businessActivitiesRaw = Activity::query()
            ->whereIn('id', $businessGrowthActivityIds)
            ->get();
        $personalActivitiesRaw = Activity::query()
            ->whereIn('id', $personalGrowthActivityIds)
            ->get();

        $businessColors = [];
        $personalColors = [];
        $rawBusinessColors = [];
        $rawPersonalColors = [];
        foreach ($businessActivitiesRaw as $activity) {
            $rawBusinessActivitiesArr[$activity->id] = $activity->name;
            $businessColors[$activity->name] = $this->getColorCode($activity->color);
            $rawBusinessColors[] = $this->getColorCode($activity->color);
        }
        foreach ($personalActivitiesRaw as $activity) {
            $rawPersonalActivitiesArr[$activity->id] = $activity->name;
            $personalColors[$activity->name] = $this->getColorCode($activity->color);
            $rawPersonalColors[] = $this->getColorCode($activity->color);
        }

        $businessSeries = [];
        $personalSeries = [];
        $businessActivityData = [];
        $personalActivityData = [];
        $cashflowData = [];

        foreach ($rawBusinessActivitiesArr as $activityName) {
            $businessActivityData[$activityName] = array_fill(0, $daysMonths, 0);
        }

        foreach ($rawPersonalActivitiesArr as $activityName) {
            $personalActivityData[$activityName] = array_fill(0, $daysMonths, 0);
        }

        foreach ($businessActivities as $activity) {
            $activityName = $rawBusinessActivitiesArr[$activity->activity_id];
            if ($request->has('filter')) {
                if ($request->get('filter') == 'month' && $request->has('year')) {
                    $day = (int)explode('. ', $activity->day)[0] - 1;
                } else if ($request->get('filter') == 'year') {
                    $day = (int)$activity->day - 1;
                }
            }
            $businessActivityData[$activityName][$day] = $activity->count;
        }

        foreach ($personalActivities as $activity) {
            $activityName = $rawPersonalActivitiesArr[$activity->activity_id];
            if ($request->has('filter')) {
                if ($request->get('filter') == 'month' && $request->has('year')) {
                    $day = (int)explode('. ', $activity->day)[0] - 1;
                } else if ($request->get('filter') == 'year') {
                    $day = (int)$activity->day - 1;
                }
            }
            $personalActivityData[$activityName][$day] = $activity->count;
        }

        foreach ($businessActivityData as $name => $data) {
            $businessSeries[] = [
                'name' => $name,
                'data' => $data,
                'color' => $businessColors[$name]
            ];
        }
        foreach ($personalActivityData as $name => $data) {
            $personalSeries[] = [
                'name' => $name,
                'data' => $data,
                'color' => $personalColors[$name]
            ];
        }

        foreach ($cashflow as $cashflowCategory) {
            $spent = round($cashflowCategory->cashflows->sum('amount'), 2);
            $budget = round($cashflowCategory->budgets->sum('amount'), 2);
            $name = $cashflowCategory->name;

            $percentageLeft = (($budget - $spent) / $budget) * 100;

            if ($percentageLeft <= 0) {
                $stroke = [
                    'name' => $name,
                    'value' => $budget,
                    'strokeHeight' => 12,
                    'strokeWidth' => 0,
                    'strokeLineCap' => 'round',
                    'strokeColor' => '#7f1d1d',
                ];
            } else if ($percentageLeft <= 25) {
                $stroke = [
                    'name' => $name,
                    'value' => $budget,
                    'strokeHeight' => 1,
                    'strokeDashArray' => 2,
                    'strokeColor' => '#7f1d1d',
                ];
            } else {
                $stroke = [
                    'name' => $name,
                    'value' => $budget,
                    'strokeHeight' => 1,
                    'strokeColor' => '#7f1d1d',
                ];

            }

            $cashflowData[] = [
                'x' => $cashflowCategory->name,
                'y' => $spent,
                'goals' => [$stroke],
                'percentage' => $percentageLeft
            ];
        }

        $axis = [];
        if ($request->has('filter')) {
            if ($request->get('filter') == 'month' && $request->has('year')) {
                for ($day = 1; $day <= $daysMonths; $day++) {
                    $axis[] = $day . '. ' . $request->month . '.';
                }
            } else if ($request->get('filter') == 'year') {
                for ($month = 1; $month <= 12; $month++) {
                    $axis[] = $month . '.';
                }
            }
        }

        $contactsSeries = [0];
        $contactsAxis = ['Celkem'];
        foreach ($contactPhasesQuery as $contactPhase) {
            $contactsSeries[] = $contactPhase->contacts_count;
            $contactsAxis[] = $contactPhase->name;
            $contactsSeries[0] += $contactPhase->contacts_count;
        }

        return Response::json([
            'contacts' => [
                'series' => $contactsSeries,
                'axis' => $contactsAxis
            ],
            'business' => [
                'series' => $businessSeries,
                'axis' => $axis
            ],
            'personal' => [
                'series' => $personalSeries,
                'axis' => $axis
            ],
            'cashflow' => $cashflowData,
            'businessSummary' => [],
            'businessColors' => $rawBusinessColors,
            'personalColors' => $rawPersonalColors
        ]);
    }

    private function getColorCode(string $color): string
    {
        $code = '#020617';

        switch ($color) {
            case 'slate':
                $code = '#64748b';
                break;
            case 'gray':
                $code = '#6b7280';
                break;
            case 'zinc':
                $code = '#71717a';
                break;
            case 'neutral':
                $code = '#737373';
                break;
            case 'stone':
                $code = '#78716c';
                break;
            case 'red':
                $code = '#ef4444';
                break;
            case 'orange':
                $code = '#f97316';
                break;
            case 'amber':
                $code = '#f59e0b';
                break;
            case 'yellow':
                $code = '#eab308';
                break;
            case 'lime':
                $code = '#84cc16';
                break;
            case 'green':
                $code = '#22c55e';
                break;
            case 'emerald':
                $code = '#10b981';
                break;
            case 'teal':
                $code = '#14b8a6';
                break;
            case 'cyan':
                $code = '#06b6d4';
                break;
            case 'sky':
                $code = '#0ea5e9';
                break;
            case 'blue':
                $code = '#3b82f6';
                break;
            case 'indigo':
                $code = '#6366f1';
                break;
            case 'violet':
                $code = '#8b5cf6';
                break;
            case 'purple':
                $code = '#a855f7';
                break;
            case 'fuchsia':
                $code = '#d946ef';
                break;
            case 'pink':
                $code = '#ec4899';
                break;
            case 'rose':
                $code = '#f43f5e';
                break;
        }

        return $code;
    }
}
