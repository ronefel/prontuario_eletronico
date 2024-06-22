<?php
namespace App\Http\Helpers;
class AgentHelper
{
    public static function isMobile() {
        return is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile"));
    }
}
