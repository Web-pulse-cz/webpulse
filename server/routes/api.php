<?php

use App\Http\Controllers\Admin\Activity\ActivityController;
use App\Http\Controllers\Admin\Activity\UserActivityController;
use App\Http\Controllers\Admin\Amenity\AmenityController;
use App\Http\Controllers\Admin\Apartment\ApartmentBlockController;
use App\Http\Controllers\Admin\Apartment\ApartmentController;
use App\Http\Controllers\Admin\Apartment\ApartmentTypeController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Biography\BiographyController;
use App\Http\Controllers\Admin\Building\BuildingController;
use App\Http\Controllers\Admin\Blog\PostCategoryController;
use App\Http\Controllers\Admin\Blog\PostController;
use App\Http\Controllers\Admin\Career\CareerApplicationController;
use App\Http\Controllers\Admin\Career\CareerController;
use App\Http\Controllers\Admin\Cashflow\CashflowBudgetController;
use App\Http\Controllers\Admin\Cashflow\CashflowCategoryController;
use App\Http\Controllers\Admin\Cashflow\CashflowController;
use App\Http\Controllers\Admin\Changelog\ChangelogController;
use App\Http\Controllers\Admin\Client\ClientController;
use App\Http\Controllers\Admin\Contact\ContactController;
use App\Http\Controllers\Admin\Contact\ContactListController;
use App\Http\Controllers\Admin\Contact\ContactPhaseController;
use App\Http\Controllers\Admin\Contact\ContactSourceController;
use App\Http\Controllers\Admin\Contact\ContactTaskController;
use App\Http\Controllers\Admin\Contract\ContractController;
use App\Http\Controllers\Admin\Country\CountryController;
use App\Http\Controllers\Admin\Currency\CurrencyController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\Customer\CustomerGroupController;
use App\Http\Controllers\Admin\Dashboard\DashboardWidgetController;
use App\Http\Controllers\Admin\Demand\DemandController;
use App\Http\Controllers\Admin\Email\EmailController;
use App\Http\Controllers\Admin\Employee\EmployeeContractController;
use App\Http\Controllers\Admin\Employee\EmployeeController;
use App\Http\Controllers\Admin\Employee\EmployeeDivisionController;
use App\Http\Controllers\Admin\Event\EventCategoryController;
use App\Http\Controllers\Admin\Event\EventController;
use App\Http\Controllers\Admin\Event\EventRegistrationController;
use App\Http\Controllers\Admin\Fakturoid\FakturoidWebhookController;
use App\Http\Controllers\Admin\Faq\FaqCategoryController;
use App\Http\Controllers\Admin\Faq\FaqController;
use App\Http\Controllers\Admin\Filemanager\FilemanagerController as AdminFilemanagerController;
use App\Http\Controllers\Admin\Food\Allergen\AllergenController;
use App\Http\Controllers\Admin\Food\Foodstuff\FoodstuffCategoryController;
use App\Http\Controllers\Admin\Food\Foodstuff\FoodstuffController;
use App\Http\Controllers\Admin\Food\Meal\MealCategoryController;
use App\Http\Controllers\Admin\Food\Meal\MealController;
use App\Http\Controllers\Admin\Food\Menu\MenuController;
use App\Http\Controllers\Admin\Food\Menu\MenuSectionController;
use App\Http\Controllers\Admin\Food\Recipe\RecipeCategoryController;
use App\Http\Controllers\Admin\Food\Recipe\RecipeController;
use App\Http\Controllers\Admin\Invoice\InvoiceController;
use App\Http\Controllers\Admin\Language\LanguageController;
use App\Http\Controllers\Admin\Logo\LogoController;
use App\Http\Controllers\Admin\Message\MessageBlueprintController;
use App\Http\Controllers\Admin\Newsletter\NewsletterController;
use App\Http\Controllers\Admin\Novelty\NoveltyController;
use App\Http\Controllers\Admin\Page\PageController;
use App\Http\Controllers\Admin\PriceOffer\PriceOfferController;
use App\Http\Controllers\Admin\Project\ProjectController;
use App\Http\Controllers\Admin\Project\ProjectCostController;
use App\Http\Controllers\Admin\Project\ProjectMilestoneController;
use App\Http\Controllers\Admin\Project\ProjectNoteController;
use App\Http\Controllers\Admin\Project\ProjectStatusController;
use App\Http\Controllers\Admin\Project\ProjectTaskBoardController;
use App\Http\Controllers\Admin\Project\ProjectTaskCategoryController;
use App\Http\Controllers\Admin\Project\ProjectTaskCommentController;
use App\Http\Controllers\Admin\Project\ProjectTaskController;
use App\Http\Controllers\Admin\Project\ProjectTimeEntryController;
use App\Http\Controllers\Admin\Project\TagController;
use App\Http\Controllers\Admin\Quiz\QuizController;
use App\Http\Controllers\Admin\Reservation\ReservationController as ApartmentReservationController;
use App\Http\Controllers\Admin\Restaurant\ReservationController;
use App\Http\Controllers\Admin\Restaurant\RestaurantTableController;
use App\Http\Controllers\Admin\Review\ReviewController;
use App\Http\Controllers\Admin\Season\SeasonController;
use App\Http\Controllers\Admin\Service\ServiceController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\Shift\ShiftController;
use App\Http\Controllers\Admin\Shift\ShiftTemplateController;
use App\Http\Controllers\Admin\Site\SiteController;
use App\Http\Controllers\Admin\Task\TaskBoardController;
use App\Http\Controllers\Admin\Task\TaskController;
use App\Http\Controllers\Admin\TaxRate\TaxRateController;
use App\Http\Controllers\Admin\TimeEntry\TimeEntryController;
use App\Http\Controllers\Admin\User\ProfileController;
use App\Http\Controllers\Admin\User\QuickAccessController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\User\UserGroupController;
use App\Http\Controllers\Admin\Voucher\VoucherController;
use App\Http\Controllers\Client\Blog\PostCategoryController as ClientPostCategoryController;
use App\Http\Controllers\Client\Blog\PostController as ClientPostController;
use App\Http\Controllers\Client\Career\CareerApplicationController as ClientCareerApplicationController;
use App\Http\Controllers\Client\Career\CareerController as ClientCareerController;
use App\Http\Controllers\Client\Demand\DemandController as ClientDemandController;
use App\Http\Controllers\Client\Event\EventCategoryController as ClientEventCategoryController;
use App\Http\Controllers\Client\Event\EventController as ClientEventController;
use App\Http\Controllers\Client\Event\EventRegistrationController as ClientEventRegistrationController;
use App\Http\Controllers\Client\Faq\FaqCategoryController as ClientFaqCategoryController;
use App\Http\Controllers\Client\Faq\FaqController as ClientFaqController;
use App\Http\Controllers\Client\Logo\LogoController as ClientLogoController;
use App\Http\Controllers\Client\Newsletter\NewsletterController as ClientNewsletterController;
use App\Http\Controllers\Client\Novelty\NoveltyController as ClientNoveltyController;
use App\Http\Controllers\Client\Page\PageController as ClientPageController;
use App\Http\Controllers\Client\Quiz\QuizController as ClientQuizController;
use App\Http\Controllers\Client\Review\ReviewController as ClientReviewController;
use App\Http\Controllers\Client\Service\ServiceController as ClientServiceController;
use App\Http\Controllers\Client\Setting\SettingController as ClientSettingController;
use App\Http\Controllers\Controller as BaseController;
use App\Http\Controllers\FilemanagerController;
use Illuminate\Support\Facades\Route;

