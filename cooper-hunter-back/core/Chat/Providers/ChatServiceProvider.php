<?php

namespace Core\Chat\Providers;

use Core\Chat\Facades\Chat;
use Core\Chat\Manager\ChatManager;
use Core\Chat\Repositories\ConversationRepository;
use Core\Chat\Repositories\MessageNotificationRepository;
use Core\Chat\Repositories\MessageRepository;
use Core\Chat\Repositories\ParticipationRepository;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChatManager::class);

        $this->app->singleton(ConversationRepository::class, static function () {
            return new ConversationRepository(Chat::getConversationModel());
        });

        $this->app->singleton(ParticipationRepository::class, static function () {
            return new ParticipationRepository(Chat::getParticipationModel());
        });

        $this->app->singleton(MessageRepository::class, static function () {
            return new MessageRepository(Chat::getMessageModel());
        });

        $this->app->singleton(MessageNotificationRepository::class, static function () {
            return new MessageNotificationRepository(Chat::getMessageNotificationModel());
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/chat.php', 'chat');

        if ($this->app->runningInConsole()) {
            $this->registerPublishes();
        }
    }

    protected function registerPublishes(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/chat.php' => config_path('chat.php')
            ],
            'chat-configs'
        );

        $this->publishes([
            __DIR__ . '/../Database/migrations/create_chat_tables.php.stub' => database_path(
                'migrations/' . date('Y_m_d_His') . '_create_chat_tables.php'
            ),
        ], 'chat-migrations');
    }
}
