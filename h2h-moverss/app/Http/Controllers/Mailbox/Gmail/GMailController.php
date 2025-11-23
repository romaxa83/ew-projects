<?php

namespace App\Http\Controllers\Mailbox\Gmail;

use App\Http\Controllers\Controller;
use App\Jobs\Gmail\SyncAccountMessagesJob;
use App\Traits\ResponseFormatter;
use App\Utils\FlashMessagesTrait;
use Dacastro4\LaravelGmail\Facade\LaravelGmail;
use App\Models\{Mailbox\Gmail\Account, Mailbox\Gmail\Message, Client, Order};
use App\Models\Employee\Email as EmployeeEmail;
use Carbon\Carbon;
use Dacastro4\LaravelGmail\LaravelGmailClass;
use Dacastro4\LaravelGmail\Services\Message\Mail as GmailMail;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Str, Exception, Auth, Cache, Log, Storage;

/**
 * Gmail Mailboxes
 */
class GMailController extends Controller
{
    use ResponseFormatter, FlashMessagesTrait;

    public function __construct()
    {
        ini_set('max_execution_time', '1500'); // 25m
        ini_set('memory_limit', '700M');
    }

    /**
     * Find customer correspondence by email.
     * @param $client_id
     * @param  int  $limit
     * @param  null  $current_date
     * @return array
     */
    public function searchMessagesByClient($client_id, int $limit = 10, $current_date = null): array
    {
        $client = Client::with('emails:id,client_id,value')->find($client_id);
        $emails = $client->emails ? $client->emails->pluck('value')->all() : null;

        $total = $client->emails ? Message::searchByEmails($emails)->count() : 0;
        $records = [];
        if ($total) {
            $records = Message::searchByEmails($emails)
                ->when($limit, function ($q, $limit) {
                    return $q->take($limit)->latest();
                })
                ->when($current_date, function ($q, $date) {
                    $q->where('updated_at', '>=', $date);
                })
                ->get();
        }

        return [
            'total' => $total,
            'records' => $records,
        ];
    }

