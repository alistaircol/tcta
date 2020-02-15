<?php
namespace App\Exception;

class FileValidationException extends \Exception
{
    private array $errors;

    public function __construct($message, array $errors)
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
