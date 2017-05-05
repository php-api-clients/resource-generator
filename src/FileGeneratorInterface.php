<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator;

use PhpParser\Node;

interface FileGeneratorInterface
{
    /**
     * FileGeneratorInterface constructor.
     * @param array $yaml
     */
    public function __construct(array $yaml);

    /**
     * @return string
     */
    public function getFilename(): string;

    /**
     * @return Node
     */
    public function generate(): Node;
}