    /**
     * Get webhook data.
     * @param  Request  $request
     * @param  Account  $_account
     * @return JsonResponse
     * @throws \JsonException
     * @throws Exception
     */
    public function webhook(Request $request, Account $_account): JsonResponse
    {
        ini_set('memory_limit', '512M');
        if ($request->has('message.data')) {
            $data = json_decode(
                base64_decode($request->message['data']),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $account = $_account->where('miscs->email', $data['emailAddress'])->firstOrFail();
            $miscs = (array) $account->miscs;

            // FIXME Tmp logs
//            $tmp_p = '/home/ally/domains/beta.allymovers.com/storage/app/';
            $tmp_p = config('gmail.path_log_file');
//            logger('TMP_FILE', [$tmp_p]);
            if ($account->id == 1 || $account->id == 32) {
                file_put_contents($tmp_p.'gmail.txt',
                    PHP_EOL.date('Y-m-d H:i:s').'---'.print_r($data, true).'----'.print_r($miscs, true));
            }
            file_put_contents($tmp_p.'all_gmail.txt',
                PHP_EOL.date('Y-m-d H:i:s').'---'.print_r($data, true).'----'.print_r($miscs, true));

            // Stop watch if account has error
            if (isset($miscs['error_type']) && $miscs['error_type'] === 'invalid_grant') {
                rescue(function () use ($account) {
                    $mailbox = new LaravelGmailClass(config(), $account->id);
                    $mailbox->stopWatch($account->miscs['email']);
                });

                return response()
                    ->json([
                        'success' => true,
                        'msg' => "Watch stopped",
                    ]);
            }

            if ($miscs['historyId'] >= $data['historyId']) {
                return response()
                    ->json([
                        'success' => true,
                        'msg' => "Processed. {$miscs['historyId']} > {$data['historyId']}",
                    ]);
            }

            $startHistoryId = $data['historyId'];
            $cache_key = "g_api_hist_cache_{$account->id}";

            // If in progress, 503 waiting...
            abort_if(Cache::has($cache_key), 503, 'Processing...');

            Cache::put($cache_key, 'processing', now()->addHour());

            [
                'messages' => $messages,
                'errors' => $errors,
            ] = $this->getMsgByHistoryId($account, $startHistoryId);

            Cache::forget($cache_key);
        } else {
            Log::info('Какая-то бредятина пришла в вебхук Gmail #'.now()->toDateTimeString(), $request->all());
            throw new Exception('Какая-то бредятина пришла в вебхук Gmail');
        }

        return response()
            ->json([
                'success' => true,
                'updated' => count($messages),
                'errors' => $errors,
                'startHistoryId' => $startHistoryId ?? 'n/a',
            ]);
    }

    /**
     * Sync messages by historyId.
     * @param  Account  $account
     * @param  int  $startHistoryId
     * @param  int|null  $limit
     * @return array
     * @throws Exception
     */
    public function getMsgByHistoryId(Account $account, int $startHistoryId, ?int $limit = 100): array
    {
        $messages = $errors = [];
        $miscs = (array) $account->miscs;

        if ($limit > 500) {
            throw new Exception('Max limit is 500');
        }

        if (empty($miscs['email'])) {
            throw new Exception('Email not found in miscs');
        }

        // Get history
        $historyList = (new LaravelGmailClass(config(), $account->id))
            ->historyList($miscs['email'], [
                'startHistoryId' => $startHistoryId,
                'maxResults' => $limit,
            ]);

        if ($startHistoryId > $miscs['historyId']) {
            $miscs['historyId'] = $startHistoryId;
        }

        $account->miscs = $miscs;
        $account->save();

        foreach ($historyList->history as $chunk) {
            foreach ($chunk->messages as $msg) {
                try {
                    $msgById = $this->msgGetByMsGId($account->id, $msg->id);
                    $messages[] = $this->parseMsg($msgById, $account->id);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }
        if ($messages) {
            $this->saveMessages($messages, $account);
        }

        return [
            'messages' => $messages,
            'errors' => $errors,
            'lastHistoryId' => (int) $historyList->historyId,
        ];
    }

    /**
     * Prefetch request to get a token from Google.
     * @return RedirectResponse
     */
    public function join(Request $request): RedirectResponse
    {
        if ($request->has('reconnect')) {
            Cache::put('gmail_reconnect', (int) $request->reconnect, 60 * 60); // 1h
        } else {
            Cache::forget('gmail_reconnect');
        }

        return (new LaravelGmailClass(config()))->redirect();
    }

    /**
     * Connect received token.
     * @param  Account  $_account
     * @param  Request  $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function joinAccount(Account $_account, Request $request): RedirectResponse
    {
        if (Cache::get('gmail_reconnect')) {
            // Reconnect
            $account = $_account->query()
                ->accounts(Auth::id(), Auth::user()->isAdmin())
                ->findOrFail(Cache::get('gmail_reconnect'));
            $account->active = 1;
        } else {
            Storage::delete('gmail/tokens/gmail-json-join.json');

            // Check for duplicates
            $mailbox = new LaravelGmailClass(config(), 'join');
            $mailbox->makeToken();

            $email = $mailbox->user();
            if (!$email) {
                self::message('Email is empty. Error msg #232', 'error');

                return redirect()->route('mailbox.home');
            }


            $find = $_account->with('user:id,name')->where('miscs->email', $email)->first();
            if ($find) {
                self::message("The account $email already exists. Holder: UID: {$find->user->id} ({$find->user->name})",
                    'error');

                return redirect()->route('mailbox.home');
            }

            // Create new account
            $account = Account::forceCreate([
                'user_id' => Auth::id(),
                'division_id' => $request->session()->get('division.id'),
                'active' => 1,
                'is_archived' => 0,
            ]);

            Storage::move('gmail/tokens/gmail-json-join.json', 'gmail/tokens/gmail-json-'.$account->id.'.json');
        }

        $mailbox = new LaravelGmailClass(config(), $account->id);
        $mailbox->makeToken();

        $miscs = (array) $account->miscs;
        $miscs['email'] = $mailbox->user();

        // remove errors
        $miscs['msg'] = '';
        unset($miscs['error_type']);

        $account->miscs = $miscs;

        $account->save();

        SyncAccountMessagesJob::dispatch([
            'ids' => [$account->id],
        ]);

        return redirect()->route('mailbox.home');
    }

    /**
     * Logout.
     * @param $id
     * @param  Account  $_account
     * @return RedirectResponse
     */
    public function logoutAccount($id, Account $_account): RedirectResponse
    {
        $account = $_account->whereUserId(Auth::id())->findOrFail($id);

        $mailbox = new LaravelGmailClass(config(), $id);
        $mailbox->logout(); //It returns exception if fails

        $account->active = 0;
        $account->save();

        return redirect()->route('mailbox.home');
    }

    /**
     * Send Email.
     * @param  Request  $request
     * @param  Account  $account
     * @return JsonResponse
     */
    public function send(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|integer',
            'to' => 'required|email|max:80',
            'cc' => 'nullable|email|max:80',
            'subject' => 'nullable|string|max:150',
            'html' => 'required|string|max:56536',
            'template_title' => 'nullable|string|max:200',
            'tpl_id' => 'nullable|integer|exists:email_templates,id',
            'order_id' => 'nullable|integer|exists:orders,id',
            'returnFormat' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:'.(8 * 1024),
        ]);

        // Check permissions
        $account->query()
            ->accounts(Auth::id(), Auth::user()->isAdmin())
            ->findOrFail($validated['account_id']);

        $acc = Account::find($validated['account_id']);

        try {
            $acc = $account->find($validated['account_id']);

            $jsonResponse = ['success' => true];
            $mail = new GmailMail(null, null, $validated['account_id']);

            $mail->from($acc->miscs['email']);

            $mail->to($validated['to']);

            $mail->bcc($validated['to']);
            $mail->cc(!empty($validated['cc'])
                ? $validated['cc']
                : $validated['to']
            );

//            if (!empty($validated['cc'])) {
//                $mail->cc($validated['cc']);
//            }

            $mail->subject($validated['subject']);
            $mail->message($validated['html']);

            if (isset($validated['attachments'])) {
                $unlink_files = [];
                foreach ($validated['attachments'] as $file) {
                    // FIX names + Add Attach
                    $file_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                    $file_name .= '.'.$file->getClientOriginalExtension();
                    $file_patch = "/tmp/$file_name";
                    $unlink_files[] = $file_patch;

                    $file->move('/tmp', $file_name);

                    $mail->attach($file_patch);
                }
            }

            $response = $mail->send();

            if (isset($unlink_files)) {
                // Remove tmp attachments
                array_map('unlink', $unlink_files);
            }

            if (!empty($validated['order_id'])) {
                $Order = Order::find($validated['order_id']);

                $msg = new Message();

                $msg->tags = 'sent';
                $msg->tag = 'sent';
                $msg->account_id = $validated['account_id'];
                $msg->msg_id = $response->id;
                $msg->subject = $validated['subject'];
                $miscs = $msg->miscs;
                $miscs['order_id'] = (int) $validated['order_id'];
                $miscs['client_id'] = $Order->client_id;
                $miscs['to'] = [['name' => $validated['to'], 'email' => $validated['to']]];
                $msg->miscs = $miscs;

                $msg->save();

                if (!empty($validated['returnFormat']) && $validated['returnFormat'] === 'communicationPanel') {
                    $msg->load(['data', 'account:id,miscs']);
                    $jsonResponse['record'] = $this->getCommunicationPanelFormat($msg, $Order);
                    //$jsonResponse['activityRecord'] = $this->getCommunicationPanelFormat($activityRecord, $Order);
                }
                // broadcast(new GmailMessageEvent($msg, $Order->division_id));
            }

        } catch (Exception $e) {
            $msg = $e->getMessage();
            $tmp = json_decode($msg, true);
            if(isset($tmp['error']) && isset($tmp['error_description'])){
                $acc = Account::find($validated['account_id']);
                if($acc){
                    $acc->update([
                        'miscs' => array_merge($acc->miscs, [
                            'msg' => 'Error: '.$tmp['error'].' - '.$tmp['error_description'],
                            'error_type' => $tmp['error']
                        ])
                    ]);
                }
            }

            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                        .(app()->environment() !== 'production' ? ' File: '.$e->getFile().' LINE: '.$e->getLine() : ''),
                ]);
        }


