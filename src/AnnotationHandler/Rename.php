<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\AnnotationHandler;

use ApiClients\Foundation\Hydrator\Annotation\Rename as RenameAnnotation;
use ApiClients\Tools\ResourceGenerator\AnnotationHandlerInterface;

class Rename implements AnnotationHandlerInterface
{
    /**
     * @param string $property
     * @param array  $yaml
     * @param $input
     * @return array
     */
    public static function handle(string $property, array $yaml, $input): array
    {
        $yaml['annotations']['Rename'][$input] = $property;
        $yaml['uses'][RenameAnnotation::class] = true;

        $yaml['properties'][$input] = $yaml['properties'][$property];
        unset($yaml['properties'][$property]);

        return $yaml;
    }
}
