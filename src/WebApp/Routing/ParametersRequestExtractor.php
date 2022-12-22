<?php

namespace WebApp\Routing;

use Slim\Psr7\Request;
use WebApp\Exceptions\MandatoryParamMissedException;

readonly class ParametersRequestExtractor
{

    public function __construct(private Request $request)
    {
    }

    public function get(string $name): NullableParameter
    {
        return new NullableParameter($this->extractValue($name));
    }

    private function extractValue(string $name): ?string
    {
        $bodyParameters = $this->request->getParsedBody() ?? [];
        $queryParameters = $this->request->getQueryParams() ?? [];
        $params = array_merge($bodyParameters, $queryParameters);
        if (!isset($params[$name])) {
            return null;
        }
        return htmlspecialchars($params[$name]);
    }

    /**
     * @throws MandatoryParamMissedException
     */
    public function getOrThrow(string $name): NonNullParameter
    {
        $value = $this->extractValue($name);
        if (empty($value)) {
            throw new MandatoryParamMissedException($name);
        }
        return new NonNullParameter($value);
    }
}
