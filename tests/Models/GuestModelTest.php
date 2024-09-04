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
    $guest = new GuestData(name: fake()->name, email: fake()->email);

    Guest::createOrUpdate($guest);

    $newGuest = new GuestData(name: 'lakm',);

    Guest::createOrUpdate($newGuest);

    assertDatabaseCount('guests', 1);

    assertDatabaseHas('guests', [...$guest->toArray(), 'name' => 'lakm']);
});

it('keep original name and email when those attributes are missing from payload', function () {
    $guest = new GuestData(name: fake()->name, email: fake()->email);

    Guest::createOrUpdate($guest);

    $newGuest = new GuestData();

    Guest::createOrUpdate($newGuest);

    assertDatabaseCount('guests', 1);

    assertDatabaseHas('guests', [...$guest->toArray()]);
});


