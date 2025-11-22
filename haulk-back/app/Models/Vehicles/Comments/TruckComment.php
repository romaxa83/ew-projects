<?php

namespace App\Models\Vehicles\Comments;

class TruckComment extends Comment
{
   public const TABLE_NAME = 'truck_comments';

   protected $table = self::TABLE_NAME;
}
