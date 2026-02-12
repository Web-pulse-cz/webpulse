<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLanguages();
        $this->seedCountries();
        $this->seedCurrencies();
        $this->seedTaxRates();

        $this->seedUserGroups();
        $this->seedUsers();
    }

    private function seedLanguages(): void
    {
        $languages = [
            [
                'name' => 'Čeština',
                'code' => 'cs',
                'iso' => 'CZ',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => [
                        'name' => 'Čeština',
                    ],
                    'sk' => [
                        'name' => 'Čeština',
                    ],
                    'en' => [
                        'name' => 'Czech',
                    ],
                    'de' => [
                        'name' => 'Tschechisch',
                    ],
                    'pl' => [
                        'name' => 'Czeski',
                    ]
                ]
            ],
            [
                'name' => 'Slovenčina',
                'code' => 'sk',
                'iso' => 'SK',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => [
                        'name' => 'Slovenčina',
                    ],
                    'sk' => [
                        'name' => 'Slovenčina',
                    ],
                    'en' => [
                        'name' => 'Slovak',
                    ],
                    'de' => [
                        'name' => 'Slowakisch',
                    ],
                    'pl' => [
                        'name' => 'Słowacki',
                    ]
                ]
            ],
            [
                'name' => 'English',
                'code' => 'en',
                'iso' => 'US',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => [
                        'name' => 'Angličtina',
                    ],
                    'sk' => [
                        'name' => 'Angličtina',
                    ],
                    'en' => [
                        'name' => 'English',
                    ],
                    'de' => [
                        'name' => 'Englisch',
                    ],
                    'pl' => [
                        'name' => 'Angielski',
                    ]
                ]
            ],
            [
                'name' => 'Deutsch',
                'code' => 'de',
                'iso' => 'DE',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => [
                        'name' => 'Němčina',
                    ],
                    'sk' => [
                        'name' => 'Nemčina',
                    ],
                    'en' => [
                        'name' => 'German',
                    ],
                    'de' => [
                        'name' => 'Deutsch',
                    ],
                    'pl' => [
                        'name' => 'Niemiecki',
                    ]
                ]
            ],
            [
                'name' => 'Polish',
                'code' => 'pl',
                'iso' => 'PL',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => [
                        'name' => 'Polština',
                    ],
                    'sk' => [
                        'name' => 'Poľština',
                    ],
                    'en' => [
                        'name' => 'Polish',
                    ],
                    'de' => [
                        'name' => 'Polnisch',
                    ],
                    'pl' => [
                        'name' => 'Polski',
                    ]
                ]
            ]
        ];

        foreach ($languages as $language) {
            $languageId = DB::table('languages')->insertGetId([
                'code' => $language['code'],
                'iso' => $language['iso'],
                'active' => $language['active'],
                'created_at' => $language['created_at'],
                'updated_at' => $language['updated_at']
            ]);
            foreach ($language['translations'] as $locale => $translation) {
                DB::table('language_translations')->insert([
                    'language_id' => $languageId,
                    'locale' => $locale,
                    'name' => $translation['name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function seedCountries(): void
    {
        $countries = [
            [
                'name' => 'Česká republika',
                'code' => 'cs',
                'iso' => 'CZ',
                'phone_prefix' => '+420',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => [
                        'name' => 'Česká republika',
                    ],
                    'sk' => [
                        'name' => 'Česká republika',
                    ],
                    'en' => [
                        'name' => 'Czech Republic',
                    ],
                    'de' => [
                        'name' => 'Tschechische Republik',
                    ],
                    'pl' => [
                        'name' => 'Czechy',
                    ]
                ]
            ],
            [
                'name' => 'Slovensko',
                'code' => 'sk',
                'iso' => 'SK',
                'phone_prefix' => '+421',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => [
                        'name' => 'Slovensko',
                    ],
                    'sk' => [
                        'name' => 'Slovensko',
                    ],
                    'en' => [
                        'name' => 'Slovakia',
                    ],
                    'de' => [
                        'name' => 'Slowakei',
                    ],
                    'pl' => [
                        'name' => 'Słowacja',
                    ]
                ]
            ],
        ];

        foreach ($countries as $country) {
            $countryId = DB::table('countries')->insertGetId([
                'code' => $country['code'],
                'iso' => $country['iso'],
                'phone_prefix' => $country['phone_prefix'],
                'active' => $country['active'],
                'created_at' => $country['created_at'],
                'updated_at' => $country['updated_at']
            ]);
            foreach ($country['translations'] as $locale => $translation) {
                DB::table('country_translations')->insert([
                    'country_id' => $countryId,
                    'locale' => $locale,
                    'name' => $translation['name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function seedCurrencies(): void
    {
        $currencies = [
            [
                'name' => 'Česká koruna',
                'code' => 'CZK',
                'rate' => 1.0, // base currency
                'decimals' => 2,
                'active' => true,
                'bank_account_number' => '329843898/0300',
                'bank_account_name' => 'Martin Hanzl',
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => ['name' => 'Česká koruna', 'symbol_before' => null, 'symbol_after' => ',-'],
                    'sk' => ['name' => 'Česká koruna', 'symbol_before' => 'CZK', 'symbol_after' => null],
                    'en' => ['name' => 'Czech koruna', 'symbol_before' => 'CZK', 'symbol_after' => null],
                    'de' => ['name' => 'Tschechische Krone', 'symbol_before' => 'CZK', 'symbol_after' => null],
                    'pl' => ['name' => 'Korona czeska', 'symbol_before' => 'CZK', 'symbol_after' => null]
                ]
            ],
            [
                'name' => 'Euro',
                'code' => 'EUR',
                'rate' => 1.0, // base currency
                'decimals' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'translations' => [
                    'cs' => ['name' => 'Euro', 'symbol_before' => null, 'symbol_after' => '€'],
                    'sk' => ['name' => 'Euro', 'symbol_before' => '€', 'symbol_after' => null],
                    'en' => ['name' => 'Euro', 'symbol_before' => '€', 'symbol_after' => null],
                    'de' => ['name' => 'Euro', 'symbol_before' => '€', 'symbol_after' => null],
                    'pl' => ['name' => 'Euro', 'symbol_before' => '€', 'symbol_after' => null]
                ]
            ]
        ];

        foreach ($currencies as $currency) {
            $currencyId = DB::table('currencies')->insertGetId([
                'code' => $currency['code'],
                'rate' => $currency['rate'],
                'decimals' => $currency['decimals'],
                'active' => $currency['active'],
                'bank_account_number' => $currency['bank_account_number'] ?? null,
                'bank_account_name' => $currency['bank_account_name'] ?? null,
                'created_at' => $currency['created_at'],
                'updated_at' => $currency['updated_at']
            ]);
            foreach ($currency['translations'] as $locale => $translation) {
                DB::table('currency_translations')->insert([
                    'currency_id' => $currencyId,
                    'locale' => $locale,
                    'name' => $translation['name'],
                    'symbol_before' => $translation['symbol_before'] ?? null,
                    'symbol_after' => $translation['symbol_after'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function seedTaxRates(): void
    {
        $taxRates = [
            [
                'name' => 'Základní sazba DPH',
                'rate' => 21.0,
            ],
            [
                'name' => 'Snížená sazba DPH',
                'rate' => 15.0,
            ],
            [
                'name' => 'Druhá snížená sazba DPH',
                'rate' => 10.0,
            ]
        ];

        foreach ($taxRates as $taxRate) {
            DB::table('tax_rates')->insert([
                'name' => $taxRate['name'],
                'rate' => $taxRate['rate'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function seedUserGroups(): void
    {
        $userGroups = [
            [
                'name' => 'Admin',
                'permissions' => [
                    [

                        'name' => 'Uživatelé',
                        'slug' => 'users', // slug based on table name
                        'permissions' => [
                            'view' => true,
                            'edit' => true,
                            'delete' => true
                        ]
                    ],
                    [

                        'name' => 'Uživatelské skupiny',
                        'slug' => 'user_groups',
                        'permissions' => [
                            'view' => true,
                            'edit' => true,
                            'delete' => true
                        ]
                    ]
                ]
            ]
        ];

        foreach ($userGroups as $userGroup) {
            $item = new UserGroup();
            $item->fill([
                'name' => $userGroup['name'],
                'permissions' => json_encode($userGroup['permissions'])
            ]);
            $item->save();
        }
    }

    private function seedUsers(): void
    {
        $users = [
            [
                'firstname' => 'Martin',
                'lastname' => 'Hanzl',
                'email' => 'martas.hanzl@email.cz',
                'password' => Hash::make('pstruh01'),
                'phone' => '773284824',
                'street' => 'Blato 102',
                'city' => 'Mikulovice',
                'zip' => 53002,
                'invitation_token' => 'LYLPSXV6',
                'user_group_id' => 1,
                //'country_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }
    }
}
