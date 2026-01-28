<?php

namespace Yourdudeken\Mpesa\Validation;

interface ValidatorInterface
{
    public function add(mixed $selector, mixed $name = null, mixed $options = null, ?string $messageTemplate = null, ?string $label = null): self;

    public function remove(string $selector, mixed $name = true, mixed $options = null): self;

    public function validate(mixed $data = null): bool;
}
