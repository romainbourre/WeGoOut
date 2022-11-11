<?php

namespace WebApp\Services\ToasterService\Toasts;

abstract class Toast
{
    private const ALERTS_TIMER = 5000;

    public function __construct(private readonly string $message, private readonly ?string $actionLink = null)
    {
    }

    public function __toString(): string
    {
        $message = $this->getMessage();
        $class = $this->getClass();
        $timer = Toast::ALERTS_TIMER;
        return "<script>Materialize.toast('$message', '$timer', '$class')</script>";
    }

    protected function getMessage(): string
    {
        if (is_null($this->actionLink)) {
            return $this->message;
        }
        return "<span>$this->message</span><a href='$this->actionLink' class=\"btn-flat toast-action\">Undo</a>";
    }

    abstract protected function getClass(): string;


}