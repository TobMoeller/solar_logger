<?php

namespace Modules\Export\Exceptions;

use Exception;
use Modules\Export\Contracts\ExportableContract;

class MissingExportEntry extends Exception
{
    public function __construct(public ExportableContract $exportable)
    {
        parent::__construct('Missing Export Entry');
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return ['exportable' => $this->exportable];
    }
}
