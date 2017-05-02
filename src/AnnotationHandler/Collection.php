<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\AnnotationHandler;

use ApiClients\Foundation\Hydrator\Annotation\Collection as CollectionAnnotation;
use ApiClients\Tools\ResourceGenerator\AnnotationHandlerInterface;

class Collection implements AnnotationHandlerInterface
{
    /**
     * @param array $yaml
     * @return array
     */
    public static function handle(string $property, array $yaml, $input): array
    {
        $yaml['annotations']['Collection'][$property] = $input;
        $yaml['uses'][CollectionAnnotation::class] = true;

        return $yaml;
    }
}
