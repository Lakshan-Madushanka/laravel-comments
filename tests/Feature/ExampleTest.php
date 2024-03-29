<?php

use Illuminate\Support\Str;

test('example', function () {
    expect(true)->toBeTrue();

    Str::uuid();
});
