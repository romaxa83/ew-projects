<?php


namespace App\Enums\Chat;


use Core\Enums\BaseEnum;

/**
 * Class ChatMenuActionEnum
 * @package App\Enums\Chat
 *
 * @method static static SUB_MENU()
 * @method static static INFORMATION_FORM()
 * @method static static REDIRECT()
 * @method static static SELECT_MODEL_FORM()
 * @method static static ONLINE_CHAT()
 */
class ChatMenuActionEnum extends BaseEnum
{
    public const SUB_MENU = 'SUB_MENU';
    public const INFORMATION_FORM = 'INFORMATION_FORM';
    public const REDIRECT = 'REDIRECT';
    public const SELECT_MODEL_FORM = 'SELECT_MODEL_FORM';
    public const ONLINE_CHAT = 'ONLINE_CHAT';

    public function isRedirect(): bool
    {
        return $this->is(self::REDIRECT);
    }

    public function isSubMenu(): bool
    {
        return $this->is(self::SUB_MENU);
    }
}
