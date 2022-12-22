<?php

namespace Adapters\MySqlDatabase\Repositories;

use Business\Exceptions\DatabaseErrorException;
use Business\Ports\ParticipationRepositoryInterface;
use PDO;

class ParticipationRepository implements ParticipationRepositoryInterface
{


    public function __construct(private readonly PDO $databaseContext)
    {
    }

    /**
     * @throws DatabaseErrorException
     */
    public function isUserParticipantOfEvent(string $userId, string $eventId): bool
    {
        $request = $this->databaseContext->prepare('SELECT COUNT(*) as exist FROM PARTICIPATE WHERE event_id = :eventId AND user_id = :userId AND PART_DATETIME_ACCEPT IS NOT NULL AND PART_DATETIME_DELETE IS NULL');
        $request->bindValue(':eventId', $eventId);
        $request->bindValue(':userId', $userId);

        if (!$request->execute()) {
            $errorMessage = self::mapPDOErrorToString($request->errorInfo());
            throw new DatabaseErrorException($errorMessage);
        }

        return ($result = $request->fetch()) && isset($result['exist']) && $result['exist'] > 0;
    }

    private static function mapPDOErrorToString(array $pdoError): string
    {
        $errorString = '';
        foreach ($pdoError as $error) {
            $errorString .= "$error ";
        }

        return $errorString;
    }

    /**
     * @throws DatabaseErrorException
     */
    public function isUserAwaitingParticipantOfEvent(string $userId, string $eventId): bool
    {
        $request = $this->databaseContext->prepare('SELECT COUNT(*) as exist FROM PARTICIPATE WHERE event_id = :eventId AND user_id = :userId AND PART_DATETIME_ACCEPT IS NULL AND PART_DATETIME_DELETE IS NULL');
        $request->bindValue(':eventId', $eventId);
        $request->bindValue(':userId', $userId);

        if (!$request->execute()) {
            $errorMessage = self::mapPDOErrorToString($request->errorInfo());
            throw new DatabaseErrorException($errorMessage);
        }

        return ($result = $request->fetch()) && isset($result['exist']) && $result['exist'] > 0;
    }
}