        return response()
            ->json($jsonResponse);
    }

    /**
     * Sync all active mailboxes.
     */
    public function cronSyncMail(): void
    {
        echo 'Gmail sync started...'.PHP_EOL;
        $accounts = Account::active()
            ->where('is_archived', 0)
            ->where(function ($q) {
//                $q->where('miscs->userLastOnline', '>', now()->subMinutes(10)->toDateTimeString()) // Юзер онлайн
                $q->whereNull('miscs->lastSync')
                    ->orWhere('miscs->lastSync', '<',
                        now()->subMinutes(20)->toDateTimeString()); // Выгрибаем сообщения раз в 20 мин
            })
            ->get();
        echo 'Selected accounts: '.$accounts->count().PHP_EOL;
        $this->fetchNewMsg('cron', $accounts);

        echo 'Finished'.PHP_EOL;
    }

    /**
     * AJAX Get accounts where user has permissions.
     * @param  Account  $account
     * @return JsonResponse
     * test @see \Tests\Feature\Mailbox\Gmail\AccountsTest
     */
    public function accounts(Account $account): JsonResponse
    {
        $uid = Auth::id();
        $is_admin = Auth::user()->isAdmin();
        $userEmail = Auth::user()->email;

        $accounts = $account->accounts($uid, $is_admin)->get()
            ->filter(function ($item) {
                return $item->miscs && !empty($item->miscs['email']);
            })
            ->map(function ($item) use ($uid) {
                return [
                    'id' => $item->id,
                    'active' => $item->active,
                    'is_archived' => $item->is_archived,
                    'division_id' => $item->division_id,
                    'division_title' => $item->division->title,
                    'email' => $item->miscs && !empty($item->miscs['email']) ? $item->miscs['email'] : null,
                    'is_holder' => $item->user_id === $uid,
                    'users' => $item->users,
                    'md5' => $item->miscs && !empty($item->miscs['email']) ? md5($item->miscs['email']) : null,
                    'msg' => $item->miscs && !empty($item->miscs['msg']) ? $item->miscs['msg'] : null,
                    'error_type' => $item->miscs && !empty($item->miscs['error_type']) ? $item->miscs['error_type'] : null,
                ];
            })
            ->sortBy(function ($item) use ($userEmail) {
                // Аккаунты с email, совпадающим с email пользователя, будут первыми
                return $item['email'] === $userEmail ? 0 : 1;
            })
            ->values();

        return response()
            ->json([
                'success' => true,
                'records' => $accounts
            ]);
    }

    /**
     * Update Gmail Account permissions.
     * @param  Request  $request
     * @param  Account  $account
     * @return JsonResponse
     */
    public function accountSetPermissions(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|integer|exists:gmail_accounts,id',
            'users.*' => 'nullable|exists:users,id',
        ]);

        $is_admin = Auth::user()->isAdmin();

        // Allowed to admins or holder of account
        $record = $account->query()
            ->when(!$is_admin, function ($q) {
                $q->whereUserId(Auth::id());
            })
            ->findOrFail($validated['account_id']);

        $record->users()->sync((array) $validated['users']);
        return response()
            ->json([
                'success' => true,
                'msg' => 'Permission updated',
            ]);
    }

    /**
     * Change account status.
     * @param  Request  $request
     * @param  Account  $account
     * @return JsonResponse
     */
    public function accountStatusToggle(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|integer|exists:gmail_accounts,id',
        ]);

        $is_admin = Auth::user()->isAdmin();

        // Allowed to admins or holder of account
        $record = $account->query()
            ->when(!$is_admin, function ($q) {
                $q->whereUserId(Auth::id());
            })
            ->findOrFail($validated['account_id']);

        $record->is_archived = !$record->is_archived;
        $record->save();

        return response()
            ->json([
                'success' => true,
                'msg' => $record->is_archived ? 'Archived' : 'NOT Archived',
            ]);
    }

    /**
     * Sync new account messages + preload folders.
     * @param  Account  $_account
     * @param  Request  $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function sync(Account $_account, Request $request): JsonResponse
    {
        $mode = $request->mode;
        // init - on start
        // refresh, auto_refresh - refresh btn or on JS schedule
        // fetch - read folder

        $accounts = $_account->active()->where('is_archived', 0)->find($_account->getUserAccountIds());
        $this->fetchNewMsg($mode, $accounts);

        $account_ids = $accounts->pluck('id')->all();
        if ($mode === 'fetch' || $mode === 'init') {
            $messages = Message::whereIn('account_id', $account_ids)
                ->whereTag($request->tag)
                ->offset($request->start)
                ->limit($request->on_page)
                ->latest('updated_at')
                ->get();
        } else {
            $messages = Message::whereIn('account_id', $account_ids)
                ->where('updated_at', '>=', request()->get('currentDate'))
                ->take(50)
                ->get();
        }

        $resp = [
            'success' => true,
            'messages' => [
                'records' => $messages,
            ],
            'lastSync' => $accounts->first()->miscs['lastSync'] ?? null, // When cron synchronised data
            'currentDate' => now()->toDateTimeString(),
        ];

        if ($mode === 'init') {
            $resp['accounts'] = $accounts->map(function ($item) {
                return [
                    'id' => $item->id,
                    'email' => $item->miscs['email'],
                    'md5' => md5($item->miscs['email']),
                ];
            })
                ->keyBy('id');
        }

        // When need update folder statistics
        if ($messages || $mode === 'init') {
            $resp['meta'] = [
                'inbox' => [
                    'total' => $this->totalInFolder('inbox', $account_ids),
                    'new' => $this->totalInFolder('inbox', $account_ids, true),
                ],
//                'draft' => [
//                    'total' => $this->totalInFolder('draft', $account_ids),
//                ],
                'sent' => [
                    'total' => $this->totalInFolder('sent', $account_ids),
                ],
                'spam' => [
                    'total' => $this->totalInFolder('spam', $account_ids),
                ],
                'trash' => [
                    'total' => $this->totalInFolder('trash', $account_ids),
                ],
            ];
        }

        return response()
            ->json($resp);
    }

    /**
     * Open thread.
     * @param  Request  $request
     * @param  Account  $_account
     * @param  Message  $message
     * @return JsonResponse
     * @throws \Dacastro4\LaravelGmail\Exceptions\AuthException
     */
    public function open(Request $request, Account $_account, Message $message): JsonResponse
    {
        $accounts = $_account->active()->find($_account->getUserAccountIds());

        $messages = $message->threadMsg($request->thread_id, $accounts->pluck('id')->all())->get();
        $messages->each(function ($item) {
            if (strpos($item->tags, 'unread') !== false) {
                $this->msgMarkAsRead($item->account_id, $item->msg_id);
            }
        });

        return response()
            ->json([
                'success' => true,
                'thread_id' => $request->thread_id,
                'messages' => $messages,
            ]);
    }

    /**
     * Mark as read.
     * @param $account_id
     * @param $msg_id
     * @throws \Dacastro4\LaravelGmail\Exceptions\AuthException
     */
    protected function msgMarkAsRead($account_id, $msg_id): void
    {
        $msg = $this->msgGetByMsGId($account_id, $msg_id);
        $msg->markAsRead();
    }

    /**
     * Get msg by ID.
     * @param $account_id
     * @param $msg_id
     * @return GmailMail
     * @throws \Dacastro4\LaravelGmail\Exceptions\AuthException
     */
    protected function msgGetByMsGId($account_id, $msg_id): GmailMail
    {
        return (new LaravelGmailClass(config(), $account_id))->message()->get($msg_id);
    }

    /**
     * Split inline tags.
     * @param $tags
     * @return string
     */
    private function formatTags($tags): string
    {
        return implode(',', array_map('strtolower', $tags));
    }

    /**
     * Get main tag of msg.
     * @param $tags
     * @return string
     */
    private function mainTag($tags): string
    {
        $tags = array_map('strtolower', $tags);
        foreach (['inbox', 'draft', 'sent', 'spam', 'trash'] as $tag) {
            if (in_array($tag, $tags, true)) {
                return $tag;
            }
        }
        return 'inbox';
    }

    /**
     * Get total msg in folder.
     * @param $folder
     * @param $ids Account IDs
     * @param  false  $countNew  Count unread
     * @return int
     */
    public function totalInFolder($folder, $ids, bool $countNew = false): int
    {
        return Message::whereIn('account_id', $ids)
            ->when($countNew, function ($q) {
                $q->whereRaw('FIND_IN_SET(?, `tags`)', ['unread']);
            })
            ->whereTag($folder)
            ->count();
    }

    /**
     * Read messages data from API by folder.
     * @param  object  $mailbox
     * @param  string  $mode
     * @param  string|null  $lastSync
     * @param  int  $account_id
     * @return array
     */
    private function getMessages(object $mailbox, string $mode, $lastSync, int $account_id): array
    {
        $messages = [];
        if ($mode === 'cron' || $mode === 'refresh') {
            $folders = ['starred', 'inbox', 'spam', 'sent', 'trash']; // draft
        } else {
            $folders = ['inbox'];
        }

        if ($lastSync) {
            // Обходим все папки
            foreach ($folders as $folder) {
                $_messages = $mailbox->message()->in($folder)->after($lastSync)->preload()->all();
                foreach ($_messages as $msg) {
                    $messages[] = $this->parseMsg($msg, $account_id);
                }
            }
        } else {
            // Инициализация ящика
            $_messages = $mailbox->message()->preload()->all();
            foreach ($_messages as $msg) {
                $messages[] = $this->parseMsg($msg, $account_id);
            }
        }

        return $messages;
    }

    /**
     * Formatting message data.
     * @param $msg
     * @param $account_id
     * @return array
     */
    private function parseMsg($msg, $account_id): array
    {
        $miscs = [
            'size' => $msg->getSize(),
            'delivered_to' => $msg->getDeliveredTo(),
            'has_attachments' => (bool) $msg->hasAttachments(),
        ];
        if (in_array('INBOX', (array) $msg->getLabels(), true)) {
            $miscs['from'] = $this->crutchGetFrom($msg);
        } else {
            $miscs['to'] = $msg->getTo();
        }
        $date = $this->crutchGetDate($msg)->setTimezone('UTC')->toDateTimeString();

        // Loo large base64...
//        if ($miscs['has_attachments']) {
//            // https://github.com/dacastro4/laravel-gmail#attachment-1
//            $_attachments = $msg->getAttachments();
//            foreach ($_attachments as $v) {
//                $miscs['attachments'][] = [
//                    'id' => $v->getId(),
//                    'name' => $v->getFileName(),
//                ];
//            }
//        }

        return [
            'account_id' => $account_id,
            'msg_id' => $msg->getId(),
            'thread_id' => $msg->getThreadId(),
            'history_id' => $msg->getHistoryId(),
            'tag' => $msg->getLabels() ? $this->mainTag($msg->getLabels()) : 'inbox',
            'tags' => $msg->getLabels() ? $this->formatTags($msg->getLabels()) : 'inbox',
            'subject' => Str::limit($msg->getSubject(), 247),
            'miscs' => $miscs,
            'created_at' => $date,
            'updated_at' => $date,
            'text' => $msg->getHtmlBody(),
        ];
    }

    /**
     * TMP method.
     * https://github.com/dacastro4/laravel-gmail/issues/245
     * @param $msg
     * @return array
     */
    public function crutchGetFrom($msg): array
    {
        $from = $msg->getHeader('From') ?? $msg->getHeader('from') ?? null;

        preg_match('/<(.*)>/', $from, $matches);

        $name = preg_replace('/ <(.*)>/', '', $from);

        return [
            'name' => $name,
            'email' => $matches[1] ?? null,
        ];
    }

    private function crutchGetDate($msg): Carbon
    {
        $date = $msg->getHeader('Date');
        $dateString = strstr($date, " (", true);

        return Carbon::parse($dateString ?: $date);
    }

    /**
     * Pull new account messages via API.
     * @param $mode
     * @param $accounts
     * @return void
     * @throws \JsonException
     */
    private function fetchNewMsg($mode, $accounts): void
    {
        // If we need to pull fresh data
        if (in_array($mode, ['refresh', 'cron'], true)) {
            foreach ($accounts as $account) {
                if ($mode === 'cron' && app()->runningInConsole()) {
                    echo 'Sync account: '.$account->miscs['email'].PHP_EOL;
                }

                try {
                    $mailbox = new LaravelGmailClass(config(), $account->id);
                    $miscs = (array) $account->miscs;
                    $ids = $miscs['updated_ids'] ?? [];
                    $now = now()->toDateTimeString();

                    if (
                        $mode === 'cron'
                        && (!isset($miscs['watchInit']) || Carbon::parse($miscs['watchInit'])->lte(now()->subDay()->toDateTimeString()))
                    ) {
                        // ReInit once per day https://developers.google.com/gmail/api/guides/push#renewing_mailbox_watch
                        // Stopping watch (one account == one watch url)
                        if (isset($miscs['historyId'])) {
                            $mailbox->stopWatch($account->miscs['email']);
                        }

                        // Set a watch for this email acc. (update once a day)
                        // Направляем что письма будут идти на Топик (в консоли гугла уже прописан url для хука)
                        $rq = new \Google_Service_Gmail_WatchRequest();
                        $rq->setTopicName('projects/allymovers-276512/topics/gmail');
                        $watch = $mailbox->setWatch($account->miscs['email'], $rq);
                        if (!isset($miscs['historyId'])) {
                            $miscs['historyId'] = (int) $watch->historyId;
                        }

                        if (app()->runningInConsole()) {
                            echo 'Watch reInit:  historyId -'.$miscs['historyId'].PHP_EOL;
                        }

                        $miscs['watchInit'] = $now;
                    }

                    // Get lastSync date
                    $lastSync = isset($miscs['lastSync'])
                        ? Carbon::parse($miscs['lastSync'])->subDay()->toDateString()
                        : null;
                    $messages = $this->getMessages($mailbox, $mode, $lastSync, $account->id);

                    $updated_ids = $this->saveMessages($messages, $account);

                    if ($mode === 'cron') {
                        $miscs['lastSync'] = $now;
                        if ($ids) {
                            $miscs['updated_ids'] = $ids;
                        }
                    } else {
                        $miscs['userLastOnline'] = $now; // Update when user is online (not used)
                    }

                    if ($mode === 'cron' && app()->runningInConsole()) {
                        echo 'Changes: '.count($updated_ids).PHP_EOL.PHP_EOL;
                    }
                    unset($miscs['error_type'], $miscs['msg']);
                } catch (\Google\Service\Exception $e) {
                    $resp = json_decode($e->getMessage(), true, 512, JSON_THROW_ON_ERROR);

                    if (is_string($resp['error'])) {
                        $miscs['msg'] = 'Error: '.$resp['error'].' - '.$resp['error_description'];
                        $miscs['error_type'] = $resp['error'];
                    } else {
                        report($e);
                        dd($e);
                    }
                }

                $account->miscs = $miscs;
                $account->save();
            }
        }
    }

    /**
     * Save/Update messages in database.
     * @param  array  $messages
     * @param  Account  $account
     * @return array Array of IDs that have been updated
     */
    private function saveMessages(array $messages, $account): array
    {
        $updated_ids = [];
        $msg_ids = collect($messages)->pluck('msg_id')->all();
        foreach ($messages as $msg) {
            $account->load([
                'messages' => function ($q) use ($msg_ids) {
                    $q->whereIn('msg_id', $msg_ids);
                },
            ]);

            $draft_ids = $account->messages
                ->filter(function ($item) {
                    return strstr($item['tags'], 'draft');
                })
                ->pluck('id')->all();
            $account->load([
                'messages.data' => function ($q) use ($draft_ids) {
                    $q->whereIn('message_id', $draft_ids);
                },
            ]);

            $create = $msg;
            unset($create['text']);

            if ($account->messages->contains('msg_id', $msg['msg_id'])) {
                // upd
                $record = $account->messages->where('msg_id', $msg['msg_id'])->first();

                if (!$record->thread_id) {
                    // MSG created previously not from API
                    $create['miscs'] = array_merge($record->miscs, $create['miscs']);

                    $record->forceFill($create);
                    $record->save();

                    $record->data()->create([
                        'text' => $msg['text'],
                    ]);

                } else {
                    // Upd tags
                    if (array_diff(explode(',', $record->tags), explode(',', $msg['tags']))) {
                        $record->tag = $msg['tag'];
                        $record->tags = $msg['tags'];
                        $record->save();
                        $updated_ids[] = $record->id;
                    }
                    // Upd texts
                    if (isset($record->data) && $record->data->text !== $msg['text']) {
                        $record->data->text = $msg['text'];
                        $record->data->save();
                        $updated_ids[] = $record->id;
                    }
                }
            } else {
                // Create
                $_msg = $account->messages()->forceCreate($create);
                try {
                    if ($_msg->tag == 'inbox') {
                        // broadcast(new GmailMessageEvent($_msg, $account->division_id));
                    }
                } catch (Exception $e) {
                    Log::error($e);
                }

                $updated_ids[] = $_msg->id;
                $_msg->data()
                    ->create([
                        'text' => $msg['text'],
                    ]);
            }
        }

        return $updated_ids;
    }

    /**
     * Get notify about broken emails.
     * @return void
     */
    public function checkBrokenAccounts(): void
    {
        if (Auth::user()->isAdmin()) {
            $accounts = Account::query()
                ->where('is_archived', 0)
                ->whereActive(1)
                ->where('miscs->error_type', 'invalid_grant')
                ->get();
        } else {
            $user = Auth::user()->load('employee');
            $emails = EmployeeEmail::whereEmployeeId($user->employee->id)->get('value')
                ->pluck('value')->all();

            $accounts = Account::query()
                ->where('is_archived', 0)
                ->whereActive(1)
                ->where('miscs->error_type', 'invalid_grant')
                ->where(function ($q) use ($emails) {
                    $q->where('user_id', Auth::id())
                        ->when($emails, function ($q, $emails) {
                            $q->orWhereIn('miscs->email', $emails);
                        });
                })
                ->get();
        }

        foreach ($accounts as $v) {
            self::message('Email "'.$v->miscs['email'].'" synchronization error. Fix it with '.
                '<a href="https://www.loom.com/share/e18c1ff0f6bc4c5e986f3af0aec02568" target="_blank">this manual</a>. Or contact with your supervisor.',
                'danger');
        }
    }

}
