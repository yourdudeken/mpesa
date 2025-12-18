<?php

namespace Yourdudeken\Mpesa\Validation;

class RuleCollection extends \SplObjectStorage
{
    /**
     * Attach a rule to the collection
     * 
     * @param object $rule The rule to attach
     * @param mixed $data Optional data
     * @return void
     */
    public function attach($rule, $data = null): void
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

    /**
     * Get hash for a rule
     * 
     * @param object $rule The rule object
     * @return string
     */
    public function getHash($rule): string
    {
        /* @var $rule Rule\AbstractValidator */
        return $rule->getUniqueId();
    }
}
