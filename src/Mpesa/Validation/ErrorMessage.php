<?php

namespace Yourdudeken\Mpesa\Validation;

class ErrorMessage
{
    protected string $template = 'Invalid';
    protected array $variables = [];

    public function __construct(string $template = '', array $variables = [])
    {
        $this->setTemplate($template);
        $this->setVariables($variables);
    }

    public function setTemplate(string $template): self
    {
        $template = trim($template);
        if ($template) {
            $this->template = $template;
        }

        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setVariables(array $variables = []): self
    {
        foreach ($variables as $k => $v) {
            $this->variables[$k] = $v;
        }

        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function __toString(): string
    {
        $result = $this->template;
        foreach ($this->variables as $k => $v) {
            if (str_contains($result, "{{$k}}")) {
                $result = str_replace("{{$k}}", (string) $v, $result);
            }
        }

        return $result;
    }
}
