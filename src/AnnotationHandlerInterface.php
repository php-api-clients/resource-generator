<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator;

interface AnnotationHandlerInterface
{
    public static function handle(string $property, array $yaml, $input): array;
}
