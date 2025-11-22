<?php

namespace App\Services\FileBrowser\Actions;

interface FileBrowserAction
{
    public function handle(): self;

    public function response();
}
