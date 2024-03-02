<?php

namespace App\Repository;

use App\Entity\FavoriteItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FavoriteItem>
 *
 * @method FavoriteItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method FavoriteItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method FavoriteItem[]    findAll()
 * @method FavoriteItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavoriteItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FavoriteItem::class);
    }

//    /**
//     * @return FavoriteItem[] Returns an array of FavoriteItem objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FavoriteItem
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
