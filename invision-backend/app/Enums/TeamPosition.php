<?php

namespace App\Enums;

enum TeamPosition: string
{
    case Leader = 'leader';
    case Member = 'member';
    case Supervisor = 'supervisor';
}
