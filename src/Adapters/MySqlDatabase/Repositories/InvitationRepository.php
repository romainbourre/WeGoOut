<?php

namespace Adapters\MySqlDatabase\Repositories;

use Business\Exceptions\DatabaseErrorException;
use Business\Ports\InvitationRepositoryInterface;
use PDO;

readonly class InvitationRepository implements InvitationRepositoryInterface
{


    public function __construct(private PDO $database)
    {
    }

    /**
     * @throws DatabaseErrorException
     */
    public function isGuestOfEvent(int $userId, string $eventId): bool
    {
        $request = $this->database->prepare('SELECT count(*) isInvited FROM GUEST WHERE user_id = :userId AND event_id = :eventId AND GUEST_DATETIME_DELETE is null');
        $request->bindValue(':userId', $userId);
        $request->bindValue(':eventId', $eventId);

        if (!$request->execute()) {
            $errorMessage = self::mapPDOErrorToString($request->errorInfo());
            throw new DatabaseErrorException($errorMessage);
        }

        return ($result = $request->fetch()) && isset($result['isInvited']) && $result['isInvited'] > 0;
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