// Fakturoid webhook (no auth required)
Route::post('webhook/fakturoid', [FakturoidWebhookController::class, 'handle']);

Route::group([
    'prefix' => 'filemanager',
], function () {
    Route::get('formats', [FilemanagerController::class, 'getImageFormats']);
    Route::group([
        'prefix' => 'upload',
    ], function () {
        Route::post('images', [FilemanagerController::class, 'uploadImages']);
        // Route::post('files', [FilemanagerController::class, 'uploadFiles']);
    });
});

Route::group([
    'prefix' => 'setting',
], function () {
    Route::get('{lang?}', [ClientSettingController::class, 'index']);
});

/**
 * -------------------------------------------------------------------------
 * Client routes
 * -------------------------------------------------------------------------
 */
Route::group([
    'prefix' => 'service',
], function () {
    Route::get('{lang?}', [ClientServiceController::class, 'index']);
    Route::get('{id}/{lang?}', [ClientServiceController::class, 'show'])->where('id', '[0-9]+');
});

Route::group([
    'prefix' => 'demand',
], function () {
    Route::post('{lang?}', [ClientDemandController::class, 'store']);
});

Route::group([
    'prefix' => 'blog',
], function () {
    Route::group([
        'prefix' => 'category',
    ], function () {
        Route::get('{lang?}', [ClientPostCategoryController::class, 'index']);
        Route::get('{id}/{lang?}', [ClientPostCategoryController::class, 'show'])->where('id', '[0-9]+');
    });

    Route::group([
        'prefix' => 'post',
    ], function () {
        Route::get('{lang?}', [ClientPostController::class, 'index']);
        Route::get('{id}/{lang?}', [ClientPostController::class, 'show'])->where('id', '[0-9]+');
    });
});

Route::group([
    'prefix' => 'page',
], function () {
    Route::get('{id}/{lang?}', [ClientPageController::class, 'show'])->where('id', '[0-9]+');
});

Route::group([
    'prefix' => 'logo',
], function () {
    Route::get('{lang?}', [ClientLogoController::class, 'index']);
});

Route::group([
    'prefix' => 'novelty',
], function () {
    Route::get('{lang?}', [ClientNoveltyController::class, 'index']);
    Route::get('{id}/{lang?}', [ClientNoveltyController::class, 'show'])->where('id', '[0-9]+');
});

Route::group([
    'prefix' => 'review',
], function () {
    Route::get('{lang?}', [ClientReviewController::class, 'index']);
});

Route::group([
    'prefix' => 'newsletter',
], function () {
    Route::post('{lang?}', [ClientNewsletterController::class, 'store']);
});

Route::group([
    'prefix' => 'faq',
], function () {
    Route::group([
        'prefix' => 'category',
    ], function () {
        Route::get('{lang?}', [ClientFaqCategoryController::class, 'index']);
        Route::get('{id}/{lang?}', [ClientFaqCategoryController::class, 'show'])->where('id', '[0-9]+');
    });
    Route::get('{lang?}', [ClientFaqController::class, 'index']);
});

