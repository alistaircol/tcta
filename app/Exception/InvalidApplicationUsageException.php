<?php
namespace App\Exception;

use Throwable;

class InvalidApplicationUsageException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = <<<STR
        Please pass a file through stdin to create a report, like:                
          docker exec -i -u 1000 tcta bash -c "php cakes report" < local-file.csv
        STR;
        parent::__construct($message, $code, $previous);
    }
}
