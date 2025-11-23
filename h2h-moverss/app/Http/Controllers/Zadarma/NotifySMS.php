<?php

namespace App\Http\Controllers\Zadarma;

use Zadarma_API\Webhook\AbstractNotify;

class NotifySMS extends AbstractNotify
{
    public $result;
    public $caller_id;
    public $caller_did;
    public $text;

    public function __construct($postData)
    {
        parent::__construct($postData);
        if ($this->result) {
            $result = json_decode($this->result, true);
            if (!empty($result))
                foreach (array_intersect_key($result, $this->toArray()) as $k => $v) {
                    $this->$k = $v;
                }
        }
    }

    public function getSignatureString()
    {
        return $this->result;
    }
}
