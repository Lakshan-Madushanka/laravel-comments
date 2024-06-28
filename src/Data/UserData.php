<?php

namespace LakM\Comments\Data;

use Livewire\Wireable;

final class UserData implements Wireable
{
    public function __construct(
        public ?string $name,
        public ?string $email = null,
        public ?string $photo = null,
    ) {
    }

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'photo' => $this->photo,
        ];
    }

    public static function fromLivewire($value): static
    {
        $name = $value['name'];
        $email = $value['email'];
        $photo = $value['photo'];

        return new UserData($name, $email, $photo);
    }
}
