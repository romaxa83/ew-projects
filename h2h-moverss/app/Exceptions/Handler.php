<?php

namespace App\Exceptions;

use App\Services\Requests\Exceptions\RequestCommandException;
use App\Services\Telegram\Telegram;
use Carbon\CarbonImmutable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Request, Throwable, Auth, Cache;


class Handler extends ExceptionHandler
{
    protected $dontReport = [
        'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException',
        'Symfony\\Component\\HttpKernel\\Exception\\MethodNotAllowedHttpException'
    ];

    protected $internalDontReport = [];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    protected function context(): array
    {
        return array_merge(parent::context(), [
            'url' => Request::fullUrl(),
            'input' => Request::except(['password', 'password_confirmation']),
            'userId' => Auth::id(),
            'email' => Auth::user() ? Auth::user()->email : null,
        ]);
    }

    public function shouldReport(Throwable $e): bool
    {
        // Проверяем, если это JS .map, возвращаем false
        if (str_contains(Request::fullUrl(), '.js.map')) {
            return false;
        }

        return parent::shouldReport($e);
    }


    /**
     * Report or log an exception.
     *
     * @param  Throwable  $exception
     * @return void
     *
     * @throws Throwable
     */
    public function report(Throwable $e): void
    {
        if($this->shouldReport($e)){
            $hash = md5($e->getMessage());
            Cache::remember('error_tg_'.$hash, now()->addMinutes(20), function () use ($e) {

                Telegram::error($e->getMessage(), Auth::user()?->email, [
                    'url' => Request::fullUrl(),
                    'file' => $e->getFile() . ' ['. $e->getLine() .']',
                    'time' => CarbonImmutable::now()->format('Y-m-d H:i:s') . ' UTC',
                    'browser' => Request::header('User-Agent', 'cli'),
                ]);

//                \Log::error($e->getMessage(), [
//                    'url' => Request::fullUrl(),
//                    'input' => Request::except(['password', 'password_confirmation']),
//                    'userId' => Auth::id(),
//                    'error' => $e,
//                ]);

                return true;
            });
        }

        // Logger v.2.4 As html
        // Logger v.2.3 Trim Max Msg
        // Logger v.2.2 Styles + Icons
        // Logger v.2.1 Added Link + format
        // Logger v.2.0 Added Context + Browser info in TG
//        if (app()->environment() === 'production' && $this->shouldReport($exception)) {
//            $h = md5($exception->getMessage());
//            Cache::remember('error_t_'.$h, now()->addMinutes(30), function () use ($exception) {
//                $text = '🌍 <b>Project:</b> '.config('app.name').PHP_EOL
//                    .'📡 <b>Link:</b> '.config('app.url').PHP_EOL;
//                if (Auth::check()) {
//                    $text .= '🪪 <b>User:</b> '.Auth::id().' ('.Auth::user()->email.')'.PHP_EOL;
//                }
//
//                $msg = Str::limit($exception->getMessage(), 3800, '... Max size used, details in logs...');
//
//                $text .= '🌐 <b>URL:</b> '.Request::url().PHP_EOL.
//                    '🧭 <b>Browser:</b> '.Request::header('User-Agent', 'cli').PHP_EOL.PHP_EOL.
//                    '❌ <b>Exception:</b>'.PHP_EOL.
//                    '<pre>'.$msg.'</pre>'.PHP_EOL.
//                    '📁 <b>FILE:</b>'.PHP_EOL.' <pre>'.$exception->getFile().':'.$exception->getLine().'</pre>';
//
//                $this->telegaMsg($text);
//
//                return true;
//            });
//        }

        parent::report($e);
    }

    public function telegaMsg($text)
    {
        $data = [
            'chat_id' => config('app.tg.errors_chat'),
            'text' => $text,
        ];

        $data_string = json_encode($data);

        $ch = curl_init('https://api.telegram.org/bot' . config('app.tg.errors_hash') . '/sendMessage?parse_mode=html');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string),
            ]
        );

        return curl_exec($ch);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        return parent::render($request, $e);
    }

    public function register(): void
    {
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 401);
            }
        });
    }
}
