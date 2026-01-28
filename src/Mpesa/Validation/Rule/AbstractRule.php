<?php
namespace Yourdudeken\Mpesa\Validation\Rule;

use Yourdudeken\Mpesa\Validation\DataWrapper\ArrayWrapper;
use Yourdudeken\Mpesa\Validation\DataWrapper\WrapperInterface;
use Yourdudeken\Mpesa\Validation\ErrorMessage;

abstract class AbstractRule
{
    // default error message when there is no LABEL attached
    const MESSAGE = 'Value is not valid';

    // default error message when there is a LABEL attached
    const LABELED_MESSAGE = '{label} is not valid';

    /**
     * The validation context
     * This is the data set that the data being validated belongs to
     * @var WrapperInterface|null
     */
    protected ?WrapperInterface $context = null;

    /**
     * Options for the validator.
     * Also passed to the error message for customization.
     *
     * @var array
     */
    protected array $options = [];

    /**
     * Custom error message template for the validator instance
     * If you don't agree with the default messages that were provided
     *
     * @var string|null
     */
    protected ?string $messageTemplate = null;

    /**
     * Result of the last validation
     *
     * @var boolean
     */
    protected bool $success = false;

    /**
     * Last value validated with the validator.
     * Stored in order to be passed to the errorMessage so that you get error
     * messages like '"abc" is not a valid email'
     *
     * @var mixed
     */
    protected mixed $value = null;

    /**
     * The error message prototype that will be used to generate the error message
     *
     * @var ErrorMessage|null
     */
    protected ?ErrorMessage $errorMessagePrototype = null;

    /**
     * Options map in case the options are passed as list instead of associative array
     *
     * @var array
     */
    protected array $optionsIndexMap = [];

    public function __construct(mixed $options = [])
    {
        $options = $this->normalizeOptions($options);
        if (is_array($options) && !empty($options)) {
            foreach ($options as $k => $v) {
                $this->setOption($k, $v);
            }
        }
    }

    /**
     * Method that parses the option variable and converts it into an array
     * You can pass anything to a validator like:
     * - a query string: 'min=3&max=5'
     * - a JSON string: '{"min":3,"max":5}'
     * - a CSV string: '5,true' (for this scenario the 'optionsIndexMap' property is required)
     *
     * @param mixed $options
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function normalizeOptions(mixed $options): array
    {
        if (!$options) {
            return [];
        }

        if (is_array($options) && $this->arrayIsAssoc($options)) {
            return $options;
        }

        $result = $options;
        if (is_string($options)) {
            $startChar = substr($options, 0, 1);
            if ($startChar == '{') {
                $result = json_decode($options, true);
            } elseif (str_contains($options, '=')) {
                $result = $this->parseHttpQueryString($options);
            } else {
                $result = $this->parseCsvString($options);
            }
        }

        if (!is_array($result)) {
            throw new \InvalidArgumentException('Validator options should be an array, JSON string or query string');
        }

        return $result;
    }

    /**
     * Converts a HTTP query string to an array
     *
     * @param string $str
     *
     * @return array
     */
    protected function parseHttpQueryString(string $str): array
    {
        parse_str($str, $arr);

        return $this->convertBooleanStrings($arr);
    }

    /**
     * Converts 'true' and 'false' strings to TRUE and FALSE
     *
     * @param mixed $v
     *
     * @return mixed
     */
    protected function convertBooleanStrings(mixed $v): mixed
    {
        if (is_array($v)) {
            return array_map([$this, 'convertBooleanStrings'], $v);
        }
        if ($v === 'true') {
            return true;
        }
        if ($v === 'false') {
            return false;
        }

        return $v;
    }

    /**
     * Parses a CSV string and converts the result into an "options" array
     * (an associative array that contains the options for the validation rule)
     *
     * @param string $str
     *
     * @return array
     */
    protected function parseCsvString(string $str): array
    {
        if (empty($this->optionsIndexMap)) {
            throw new \InvalidArgumentException(sprintf(
                'Class %s is missing the `optionsIndexMap` property',
                get_class($this)
            ));
        }

        $options = explode(',', $str);
        $result = [];
        foreach ($options as $k => $v) {
            if (!isset($this->optionsIndexMap[$k])) {
                throw new \InvalidArgumentException(sprintf(
                    'Class %s does not have the index %d configured in the `optionsIndexMap` property',
                    get_class($this),
                    $k
                ));
            }
            $result[$this->optionsIndexMap[$k]] = $v;
        }

        return $this->convertBooleanStrings($result);
    }

    /**
     * Checks if an array is associative (ie: the keys are not numbers in sequence)
     *
     * @param array $arr
     *
     * @return bool
     */
    protected function arrayIsAssoc(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }


    /**
     * Generates a unique string to identify the validator.
     * It is used to compare 2 validators so you don't add the same rule twice in a validator object
     *
     * @return string
     */
    public function getUniqueId(): string
    {
        $options = $this->options;
        ksort($options);
        return get_called_class() . '|' . json_encode($options);
    }

