<?php

namespace Modules\Export\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Modules\Export\Contracts\ExportableContract;

class ExportFailed extends Exception
{
    public function __construct(public ExportableContract $exportable, public Response $response)
    {
        parent::__construct('Export failed');
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'exportable' => $this->exportable,
            'response' => $this->response,
        ];
    }
}
