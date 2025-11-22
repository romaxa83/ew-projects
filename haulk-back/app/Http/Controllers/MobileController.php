<?php


namespace App\Http\Controllers;


class MobileController extends Controller
{
    /**
     * Send response
     *
     * @param array $data
     * @return array
     */
    protected function answer(array $data)
    {
        \Log::debug($_SERVER['REQUEST_URI']);
        \Log::debug(request()->all());
        return response()->json($data);
    }

    /**
     * Send success response
     *
     * @param array $data
     * @param null $message
     * @return array
     */
    protected function success(array $data = [], $message = null)
    {
        $response = [];
        $response['success'] = true;
        if (empty($data) === false) {
            $response += $data;
        }
        return $this->answer($response);
    }

    /**
     * Send error response
     *
     * @param $code
     * @param null $message
     * @param array $data
     * @param string|null $errorDescription
     * @return array
     */
    protected function error($code, $message = null, array $data = [], $errorDescription = null)
    {
        $response = [];
        $response['success'] = false;
        $response['error'] = [];
        $response['error']['code'] = $code;
        $response['error']['message'] = $message ?: __("errors.{$code}", ['Unknown status']);
        if ($errorDescription !== null) {
            $response['error']['description'] = $errorDescription;
        }
        if (empty($data) === false) {
            $response += $data;
        }
        return $this->answer($response);
    }

    /**
     * Send error response after validation
     *
     * @param null $message
     * @param array $additionalFields
     * @return array
     */
    protected function validationError($message = null, array $additionalFields = [])
    {
        $response = [];
        $response['success'] = false;
        $response['error'] = [];
        $response['error']['code'] = 422;
        $response['error']['message'] = $message ?: __("errors.422");
        return $this->answer($response + $additionalFields);
    }
}