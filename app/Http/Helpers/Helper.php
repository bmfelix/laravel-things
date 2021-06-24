<?php

namespace App\Http\Helpers;

class Helper
{
    public static function abortApplication($e): void
    {
        \App::abort('403', $e->getMessage());
    }

    public static function getCommonLibrary()
    {
        return 'QS36F';
    }

    private static function TrimArray($Input){
        if (!is_array($Input))
            return trim($Input);

        return array_map('TrimArray', $Input);
    }
}
