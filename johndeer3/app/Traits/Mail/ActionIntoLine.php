<?php

namespace App\Traits\Mail;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Notifications\Action;

trait ActionIntoLine
{
    private function makeActionIntoLine(Action $action): Htmlable
    {
        return new class($action) implements Htmlable
        {
            public $action;

            public function __construct(Action $action)
            {
                $this->action = $action;
            }

            public function toHtml()
            {
                return $this->strip($this->table());
            }

            private function table()
            {
                return sprintf(
                    '<table class="action"  width="100" cellpadding="0" cellspacing="0">
                        <tr>
                        <td align="center">%s</td>
                    </tr></table>
                ',
                    $this->btn()
                );
            }

            private function btn()
            {
                return sprintf(
                    '<a
                        href="%s"
                        class="button button-primary"
                        target="_blank"
                        style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; box-sizing: border-box; border-radius: 3px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); color: #fff; display: inline-block; text-decoration: none; -webkit-text-size-adjust: none; background-color: #42804b; border-top: 10px solid #42804b; border-right: 18px solid #42804b; border-bottom: 10px solid #42804b; border-left: 18px solid #42804b; width:100%%  ; font-size: 18px "
                    >%s</a>',
                    htmlspecialchars($this->action->url),
                    htmlspecialchars($this->action->text)
                );
            }

            private function strip($text)
            {
                return str_replace("\n", ' ', $text);
            }

        };
    }
}
