<?php

namespace WezomCms\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use WezomCms\Core\Exceptions\CheckForSpamException;
use WezomCms\Core\Services\CheckForSpam as CheckForSpamService;

class CheckForSpam
{
    /**
     * @var CheckForSpamService
     */
    protected $checkForSpam;

    /**
     * CheckForSpam constructor.
     * @param  CheckForSpamService  $checkForSpam
     */
    public function __construct(CheckForSpamService $checkForSpam)
    {
        $this->checkForSpam = $checkForSpam;
    }

    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle($request, Closure $next)
    {
        try {
            $this->checkForSpam->check($request->input('email'), $request->input('username'), $request->ip());
        } catch (CheckForSpamException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
