<?php

namespace Yourdudeken\Mpesa\Validation;

class RuleCollection extends \SplObjectStorage
{
    /**
     * @param Rule\AbstractRule $object
     * @param mixed|null $info
     */
    public function attach(object $object, mixed $info = null): void
    {
        if ($this->contains($object)) {
            return;
        }

        if ($object instanceof Rule\Required) {
            $rules = [];
            foreach ($this as $r) {
                $rules[] = $r;
                $this->detach($r);
            }
            array_unshift($rules, $object);
            foreach ($rules as $r) {
                parent::attach($r);
            }

            return;
        }

        parent::attach($object);
    }

    /**
     * @param Rule\AbstractRule $object
     * @return string
     */
    public function getHash(object $object): string
    {
        return $object->getUniqueId();
    }
}
