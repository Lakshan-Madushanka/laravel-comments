<?php

namespace LakM\Comments\Data;

use Livewire\Wireable;

class UserData implements Wireable
{
    public function __construct(
     public ?string $name,
     public ?string $photo,
    )
    {}

    public function toLivewire()
    {
        return [
            'name' => $this->name,
            'photo' => $this->photo,
        ];
    }

    public static function fromLivewire($value)
    {
        $name = $value['name'];
        $photo = $value['photo'];

        return new static($name, $photo);
    }

}
