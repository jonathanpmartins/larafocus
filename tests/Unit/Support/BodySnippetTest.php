<?php

use Larafocus\Support\BodySnippet;

covers(BodySnippet::class);

test('a short body is returned unchanged', function () {
    expect(BodySnippet::of('service unavailable'))->toBe('service unavailable');
});

test('surrounding whitespace is trimmed', function () {
    expect(BodySnippet::of("  \n\tservice unavailable  \n"))->toBe('service unavailable');
});

test('a whitespace-only body becomes empty', function () {
    expect(BodySnippet::of("  \n\t  "))->toBe('');
});

test('a body of exactly the maximum length is kept whole', function () {
    $body = str_repeat('y', 1024);

    expect(BodySnippet::of($body))->toBe($body);
});

test('a longer body is cut at the maximum length and marked with an ellipsis', function () {
    $body = 'A'.str_repeat('x', 1024);

    $snippet = BodySnippet::of($body);

    expect($snippet)
        ->toBe(mb_substr($body, 0, 1024).'…')
        ->toStartWith('A')
        ->toEndWith('…');
});
