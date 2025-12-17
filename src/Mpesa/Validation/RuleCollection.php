<?php

namespace Mpesa\Validation;

class RuleCollection extends \SplObjectStorage
{
    public function attach(object $rule, mixed $data = null): void
    {
        if ($this->contains($rule)) {
            return;
        }
        if ($rule instanceof Rule\Required) {
            $rules = array();
            foreach ($this as $r) {
                $rules[] = $r;
                $this->detach($r);
            }
            array_unshift($rules, $rule);
            foreach ($rules as $r) {
                parent::attach($r);
            }

            return;
        }

        parent::attach($rule);
    }

    public function getHash(object $rule): string
    {
        /* @var $rule Rule\AbstractValidator */
        return $rule->getUniqueId();
    }
}
