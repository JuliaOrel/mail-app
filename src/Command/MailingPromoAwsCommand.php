<?php

namespace App\Command;

use App\Entity\Mailing;
use App\Entity\MailingItem;
use App\Entity\UserClients;
use App\Form\Handler\MailingFormHandler;
use App\Repository\MailingItemRepository;
use App\Repository\MailingRepository;
use App\Repository\UserClientsRepository;
use App\Utils\Manager\MailingManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\Persistence\ManagerRegistry;

class MailingPromoAwsCommand extends Command
{
    const TYPE_ARG = "test";
    const SLEEP = 1;
    const TYPE_MARKER_MAIL = "promo-aws";
    protected static $defaultName = 'app:mailing:send:mail-promo-aws';
    protected static $defaultDescription = 'Mailing news and promo for men';

    private $customConnect;
    private $mailing;
    private $mailingItem;
    private $userClientsRepository;
    private $mailingFormHandler;
    private $mailingManager;
    public function __construct(ManagerRegistry $doctrine, 
                                MailingRepository $mailing, 
                                MailingItemRepository $mailingItem, 
                                UserClientsRepository $userClientsRepository,
                                MailingFormHandler $mailingFormHandler,
                                MailingManager $mailingManager) 
    {
        $this->customConnect = $doctrine->getConnection('customer');
        $this->mailing = $mailing;
        $this->mailingItem = $mailingItem;
        $this->userClientsRepository = $userClientsRepository;
        $this->mailingFormHandler = $mailingFormHandler;
        $this->mailingManager = $mailingManager;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $isTest = false;
        if ($arg1 && $arg1 == self::TYPE_ARG) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
            $isTest = true;
        }

        $m = $this->mailingManager->getMailingPromoCronTask();
        if (!$m) {
            $io->success("Cron PROMO doesn't have a task");
            return Command::SUCCESS;
        }
        $us = new UserClients();
        
        if ($isTest) {
            $io->note("It is test");
            $uss = $us->getUsersForMailingPromo($this->customConnect, $this->userClientsRepository, [200000, 274755, 102449, 376650, 117355]);
        } else {
            $uss = $us->getUsersForMailingPromo($this->customConnect, $this->userClientsRepository);
        }
        $emails = array();
        $marker = $this->mailingFormHandler->createMarkerEmailUTM(self::TYPE_MARKER_MAIL);
        $this->mailingManager->saveMailingStatus($m, MailingManager::MAILING_STATUS_BUSY);
        foreach ($uss as $key => $value) {
            $email = strtolower($value['user_email']);
            if (!empty($emails[$email])) {
              continue;
            }
            $emails[$email] = $email;
            $isActive = $us->userIsActive($this->customConnect, $this->userClientsRepository, $value["user_id"]);
            if (!$isActive) {
                continue;
            }
            $isUnsubscribeNews = $us->deliveryCheckUnsubscribeNews($this->customConnect, $this->userClientsRepository, $value["user_id"]);
            if ($isUnsubscribeNews) {
                $this->mailingFormHandler->userUnsubscribeMailing($m, $value);
                continue;
            }
            $hashUnsub = $us->deliveryGetUnsubscribeUserHash($this->customConnect, $this->userClientsRepository, $value["user_id"]);
            if (!$hashUnsub) {
              continue;
            }
            $unsubsribeUrl = $us->mailPromoGetUrlUnsub($hashUnsub);
            $uid = $value["user_id"];
            $fname = $value["user_name"];
            $lname = $value["user_surname"];
            $data = [
                "user_id" => $value["user_id"],
                "user_name" => $value["user_name"],
                "user_surname" => $value["user_surname"],
                "user_email" => $email,
                "unsubsribe_url" => $unsubsribeUrl,
                "hash_unsub" => $hashUnsub,
                "pixel" => $this->mailingManager->createUrlPixel($value["user_id"], $m->getId()),
                "marker" => $marker
            ];
            $io->note(sprintf('User %s: %s', $value["user_id"], $email));
            $this->mailingFormHandler->processSendMailing($m, $data);
            sleep(self::SLEEP);
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        $this->mailingManager->saveMailingStatus($m, MailingManager::MAILING_STATUS_FINISHED);

        return Command::SUCCESS;
    }
}
