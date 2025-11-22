<?php

namespace App\Services\Email;

use App\Exceptions\EmailVerifyException;
use App\Exceptions\ErrorsCode;
use App\Models\Verify\EmailVerify;
use App\Services\Tokenizer;
use App\ValueObjects\Token;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use DB;
use Illuminate\Database\Eloquent\Model;

class EmailVerifyService
{
    public function __construct()
    {}

    public function create(Model $model, $fromEdit = false): EmailVerify
    {
        EmailVerify::checkModel($model);
        $this->checkExistRecord($model, $fromEdit);

        $verifyModel = new EmailVerify();
        $verifyModel->entity_type = $model::class;
        $verifyModel->entity_id = $model->id;
        $verifyModel->email_token = $this->getEmailToken($model);

        $verifyModel->save();

        return $verifyModel;
    }

    public function verify(EmailVerify $emailVerify): void
    {
        DB::beginTransaction();
        try {
            if($emailVerify->email_token->isExpiredToNow()){
                $emailVerify->delete();
                throw new EmailVerifyException(__('error.email_verify.expired email token'), ErrorsCode::EMAIL_TOKEN_EXPIRED);
            }

            $emailVerify->entity->email_verify = true;
            $emailVerify->entity->save();

            $emailVerify->verify = true;
            $emailVerify->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new EmailVerifyException($e->getMessage(), $e->getCode());
        }

//        $emailVerify->delete();
    }

    public function checkExistRecord(Model $model, $fromEdit = false)
    {
        if($model->emailVerifyObj){
            if($model->emailVerifyObj->email_token->isExpiredToNow() || $fromEdit){
                return $model->emailVerifyObj()->delete();
            }

            throw new EmailVerifyException(
                __('error.email_verify.active email token'),
                ErrorsCode::ACTIVE_EMAIL_VERIFY_TOKEN
            );
        }

        return false;
    }

    public function getEmailToken(Model $model): Token
    {
        $interval = EmailVerify::getTokenInterval($model);
        return (new Tokenizer(new CarbonInterval($interval)))
            ->generate(CarbonImmutable::now());
    }

}
