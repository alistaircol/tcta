<?php
namespace App\Command;

use App\Exception\InvalidFileException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use League\Csv\Reader;
use League\Csv\Writer;

class CreateCakeReportCommand extends Command
{
    protected static $defaultName = 'report';

    protected Reader $reader;
    protected Writer $writer;

    protected function configure()
    {
        // Summary
        $this
            ->setDescription('Provides a summary for the years cake requirement.')
            ->setHelp(<<<STR
                This command will create a report detailing the date where cakes are required,
                the size, and whose birthdays they are for.
            STR);

        // We need a file containing names and birthdates to process
        $this->addArgument(
            'file',
            InputArgument::REQUIRED,
            'The file containing names and birth dates is required.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $filename = __CAKES__ . $input->getArgument('file');

        try {
            $this->validateFile($filename);
            $this->buildCsv($filename);
            $this->buildReport();

            $io->success('Done!');
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return 1;
        }

        return 0;
    }


    /**
     * @param string $filename
     * @throws InvalidFileException
     */
    private function validateFile(string $filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new InvalidFileException('The given file does not exist or is not readable.');
        }
    }

    private function buildCsv(string $filename)
    {
        $this->reader = Reader::createFromPath($filename, 'r');
        $this->reader->setHeaderOffset(0);
    }

    private function buildReport()
    {
        //
    }
}
