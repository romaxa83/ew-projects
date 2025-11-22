<?php

namespace App\Services\Commercial;

use Adldap\AdldapException;
use Adldap\Laravel\Facades\Adldap;
use Adldap\Models\Group;
use Adldap\Models\User;
use App\Enums\Formats\DatetimeEnum;
use App\Exceptions\Commercial\LdapGeneralException;
use App\Models\Commercial\RDPAccount;
use Illuminate\Support\Carbon;

class RDPService
{
    private const NEXTCLOUD_AUTH_PASSWORD = 'nextcloudAuthPassword';
    private const EXISTS_ACCOUNT_SUFFIX = '01';

    /**
     * @throws AdldapException
     */
    public function updatePasswordExpirationDate(RDPAccount $account, string $expirationDate): RDPAccount
    {
        $account->end_date = Carbon::createFromFormat(DatetimeEnum::DATE, $expirationDate)->endOfDay();

        if (!$account->active && $account->end_date->gt(now())) {
            $account->active = true;
        }

        $account->save();

        $this->createOrUpdateUser($account);

        return $account;
    }

    /**
     * @throws AdldapException
     */
    public function createOrUpdateUser(RDPAccount $account): void
    {
        if (isTesting()) {
            return;
        }

        $user = $this->getRdpUser($account);

        //modify not yet created user before creating on AD
        if (!$account->created_on_ad && $user) {
            $account->login .= self::EXISTS_ACCOUNT_SUFFIX;
            $account->save();
        }

        if ($account->created_on_ad && $user) {
            $this->updateUser($user, $account);

            return;
        }

        if (!$account->created_on_ad) {
            $this->createUser($account);
        }
    }

    protected function getRdpUser(RDPAccount $account): ?User
    {
        /** @var User $user */
        $user = Adldap::search()->users()->find($account->login);

        return $user;
    }

    /**
     * @throws AdldapException
     */
    private function updateUser(User $user, RDPAccount $account): void
    {
        $this->setAccountSecurity($user, $account);

        if ($account->wasChanged('active') || $account->isDirty('active')) {
            if ($account->active) {
                $this->activateAccount($user);
            }
        }

        $user->save();
    }

    /**
     * @throws AdldapException
     */
    protected function setAccountSecurity(User $user, RDPAccount $account): void
    {
        $params = $user->getUserParameters();
        $params->set('CtxMaxIdleTime', config('commercial.rdp.account.idle_session'));
        $params->set('CtxMaxConnectionTime', config('commercial.rdp.account.limit_session'));
        $user->setUserParameters($params);

        $user->setAccountExpiry($account->end_date->getTimestamp());
        $user->setPassword($account->password);
        $user->setAttribute(self::NEXTCLOUD_AUTH_PASSWORD, $account->password);
    }

    protected function activateAccount(User $user): void
    {
        $ac = $user->getUserAccountControlObject();
        $ac->accountIsNormal();
        $user->setUserAccountControl($ac);
    }

    /**
     * @throws AdldapException
     */
    private function createUser(RDPAccount $account): void
    {
        $user = Adldap::make()->user();

        $dn = $user->getDnBuilder()
            ->addCn($account->login)
            ->addOu(config('commercial.rdp.account.ou'));

        $user->setDn($dn);

        $user->setEmail($account->member->getEmailString());

        $user->givenname = $account->login;
        $user->setDisplayName($account->login);
        $user->samaccountname = $account->login;
        $user->userprincipalname = $account->login . config('commercial.rdp.account.principal');

        $this->setAccountSecurity($user, $account);

        $this->activateAccount($user);

        $user->save();

        $this->getUserGroup()->addMember($user);

        $account->created_on_ad = true;
        $account->save();
    }

    protected function getUserGroup(): Group
    {
        /** @var Group $group */
        $group = Adldap::search()->groups()->find(config('commercial.rdp.account.group'));

        return $group;
    }

    public function deactivate(RDPAccount $account): RDPAccount
    {
        if (isTesting()) {
            $account->active = false;
            $account->save();

            return $account;
        }

        if ($account->created_on_ad && $user = $this->getRdpUser($account)) {
            $this->disableAccount($user);
            $user->save();

            $account->active = false;
            $account->save();

            return $account;
        }

        throw new LdapGeneralException('User not found on AD');
    }

    protected function disableAccount(User $user): void
    {
        $ac = $user->getUserAccountControlObject();
        $ac->accountIsDisabled();
        $user->setUserAccountControl($ac);
    }

    public function delete(RDPAccount $account): bool
    {
        if (isTesting()) {
            return $account->delete();
        }

        if ($account->created_on_ad && $user = $this->getRdpUser($account)) {
            $user->delete();

            return $account->delete();
        }

        throw new LdapGeneralException('User not found on AD');
    }
}