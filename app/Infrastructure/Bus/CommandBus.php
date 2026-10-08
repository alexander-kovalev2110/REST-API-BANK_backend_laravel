<?php

namespace App\Infrastructure\Bus;

use Illuminate\Support\Facades\Log;

final class CommandBus
{
    public function dispatch(object $command): mixed
    {
        $start = microtime(true);

        $className = get_class($command);
        $handlerClass = $className . 'Handler';

        if (!class_exists($handlerClass)) {
            // Try replacing Command with Handler
            $handlerClass = preg_replace('/Command$/', 'Handler', $className);
        }

        $handler = app($handlerClass);
        $result = $handler($command);

        $duration = round((microtime(true) - $start) * 1000, 2);
        $shortName = (new \ReflectionClass($command))->getShortName();
        Log::info(sprintf('%s - %sms', $shortName, $duration));

        return $result;
    }
}