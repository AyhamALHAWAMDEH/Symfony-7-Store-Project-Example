<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findFilteredProducts($searchTerm, $origin, $brand, $maxPrice, $minPrice, $category, $order, $orderBy)
    {
        $qb = $this->createQueryBuilder('p');

        if ($searchTerm) {
            $qb->orWhere('p.productName LIKE :term')
                ->orWhere('p.shortDescription LIKE :term')
                ->orWhere('p.longDescription LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        }

        if ($origin) {
            $qb->andWhere('p.productOrigin = :origin')
                ->setParameter('origin', $origin);
        }

        if ($brand) {
            $qb->andWhere('p.productBrand = :brand')
                ->setParameter('brand', $brand);
        }

        if ($maxPrice) {
            $qb->andWhere('p.productPrice <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        if ($minPrice) {
            $qb->andWhere('p.productPrice >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($category) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }

        if ($order && in_array($order, ['asc', 'desc']) && $orderBy) {
            $direction = $order == 'desc' ? 'DESC' : 'ASC';
            $qb->orderBy('p.' . $orderBy, $direction);
        }

        return $qb->getQuery()->getResult();
    }

    public function findProductsWithOffers()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.offer IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
