<?php
namespace Yourdudeken\Mpesa\Validation;

use Yourdudeken\Mpesa\Validation\ValidatorInterface;

class Validator implements ValidatorInterface
{
    const RULE_REQUIRED = 'required';

    const RULE_REQUIRED_WITH = 'requiredwith';

    const RULE_REQUIRED_WITHOUT = 'requiredwithout';

    const RULE_REQUIRED_WHEN = 'requiredwhen';

    // string rules
    const RULE_ALPHA = 'alpha';

    const RULE_ALPHANUMERIC = 'alphanumeric';

    const RULE_ALPHANUMHYPHEN = 'alphanumhyphen';

    const RULE_LENGTH = 'length';

    const RULE_MAX_LENGTH = 'maxlength';

    const RULE_MIN_LENGTH = 'minlength';

    const RULE_FULLNAME = 'fullname';

    // array rules
    const RULE_ARRAY_LENGTH = 'arraylength';

    const RULE_ARRAY_MIN_LENGTH = 'arrayminlength';

    const RULE_ARRAY_MAX_LENGTH = 'arraymaxlength';

    const RULE_IN_LIST = 'inlist';

    const RULE_NOT_IN_LIST = 'notinlist';

    // date rules
    const RULE_DATE = 'date';

    const RULE_DATETIME = 'datetime';

    const RULE_TIME = 'time';

    // number rules
    const RULE_BETWEEN = 'between';

    const RULE_GREATER_THAN = 'greaterthan';

    const RULE_LESS_THAN = 'lessthan';

    const RULE_NUMBER = 'number';

    const RULE_INTEGER = 'integer';
    // regular expression rules
    const RULE_REGEX = 'regex';

    const RULE_NOT_REGEX = 'notregex';
    // other rules
    const RULE_EMAIL = 'email';

    const RULE_EMAIL_DOMAIN = 'emaildomain';

    const RULE_URL = 'url';

    const RULE_WEBSITE = 'website';

    const RULE_IP = 'ipaddress';

    const RULE_MATCH = 'match';

    const RULE_EQUAL = 'equal';

    const RULE_CALLBACK = 'callback';

    // files rules
    const RULE_FILE_EXTENSION = 'fileextension';
    const RULE_FILE_SIZE = 'filesize';
    const RULE_IMAGE = 'image';
    const RULE_IMAGE_HEIGHT = 'imageheight';
    const RULE_IMAGE_WIDTH = 'imagewidth';
    const RULE_IMAGE_RATIO = 'imageratio';
    // upload rules
    const RULE_UPLOAD_REQUIRED = 'uploadrequired';
    const RULE_UPLOAD_EXTENSION = 'uploadextension';
    const RULE_UPLOAD_SIZE = 'uploadsize';
    const RULE_UPLOAD_IMAGE = 'uploadimage';
    const RULE_UPLOAD_IMAGE_HEIGHT = 'uploadimageheight';
    const RULE_UPLOAD_IMAGE_WIDTH = 'uploadimagewidth';
    const RULE_UPLOAD_IMAGE_RATIO = 'uploadimageratio';

    /**
     * @var boolean
     */
    protected bool $wasValidated = false;

    /**
     * @var array<string, ValueValidator>
     */
    protected array $rules = [];

    /**
     * @var array
     */
    protected array $messages = [];

    /**
     * @var RuleFactory
     */
    protected RuleFactory $ruleFactory;

    /**
     * @var ErrorMessage
     */
    protected ErrorMessage $errorMessagePrototype;

    /**
     * The object that will contain the data
     *
     * @var DataWrapper\WrapperInterface|null
     */
    protected ?DataWrapper\WrapperInterface $dataWrapper = null;

    public function __construct(RuleFactory $ruleFactory = null, ErrorMessage $errorMessagePrototype = null)
    {
        $this->ruleFactory = $ruleFactory ?: new RuleFactory();
        $this->errorMessagePrototype = $errorMessagePrototype ?: new ErrorMessage();
    }

    /**
     * Retrieve the rule factory
     *
     * @return RuleFactory
     */
    public function getRuleFactory(): RuleFactory
    {
        return $this->ruleFactory;
    }

    /**
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
     * Retrieve the error message prototype
     *
     * @return ErrorMessage
     */
    public function getErrorMessagePrototype(): ErrorMessage
    {
        return $this->errorMessagePrototype;
    }

