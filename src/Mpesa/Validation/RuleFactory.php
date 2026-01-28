<?php

namespace Yourdudeken\Mpesa\Validation;

use Yourdudeken\Mpesa\Validation\Rule\Callback as CallbackRule;

class RuleFactory
{
    /**
     * Validator map allows for flexibility when creating a validation rule
     *
     * @var array
     */
    protected array $validatorsMap = [];

    /**
     * @var array
     */
    protected array $errorMessages = [];

    /**
     * @var array
     */
    protected array $labeledErrorMessages = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registerDefaultRules();
    }

    /**
     * Set up the default rules that come with the library
     */
    protected function registerDefaultRules(): void
    {
        $rulesClasses = [
            'Alpha',
            'AlphaNumeric',
            'AlphaNumHyphen',
            'ArrayLength',
            'ArrayMaxLength',
            'ArrayMinLength',
            'Between',
            'Callback',
            'Date',
            'DateTime',
            'Email',
            'EmailDomain',
            'Equal',
            'FullName',
            'GreaterThan',
            'InList',
            'Integer',
            'IpAddress',
            'Length',
            'LessThan',
            'MatchField',
            'MaxLength',
            'MinLength',
            'NotInList',
            'NotRegex',
            'Number',
            'Regex',
            'Required',
            'RequiredWhen',
            'RequiredWith',
            'RequiredWithout',
            'Time',
            'Url',
            'Website',
            'File\Extension',
            'File\Image',
            'File\ImageHeight',
            'File\ImageRatio',
            'File\ImageWidth',
            'File\Size',
            'Upload\Required',
            'Upload\Extension',
            'Upload\Image',
            'Upload\ImageHeight',
            'Upload\ImageRatio',
            'Upload\ImageWidth',
            'Upload\Size',
        ];
        foreach ($rulesClasses as $class) {
            $fullClassName = '\\' . __NAMESPACE__ . '\Rule\\' . $class;
            $name = strtolower(str_replace('\\', '', $class));
            $errorMessage = constant($fullClassName . '::MESSAGE');
            $labeledErrorMessage = constant($fullClassName . '::LABELED_MESSAGE');
            $this->register($name, $fullClassName, $errorMessage, $labeledErrorMessage);
        }
    }


    /**
     * Register a class to be used when creating validation rules
     *
     * @param string $name
     * @param string $class
     * @param string $errorMessage
     * @param string $labeledErrorMessage
     *
     * @return $this
     */
    public function register(string $name, string $class, string $errorMessage = '', string $labeledErrorMessage = ''): self
    {
        if (is_subclass_of($class, '\Yourdudeken\Mpesa\Validation\Rule\AbstractRule')) {
            $this->validatorsMap[$name] = $class;
        }
        if ($errorMessage) {
            $this->errorMessages[$name] = $errorMessage;
        }
        if ($labeledErrorMessage) {
            $this->labeledErrorMessages[$name] = $labeledErrorMessage;
        }

        return $this;
    }

    /**
     * Factory method to construct a validator
     *
     * @param string|callable $name
     * @param mixed|null $options
     * @param string|null $messageTemplate
     * @param string|null $label
     *
     * @return AbstractRule
     * @throws \InvalidArgumentException
     */
    public function createRule(mixed $name, mixed $options = null, ?string $messageTemplate = null, ?string $label = null): AbstractRule
    {
        $validator = $this->construcRuleByNameAndOptions($name, $options);

        // no message template, try to get it from the registry
        if (!$messageTemplate) {
            $messageTemplate = $this->getSuggestedMessageTemplate($name, (bool) $label);
        }

        if ($messageTemplate !== null && $messageTemplate !== '') {
            $validator->setMessageTemplate($messageTemplate);
        }
        if ($label !== null && $label !== '') {
            $validator->setOption('label', $label);
        }

        return $validator;
    }

    /**
     * Set default error message for a rule
     *
     * @param string $rule
     * @param string|null $messageWithoutLabel
     * @param string|null $messageWithLabel
     *
     * @return $this
     */
    public function setMessages(string $rule, ?string $messageWithoutLabel = null, ?string $messageWithLabel = null): self
    {
        if ($messageWithoutLabel) {
            $this->errorMessages[$rule] = $messageWithoutLabel;
        }
        if ($messageWithLabel) {
            $this->labeledErrorMessages[$rule] = $messageWithLabel;
        }

        return $this;
    }

    /**
     * Get the suggested error message template
     *
     * @param mixed $name
     * @param bool $withLabel
     *
     * @return string|null
     */
    protected function getSuggestedMessageTemplate(mixed $name, bool $withLabel): ?string
    {
        $noLabelMessage = is_string($name) && isset($this->errorMessages[$name]) ? $this->errorMessages[$name] : null;
        if ($withLabel) {
            return is_string($name) && isset($this->labeledErrorMessages[$name]) ?
                $this->labeledErrorMessages[$name] :
                $noLabelMessage;
        }

        return $noLabelMessage;
    }

    /**
     * Construct rule by name and options
     * 
     * @param mixed $name
     * @param mixed $options
     * 
     * @return AbstractRule
     */
    protected function construcRuleByNameAndOptions(mixed $name, mixed $options): AbstractRule
    {
        if (is_callable($name)) {
            $validator = new CallbackRule([
                'callback' => $name,
                'arguments' => $options
            ]);
        } elseif (is_string($name)) {
            $name = trim($name);
            // use the validator map
            if (isset($this->validatorsMap[strtolower($name)])) {
                $name = $this->validatorsMap[strtolower($name)];
            }
            // try if the validator is the name of a class in the package
            if (class_exists('\Yourdudeken\Mpesa\Validation\Rule\\' . $name, false)) {
                $name = '\Yourdudeken\Mpesa\Validation\Rule\\' . $name;
            }
            // at this point we should have a class that can be instanciated
            if (class_exists($name) && is_subclass_of($name, '\Yourdudeken\Mpesa\Validation\Rule\AbstractRule')) {
                $validator = new $name($options);
            }
        }

        if (!isset($validator)) {
            throw new \InvalidArgumentException(
                sprintf('Impossible to determine the validator based on the name: %s', is_string($name) ? $name : gettype($name))
            );
        }

        return $validator;
    }
}
