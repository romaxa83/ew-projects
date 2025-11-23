<?php

namespace WezomCms\Core\Services;

use Crawler;
use Illuminate\Config\Repository as ConfigRepository;
use JohannEbert\LaravelSpamProtector\SpamProtector;
use Livewire\Component;
use WezomCms\Core\Exceptions\CheckForSpamException;
use WezomCms\Core\Exceptions\CrawlerException;
use WezomCms\Core\Exceptions\SpamProtectorException;
use WezomCms\Core\Foundation\JsResponse;

class CheckForSpam
{
    /**
     * @var ConfigRepository
     */
    protected $config;

    /**
     * @var SpamProtector
     */
    protected $spamProtector;

    /**
     * CheckForSpam constructor.
     * @param  ConfigRepository  $config
     * @param  SpamProtector  $spamProtector
     */
    public function __construct(ConfigRepository $config, SpamProtector $spamProtector)
    {
        $this->config = $config;

        $this->spamProtector = $spamProtector;
    }

    /**
     * @param  string|null  $email
     * @param  string|null  $username
     * @param  string|null  $ip
     * @throws CrawlerException
     * @throws SpamProtectorException
     */
    public function check(?string $email = null, ?string $username = null, ?string $ip = null)
    {
        if ($this->config->get('cms.core.main.protection.crawler') && Crawler::isCrawler()) {
            $this->logTheAttack('crawler');

            throw new CrawlerException();
        }

        $spammedField = null;
        try {
            if (
                $this->config->get('cms.core.main.protection.spam.email')
                && $email
                && $this->spamProtector->isSpamEmail($email)
            ) {
                $spammedField = 'email';
            } elseif (
                $this->config->get('cms.core.main.protection.spam.username')
                && $username
                && $this->spamProtector->isSpamUsername($username)
            ) {
                $spammedField = 'username';
            } elseif (
                $this->config->get('cms.core.main.protection.spam.ip')
                && $this->spamProtector->isSpamIp($ip ?: request()->ip())
            ) {
                $spammedField = 'ip';
            }
        } catch (\Exception $e) {
            // Do nothing if spam protector API doesn't work.
        }

        if (null !== $spammedField) {
            $this->logTheAttack($spammedField);

            throw new SpamProtectorException($spammedField);
        }
    }

    /**
     * @param  Component  $component
     * @param  string|null  $email
     * @param  string|null  $username
     * @return bool
     */
    public function checkInComponent(Component $component, ?string $email = null, ?string $username = null): bool
    {
        try {
            $this->check($email, $username);
        } catch (CheckForSpamException $e) {
            JsResponse::make()
                ->success(false)
                ->notification($e->getMessage(), 'warning', 10)
                ->emit($component);

            return false;
        }

        return true;
    }

    /**
     * @param  string|null  $field
     */
    protected function logTheAttack(?string $field = null)
    {
        $request = app('request');

        logger('Spam attack', [
            'ip' => $request->ip(),
            'field' => $field,
            'user_agent' => $request->userAgent(),
        ]);
    }
}