    /**
     * Add rules
     *
     * @param mixed $selector
     * @param mixed|null $name
     * @param mixed|null $options
     * @param string|null $messageTemplate
     * @param string|null $label
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function add(mixed $selector, mixed $name = null, mixed $options = null, ?string $messageTemplate = null, ?string $label = null): self
    {
        // the $selector is an associative array with $selector => $rules
        if (func_num_args() == 1) {
            if (!is_array($selector)) {
                throw new \InvalidArgumentException('If $selector is the only argument it must be an array');
            }

            return $this->addMultiple($selector);
        }

        if (is_string($selector)) {
            // check if the selector is in the form of 'selector:Label'
            if (str_contains($selector, ':')) {
                [$selector, $label] = explode(':', $selector, 2);
            }

            $this->ensureSelectorRulesExist($selector, $label);
            $this->rules[$selector]->add($name, $options, $messageTemplate, $label);
        }

        return $this;
    }

    /**
     * @param array $selectorRulesCollection
     *
     * @return $this
     */
    public function addMultiple(array $selectorRulesCollection): self
    {
        foreach ($selectorRulesCollection as $selector => $rules) {
            // a single rule was passed for the $valueSelector
            if (!is_array($rules)) {
                $this->add($selector, $rules);
                continue;
            }

            // multiple rules were passed for the same $valueSelector
            foreach ($rules as $rule) {
                // the rule is an array, this means it contains $name, $options, $messageTemplate, $label
                if (is_array($rule)) {
                    array_unshift($rule, $selector);
                    call_user_func_array([$this, 'add'], $rule);
                    // the rule is only the name of the validator
                } else {
                    $this->add($selector, $rule);
                }
            }
        }

        return $this;
    }

    /**
     * @param string $selector
     * @param mixed $name
     * @param mixed|null $options
     *
     * @return $this
     */
    public function remove(string $selector, mixed $name = true, mixed $options = null): self
    {
        if (!array_key_exists($selector, $this->rules)) {
            return $this;
        }
        
        $this->rules[$selector]->remove($name, $options);

        return $this;
    }

    /**
     * The data wrapper will be used to wrap around the data passed to the validator
     *
     * @param mixed|null $data
     *
     * @return DataWrapper\WrapperInterface
     */
    public function getDataWrapper(mixed $data = null): DataWrapper\WrapperInterface
    {
        // if $data is set reconstruct the data wrapper
        if (!$this->dataWrapper || $data !== null) {
            $this->dataWrapper = new DataWrapper\ArrayWrapper($data);
        }

        return $this->dataWrapper;
    }

    public function setData(mixed $data): self
    {
        $this->getDataWrapper($data);
        $this->wasValidated = false;
        // reset messages
        $this->messages = [];

        return $this;
    }

    /**
     * Performs the validation
     *
     * @param mixed|null $data
     *
     * @return boolean
     */
    public function validate(mixed $data = null): bool
    {
        if ($data !== null) {
            $this->setData($data);
        }
        // data was already validated, return the results immediately
        if ($this->wasValidated === true) {
            return count($this->messages) === 0;
        }

        if (!$this->dataWrapper) {
            return true;
        }

        foreach ($this->rules as $selector => $valueValidator) {
            foreach ($this->dataWrapper->getItemsBySelector($selector) as $valueIdentifier => $value) {
                if (!$valueValidator->validate($value, $valueIdentifier, $this->dataWrapper)) {
                    foreach ($valueValidator->getMessages() as $message) {
                        $this->addMessage((string) $valueIdentifier, $message);
                    }
                }
            }
        }
        $this->wasValidated = true;

        return count($this->messages) === 0;
    }

    /**
     * @param string $item
     * @param mixed|null $message
     *
     * @return $this
     */
    public function addMessage(string $item, mixed $message = null): self
    {
        if ($message === null || $message === '') {
            return $this;
        }
        if (!array_key_exists($item, $this->messages)) {
            $this->messages[$item] = [];
        }
        $this->messages[$item][] = $message;

        return $this;
    }

    /**
     * Clears the messages of an item
     *
     * @param string|null $item
     *
     * @return $this
     */
    public function clearMessages(?string $item = null): self
    {
        if ($item !== null) {
            unset($this->messages[$item]);
        } else {
            $this->messages = [];
        }

        return $this;
    }

    /**
     * @param string|null $item
     *
     * @return array
     */
    public function getMessages(?string $item = null): array
    {
        if ($item !== null) {
            return $this->messages[$item] ?? [];
        }

        return $this->messages;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param string $selector
     * @param string|null $label
     */
    protected function ensureSelectorRulesExist(string $selector, ?string $label = null): void
    {
        if (!isset($this->rules[$selector])) {
            $this->rules[$selector] = new ValueValidator(
                $this->getRuleFactory(),
                $this->getErrorMessagePrototype(),
                $label
            );
        }
    }
}
