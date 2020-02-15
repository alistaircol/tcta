<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Carbon\Carbon;

use League\Csv\Reader;
use League\Csv\Writer;

use App\Exception\InvalidApplicationUsageException;
use App\Exception\FileValidationException;

use App\Person;
use App\CakeDistribution;

class CreateCakeReportCommand extends Command
{
    protected static $defaultName = 'report';

    // Incoming file fields
    protected string $stdin;
    protected array $expected_input_headers = [
        'Name',
        'DOB',
    ];
    protected Reader $reader;
    protected array $input_file_validation_errors = [];

    // Outgoing report fields
    protected Writer $writer;

    /**
     * Setup the command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Provides a summary for the years cake requirement.')
            ->setHelp(<<<STR
                This command will create a report detailing the date where cakes are required, the size, and whose birthdays they are for.
                
                Pass a file through stdin:                
                  docker exec -i -u 1000 tcta bash -c "php cakes report" < local-file.csv
            STR);

        $this->stdin = file_get_contents('php://stdin');
    }

    /**
     * Handle processing the cake days for employees.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // If no contents from stdin then show an error with example usage
            if ($this->stdin === null) {
                throw new InvalidApplicationUsageException();
            }

            try {
                $this->buildInputCsv();
                $this->validateInputCsv();
                $this->buildReport();

                $output->writeln($this->writer->getContent());

            } catch (FileValidationException $e) {
                $io->error('There were errors with the file. Please fix them and try again.');

                $errors = $e->getErrors();
                foreach ($errors as $error) {
                    $output->writeln('Row ' . $error['row'] . '. Column \'' . $error['field'] . '\': ' . $error['error']);
                }
                throw $e;
            }
        } catch (\Throwable $e) {
            // don't use pretty print for this, long lines are required
            if ($e instanceof InvalidApplicationUsageException) {
                $output->writeln('');
                $output->writeln($e->getMessage());
                $output->writeln('');
            } else {
                $io->error($e->getMessage());
            }
            return 1;
        }

        return 0;
    }

    /**
     * Build a CSV reader instance from stdin
     *
     * @throws \Exception
     */
    private function buildInputCsv(): void
    {
        $this->reader = Reader::createFromString($this->stdin);
        $this->reader->setHeaderOffset(0);
    }

    /**
     * Validates the incoming file, throws exception if errors.
     *
     * @throws FileValidationException
     */
    private function validateInputCsv(): void
    {
        $this->validateInputCsvHeaders();
        $this->validateInputCsvContent();
    }

    /**
     * @throws FileValidationException
     */
    private function validateInputCsvHeaders()
    {
        $header = $this->reader->getHeader();

        foreach ($this->expected_input_headers as $name) {
            if (!in_array($name, $header)) {
                $this->input_file_validation_errors[] = [
                    'row' => 1,
                    'field' => $name,
                    'error' => 'This column is required.',
                ];
            }
        }

        // We want to bail here because it won't be possible to validate any further
        if (count($this->input_file_validation_errors) > 0) {
            throw new FileValidationException(
                'The input file has errors.',
                $this->input_file_validation_errors
            );
        }
    }

    /**
     * @throws FileValidationException
     */
    private function validateInputCsvContent()
    {
        $records = $this->reader->getRecords();
        foreach ($records as $row => $content) {
            $actual_row = $row + 1;
            foreach ($this->expected_input_headers as $name) {
                if (!strlen($content[$name])) {
                    $this->input_file_validation_errors[] = [
                        'row' => $actual_row,
                        'field' => $name,
                        'error' => 'Can not be blank.',
                    ];
                }
            }

            try {
                // Carbon gets next valid date if date is invalid..
                if (strlen($content['DOB']) != 10) {
                    throw new Exception('Invalid date length.');
                }

                // YYYY-MM-DD
                // 0123456789
                $month = substr($content['DOB'], 5, 2);
                $day   = substr($content['DOB'], 8, 2);
                $year  = substr($content['DOB'], 0, 4);

                $valid_date = checkdate($month, $day, $year);
                if (!$valid_date) {
                    throw new Exception('Invalid date');
                }
            } catch (\Throwable $e) {
                $this->input_file_validation_errors[] = [
                    'row' => $actual_row,
                    'field' => 'DOB',
                    'error' => 'Invalid date, please give in Y-m-d format, e.g. ' . date('Y-m-d') . '.',
                ];
            }
        }

        if (count($this->input_file_validation_errors)) {
            throw new FileValidationException(
                'The input file has errors.',
                $this->input_file_validation_errors
            );
        }
    }

    private function buildReport()
    {
        $people = new \ArrayIterator([], \ArrayIterator::STD_PROP_LIST);
        foreach ($this->reader->getRecords() as $record) {
            $person = (new Person())->name($record['Name'])->dob(new Carbon($record['DOB']));
            $people->append($person);
        }

        $cake_distribution = new CakeDistribution();
        $cake_distribution->setPeople($people);

        $distribution = $cake_distribution->getDistribution();

        $writer = Writer::createFromString('');
        $writer->insertOne(['Date', 'Number of Small Cakes', 'Number of Large Cakes', 'Names of people getting cake']);

        foreach ($distribution as $cake_date => $cake) {
            $names = implode(', ', $cake->getPeople());
            $writer->insertOne([
                'Date' => $cake_date,
                'Number of Small Cakes' => $cake->getCakeSize() == 'small' ? 1 : 0, // this is not ideal
                'Number of Large Cakes' => $cake->getCakeSize() == 'large' ? 1 : 0, // this is not ideal
                'Names of people getting cake' => $names,
            ]);
        }

        $this->writer = $writer;

    }
}
