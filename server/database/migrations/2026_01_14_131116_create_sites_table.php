<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('hash');
            $table->boolean('is_secure')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        $settings = [
            'enabled_locales' => [1, 2, 12],
            'enabled_currencies' => [1, 3],
            'default_locale' => 1,
            'default_currency' => 1,
            'enabled_modules' => [
                'posts',
                'pages',
                'novelties',
                'services',
                'events',
                'reviews',
                'logos',
                'careers',
                'quizzes',
                'newsletters',
                'demands',
                'contacts',
                'users_has_activities',
                'message_blueprints',
                'calendars',
                'cashflows',
                'leagues',
                'clients',
                'biographies',
                'projects',
                'price_offers',
                'trackings',
                'invoices',
                'suppliers',
                'employees',
                'tasks',
                'contracts',
                'users',
                'settings',
                'activities',
                'tax_rates',
                'languages',
                'countries',
                'currencies',
                'emails'
            ]
        ];
        \Illuminate\Support\Facades\DB::table('sites')->insert([
            'name' => 'Martin Hanzl',
            'url' => 'martinhanzl.cz',
            'hash' => \Illuminate\Support\Str::random(128),
            'is_secure' => true,
            'is_active' => true,
            'settings' => json_encode($settings),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('sites')->insert([
            'name' => 'Test site',
            'url' => 'web-pulse.cz',
            'hash' => \Illuminate\Support\Str::random(128),
            'is_secure' => true,
            'is_active' => true,
            'settings' => json_encode($settings),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('sites_has_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('sites_has_users')->insert([
            'site_id' => 1,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('sites_has_users')->insert([
            'site_id' => 2,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('siteable', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->morphs('siteable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
        Schema::dropIfExists('sites_has_users');
        Schema::dropIfExists('siteable');
    }
};
