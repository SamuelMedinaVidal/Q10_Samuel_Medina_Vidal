<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Buscar productos por nombre o categoría
     */
    public function searchByNameOrCategory(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.activo = :activo')
            ->andWhere('p.cantidad > 0')
            ->andWhere('p.nombre LIKE :query OR p.categoria LIKE :query')
            ->setParameter('activo', true)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener productos activos con stock
     */
    public function findActiveWithStock(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.activo = :activo')
            ->andWhere('p.cantidad > 0')
            ->setParameter('activo', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener productos por categoría
     */
    public function findByCategory(string $categoria): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.activo = :activo')
            ->andWhere('p.cantidad > 0')
            ->andWhere('p.categoria = :categoria')
            ->setParameter('activo', true)
            ->setParameter('categoria', $categoria)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener productos de un vendedor específico
     */
    public function findByVendedor(User $vendedor): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.vendedor = :vendedor')
            ->setParameter('vendedor', $vendedor)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener categorías únicas
     */
    public function findAllCategories(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('DISTINCT p.categoria')
            ->where('p.activo = :activo')
            ->setParameter('activo', true)
            ->orderBy('p.categoria', 'ASC')
            ->getQuery()
            ->getResult();

        return array_column($result, 'categoria');
    }
}
