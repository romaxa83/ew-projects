<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;

/**
 * Mailbox Dashboard.
 */
class MailboxController extends Controller
{
    /**
     * Mailbox Dashboard.
     * @return Renderable
     */
    public function home(): Renderable
    {
        return view('layouts.simple-component', [
            'component' => 'app-mail-box',
            'title' => 'MailBox',
            'breadcrumbs' => [
                [
                    'title' => 'MailBox',
                ],
            ]
        ]);
    }

}
