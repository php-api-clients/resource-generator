<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\AnnotationHandler;

use ApiClients\Foundation\Hydrator\Annotations\Nested as NestedAnnotation;
use ApiClients\Tools\ResourceGenerator\AnnotationHandlerInterface;

class Nested implements AnnotationHandlerInterface
{
    /**
     * @param string $property
     * @param array $yaml
     * @param $input
     * @return array
     */
    public static function handle(string $property, array $yaml, $input): array
    {
        $yaml['annotations']['Nested'][$property] = $input;
        $yaml['uses'][NestedAnnotation::class] = true;

        return $yaml;
    }
}
