<?php

namespace App\Enums;

enum AnalyseLevel: string
{
    case PARENT = 'PARENT';
    case CHILD = 'CHILD';
    case NORMAL = 'NORMAL';
}
