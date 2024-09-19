<?php

use LakM\Comments\Data\GuestData;
use LakM\Comments\Models\Guest;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('can create a guest', function () {
    $guest = new GuestData(name: fake()->name, email: fake()->email);

    Guest::createOrUpdate($guest);

    assertDatabaseHas('guests', $guest->toArray());
});

it('can update already existing guest', function () {
    onGuestMode();

    $guest = new GuestData(name: fake()->name, email: fake()->email);

    Guest::createOrUpdate($guest);

    $newGuest = new GuestData(name: 'lakm', email: $guest->email);

    Guest::createOrUpdate($newGuest);

    assertDatabaseCount('guests', 1);

    assertDatabaseHas('guests', [...$guest->toArray(), 'name' => 'lakm']);
});
