<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ImportFileTest extends TestCase
{
    // I would use this but I couldn't find a quick way to test
    // passing stdin to it, so doing it a hacky way.
    // https://symfony.com/doc/current/console.html#testing-commands

    private $report_command = 'php /var/www/html/tcta/cakes report';

    public function testFileWithInvalidHeadersWillGiveErrors()
    {
        $csv = <<<STR
        NAAAAAAAME,DOB
        STR;

        $command = $this->report_command . ' << "CSV"' . PHP_EOL . $csv . PHP_EOL . 'CSV';
        exec($command, $output, $exit_code);

        $this->assertTrue($this->contains($output, 'Row 1. Column \'Name\': This column is required.'));
        $this->assertEquals(1, $exit_code);
    }

    public function testFileWithEmptyNameWillGiveError()
    {
        $csv = <<<STR
        Name,DOB
        ,1993-08-14
        STR;

        $command = $this->report_command . ' << "CSV"' . PHP_EOL . $csv . PHP_EOL . 'CSV';
        exec($command, $output, $exit_code);

        $this->assertTrue($this->contains($output, 'Row 2. Column \'Name\': Can not be blank.'));
        $this->assertEquals(1, $exit_code);
    }

    public function testFileWithEmptyDobWillGiveError()
    {
        $csv = <<<STR
        Name,DOB
        Ally,
        STR;

        $command = $this->report_command . ' << "CSV"' . PHP_EOL . $csv . PHP_EOL . 'CSV';
        exec($command, $output, $exit_code);

        $this->assertTrue($this->contains($output, 'Row 2. Column \'DOB\': Can not be blank.'));
        $this->assertEquals(1, $exit_code);
    }

    public function testFileWithInvalidDobWillGiveError()
    {
        $csv = <<<STR
        Name,DOB
        Ally,1993-99-99
        STR;

        $command = $this->report_command . ' << "CSV"' . PHP_EOL . $csv . PHP_EOL . 'CSV';
        exec($command, $output, $exit_code);

        $this->assertTrue($this->contains($output, 'Row 2. Column \'DOB\': Invalid date, please give in Y-m-d format'));
        $this->assertEquals(1, $exit_code);
    }

    public function testValidFileWillBeSuccessful()
    {
        $csv = <<<STR
        Name,DOB
        Ally,1993-08-14
        STR;

        $command = $this->report_command . ' << "CSV"' . PHP_EOL . $csv . PHP_EOL . 'CSV';
        exec($command, $output, $exit_code);

        $this->assertTrue($this->contains($output, 'Date,"Number of Small Cakes","Number of Large Cakes","Names of people getting cake"'));
        $this->assertTrue($this->contains($output, '2020-08-17,1,0,Ally'));
        $this->assertEquals(0, $exit_code);
    }

    /**
     * @param array $lines
     * @param string $contains
     * @return bool
     */
    private function contains(array $lines, string $contains): bool
    {
        $found = false;

        foreach ($lines as $line) {
            if (strpos($line, $contains) !== false) {
                $found = true;
            }
        }

        return $found;
    }
}
