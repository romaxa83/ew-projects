<?php

namespace App\PAMI\Service;

use App\PAMI\Client\Impl\ClientAMI;
use App\PAMI\Message\Action\ActionMessage;
use App\PAMI\Message\Action\CommandAction;
use App\PAMI\Message\Action\CoreShowChannelsAction;
use App\PAMI\Message\Action\PingAction;
use App\PAMI\Message\Action\QueueWithdrawCallerAction;
use App\PAMI\Message\Action\RedirectAction;

class SendActionService
{
    public function QueueWithdrawCaller(
        string $withdrawInfo,
        string $queue,
        string $caller,
    ): bool
    {
        try {
            $command = new QueueWithdrawCallerAction(
                $withdrawInfo,
                $queue,
                $caller
            );

            $res = $this->send($command);

            if($res->isSuccess()){
                logger_info("[ami-sender] TRANSFERS - [{$withdrawInfo}] SUCCESS");
            } else {
                logger_info("[ami-sender] TRANSFERS - [{$withdrawInfo}] FAIL");
            }

            return $res->isSuccess();
        } catch (\Exception $e) {
            logger_info("[ami-sender] QueueWithdrawCaller -- [{$withdrawInfo}] FAIL", [$e]);
            throw new \Exception($e->getMessage());
        }
    }

    public function QueueRedirect(
        string $channel,
        string $extension,
        string $context = 'crm_transfer',
        string $priority = '1',
    ): bool
    {
//        dd($channel, $extension, $context, $priority);
        try {
            $command = new RedirectAction(
                $channel,
                $extension,
                $context,
                $priority
            );

            $res = $this->send($command);

            if($res->isSuccess()){
                logger_info("[ami-sender] QueueRedirect - [{$channel}, {$extension}] SUCCESS");
            } else {
                logger_info("[ami-sender] QueueRedirect - [{$channel}, {$extension}] FAIL");
            }

            return $res->isSuccess();
        } catch (\Exception $e) {
            logger_info("[ami-sender] QueueRedirect -- [{$channel}, {$extension}] FAIL", [$e]);
            throw new \Exception($e->getMessage());
        }
    }

    public function CommandAction(string $command)
    {
        try {
            $action = new CommandAction($command);
            $action->setActionID(12345);

            $res = $this->send($action);

            if($res->isSuccess()){
                logger_info("[ami-sender] COMMAND ACTION to ami - [{$command}] SUCCESS");
            } else {
                logger_info("[ami-sender] COMMAND ACTION to ami - [{$command}] FAIL");
            }

            return $res;
        } catch (\Exception $e) {
            logger_info("[ami-sender] COMMAND ACTION to ami -- [{$command}] FAIL", [$e]);
            throw new \Exception($e->getMessage());
        }
    }

    public function PingAction()
    {
        try {
            $action = new PingAction();
            $action->setActionID(123456);

            $res = $this->send($action);

            if($res->isSuccess()){
                logger_info("[ami-sender] PING ACTION to ami SUCCESS");
            } else {
                logger_info("[ami-sender] PING ACTION to ami FAIL");
            }

            return $res;
        } catch (\Exception $e) {
            logger_info("[ami-sender] PING ACTION to ami - FAIL", [$e]);
            throw new \Exception($e->getMessage());
        }
    }

    public function CoreShowChannelsAction()
    {
        try {
            $action = new CoreShowChannelsAction();

            $res = $this->send($action);

            if($res->isSuccess()){
                logger_info("[ami-sender] CORE SHOW CHANNEL ACTION to ami SUCCESS");
            } else {
                logger_info("[ami-sender] CORE SHOW CHANNEL ACTION to ami FAIL");
            }

            return $res;
        } catch (\Exception $e) {
            logger_info("[ami-sender] CORE SHOW CHANNEL ACTION to ami - FAIL", [$e]);
            throw new \Exception($e->getMessage());
        }
    }

    private function send(ActionMessage $action)
    {
        logger_info("[ami-sender] AMI SEND ACTION", [$action->getKeys()]);
        try {
            /** @var $client ClientAMI */
            $client = resolve(ClientAMI::class);
            $client->open();

            $res = $client->send($action);

            $client->close();

            logger_info("[ami-sender] AMI RESPONSE", [$res->getRawContent()]);

            return $res;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
