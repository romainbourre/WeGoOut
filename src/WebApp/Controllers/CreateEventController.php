<?php

namespace WebApp\Controllers;


use Business\Exceptions\ValidationException;
use Business\Ports\EventCategoryRepositoryInterface;
use Business\UseCases\CreateEvent\CreateEventRequest;
use Business\UseCases\CreateEvent\CreateEventUseCase;
use DateTime;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use System\Logging\LoggerInterface;
use WebApp\Exceptions\MandatoryParamMissedException;
use WebApp\Routing\ParametersRequestExtractor;

class  CreateEventController extends AppController
{

    public function __construct(
        private readonly LoggerInterface                  $logger,
        private readonly CreateEventUseCase               $createEventUseCase,
        private readonly EventCategoryRepositoryInterface $categoryRepository
    )
    {
        parent::__construct();
    }

    public function getView(): void
    {
        $this->addJsScript('create-view.js');
        $this->addJsScript('create-date.js');
        $this->addJsScript('create-ctrl.js');
        $this->addJsScript('create-location.js');
    }

    /**
     * @return Response
     */
    public function getCreateEventForm(): Response
    {
        try {
            $eventCategories = $this->categoryRepository->all()->toArray();
            $content = $this->render('create.view-create', compact('eventCategories'));
            return $this->ok($content);
        } catch (Exception $exception) {
            $this->logger->logCritical($exception->getMessage(), $exception);
            return $this->internalServerError();
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function createEvent(Request $request): Response
    {
        try {
            $createEventRequest = $this->extractCreateEventRequest($request);
            $this->createEventUseCase->handle($createEventRequest);
            return $this->ok();
        } catch (ValidationException $e) {
            $this->logger->logTrace($e->getMessage());
            return $this->badRequest($e->getMessage());
        } catch (Exception $e) {
            $this->logger->logCritical($e->getMessage(), $e);
            return $this->internalServerError();
        }
    }

    /**
     * @throws MandatoryParamMissedException
     */
    private function extractCreateEventRequest(Request $request): CreateEventRequest
    {
        $params = new ParametersRequestExtractor($request);

        $startAtDate = $params->getOrThrow('create-event-dateBegin-field');
        $startAtTime = $params->getOrThrow('create-event-timeBegin-field');
        $startAt = DateTime::createFromFormat('d/m/Y H:i', "$startAtDate $startAtTime");

        $endAtDate = $params->get('create-event-dateEnd-field');
        $endAtTime = $params->get('create-event-timeEnd-field');
        if ($endAtDate->isEmpty() || $endAtTime->isEmpty()) {
            $endAt = null;
        } else {
            $endAt = DateTime::createFromFormat('d/m/Y H:i', "$endAtDate $endAtTime");
        }

        return new CreateEventRequest(
            title: $params->getOrThrow('create-event-title-field'),
            visibility: $params->getOrThrow('create-event-circle-switch')->asString(),
            categoryId: $params->getOrThrow('create-event-category-select')->asInt(),
            description: $params->get('create-event-desc-text')->asString(''),
            isGuestsOnly: $params->get('create-event-guest-check')->asBool(),
            participantsLimit: $params->get('create-event-participants-number')->asInt(),
            startAt: $startAt,
            endAt: $endAt,
            address: $params->getOrThrow('create-event-location-field')->asString(),
            addressDetails: $params->get('create-event-compAddress-text')->asString(''),
            postalCode: $params->getOrThrow('create-event-postal-hidden')->asString(),
            city: $params->getOrThrow('create-event-city-hidden')->asString(),
            country: $params->getOrThrow('create-event-country-hidden')->asString(),
            latitude: $params->getOrThrow('create-event-latitude-hidden')->asFloat(),
            longitude: $params->getOrThrow('create-event-longitude-hidden')->asFloat(),
        );
    }
}
