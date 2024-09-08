<?php

namespace LakM\Comments\Data;

use Illuminate\Contracts\Support\Arrayable;
use Livewire\Wireable;

final class MessageData implements Wireable, Arrayable
{
    public function __construct(
        public string $text,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $ip_address = null,
    ) {
        if (is_null($this->ip_address)) {
            $this->ip_address = request()->ip();
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            ip_address: request()->ip()
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'name' => $this->name,
            'email' =>  $this->email,
            'ip_address' => $this->ip_address,
        ];
    }

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'ip_address' => $this->ip_address,
        ];
    }

    public static function fromLivewire($value): static
    {
        $name = $value['name'];
        $email = $value['email'];
        $ip_address = $value['ip_address'];

        return new MessageData($name, $email, $ip_address);
    }
}
