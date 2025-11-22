<?php

namespace Tests\Unit\Models\Users;

use App\Models\Localization\Language;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_has_language(): void
    {
        $user = User::factory()->create();

        self::assertInstanceOf(Language::class, $user->language);
    }

    /**
     * @see \App\Traits\AddSelectTrait
     */
    public function test_it_has_not_selected_filed_when_that_field_use_on_eager_loading_but_that_field_do_not_add_to_select(
    ): void
    {
        User::factory()
            ->create();

        $lang = Language::default()
            ->first()->slug;

        $user = User::query()
            ->select(['first_name', 'lang'])
            ->with('language')
            ->first();

        self::assertEquals($lang, $user->lang);

        $user = User::query()
            ->select(['first_name', 'lang'])
            ->with('language')
            ->first();
        self::assertEquals($lang, $user->lang);

        $user = User::query()
            ->select(['first_name'])
            ->first();
        self::assertNull($user->lang);

        $user = User::query()
            ->select(['first_name', 'lang'])
            ->first();
        self::assertNotNull($user->lang);

        $user = User::query()->first();
        self::assertNotNull($user->lang);
    }
}
