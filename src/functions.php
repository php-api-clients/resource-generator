<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator;

use Symfony\Component\Yaml\Yaml;

/**
 * @param string $filename
 * @return array
 */
function readYaml(string $filename): array
{
    return Yaml::parse(file_get_contents($filename));
}
