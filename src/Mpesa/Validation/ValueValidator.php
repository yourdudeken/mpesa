<?php

namespace Yourdudeken\Mpesa\Validation;

use Yourdudeken\Mpesa\Validation\Rule\AbstractRule;

class ValueValidator
{
    /**
     * The error messages generated after validation or set manually
     *
     * @var array
     */
    protected array $messages = [];

    /**
     * Will be used to construct the rules
     *
     * @var RuleFactory
     */
    protected RuleFactory $ruleFactory;

    /**
     * The prototype that will be used to generate the error message
     *
     * @var ErrorMessage
     */
    protected ErrorMessage $errorMessagePrototype;

    /**
     * The rule collections for the validation
     *
     * @var RuleCollection
     */
    protected RuleCollection $rules;

    /**
     * The label of the value to be validated
     *
     * @var string|null
     */
    protected ?string $label = null;


    public function __construct(
        RuleFactory $ruleFactory = null,
        ErrorMessage $errorMessagePrototype = null,
        ?string $label = null
    ) {
        $this->ruleFactory = $ruleFactory ?: new RuleFactory();
        $this->errorMessagePrototype = $errorMessagePrototype ?: new ErrorMessage();
        if ($label) {
            $this->label = $label;
        }
        $this->rules = new RuleCollection();
    }

    public function setLabel(?string $label = null): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Add 1 or more validation rules
     *
     * @param mixed $name
     * @param mixed $options
     * @param string|null $messageTemplate
     * @param string|null $label
     *
     * @return $this
     */
    public function add(mixed $name, mixed $options = null, ?string $messageTemplate = null, ?string $label = null): self
    {
        if (is_array($name) && !is_callable($name)) {
            return $this->addMultiple($name);
        }
        if (is_string($name)) {
            // rule was supplied like 'required | email'
            if (str_contains($name, ' | ')) {
                return $this->addMultiple(explode(' | ', $name));
            }
            // rule was supplied like this 'length(2,10)(error message template)(label)'
            if (str_contains($name, '(')) {
                [$name, $options, $messageTemplate, $label] = $this->parseRule($name);
            }
        }

        // check for the default label
        if (!$label && $this->label) {
            $label = $this->label;
        }

        $validator = $this->ruleFactory->createRule($name, $options, $messageTemplate, $label);

        return $this->addRule($validator);
    }

    /**
     * @param array $rules
     *
     * @return $this
     */
    public function addMultiple(array $rules): self
    {
        foreach ($rules as $singleRule) {
            // make sure the rule is an array (the parameters of subsequent calls);
            $singleRule = is_array($singleRule) ? $singleRule : array(
                $singleRule
            );
            call_user_func_array([$this, 'add'], $singleRule);
        }

        return $this;
    }

    /**
     * @param AbstractRule $validationRule
     *
     * @return $this
     */
    public function addRule(AbstractRule $validationRule): self
    {
        $validationRule->setErrorMessagePrototype($this->errorMessagePrototype);
        $this->rules->attach($validationRule);

        return $this;
    }

    /**
     * Remove validation rule
     *
     * @param mixed $name
     * @param mixed $options
     *
     * @return $this
     */
    public function remove(mixed $name = true, mixed $options = null): self
    {
        if ($name === true) {
            $this->rules = new RuleCollection();

            return $this;
        }
        $validator = $this->ruleFactory->createRule($name, $options);
        $this->rules->detach($validator);

        return $this;
    }

    /**
     * Converts a rule that was supplied as string into a set of options that define the rule
     *
     * @param string $ruleAsString
     *
     * @return array
     */
    protected function parseRule(string $ruleAsString): array
    {
        $ruleAsString = trim($ruleAsString);
        $options = [];
        $messageTemplate = null;
        $label = null;

        $name = substr($ruleAsString, 0, strpos($ruleAsString, '('));
        $ruleAsString = substr($ruleAsString, strpos($ruleAsString, '('));
        $matches = [];
        preg_match_all('/\(([^\)]*)\)/', $ruleAsString, $matches);

        if (isset($matches[1])) {
            if (isset($matches[1][0]) && $matches[1][0]) {
                $options = $matches[1][0];
            }
            if (isset($matches[1][1]) && $matches[1][1]) {
                $messageTemplate = $matches[1][1];
            }
            if (isset($matches[1][2]) && $matches[1][2]) {
                $label = $matches[1][2];
            }
        }

        return [$name, $options, $messageTemplate, $label];
    }


    public function validate(mixed $value, mixed $valueIdentifier = null, ?DataWrapper\WrapperInterface $context = null): bool
    {
        $this->messages = [];
        $isRequired = false;
        foreach ($this->rules as $rule) {
            if ($rule instanceof Rule\Required) {
                $isRequired = true;
                break;
            }
        }

        if (!$isRequired && $value === null) {
            return true;
        }

        /* @var $rule AbstractRule */
        foreach ($this->rules as $rule) {
            $rule->setContext($context);
            if (!$rule->validate($value, $valueIdentifier)) {
                $this->addMessage($rule->getMessage());
            }
            // if field is required and we have an error,
            // do not continue with the rest of rules
            if ($isRequired && count($this->messages)) {
                break;
            }
        }

        return count($this->messages) === 0;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function addMessage(mixed $message): self
    {
        array_push($this->messages, $message);

        return $this;
    }

    public function getRules(): RuleCollection
    {
        return $this->rules;
    }
}
