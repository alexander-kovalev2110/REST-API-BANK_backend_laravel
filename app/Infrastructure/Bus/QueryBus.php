<?php

namespace App\Infrastructure\Bus;

use Illuminate\Support\Facades\Log;

final class QueryBus
{
    public function ask(object $query): mixed
    {
        $start = microtime(true);

        $className = get_class($query);
        $handlerClass = preg_replace('/Query$/', 'Handler', $className);

        $handler = app($handlerClass);
        $result = $handler($query);

        $duration = round((microtime(true) - $start) * 1000, 2);
        $shortName = (new \ReflectionClass($query))->getShortName();
        Log::info(sprintf('%s - %sms', $shortName, $duration));

        return $result;
    }
}