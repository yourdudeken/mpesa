<?php

namespace Yourdudeken\Mpesa\Validation\Rule;

class Time extends Date
{
    const MESSAGE = 'This input must be a time having the format {format}';
    const LABELED_MESSAGE = '{label} must be a time having the format {format}';

    protected array $options = [
        'format' => 'H:i:s'
    ];
}
