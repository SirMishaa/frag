<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class DuplicateFileException extends Exception
{
    public function __construct(
        public readonly string $checksum,
        public readonly string $filename,
    ) {
        parent::__construct('Duplicate file detected');
    }
}
