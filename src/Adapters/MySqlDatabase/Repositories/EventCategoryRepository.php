<?php

namespace Adapters\MySqlDatabase\Repositories;

use Business\Entities\EventCategory;
use Business\Exceptions\DatabaseErrorException;
use Business\Ports\EventCategoryRepositoryInterface;
use PDO;
use PhpLinq\Interfaces\ILinq;
use PhpLinq\PhpLinq;

readonly class EventCategoryRepository implements EventCategoryRepositoryInterface
{


    public function __construct(private PDO $context)
    {
    }

    /**
     * @throws DatabaseErrorException
     */
    public function getById(int $id): ?EventCategory
    {
        $request = $this->context->prepare('SELECT CAT_ID as id, CAT_NAME as name FROM CATEGORY WHERE CAT_ID = :id');
        if (!$request->execute(['id' => $id])) {
            $errorMessage = self::mapPDOErrorToString($request->errorInfo());
            throw new DatabaseErrorException($errorMessage);
        }
        if (!$result = $request->fetch(PDO::FETCH_ASSOC)) {
            return null;
        }
        return new EventCategory($result['id'], $result['name']);
    }

    /**
     * @return ILinq<EventCategory>
     * @throws DatabaseErrorException
     */
    public function all(): ILinq
    {
        $request = $this->context->prepare('SELECT CAT_ID as id, CAT_NAME as name FROM CATEGORY');
        if (!$request->execute()) {
            $errorMessage = self::mapPDOErrorToString($request->errorInfo());
            throw new DatabaseErrorException($errorMessage);
        }

        $categories = new PhpLinq();
        while ($result = $request->fetch(PDO::FETCH_ASSOC)) {
            $category = new EventCategory($result['id'], $result['name']);
            $categories->add($category);
        }
        return $categories;
    }

    private static function mapPDOErrorToString(array $pdoError): string
    {
        $errorString = '';
        foreach ($pdoError as $error) {
            $errorString .= "$error ";
        }
        return $errorString;
    }

}
