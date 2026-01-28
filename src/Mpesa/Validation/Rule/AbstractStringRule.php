<?php

namespace Yourdudeken\Mpesa\Validation\Rule;

abstract class AbstractStringRule extends AbstractRule
{
    protected function getStringLength(mixed $str): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen(
                $str,
                (isset($this->options['encoding']) && $this->options['encoding']) ?
                $this->options['encoding'] : mb_internal_encoding()
            );
        }

        return strlen($str);
    }
}
