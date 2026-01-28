<?php
namespace Yourdudeken\Mpesa\Validation\Rule;

class ArrayLength extends AbstractRule
{
    const OPTION_MIN = 'min';
    const OPTION_MAX = 'max';

    const MESSAGE = 'This input should contain between {min} and {max} items';
    const LABELED_MESSAGE = '{label} should contain between {min} and {max} items';

    protected array $options = [];

    protected array $optionsIndexMap = [
        0 => self::OPTION_MIN,
        1 => self::OPTION_MAX
    ];

    public function validate(mixed $value, mixed $valueIdentifier = null): bool
    {
        $this->value  = $value;
        $maxValidator = new ArrayMaxLength();
        if (isset($this->options['max'])) {
            $maxValidator->setOption('max', $this->options['max']);
        }
        $minValidator = new ArrayMinLength();
        if (isset($this->options['min'])) {
            $minValidator->setOption('min', $this->options['min']);
        }
        $this->success = $minValidator->validate($value, $valueIdentifier) && $maxValidator->validate(
            $value,
            $valueIdentifier
        );

        return $this->success;
    }
}
