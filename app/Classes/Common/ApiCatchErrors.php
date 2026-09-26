<?php

namespace App\Classes\Common;

use App\Constants\MessageConstant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiCatchErrors
{
    /**
     * Summary: Rollback db transaction failure something
     *
     * @throws Throwable
     */
    public static function rollback(?Exception $exception = null,
        string $message = MessageConstant::GENERAL_RESPONSE_ERROR_MESSAGE): void
    {
        DB::rollBack();

        self::throw($exception, $message);
    }

    /**
     * Summary: Throw error log
     */
    public static function throw(?Exception $exception = null,
        string $message = MessageConstant::GENERAL_RESPONSE_ERROR_MESSAGE): void
    {
        Log::error(
            $message.
            '(File: '.$exception?->getFile().
            ' (Line: '.$exception?->getLine().')'.
            ' Error message: '.$exception?->getMessage()
        );
    }
}
