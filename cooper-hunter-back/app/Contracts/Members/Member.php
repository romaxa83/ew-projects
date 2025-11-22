<?php

namespace App\Contracts\Members;

use App\Contracts\Roles\HasGuardUser;
use App\Contracts\Roles\HasRolesContract;

interface Member extends MemberMustVerifyEmail, HasRolesContract, HasGuardUser
{}
