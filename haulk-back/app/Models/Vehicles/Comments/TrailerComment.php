<?php

namespace App\Models\Vehicles\Comments;

class TrailerComment extends Comment
{
   public const TABLE_NAME = 'trailer_comments';

   protected $table = self::TABLE_NAME;
}
