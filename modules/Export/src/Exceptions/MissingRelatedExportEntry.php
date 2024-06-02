<?php

namespace Modules\Export\Exceptions;

use Exception;
use Modules\Export\Contracts\ExportableContract;

class MissingRelatedExportEntry extends Exception
{
    public function __construct(public ExportableContract $exportable, public ExportableContract|string $relation)
    {
        parent::__construct('Missing related Export Entry');
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return ['exportable' => $this->exportable, 'related' => $this->relation];
    }
}
