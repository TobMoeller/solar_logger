<?php

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

it('creats a pending request instance', function () {
    Config::set('export.export_to_server.base_url', 'http://foo.bar');
    Config::set('export.export_to_server.token', '::token::');
    Config::set('export.export_to_server.timeout', 123);
    Config::set('export.export_to_server.connect_timeout', 321);

    Http::partialMock()
        ->shouldReceive('baseUrl')
        ->once()
        ->with('http://foo.bar')
        ->andReturn(new PendingRequest());

    expect(Http::exportServer())
        ->toBeInstanceOf(PendingRequest::class)
        ->getOptions()->toMatchArray([
            'headers' => [
                'Authorization' => 'Bearer ::token::',
            ],
            'timeout' => 123,
            'connect_timeout' => 321,
        ]);
});
