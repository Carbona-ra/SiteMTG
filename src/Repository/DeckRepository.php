<?php

namespace App\Repository;

use App\Entity\Deck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deck>
 */
class DeckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deck::class);
    }



    public function findByCritaire(array $critaire): array
    {
        $qb = $this->createQueryBuilder('d');

        if (!empty($critaire['name'])) {
            $qb->andWhere('d.name LIKE :name')
               ->setParameter('name', '%'.$critaire['name'].'%');
        }

        if (!empty($critaire['commanderName'])) {
            $qb->andWhere('d.commanderName LIKE :commanderName')
               ->setParameter('commanderName', '%'.$critaire['commanderName'].'%');
        }

        if (!empty($critaire['Creator'])) {
            $qb->join('d.Creator', 'c')
               ->andWhere('c.id = :creatorId')
               ->setParameter('creatorId', $critaire['Creator']->getId());
        }

        return $qb->getQuery()->getResult();
    }

    

//    /**
//     * @return Deck[] Returns an array of Deck objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Deck
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
