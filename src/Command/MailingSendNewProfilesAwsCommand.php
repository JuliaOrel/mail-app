<?php

namespace App\Command;

use App\Entity\UserClients;
use App\Form\Handler\MailingFormHandler;
use App\Repository\MailingItemRepository;
use App\Repository\MailingRepository;
use App\Repository\UserClientsRepository;
use App\Utils\Manager\MailingManager;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailingSendNewProfilesAwsCommand extends Command
{
    const TYPE_ARG = "test";
    const SLEEP = 1;
    const TYPE_MARKER_MAIL = "new-profiles-aws";
    protected static $defaultName = 'app:mailing:send:new-profiles-aws';
    protected static $defaultDescription = 'Mailing send info about new profiles from veronikalove for men';

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

        $m = $this->mailingManager->getMailingNewProfilesCronTask();
        $marker = $this->mailingFormHandler->createMarkerEmailUTM(self::TYPE_MARKER_MAIL); 
        
        if (!$m) {
            $io->success("Cron NEW PROFILES doesn't have a task");
            return Command::SUCCESS;
        }
        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }
        $us = new UserClients();
        
        if ($isTest) {
            $io->note("It is test");
            $uss = $us->getUsersForMailingNewProfiles($this->customConnect, $this->userClientsRepository, [200000, 274755, 102449, 376650, 117355]);
        } else {
            $uss = $us->getUsersForMailingNewProfiles($this->customConnect, $this->userClientsRepository);
        }
        if (empty($uss)) {
            $io->warning('Mailing does not have users for new profiles');
            return Command::INVALID;
        }
        $ladies = $us->getNewProfilesLadies($this->customConnect, $this->userClientsRepository);
        if (empty($uss)) {
            $io->warning('Mailing does not have ladies for new profiles');
            return Command::INVALID;
        }
        $emails = array();
        if (!$isTest) {
            $date = new DateTimeImmutable();
            $date->format('Y-m-d H:i:s');     
            $m->setScheduledAt($date);
            $this->mailingManager->saveMailingStatus($m, MailingManager::MAILING_STATUS_BUSY);
        }
        
        $cnt = 0;
        foreach ($uss as $key => $man) {
            $email = strtolower($man['user_email']);
            if (!empty($emails[$email])) {
              continue;
            }
            $emails[$email] = $email;
            $uid = $man['user_id'];
            $email = $man['user_email'];
            $fn = (!empty($man["user_name"]))?$man["user_name"]:"";
            $ln = (!empty($man["user_surname"]))?$man["user_surname"]:"";
            $isActive = $us->userIsActive($this->customConnect, $this->userClientsRepository, $uid);
            if (!$isActive) {
                continue;
            }
            $isUnsubscribeNewMails = $us->deliveryCheckUnsubscribeNews($this->customConnect, $this->userClientsRepository, $uid);
            if ($isUnsubscribeNewMails) {
                $this->mailingFormHandler->userUnsubscribeMailing($m, $man);
                continue;
            }
            $hashUnsub = $us->deliveryGetUnsubscribeUserHashNewProfiles($this->customConnect, $this->userClientsRepository, $man["user_id"]);
            if (!$hashUnsub) {
              continue;
            }
            $sent = $this->mailingManager->isMailingItemSent($uid, $email, $m->getId());
            if ($sent) {
                $io->note(sprintf('User already sent %s: %s', $uid, $email));
                continue;
            }
            $unsubsribeUrl = $us->mailPromoGetUrlUnsub($hashUnsub);
            $data = [
                "ladies" => $ladies,
                "user_id" => $man["user_id"],
                "user_name" => $man["user_name"],
                "user_surname" => $man["user_surname"],
                "user_email" => $email,
                "unsubsribe_url" => $unsubsribeUrl,
                "hash_unsub" => $hashUnsub,
                "pixel" => $this->mailingManager->createUrlPixel($man["user_id"], $m->getId()),
                "marker" => $marker
            ];     
            $this->mailingFormHandler->processSendMailingNewMails($m, $data);
            $cnt++;
            $io->note(sprintf('User %s: %s ', $uid, $email));
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        if (!$isTest) {
            $date = new DateTimeImmutable();
            $date->format('Y-m-d H:i:s');     
            $m->setScheduledAt($date);
            $m->setQuantity($cnt);
            $this->mailingManager->saveMailingStatus($m, MailingManager::MAILING_STATUS_FINISHED);
            $ma = $this->mailingManager->getMailingNewMailsCronTask();
            if (!$ma) {
                $m->setQuantity(0);
                $date->modify('+1 day'); 
                $m->setScheduledAt($date);
                $this->mailingManager->createMailingCloneStatus($m, MailingManager::MAILING_STATUS_ACTIVE);
            }
        }

        return Command::SUCCESS;
    }
}
