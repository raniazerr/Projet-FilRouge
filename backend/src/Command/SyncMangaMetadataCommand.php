<?php

namespace App\Command;

use App\Repository\MangaRepository;
use App\Service\MangaApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;

#[AsCommand(
    name: 'app:manga:sync-metadata',
    description: 'Renseigne auteurs, volumes et statut des mangas depuis la source externe',
)]
class SyncMangaMetadataCommand extends Command
{
    // La source limite le debit : on espace les appels.
    private const PAUSE_ENTRE_APPELS_US = 400_000;

    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaApiService $api,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'tout',
            null,
            InputOption::VALUE_NONE,
            'Rafraichit egalement les mangas deja renseignes'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tout = (bool) $input->getOption('tout');

        $aTraiter = array_filter(
            $this->mangaRepository->findAll(),
            fn($manga) => $tout || null === $manga->getAuteurs()
        );

        if (!$aTraiter) {
            $io->success('Rien a faire : tous les mangas sont deja renseignes.');

            return Command::SUCCESS;
        }

        $io->progressStart(count($aTraiter));
        $misAJour = 0;
        $echecs = [];

        foreach ($aTraiter as $manga) {
            try {
                $apiData = $this->api->getManga($manga->getApiId())['data'] ?? null;

                if (null === $apiData) {
                    $echecs[] = sprintf('%s (api_id %d) : reponse sans donnees', $manga->getTitre(), $manga->getApiId());
                } else {
                    $manga->setAuteurs(array_map(fn($a) => $a['name'], $apiData['authors'] ?? []));
                    $manga->setVolumes($apiData['volumes'] ?? null);
                    $manga->setStatut($apiData['status'] ?? null);
                    ++$misAJour;
                }
            } catch (HttpClientExceptionInterface $e) {
                $echecs[] = sprintf('%s (api_id %d) : %s', $manga->getTitre(), $manga->getApiId(), $e->getMessage());
            }

            $io->progressAdvance();
            usleep(self::PAUSE_ENTRE_APPELS_US);
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success(sprintf('%d manga(s) mis a jour.', $misAJour));

        if ($echecs) {
            $io->warning(sprintf('%d echec(s), a relancer plus tard :', count($echecs)));
            $io->listing($echecs);
        }

        return Command::SUCCESS;
    }
}
