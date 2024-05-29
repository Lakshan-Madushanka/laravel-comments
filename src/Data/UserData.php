<?php

namespace LakM\Comments\Data;

use Livewire\Wireable;

class UserData implements Wireable
{
    public function __construct(
        public ?string $name,
        public ?string $email = null,
        public ?string $photo = null,
    ) {
    }

    public function toLivewire()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'photo' => $this->photo,
        ];
    }

    public static function fromLivewire($value)
    {
        $name = $value['name'];
        $email = $value['email'];
        $photo = $value['photo'];

        return new static($name, $email, $photo);
    }

}
