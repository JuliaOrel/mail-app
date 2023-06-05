<?php

namespace App\Repository;

use App\Entity\UserClients;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserClients>
 *
 * @method UserClients|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserClients|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserClients[]    findAll()
 * @method UserClients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserClientsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserClients::class);
    }
    
    /**
     * @return array[] Returns an array of UserClients objects
     */
    public function getUsersForMailingNewMails($customConnect, array $users = null): array
    {
    $usersStr = "";
    if ($users) {            
        $usersStr = implode(",", $users);
        $usersStr = ' AND A.user_id IN(' . $usersStr . ')';
    }
        $sql = "SELECT A.user_id, A.user_name, A.user_surname, A.user_email, C.unsubscribe_daily,  A.user_visible 
            FROM bm_users A
            LEFT JOIN bm_users_unsubscribe AS C ON A.user_id = C.user_id
            RIGHT JOIN bm_users_confirmed AS D ON A.user_id = D.user_id AND D.confirmed_date IS NOT NULL
            WHERE A.user_gender =1 
                {$usersStr}
                AND A.user_status = 0
                AND (C.unsubscribe_message IS NULL OR C.unsubscribe_message = 0)
            GROUP BY A.user_id";
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    
    /**
     * @return array[] Returns an array of UserClients objects
    */
    public function getUsersForMailingNewProfiles($customConnect, array $users = null): array
    {
        $usersStr = "";
        if ($users) {            
            $usersStr = implode(",", $users);
            $usersStr = ' AND A.user_id IN(' . $usersStr . ')';
        }
        $sql = "SELECT A.user_id, A.user_name, A.user_surname, A.user_email, C.unsubscribe_daily
            FROM bm_users A
            LEFT JOIN bm_users_unsubscribe AS C ON A.user_id = C.user_id
            RIGHT JOIN bm_users_confirmed AS D ON A.user_id = D.user_id AND D.confirmed_date IS NOT NULL
            WHERE A.user_gender =1 {$usersStr}
            AND A.user_status =0
            AND (C.unsubscribe_daily IS NULL OR C.unsubscribe_daily = 0)";
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }

    
    /**
     * @return array[] Returns an array of UserClients objects
    */
    function getNewProfilesLadies($customConnect){
        $sql = "SELECT 
                    A.user_id as id, 
                    A.user_name as FirstName,
                    C.info_birthday as b_date,
                    A.user_avatar as photo
                    FROM bm_users A
                    RIGHT JOIN bm_users_info AS C ON A.user_id=C.user_id
                    WHERE A.user_gender=2 AND A.user_status=0 
                    ORDER BY A.user_id DESC
                    LIMIT 10";
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    
   /**
    * @return array[] Returns an array of UserClients objects
    */
    public function getUsersForMailingPromo($customConnect): array
    {
        $sql = 'SELECT A.user_id, A.user_name, A.user_surname, A.user_email, C.unsubscribe_news
            FROM bm_users A
            LEFT JOIN bm_users_unsubscribe AS C ON A.user_id = C.user_id
            RIGHT JOIN bm_users_confirmed AS D ON A.user_id = D.user_id AND D.confirmed_date IS NOT NULL
            WHERE A.user_gender = 1
            AND A.user_status = 0 AND (C.unsubscribe_news IS NULL OR C.unsubscribe_news = 0)';
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    
    /**
     * @return array[] Returns an array of UserClients objects
     */
    public function getUsersForDefUsersMailingPromo($customConnect, array $users): array
    {
        if (!$users) {
            return [];
        }
        $usersStr = implode(",", $users);
        $sql = 'SELECT A.user_id, A.user_name, A.user_surname, A.user_email, C.unsubscribe_news
            FROM bm_users A
            LEFT JOIN bm_users_unsubscribe AS C ON A.user_id = C.user_id
            RIGHT JOIN bm_users_confirmed AS D ON A.user_id = D.user_id AND D.confirmed_date IS NOT NULL
            WHERE A.user_gender = 1 AND A.user_id IN(' . $usersStr . ')
            AND A.user_status = 0
                AND (C.unsubscribe_news IS NULL OR C.unsubscribe_news = 0)';
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }
    
    /**
     * @return array[] Returns an array of Ladies objects
     */
     public function getNewMailsLadies($customConnect, int $uid, string $fn, string $ln): array
     {
        $ladies = array();
        $sql = 'SELECT mcuft.user_from, bmu.user_avatar, bmu.user_name, mcuft.mct_id, mcuft.last_id
                    FROM `messages_counter_user_from_to` AS mcuft
                    LEFT JOIN bm_users AS bmu ON bmu.user_id = mcuft.user_from
                    WHERE mcuft.user_to = :uid
                    AND mcuft.mct_id IN ( 2, 11 )
                    AND mcuft.last_date > ( NOW( ) - INTERVAL 60 HOUR )
                    ORDER BY mcuft.last_date DESC
                    LIMIT 8';
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery(["uid" => $uid]);
        $mailsInfo = $resultSet->fetchAllAssociative();

        foreach ($mailsInfo as $k => $mail) {
            if ($k > 3) {
              continue;
            }
            $mctId = (int) $mail['mct_id'];
            $lastId = (int) $mail['last_id'];
            if ($mctId == 2) {
              $messageSql = "SELECT mail_subject AS subject, mail_date, mail_attach
                                    FROM bm_mails
                                    WHERE mail_id = :last_id";
            } elseif ($mctId == 11) {
              $messageSql = "SELECT imm.subject, imm.created_act AS mail_date, ima.name AS mail_attach
                                FROM introductional_mail_messages as imm
                                LEFT JOIN introductional_mail_users as imu
                                ON imm.message_id = imu.message_id
                                LEFT JOIN introductional_mail_attachments as ima
                                ON imm.message_id = ima.message_id
                                WHERE imu.id = :last_id";
            } else {
                continue;
            }
            $stmt = $customConnect->prepare($messageSql);
            $resultSet = $stmt->executeQuery(["last_id" => $lastId]);
            $message = $resultSet->fetchAllAssociative();
            $message = reset($message);
            $subject = (!empty($message["subject"]))?$message["subject"]:"";
            if (empty($subject)) {
                continue;
            }
            $ladies[$k] = [];
            $ladies[$k]['subject'] = UserClients::formatIntroductionalMsg($subject, $fn, $ln);            
            list($tmp_date, $ladies[$k]['time']) = explode(" ", $message['mail_date']);
            $date = new DateTime($tmp_date);
            $ladies[$k]['date'] = $date->format('d F Y');
            $ladies[$k]['lady_id'] = $mail['user_from'];
            $ladies[$k]['lady_id2'] = number_format($mail['user_from'], 0, '', ' ');
            $ladies[$k]['lady_name'] = $mail['user_name'];
            $ladies[$k]['lady_avatar'] = "https://veronikalove.com/images/frontend/avatars/big/" . $mail['user_avatar'];
            if (!empty($message->mail_attach)) {
                $ladies[$k]['attachment_type'] = UserClients::checkTypeAttach($message->mail_attach);
            }
            else {
                $ladies[$k]['attachment_type'] = 1;
            }
        }
        
        return $ladies;
    }
    
    /**
     * @return int Returns int
     */
     public function getUserStatus($customConnect, int $uid): int
     {
         $sql = 'SELECT user_id FROM bm_users WHERE user_gender =1 AND user_status = 0 AND user_id = :uid LIMIT 1';
         $stmt = $customConnect->prepare($sql);
         $resultSet = $stmt->executeQuery(["uid" => $uid]);
         return $resultSet->fetchOne();
     }
    
     /**
     * @return int Returns int
      */
      public function getUserUnsubscribeNews($customConnect, int $uid): int
      {
        $sql = 'SELECT user_id FROM bm_users_unsubscribe WHERE unsubscribe_news = 1 AND user_id = :uid LIMIT 1';
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery(["uid" => $uid]);
        return $resultSet->fetchOne();
      }
    
     /**
     * @return int Returns int
      */
      public function getUserUnsubscribeNewMails($customConnect, int $uid): int
      {
        $sql = 'SELECT user_id FROM bm_users_unsubscribe WHERE unsubscribe_message = 1 AND user_id = :uid LIMIT 1';
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery(["uid" => $uid]);
        return $resultSet->fetchOne();
      }
    
      /**
      * @return string Returns string
       */
       public function getUserUnsubscribeHash($customConnect, int $uid): string
       {
        $sql = "SELECT `user_hash` FROM bm_users_unsubscribe_email WHERE user_id = :uid AND type_unsubscribe = 'unsubscribe_news'";
        $stmt = $customConnect->prepare($sql);
        $resultSet = $stmt->executeQuery(["uid" => $uid]);
        return $resultSet->fetchOne();
       }
    
       /**
       * @return string Returns string
        */
        public function getUnsubscribeUserHashNewMails($customConnect, int $uid): string
        {
         $sql = "SELECT `user_hash` FROM bm_users_unsubscribe_email WHERE user_id = :uid AND type_unsubscribe = 'unsubscribe_message'";
         $stmt = $customConnect->prepare($sql);
         $resultSet = $stmt->executeQuery(["uid" => $uid]);
         return $resultSet->fetchOne();
        }
    
        /**
        * @return string Returns string
         */
         public function getUnsubscribeUserHashNewProfiles($customConnect, int $uid): string
         {
          $sql = "SELECT `user_hash` FROM bm_users_unsubscribe_email WHERE user_id = :uid AND type_unsubscribe = 'unsubscribe_daily'";
          $stmt = $customConnect->prepare($sql);
          $resultSet = $stmt->executeQuery(["uid" => $uid]);
          return $resultSet->fetchOne();
         }

//    public function findOneBySomeField($value): ?UserClients
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
