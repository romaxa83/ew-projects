<?php

namespace WezomCms\Supports\Services;

use WezomCms\Supports\Models\Support;

class SupportService
{
    public function create(array $data): Support
    {
        $message = new Support();
        $message->name = $data['name'] ?? null;
        $message->email = $data['email'] ?? null;
        $message->text = isset($data['message']) ? trim(strip_tags($data['message'])) : null;
        $message->save();

        return $message;
    }
}
