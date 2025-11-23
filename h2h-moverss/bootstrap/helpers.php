<?php

/*
 * dd() with headers
 */

use App\Services\Databases\TransactionService;

if (!function_exists('email_app_name')) {
    function email_app_name(): string
    {
        return str_replace('_', ' ', config('mail.from.name'));
    }
}

if (!function_exists('ddh')) {
    function ddh($var){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        dd($var);
    }
}

/*
 * dump() with headers
 */
if (!function_exists('dumph')) {
    function dumph($var){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        dump($var);
    }
}


function format_phone_number($mynum, $mask)
{
    /*********************************************************************/
    /*   Purpose: Return either masked phone number or false             */
    /*     Masks: Val=1 or xxx xxx xxxx                                             */
    /*            Val=2 or xxx xxx.xxxx                                             */
    /*            Val=3 or xxx.xxx.xxxx                                             */
    /*            Val=4 or (xxx) xxx xxxx                                           */
    /*            Val=5 or (xxx) xxx.xxxx                                           */
    /*            Val=6 or (xxx).xxx.xxxx                                           */
    /*            Val=7 or (xxx) xxx-xxxx                                           */
    /*            Val=8 or (xxx)-xxx-xxxx                                           */
    /*********************************************************************/
    $val_num = validate_phone_number($mynum);
    if (!$val_num && !is_string($mynum)) {
        echo "Number $mynum is not a valid phone number! \n";
        return false;
    }   // end if !$val_num
    if (($mask == 1) || ($mask == 'xxx xxx xxxx')) {
        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
            '$1 $2 $3' . " \n", $mynum);
        return $phone;
    }   // end if $mask == 1
    if (($mask == 2) || ($mask == 'xxx xxx.xxxx')) {
        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
            '$1 $2.$3' . " \n", $mynum);
        return $phone;
    }   // end if $mask == 2
    if (($mask == 3) || ($mask == 'xxx.xxx.xxxx')) {
        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
            '$1.$2.$3' . " \n", $mynum);
        return $phone;
    }   // end if $mask == 3
    if (($mask == 4) || ($mask == '(xxx) xxx xxxx')) {
        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
            '($1) $2 $3' . " \n", $mynum);
        return $phone;
    }   // end if $mask == 4
    if (($mask == 5) || ($mask == '(xxx) xxx.xxxx')) {
        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
            '($1) $2.$3' . " \n", $mynum);
        return $phone;
    }   // end if $mask == 5
    if (($mask == 6) || ($mask == '(xxx).xxx.xxxx')) {
        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
            '($1).$2.$3' . " \n", $mynum);
        return $phone;
    }   // end if $mask == 6
    if (($mask == 7) || ($mask == '(xxx) xxx-xxxx')) {
        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
            '($1) $2-$3' . " \n", $mynum);
        return $phone;
    }   // end if $mask == 7
    if (($mask == 8) || ($mask == '(xxx)-xxx-xxxx')) {
        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
            '($1)-$2-$3' . " \n", $mynum);
        return $phone;
    }   // end if $mask == 8
    return false;       // Returns false if no conditions meet or input
}  // end function format_phone_number

function validate_phone_number($phone)
{
    /*********************************************************************/
    /*   Purpose:   To determine if the passed string is a valid phone  */
    /*              number following one of the establish formatting        */
    /*                  styles for phone numbers.  This function also breaks    */
    /*                  a valid number into it's respective components of:      */
    /*                          3-digit area code,                                      */
    /*                          3-digit exchange code,                                  */
    /*                          4-digit subscriber number                               */
    /*                  and validates the number against 10 digit US NANPA  */
    /*                  guidelines.                                                         */
    /*********************************************************************/
    $format_pattern = '/^(?:(?:\((?=\d{3}\)))?(\d{3})(?:(?<=\(\d{3})\))' .
        '?[\s.\/-]?)?(\d{3})[\s\.\/-]?(\d{4})\s?(?:(?:(?:' .
        '(?:e|x|ex|ext)\.?\:?|extension\:?)\s?)(?=\d+)' .
        '(\d+))?$/';
    $nanpa_pattern = '/^(?:1)?(?(?!(37|96))[2-9][0-8][0-9](?<!(11)))?' .
        '[2-9][0-9]{2}(?<!(11))[0-9]{4}(?<!(555(01([0-9]' .
        '[0-9])|1212)))$/';

    // Init array of variables to false
    $valid = array('format' => false,
        'nanpa' => false,
        'ext' => false,
        'all' => false);

    //Check data against the format analyzer
    if (preg_match($format_pattern, $phone, $matchset)) {
        $valid['format'] = true;
    }

    //If formatted properly, continue
    //if($valid['format']) {
    if (!$valid['format']) {
        return false;
    } else {
        //Set array of new components
        $components = array('ac' => $matchset[1], //area code
            'xc' => $matchset[2], //exchange code
            'sn' => $matchset[3] //subscriber number
        );
        //              $components =   array ( 'ac' => $matchset[1], //area code
        //                                              'xc' => $matchset[2], //exchange code
        //                                              'sn' => $matchset[3], //subscriber number
        //                                              'xn' => $matchset[4] //extension number
        //                                              );

        //Set array of number variants
        $numbers = array('original' => $matchset[0],
            'stripped' => substr(preg_replace('[\D]', '', $matchset[0]), 0, 10)
        );

        //Now let's check the first ten digits against NANPA standards
        if (preg_match($nanpa_pattern, $numbers['stripped'])) {
            $valid['nanpa'] = true;
        }

        //If the NANPA guidelines have been met, continue
        if ($valid['nanpa']) {
            if (!empty ($components['xn'])) {
                if (preg_match('/^[\d]{1,6}$/', $components['xn'])) {
                    $valid['ext'] = true;
                }   // end if if preg_match
            } else {
                $valid['ext'] = true;
            }   // end if if  !empty
        }   // end if $valid nanpa

        //If the extension number is valid or non-existent, continue
        if ($valid['ext']) {
            $valid['all'] = true;
        }   // end if $valid ext
    }   // end if $valid
    return $valid['all'];
}