Route::group([
    'prefix' => 'event',
], function () {
    Route::group([
        'prefix' => 'category',
    ], function () {
        Route::get('{lang?}', [ClientEventCategoryController::class, 'index']);
        Route::get('{id}/{lang?}', [ClientEventCategoryController::class, 'show'])->where('id', '[0-9]+');
    });

    Route::group([
        'prefix' => 'registration',
    ], function () {
        Route::post('{lang?}', [ClientEventRegistrationController::class, 'store']);
    });

    Route::get('{lang?}', [ClientEventController::class, 'index']);
    Route::get('{id}/{lang?}', [ClientEventController::class, 'show'])->where('id', '[0-9]+');
});

Route::group([
    'prefix' => 'career',
], function () {
    Route::group([
        'prefix' => 'application',
    ], function () {
        Route::post('{lang?}', [ClientCareerApplicationController::class, 'store']);
    });

    Route::get('{lang?}', [ClientCareerController::class, 'index']);
    Route::get('{id}/{lang?}', [ClientCareerController::class, 'show'])->where('id', '[0-9]+');
});

Route::group([
    'prefix' => 'quiz',
], function () {
    Route::get('', [ClientQuizController::class, 'index']);
    Route::get('{id}', [ClientQuizController::class, 'show'])->where('id', '[0-9]+');
    Route::post('{id}', [ClientQuizController::class, 'store'])->where('id', '[0-9]+');
    Route::get('filter', [ClientQuizController::class, 'filters']);
});

/**
 * -------------------------------------------------------------------------
 * Admin routes
 * -------------------------------------------------------------------------
 */
