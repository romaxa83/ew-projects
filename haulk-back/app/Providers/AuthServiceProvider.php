<?php

namespace App\Providers;

use App\Http\Resources\Saas\Support\Crm\SupportResource as CrmSupportResource;
use App\Http\Resources\Saas\Support\Backoffice\SupportResource as BackOfficeSupportResource;
use App\Models\Contacts\Contact;
use App\Models\Library\LibraryDocument;
use App\Models\Orders\Order;
use App\Models\QuestionAnswer\QuestionAnswer;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Users\User;
use App\Policies\Contacts\ContactPolicy;
use App\Policies\Library\LibraryDocumentPolicy;
use App\Policies\Orders\OrderPolicy;
use App\Policies\QuestionAnswer\QuestionAnswerPolicy;
use App\Policies\Saas\Support\SupportRequestPolicy;
use App\Policies\Users\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Contact::class => ContactPolicy::class,
        Order::class => OrderPolicy::class,
        LibraryDocument::class => LibraryDocumentPolicy::class,
        QuestionAnswer::class => QuestionAnswerPolicy::class,
        SupportRequest::class => SupportRequestPolicy::class,
        CrmSupportResource::class => SupportRequestPolicy::class,
        BackOfficeSupportResource::class => SupportRequestPolicy::class
    ];

    public function boot(): void
    {
        $this->registerPolicies();

//        Passport::routes();
        Passport::tokensExpireIn(
            now()->addMinutes(config('auth.oauth_tokens_expire_in'))
        );
        Passport::refreshTokensExpireIn(
            now()->addMinutes(config('auth.oauth_refresh_tokens_expire_in'))
        );
        Passport::personalAccessTokensExpireIn(
            now()->addMinutes(config('auth.oauth_personal_access_tokens_expire_in'))
        );
    }
}