if (!function_exists('logger_ringostat')) {

    function logger_ringostat($message, array $context = [])
    {
        if(config('logging.channels.ringostat.enable')){
            Illuminate\Support\Facades\Log::channel('ringostat')
                ->info($message, $context);
        }
    }
}

if (!function_exists('logger_ringostat_ai')) {

    function logger_ringostat_ai($message, array $context = [])
    {
        if(config('logging.channels.ringostatai.enable')){
            Illuminate\Support\Facades\Log::channel('ringostatai')
                ->info($message, $context);
        }
    }
}

if (!function_exists('logger_twilio')) {

    function logger_twilio($message, array $context = [])
    {
        if(config('logging.channels.twilio.enable')){
            Illuminate\Support\Facades\Log::channel('twilio')
                ->info($message, $context);
        }
    }
}

if (!function_exists('logger_zadarma')) {

    function logger_zadarma($message, array $context = [])
    {
        if(config('logging.channels.zadarma.enable')){
            Illuminate\Support\Facades\Log::channel('zadarma')
                ->info($message, $context);
        }
    }
}

if (!function_exists('logger_info')) {

    function logger_info($message, array $context = [])
    {
        if(config('logging.channels.info.enable')){
            Illuminate\Support\Facades\Log::channel('info')->info($message, $context);
        }
    }
}

if (!function_exists('logger_gmail')) {

    function logger_gmail($message, array $context = [])
    {
        if(config('logging.channels.gmail.enable')){
            Illuminate\Support\Facades\Log::channel('gmail')->info($message, $context);
        }
    }
}

if (!function_exists('logger_vapi')) {

    function logger_vapi($message, array $context = [])
    {
        if(config('logging.channels.vapi.enable')){
            Illuminate\Support\Facades\Log::channel('vapi')->info($message, $context);
        }
    }
}

if (!function_exists('hash_data')) {

    function hash_data(array|string|int $data): string
    {
        if(is_array($data)){
            $data = json_encode($data);
        }

        return md5($data);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): \Illuminate\Contracts\Auth\Authenticatable|\App\User|null
    {
        return Auth::guard()->user();
    }
}

if (!function_exists('clear_phone')) {
    function clear_phone($phone){
        $cleared = preg_replace("/[^0-9]/", "", $phone);
        if ((strpos($phone, '+1') === 0 || strlen($cleared) == 11) && $cleared[0] == '1') {
            $cleared = substr($cleared, 1);
        }
        if (strlen($cleared) == 11 && $cleared[0] == '0') {
            $cleared = substr($cleared, 1);
        }

        return $cleared;
    }
}

if (!function_exists('is_testing')) {
    function is_testing(): bool
    {
        return app()->environment('testing');
    }
}

if (!function_exists('to_bool')) {
    function to_bool(mixed $value): ?bool
    {
        if($value === 'true'){
            return true;
        }
        if($value === 'false'){
            return false;
        }

        return $value;
    }
}

if (!function_exists('to_int')) {
    function to_int(float|int|null|string $value): int
    {
        return round($value);
    }
}

if (!function_exists('human_filesize')) {
    /**
     * Get user-friendly file size.
     * @param  int  $bytes
     * @param  int  $dec  Символов после точки
     * @return string
     */
    function human_filesize(int $bytes, int $dec = 2): string
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f %s", ($bytes / (1024 ** $factor)), $size[$factor]);
    }
}

if (!function_exists('make_transaction')) {
    /**
     * @param  Closure  $action
     * @param  array<Illuminate\Database\Connection>  $connections
     * @return mixed
     * @throws Throwable
     */
    function make_transaction(Closure $action, array $connections = []): mixed
    {
        return app(TransactionService::class)->handle($action, $connections);
    }
}

if (! function_exists('json_to_array')) {
    function json_to_array(?string $jsonString = ''): array
    {
        $jsonString = trim($jsonString);
        if ($jsonString === '') {
            return [];
        }

        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
    }
}

if (! function_exists('array_to_json')) {
    function array_to_json(array $array, $options = 0): string
    {
        return json_encode($array, JSON_THROW_ON_ERROR | $options);
    }
}

