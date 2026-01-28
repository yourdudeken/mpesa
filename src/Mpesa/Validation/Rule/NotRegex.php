<?php
namespace Yourdudeken\Mpesa\Validation\Rule;

class NotRegex extends Regex
{
    const MESSAGE = 'This input should not match the regular expression {pattern}';
    const LABELED_MESSAGE = '{label} Tshould not match the regular expression {pattern}';

    public function validate(mixed $value, mixed $valueIdentifier = null): bool
    {
        parent::validate($value, $valueIdentifier);
        $this->success = ! $this->success;

        return $this->success;
    }
}
