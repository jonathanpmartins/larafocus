<?php

use Larafocus\Focus;
use Larafocus\Infrastructure\FocusManager;

covers(FocusManager::class);

test('resolveFileUrl rejects path traversal with double dots', function () {
    Focus::resolveFileUrl('/../../../etc/passwd');
})->throws(InvalidArgumentException::class, 'must not contain path traversal');

test('resolveFileUrl rejects path with embedded double dots', function () {
    Focus::resolveFileUrl('/v2/nfse/../../../admin');
})->throws(InvalidArgumentException::class, 'must not contain path traversal');

test('resolveFileUrl rejects path with null bytes', function () {
    Focus::resolveFileUrl("/v2/nfse/abc\0.xml");
})->throws(InvalidArgumentException::class, 'must not contain null bytes');

test('resolveFileUrl rejects path not starting with forward slash', function () {
    Focus::resolveFileUrl('v2/nfse/abc.xml');
})->throws(InvalidArgumentException::class, 'must start with');

test('resolveFileUrl rejects empty path', function () {
    Focus::resolveFileUrl('');
})->throws(InvalidArgumentException::class, 'must start with');

test('resolveFileUrl accepts valid path with single dots', function () {
    expect(Focus::resolveFileUrl('/v2/nfse/abc123.xml'))
        ->toBe('https://homologacao.focusnfe.com.br/v2/nfse/abc123.xml');
});
