<?php
namespace Yourdudeken\Mpesa\Validation\Rule;

class DateTime extends Date
{
    const MESSAGE = 'This input must be a date having the format {format}';

    const LABELED_MESSAGE = '{label} must be a date having the format {format}';

    protected array $options = [
        'format' => 'Y-m-d H:i:s'
    ];
}
