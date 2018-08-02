<?php

namespace App\Command;

use App\Entity\Supporter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AppImportSupportersCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:import-supporters';

    protected function configure()
    {
        $this
            ->setDescription('Importeer csv met geboortedatum en lidnummer van de supoprter')
            ->addArgument('file', InputArgument::REQUIRED, 'link to csv file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');
        $em = $this->getContainer()->get('doctrine')->getManager();

        if ($file) {
            $io->note(sprintf('File being imported: %s', $file));
            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $data = $serializer->decode(file_get_contents($file), 'csv');
            $duplicates = 0;
            $rep = $em->getRepository(Supporter::class);

            foreach ($data as $index => $row) {
                $supporter = new Supporter();
                $row = explode(';', reset($row));
                $supporter->setBirthDate(new \DateTime($row[0]));
                $supporter->setSupporterId($row[1]);
                // check for duplicate entry
                $existingSupporter = $rep->findOneBy([
                    'supporterId' => $supporter->getSupporterId(),
                ]);
                if (!empty($existingSupporter)) {
                    $duplicates++;
                    continue;
                }
                $em->persist($supporter);
            }
            $em->flush();
            $io->note(sprintf('There were %s duplicate supporterIds', $duplicates));
            $io->success('Supporters have been successfully imported!');
        }

    }
}
