<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

/**
 * @template C of object
 */
interface DatabaseConnectionInterface
{
    /**
     * Gets the database connection object used by the EntityManager.
     *
     * @return C
     */
    public function getConnection();
}