    /**
     * Set an option for the validator.
     *
     * The options are also be passed to the error message.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setOption(string $name, mixed $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Get an option for the validator.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    /**
     * The context of the validator can be used when the validator depends on other values
     * that are not known at the moment the validator is constructed
     * For example, when you need to validate an email field matches another email field,
     * to confirm the email address
     *
     * @param mixed $context
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setContext(mixed $context = null): self
    {
        if ($context === null) {
            return $this;
        }
        if (is_array($context)) {
            $context = new ArrayWrapper($context);
        }
        if (!is_object($context) || !$context instanceof WrapperInterface) {
            throw new \InvalidArgumentException(
                'Validator context must be either an array or an instance of Yourdudeken\Mpesa\Validator\DataWrapper\WrapperInterface'
            );
        }
        $this->context = $context;

        return $this;
    }

    /**
     * Custom message for this validator to used instead of the the default one
     *
     * @param string|null $messageTemplate
     *
     * @return $this
     */
    public function setMessageTemplate(?string $messageTemplate): self
    {
        $this->messageTemplate = $messageTemplate;

        return $this;
    }

    /**
     * Retrieves the error message template (either the global one or the custom message)
     *
     * @return string
     */
    public function getMessageTemplate(): string
    {
        if ($this->messageTemplate) {
            return $this->messageTemplate;
        }
        if (isset($this->options['label'])) {
            return constant(get_class($this) . '::LABELED_MESSAGE');
        }

        return constant(get_class($this) . '::MESSAGE');
    }

    /**
     * Validates a value
     *
     * @param mixed $value
     * @param mixed|null $valueIdentifier
     *
     * @return bool
     */
    abstract public function validate(mixed $value, mixed $valueIdentifier = null): bool;

    /**
     * Sets the error message prototype that will be used when returning the error message
     * when validation fails.
     * This option can be used when you need translation
     *
     * @param ErrorMessage $errorMessagePrototype
     *
     * @return $this
     */
    public function setErrorMessagePrototype(ErrorMessage $errorMessagePrototype): self
    {
        $this->errorMessagePrototype = $errorMessagePrototype;

        return $this;
    }

    /**
     * Returns the error message prototype.
     * It constructs one if there isn't one.
     *
     * @return ErrorMessage
     */
    public function getErrorMessagePrototype(): ErrorMessage
    {
        if (!$this->errorMessagePrototype) {
            $this->errorMessagePrototype = new ErrorMessage();
        }

        return $this->errorMessagePrototype;
    }

    /**
     * Retrieve the error message if validation failed
     *
     * @return ErrorMessage|null
     */
    public function getMessage(): ?ErrorMessage
    {
        if ($this->success) {
            return null;
        }
        $message = $this->getPotentialMessage();
        $message->setVariables([
            'value' => $this->value
        ]);

        return $message;
    }

    /**
     * Retrieve the potential error message.
     * Example: when you do client-side validation you need to access the "potential error message" to be displayed
     *
     * @return ErrorMessage
     */
    public function getPotentialMessage(): ErrorMessage
    {
        $message = clone ($this->getErrorMessagePrototype());
        $message->setTemplate($this->getMessageTemplate());
        $message->setVariables($this->options);

        return $message;
    }

    /**
     * Method for determining the path to a related item.
     * Eg: for `lines[5][price]` the related item `lines[*][quantity]`
     * has the value identifier as `lines[5][quantity]`
     *
     * @param mixed $valueIdentifier
     * @param string $relatedItem
     *
     * @return string
     */
    protected function getRelatedValueIdentifier(mixed $valueIdentifier, string $relatedItem): string
    {
        // in case we don't have a related path
        if (!str_contains($relatedItem, '*')) {
            return $relatedItem;
        }

        // lines[*][quantity] is converted to ['lines', '*', 'quantity']
        $relatedItemParts = explode('[', str_replace(']', '', $relatedItem));
        // lines[5][price] is ['lines', '5', 'price']
        $valueIdentifierParts = explode('[', str_replace(']', '', (string) $valueIdentifier));

        if (count($relatedItemParts) !== count($valueIdentifierParts)) {
            return $relatedItem;
        }

        // the result should be ['lines', '5', 'quantity']
        $relatedValueIdentifierParts = [];
        foreach ($relatedItemParts as $index => $part) {
            if ($part === '*' && isset($valueIdentifierParts[$index])) {
                $relatedValueIdentifierParts[] = $valueIdentifierParts[$index];
            } else {
                $relatedValueIdentifierParts[] = $part;
            }
        }

        $relatedValueIdentifier = implode('][', $relatedValueIdentifierParts) . ']';
        $relatedValueIdentifier = str_replace(
            $relatedValueIdentifierParts[0] . ']',
            $relatedValueIdentifierParts[0],
            $relatedValueIdentifier
        );

        return $relatedValueIdentifier;
    }
}
