<?php

namespace App\Services\Requests;

enum RequestMethodEnum: string {
    case Get = "GET";
    case Post = "POST";
    case Put = "PUT";
    case Delete = "DELETE";
}
