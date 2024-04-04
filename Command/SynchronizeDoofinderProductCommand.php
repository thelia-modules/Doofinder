<?php

namespace Doofinder\Command;

use Doofinder\Service\DoofinderService;
use Doofinder\Shared\Exceptions\ApiException;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Log\Tlog;

class SynchronizeDoofinderProductCommand extends ContainerAwareCommand
{
    public function __construct(
        protected DoofinderService $doofinderService,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('module:doofinder:synchronize')
            ->setDescription('Synchronize product with Doofinder API');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initRequest();

        $output->write("Product synchronization start\n");

        try {
            $results = $this->doofinderService->synchronizeDoofinderProducts();

            $output->write($results);
        } catch (ApiException|PropelException $e) {
            Tlog::getInstance()->error($e->getMessage()." : ". $e->getBody());
            $output->write("Product synchronization Failed\n");
            $output->write("Erreur " .$e->getMessage()." : ". $e->getBody() );
        }

        $output->write("End of Product synchronization\n");

        return 0;
    }
}