Route::group([
    'prefix' => 'admin',
], function () {
    Route::post('register', [RegisterController::class, 'index']);

    // Login, logout, and refresh token routes
    Route::group([
        'prefix' => 'auth',
    ], function () {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('refresh', [LoginController::class, 'refresh'])->middleware('auth:sanctum');
        Route::get('me', [LoginController::class, 'me'])->middleware('auth:sanctum');
        Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
        Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);
    });

    // User routes
    Route::group([
        'middleware' => ['auth:sanctum', 'check.group.site'],
    ], function () {
        // Quick access routes
        Route::group([
            'prefix' => 'quick-access',
        ], function () {
            Route::get('', [QuickAccessController::class, 'index']);
            Route::get('{id}', [QuickAccessController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [QuickAccessController::class, 'store']);
            Route::delete('{id}', [QuickAccessController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Profile routes
        Route::group([
            'prefix' => 'profile',
        ], function () {
            Route::get('', [ProfileController::class, 'index']);
            Route::post('', [ProfileController::class, 'store']);
            Route::post('password', [ProfileController::class, 'password']);
        });

        // User routes
        Route::group([
            'prefix' => 'user',
        ], function () {
            // User group routes
            Route::group([
                'prefix' => 'group',
            ], function () {
                Route::get('', [UserGroupController::class, 'index']);
                Route::get('{id}', [UserGroupController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [UserGroupController::class, 'store']);
                Route::delete('{id}', [UserGroupController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::group([
                'prefix' => 'activity',
            ], function () {
                Route::get('', [UserActivityController::class, 'index']);
                Route::get('{id}', [UserActivityController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [UserActivityController::class, 'store']);
                Route::delete('{id}', [UserActivityController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [UserController::class, 'index']);
            Route::get('{id}', [UserController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [UserController::class, 'store']);
            Route::delete('{id}', [UserController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Contact routes
        Route::group([
            'prefix' => 'contact',
        ], function () {
            // Contact phase routes
            Route::group([
                'prefix' => 'phase',
            ], function () {
                Route::get('', [ContactPhaseController::class, 'index']);
                Route::get('{id}', [ContactPhaseController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ContactPhaseController::class, 'store']);
                Route::delete('{id}', [ContactPhaseController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Contact source routes
            Route::group([
                'prefix' => 'source',
            ], function () {
                Route::get('', [ContactSourceController::class, 'index']);
                Route::get('{id}', [ContactSourceController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ContactSourceController::class, 'store']);
                Route::delete('{id}', [ContactSourceController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Contact task routes
            Route::group([
                'prefix' => 'task',
            ], function () {
                Route::get('', [ContactTaskController::class, 'index']);
                Route::get('{id}', [ContactTaskController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ContactTaskController::class, 'store']);
                Route::delete('{id}', [ContactTaskController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Contact list routes
            Route::group([
                'prefix' => 'list',
            ], function () {
                Route::get('', [ContactListController::class, 'index']);
                Route::get('{id}', [ContactListController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ContactListController::class, 'store']);
                Route::delete('{id}', [ContactListController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [ContactController::class, 'index']);
            Route::get('{id}', [ContactController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ContactController::class, 'store']);
            Route::delete('{id}', [ContactController::class, 'destroy'])->where('id', '[0-9]+');
            Route::post('history/{id}/{historyId?}', [ContactController::class, 'history'])->where('id', '[0-9]+')->where('historyId', '[0-9]+');
            Route::delete('history/destroy/{id}', [ContactController::class, 'historyDestroy'])->where('id', '[0-9]+');
        });

        Route::group([
            'prefix' => 'message',
        ], function () {
            Route::group([
                'prefix' => 'blueprint',
            ], function () {
                Route::get('', [MessageBlueprintController::class, 'index']);
                Route::get('{id}', [MessageBlueprintController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [MessageBlueprintController::class, 'store']);
                Route::delete('{id}', [MessageBlueprintController::class, 'destroy'])->where('id', '[0-9]+');
            });
        });

        // Activity routes
        Route::group([
            'prefix' => 'activity',
        ], function () {
            Route::get('', [ActivityController::class, 'index']);
            Route::get('{id}', [ActivityController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ActivityController::class, 'store']);
            Route::delete('{id}', [ActivityController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Cashflow routes
        Route::group([
            'prefix' => 'cashflow',
        ], function () {
            // Cashflow category routes
            Route::group([
                'prefix' => 'category',
            ], function () {
                Route::get('', [CashflowCategoryController::class, 'index']);
                Route::get('{id}', [CashflowCategoryController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [CashflowCategoryController::class, 'store']);
                Route::delete('{id}', [CashflowCategoryController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Cashflow budget routes
            Route::group([
                'prefix' => 'budget',
            ], function () {
                Route::post('{id?}', [CashflowBudgetController::class, 'store']);
            });

            Route::post('{id?}', [CashflowController::class, 'store']);
        });

        // Tax-rate routes
        Route::group([
            'prefix' => 'tax-rate',
        ], function () {
            Route::get('', [TaxRateController::class, 'index']);
            Route::get('{id}', [TaxRateController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [TaxRateController::class, 'store']);
            Route::delete('{id}', [TaxRateController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Language routes
        Route::group([
            'prefix' => 'language',
        ], function () {
            Route::get('', [LanguageController::class, 'index']);
            Route::get('{id}', [LanguageController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [LanguageController::class, 'store']);
            Route::delete('{id}', [LanguageController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Currency routes
        Route::group([
            'prefix' => 'currency',
        ], function () {
            Route::get('', [CurrencyController::class, 'index']);
            Route::get('{id}', [CurrencyController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [CurrencyController::class, 'store']);
            Route::delete('{id}', [CurrencyController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Country routes
        Route::group([
            'prefix' => 'country',
        ], function () {
            Route::get('', [CountryController::class, 'index']);
            Route::get('{id}', [CountryController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [CountryController::class, 'store']);
            Route::delete('{id}', [CountryController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Service routes
        Route::group([
            'prefix' => 'service',
        ], function () {
            Route::get('', [ServiceController::class, 'index']);
            Route::get('{id}', [ServiceController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ServiceController::class, 'store']);
            Route::delete('{id}', [ServiceController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Novelties routes
        Route::group([
            'prefix' => 'novelty',
        ], function () {
            Route::get('', [NoveltyController::class, 'index']);
            Route::get('{id}', [NoveltyController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [NoveltyController::class, 'store']);
            Route::delete('{id}', [NoveltyController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Demands routes
        Route::group([
            'prefix' => 'demand',
        ], function () {
            Route::get('', [DemandController::class, 'index']);
            Route::get('{id}', [DemandController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [DemandController::class, 'store']);
            Route::delete('{id}', [DemandController::class, 'destroy'])->where('id', '[0-9]+');
        });

        Route::group([
            'prefix' => 'post',
        ], function () {
            Route::group([
                'prefix' => 'category',
            ], function () {
                Route::get('', [PostCategoryController::class, 'index']);
                Route::get('{id}', [PostCategoryController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [PostCategoryController::class, 'store']);
                Route::delete('{id}', [PostCategoryController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [PostController::class, 'index']);
            Route::get('{id}', [PostController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [PostController::class, 'store']);
            Route::delete('{id}', [PostController::class, 'destroy'])->where('id', '[0-9]+');
            Route::post('{id}/file', [PostController::class, 'uploadFile'])->where('id', '[0-9]+');
            Route::get('{postId}/file/{fileId}', [PostController::class, 'downloadFile'])->where(['postId' => '[0-9]+', 'fileId' => '[0-9]+']);
            Route::delete('{postId}/file/{fileId}', [PostController::class, 'deleteFile'])->where(['postId' => '[0-9]+', 'fileId' => '[0-9]+']);
        });

        // Page routes
        Route::group([
            'prefix' => 'page',
        ], function () {
            Route::get('', [PageController::class, 'index']);
            Route::get('{id}', [PageController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [PageController::class, 'store']);
            Route::delete('{id}', [PageController::class, 'destroy'])->where('id', '[0-9]+');
            Route::post('{id}/file', [PageController::class, 'uploadFile'])->where('id', '[0-9]+');
            Route::get('{pageId}/file/{fileId}', [PageController::class, 'downloadFile'])->where(['pageId' => '[0-9]+', 'fileId' => '[0-9]+']);
            Route::delete('{pageId}/file/{fileId}', [PageController::class, 'deleteFile'])->where(['pageId' => '[0-9]+', 'fileId' => '[0-9]+']);
        });

        // Filemanager routes
        Route::group([
            'prefix' => 'filemanager',
        ], function () {
            Route::get('', [AdminFilemanagerController::class, 'index']);
            Route::get('{id}', [AdminFilemanagerController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [AdminFilemanagerController::class, 'store']);
            Route::delete('{id}', [AdminFilemanagerController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Client routes
        Route::group([
            'prefix' => 'client',
        ], function () {
            Route::get('', [ClientController::class, 'index']);
            Route::get('{id}', [ClientController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ClientController::class, 'store']);
            Route::delete('{id}', [ClientController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Invoice routes
        Route::group([
            'prefix' => 'invoice',
        ], function () {
            Route::get('', [InvoiceController::class, 'index']);
            Route::get('{id}', [InvoiceController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [InvoiceController::class, 'store']);
            Route::delete('{id}', [InvoiceController::class, 'destroy'])->where('id', '[0-9]+');
            Route::get('{invoiceId}/file/{fileId}', [InvoiceController::class, 'downloadFile'])->where(['invoiceId' => '[0-9]+', 'fileId' => '[0-9]+']);
        });

        // Projects routes
        Route::group([
            'prefix' => 'project',
        ], function () {
            // Project status routes
            Route::group([
                'prefix' => 'status',
            ], function () {
                Route::get('', [ProjectStatusController::class, 'index']);
                Route::get('{id}', [ProjectStatusController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ProjectStatusController::class, 'store']);
                Route::delete('{id}', [ProjectStatusController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Tag routes
            Route::group([
                'prefix' => 'tag',
            ], function () {
                Route::get('', [TagController::class, 'index']);
                Route::get('{id}', [TagController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [TagController::class, 'store']);
                Route::delete('{id}', [TagController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [ProjectController::class, 'index']);
            Route::get('{id}', [ProjectController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ProjectController::class, 'store']);
            Route::delete('{id}', [ProjectController::class, 'destroy'])->where('id', '[0-9]+');
            Route::post('{id}/file', [ProjectController::class, 'uploadFile'])->where('id', '[0-9]+');
            Route::get('{projectId}/file/{fileId}', [ProjectController::class, 'downloadFile'])->where(['projectId' => '[0-9]+', 'fileId' => '[0-9]+']);
            Route::delete('{projectId}/file/{fileId}', [ProjectController::class, 'deleteFile'])->where(['projectId' => '[0-9]+', 'fileId' => '[0-9]+']);

            // Project sub-resources
            Route::group([
                'prefix' => '{projectId}',
            ], function () {
                // Milestones
                Route::post('milestone/{id?}', [ProjectMilestoneController::class, 'store']);
                Route::delete('milestone/{id}', [ProjectMilestoneController::class, 'destroy'])->where('id', '[0-9]+');
                Route::post('milestone/{id}/complete', [ProjectMilestoneController::class, 'complete'])->where('id', '[0-9]+');

                // Task categories
                Route::get('task-category', [ProjectTaskCategoryController::class, 'index']);
                Route::post('task-category/{id?}', [ProjectTaskCategoryController::class, 'store']);
                Route::delete('task-category/{id}', [ProjectTaskCategoryController::class, 'destroy'])->where('id', '[0-9]+');

                // Task boards
                Route::get('task-board', [ProjectTaskBoardController::class, 'index']);
                Route::post('task-board/{id?}', [ProjectTaskBoardController::class, 'store']);
                Route::delete('task-board/{id}', [ProjectTaskBoardController::class, 'destroy'])->where('id', '[0-9]+');

                // Tasks
                Route::get('task', [ProjectTaskController::class, 'index']);
                Route::get('task/{id}', [ProjectTaskController::class, 'show'])->where('id', '[0-9]+');
                Route::post('task/{id?}', [ProjectTaskController::class, 'store']);
                Route::post('task/{id}/move', [ProjectTaskController::class, 'move'])->where('id', '[0-9]+');
                Route::post('task/reorder', [ProjectTaskController::class, 'reorder']);
                Route::delete('task/{id}', [ProjectTaskController::class, 'destroy'])->where('id', '[0-9]+');

                // Task comments
                Route::post('task/{taskId}/comment/{id?}', [ProjectTaskCommentController::class, 'store'])->where('taskId', '[0-9]+');
                Route::delete('task/{taskId}/comment/{id}', [ProjectTaskCommentController::class, 'destroy'])->where(['taskId' => '[0-9]+', 'id' => '[0-9]+']);

                // Time entries (project-scoped)
                Route::post('time-entry/{id?}', [ProjectTimeEntryController::class, 'store']);
                Route::delete('time-entry/{id}', [ProjectTimeEntryController::class, 'destroy'])->where('id', '[0-9]+');
                Route::post('timer/start', [ProjectTimeEntryController::class, 'startTimer']);
                Route::post('timer/{id}/stop', [ProjectTimeEntryController::class, 'stopTimer'])->where('id', '[0-9]+');

                // Costs
                Route::post('cost/{id?}', [ProjectCostController::class, 'store']);
                Route::delete('cost/{id}', [ProjectCostController::class, 'destroy'])->where('id', '[0-9]+');

                // Notes
                Route::post('note/{id?}', [ProjectNoteController::class, 'store']);
                Route::delete('note/{id}', [ProjectNoteController::class, 'destroy'])->where('id', '[0-9]+');
            })->where('projectId', '[0-9]+');
        });

        // Global task routes
        Route::group([
            'prefix' => 'task',
        ], function () {
            Route::get('', [TaskController::class, 'index']);
            Route::get('{id}', [TaskController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [TaskController::class, 'store']);
            Route::post('{id}/move', [TaskController::class, 'move'])->where('id', '[0-9]+');
            Route::delete('{id}', [TaskController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Global task board routes
        Route::group([
            'prefix' => 'task-board',
        ], function () {
            Route::get('', [TaskBoardController::class, 'index']);
            Route::post('{id?}', [TaskBoardController::class, 'store']);
            Route::delete('{id}', [TaskBoardController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Global time tracking routes
        Route::group([
            'prefix' => 'time-entry',
        ], function () {
            Route::get('', [TimeEntryController::class, 'index']);
            Route::post('{id?}', [TimeEntryController::class, 'store']);
            Route::delete('{id}', [TimeEntryController::class, 'destroy'])->where('id', '[0-9]+');
            Route::get('running', [TimeEntryController::class, 'getRunning']);
            Route::post('timer/start', [TimeEntryController::class, 'startTimer']);
            Route::post('timer/{id}/stop', [TimeEntryController::class, 'stopTimer'])->where('id', '[0-9]+');
            Route::get('export-pdf', [TimeEntryController::class, 'exportPdf']);
        });

        // Customer routes
        Route::group([
            'prefix' => 'customer',
        ], function () {
            // Customer groups
            Route::group([
                'prefix' => 'group',
            ], function () {
                Route::get('', [CustomerGroupController::class, 'index']);
                Route::get('{id}', [CustomerGroupController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [CustomerGroupController::class, 'store']);
                Route::delete('{id}', [CustomerGroupController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [CustomerController::class, 'index']);
            Route::get('{id}', [CustomerController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [CustomerController::class, 'store']);
            Route::delete('{id}', [CustomerController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Voucher routes
        Route::group([
            'prefix' => 'voucher',
        ], function () {
            Route::get('', [VoucherController::class, 'index']);
            Route::get('{id}', [VoucherController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [VoucherController::class, 'store']);
            Route::delete('{id}', [VoucherController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Employee routes
        Route::group([
            'prefix' => 'employee',
        ], function () {
            // Divisions
            Route::group([
                'prefix' => 'division',
            ], function () {
                Route::get('', [EmployeeDivisionController::class, 'index']);
                Route::get('{id}', [EmployeeDivisionController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [EmployeeDivisionController::class, 'store']);
                Route::delete('{id}', [EmployeeDivisionController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [EmployeeController::class, 'index']);
            Route::get('{id}', [EmployeeController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [EmployeeController::class, 'store']);
            Route::delete('{id}', [EmployeeController::class, 'destroy'])->where('id', '[0-9]+');
            Route::post('{id}/file', [EmployeeController::class, 'uploadFile'])->where('id', '[0-9]+');
            Route::get('{employeeId}/file/{fileId}', [EmployeeController::class, 'downloadFile'])->where(['employeeId' => '[0-9]+', 'fileId' => '[0-9]+']);
            Route::delete('{employeeId}/file/{fileId}', [EmployeeController::class, 'deleteFile'])->where(['employeeId' => '[0-9]+', 'fileId' => '[0-9]+']);

            // Contracts (nested under employee)
            Route::group([
                'prefix' => '{employeeId}/contract',
            ], function () {
                Route::get('', [EmployeeContractController::class, 'index']);
                Route::get('{id}', [EmployeeContractController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [EmployeeContractController::class, 'store']);
                Route::delete('{id}', [EmployeeContractController::class, 'destroy'])->where('id', '[0-9]+');
                Route::get('{id}/pdf', [EmployeeContractController::class, 'pdf'])->where('id', '[0-9]+');
            })->where('employeeId', '[0-9]+');
        });

        // Shift routes
        Route::group([
            'prefix' => 'shift',
        ], function () {
            // Shift templates
            Route::group([
                'prefix' => 'template',
            ], function () {
                Route::get('', [ShiftTemplateController::class, 'index']);
                Route::get('{id}', [ShiftTemplateController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ShiftTemplateController::class, 'store']);
                Route::delete('{id}', [ShiftTemplateController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [ShiftController::class, 'index']);
            Route::get('{id}', [ShiftController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ShiftController::class, 'store']);
            Route::delete('{id}', [ShiftController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Dashboard and statistics routes
        Route::get('dashboard/widget', [DashboardWidgetController::class, 'index']);
        Route::post('dashboard/widget', [DashboardWidgetController::class, 'store']);
        Route::get('dashboard/contact', [BaseController::class, 'dashboardContact']);
        Route::get('statistics', [BaseController::class, 'statistics']);

        // Contract routes
        Route::group([
            'prefix' => 'contract',
        ], function () {
            Route::get('', [ContractController::class, 'index']);
            Route::get('{id}', [ContractController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ContractController::class, 'store']);
            Route::delete('{id}', [ContractController::class, 'destroy'])->where('id', '[0-9]+');
            Route::get('{contractId}/file/{fileId}', [ContractController::class, 'downloadFile']);
        });

        // Price offer routes
        Route::group([
            'prefix' => 'price-offer',
        ], function () {
            Route::get('', [PriceOfferController::class, 'index']);
            Route::get('{id}', [PriceOfferController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [PriceOfferController::class, 'store']);
            Route::delete('{id}', [PriceOfferController::class, 'destroy'])->where('id', '[0-9]+');
            Route::post('{id}/accept', [PriceOfferController::class, 'accept'])->where('id', '[0-9]+');
            Route::post('{id}/reject', [PriceOfferController::class, 'reject'])->where('id', '[0-9]+');
            Route::get('{id}/pdf', [PriceOfferController::class, 'pdf'])->where('id', '[0-9]+');
            Route::get('{offerId}/file/{fileId}', [PriceOfferController::class, 'downloadFile']);
        });

        // Log routes
        Route::group([
            'prefix' => 'log',
        ], function () {
            Route::group([
                'prefix' => 'email',
            ], function () {
                Route::get('', [EmailController::class, 'index']);
                Route::get('{id}', [EmailController::class, 'show'])->where('id', '[0-9]+');
            });
        });

        // Service routes
        Route::group([
            'prefix' => 'setting',
        ], function () {
            Route::get('', [SettingController::class, 'index']);
            Route::get('{id}', [SettingController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [SettingController::class, 'store']);
            Route::delete('{id}', [SettingController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Review routes
        Route::group([
            'prefix' => 'review',
        ], function () {
            Route::get('', [ReviewController::class, 'index']);
            Route::get('{id}', [ReviewController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ReviewController::class, 'store']);
            Route::delete('{id}', [ReviewController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Logo routes
        Route::group([
            'prefix' => 'logo',
        ], function () {
            Route::get('', [LogoController::class, 'index']);
            Route::get('{id}', [LogoController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [LogoController::class, 'store']);
            Route::delete('{id}', [LogoController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Newsletter routes
        Route::group([
            'prefix' => 'newsletter',
        ], function () {
            Route::get('', [NewsletterController::class, 'index']);
            Route::get('{id}', [NewsletterController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Faq routes
        Route::group([
            'prefix' => 'faq',
        ], function () {
            Route::group([
                'prefix' => 'category',
            ], function () {
                Route::get('', [FaqCategoryController::class, 'index']);
                Route::get('{id}', [FaqCategoryController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [FaqCategoryController::class, 'store']);
                Route::delete('{id}', [FaqCategoryController::class, 'destroy'])->where('id', '[0-9]+');
            });
            Route::get('', [FaqController::class, 'index']);
            Route::get('{id}', [FaqController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [FaqController::class, 'store']);
            Route::delete('{id}', [FaqController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Event routes
        Route::group([
            'prefix' => 'event',
        ], function () {
            Route::group([
                'prefix' => 'category',
            ], function () {
                Route::get('', [EventCategoryController::class, 'index']);
                Route::get('{id}', [EventCategoryController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [EventCategoryController::class, 'store']);
                Route::delete('{id}', [EventCategoryController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::group([
                'prefix' => 'registration',
            ], function () {
                Route::get('', [EventRegistrationController::class, 'index']);
                Route::get('{id}', [EventRegistrationController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [EventRegistrationController::class, 'store']);
                Route::delete('{id}', [EventRegistrationController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [EventController::class, 'index']);
            Route::get('{id}', [EventController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [EventController::class, 'store']);
            Route::delete('{id}', [EventController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Accommodation routes
        Route::group([
            'prefix' => 'building',
        ], function () {
            Route::get('', [BuildingController::class, 'index']);
            Route::get('{id}', [BuildingController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [BuildingController::class, 'store']);
            Route::delete('{id}', [BuildingController::class, 'destroy'])->where('id', '[0-9]+');
        });

        Route::group([
            'prefix' => 'amenity',
        ], function () {
            Route::get('', [AmenityController::class, 'index']);
            Route::get('{id}', [AmenityController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [AmenityController::class, 'store']);
            Route::delete('{id}', [AmenityController::class, 'destroy'])->where('id', '[0-9]+');
        });

        Route::group([
            'prefix' => 'season',
        ], function () {
            Route::get('', [SeasonController::class, 'index']);
            Route::get('{id}', [SeasonController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [SeasonController::class, 'store']);
            Route::delete('{id}', [SeasonController::class, 'destroy'])->where('id', '[0-9]+');
        });

        Route::group([
            'prefix' => 'apartment',
        ], function () {
            Route::group([
                'prefix' => 'type',
            ], function () {
                Route::get('', [ApartmentTypeController::class, 'index']);
                Route::get('{id}', [ApartmentTypeController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ApartmentTypeController::class, 'store']);
                Route::delete('{id}', [ApartmentTypeController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::group([
                'prefix' => 'block',
            ], function () {
                Route::get('', [ApartmentBlockController::class, 'index']);
                Route::get('{id}', [ApartmentBlockController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ApartmentBlockController::class, 'store']);
                Route::delete('{id}', [ApartmentBlockController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::group([
                'prefix' => 'reservation',
            ], function () {
                Route::get('', [ApartmentReservationController::class, 'index']);
                Route::get('{id}', [ApartmentReservationController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ApartmentReservationController::class, 'store']);
                Route::delete('{id}', [ApartmentReservationController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [ApartmentController::class, 'index']);
            Route::get('{id}', [ApartmentController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ApartmentController::class, 'store']);
            Route::delete('{id}', [ApartmentController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Career routes
        Route::group([
            'prefix' => 'career',
        ], function () {
            Route::group([
                'prefix' => 'application',
            ], function () {
                Route::get('', [CareerApplicationController::class, 'index']);
                Route::get('{id}', [CareerApplicationController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [CareerApplicationController::class, 'store']);
                Route::delete('{id}', [CareerApplicationController::class, 'destroy'])->where('id', '[0-9]+');
            });

            Route::get('', [CareerController::class, 'index']);
            Route::get('{id}', [CareerController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [CareerController::class, 'store']);
            Route::delete('{id}', [CareerController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Quiz routes
        Route::group([
            'prefix' => 'quiz',
        ], function () {
            Route::get('', [QuizController::class, 'index']);
            Route::get('{id}', [QuizController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [QuizController::class, 'store']);
            Route::delete('{id}', [QuizController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Biographies routes
        Route::group([
            'prefix' => 'biography',
        ], function () {
            Route::get('', [BiographyController::class, 'index']);
            Route::get('{id}', [BiographyController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [BiographyController::class, 'store']);
            Route::delete('{id}', [BiographyController::class, 'destroy'])->where('id', '[0-9]+');
            Route::get('download/{id}', [BiographyController::class, 'download'])->where('id', '[0-9]+');
            Route::post('replicate/{id}', [BiographyController::class, 'replicate'])->where('id', '[0-9]+');
        });

        // Sites routes
        Route::group([
            'prefix' => 'site',
        ], function () {
            Route::get('', [SiteController::class, 'index']);
            Route::get('{id}', [SiteController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [SiteController::class, 'store']);
            Route::delete('{id}', [SiteController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Changelog routes
        Route::group([
            'prefix' => 'changelog',
        ], function () {
            Route::get('', [ChangelogController::class, 'index']);
            Route::get('{id}', [ChangelogController::class, 'show'])->where('id', '[0-9]+');
            Route::post('{id?}', [ChangelogController::class, 'store']);
            Route::delete('{id}', [ChangelogController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // Routes relatable with food
        Route::group([
            'prefix' => 'food',
        ], function () {
            // Allergen routes
            Route::group([
                'prefix' => 'allergen',
            ], function () {
                Route::get('', [AllergenController::class, 'index']);
                Route::get('{id}', [AllergenController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [AllergenController::class, 'store']);
                Route::delete('{id}', [AllergenController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Foodstuff routes
            Route::group([
                'prefix' => 'foodstuff',
            ], function () {
                Route::group([
                    'prefix' => 'category',
                ], function () {
                    Route::get('', [FoodstuffCategoryController::class, 'index']);
                    Route::get('{id}', [FoodstuffCategoryController::class, 'show'])->where('id', '[0-9]+');
                    Route::post('{id?}', [FoodstuffCategoryController::class, 'store']);
                    Route::delete('{id}', [FoodstuffCategoryController::class, 'destroy'])->where('id', '[0-9]+');
                });

                Route::get('', [FoodstuffController::class, 'index']);
                Route::get('{id}', [FoodstuffController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [FoodstuffController::class, 'store']);
                Route::delete('{id}', [FoodstuffController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Meal routes
            Route::group([
                'prefix' => 'meal',
            ], function () {
                Route::group([
                    'prefix' => 'category',
                ], function () {
                    Route::get('', [MealCategoryController::class, 'index']);
                    Route::get('{id}', [MealCategoryController::class, 'show'])->where('id', '[0-9]+');
                    Route::post('{id?}', [MealCategoryController::class, 'store']);
                    Route::delete('{id}', [MealCategoryController::class, 'destroy'])->where('id', '[0-9]+');
                });

                Route::get('', [MealController::class, 'index']);
                Route::get('{id}', [MealController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [MealController::class, 'store']);
                Route::delete('{id}', [MealController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Recipe routes
            Route::group([
                'prefix' => 'recipe',
            ], function () {
                Route::group([
                    'prefix' => 'category',
                ], function () {
                    Route::get('', [RecipeCategoryController::class, 'index']);
                    Route::get('{id}', [RecipeCategoryController::class, 'show'])->where('id', '[0-9]+');
                    Route::post('{id?}', [RecipeCategoryController::class, 'store']);
                    Route::delete('{id}', [RecipeCategoryController::class, 'destroy'])->where('id', '[0-9]+');
                });

                Route::get('', [RecipeController::class, 'index']);
                Route::get('{id}', [RecipeController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [RecipeController::class, 'store']);
                Route::delete('{id}', [RecipeController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Menu section routes
            Route::group([
                'prefix' => 'menu-section',
            ], function () {
                Route::get('', [MenuSectionController::class, 'index']);
                Route::get('{id}', [MenuSectionController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [MenuSectionController::class, 'store']);
                Route::delete('{id}', [MenuSectionController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // Menu routes
            Route::group([
                'prefix' => 'menu',
            ], function () {
                Route::get('', [MenuController::class, 'index']);
                Route::get('{id}', [MenuController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [MenuController::class, 'store']);
                Route::delete('{id}', [MenuController::class, 'destroy'])->where('id', '[0-9]+');
                Route::get('{id}/pdf', [MenuController::class, 'pdf'])->where('id', '[0-9]+');
            });

            // Restaurant table routes
            Route::group([
                'prefix' => 'table',
            ], function () {
                Route::get('', [RestaurantTableController::class, 'index']);
                Route::get('{id}', [RestaurantTableController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [RestaurantTableController::class, 'store']);
                Route::delete('{id}', [RestaurantTableController::class, 'destroy'])->where('id', '[0-9]+');
                Route::post('refresh-statuses', [RestaurantTableController::class, 'refreshStatuses']);
            });

            // Reservation routes
            Route::group([
                'prefix' => 'reservation',
            ], function () {
                Route::get('', [ReservationController::class, 'index']);
                Route::get('{id}', [ReservationController::class, 'show'])->where('id', '[0-9]+');
                Route::post('{id?}', [ReservationController::class, 'store']);
                Route::post('{id}/status', [ReservationController::class, 'updateStatus'])->where('id', '[0-9]+');
                Route::delete('{id}', [ReservationController::class, 'destroy'])->where('id', '[0-9]+');
            });
        });
    });
});
