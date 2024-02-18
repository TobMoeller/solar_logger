<?php

namespace App\Enums;

enum InverterCommand: string
{
    case UDC = 'REFU.GetParameter 1104';
    case IDC = 'REFU.GetParameter 1105';
    case PAC = 'REFU.GetParameter 1106';
    case PDC = 'REFU.GetParameter 1107';
    case YIELD_TODAY = 'REFU.GetParameter 1150,0';
    case YIELD_YESTERDAY = 'REFU.GetParameter 1150,1';
    case YIELD_MONTH = 'REFU.GetParameter 1153';
    case YIELD_YEAR = 'REFU.GetParameter 1154';
}
