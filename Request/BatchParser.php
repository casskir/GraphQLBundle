<?php

/*
 * This file is part of the OverblogGraphQLBundle package.
 *
 * (c) Overblog <http://github.com/overblog/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Overblog\GraphQLBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BatchParser implements ParserInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function parse(Request $request)
    {
        // Extracts the GraphQL request parameters
        $data = $this->getParsedBody($request);

        if (empty($data)) {
            throw new BadRequestHttpException('Must provide at least one valid query.');
        }

        foreach ($data as $i => &$entry) {
            if (empty($entry[static::PARAM_QUERY]) || !is_string($entry[static::PARAM_QUERY])) {
                throw new BadRequestHttpException(sprintf('No valid query found in node "%s"', $i));
            }

            $entry = $entry + [
                static::PARAM_VARIABLES => null,
                static::PARAM_OPERATION_NAME => null,
            ];
        }

        return $data;
    }

    /**
     * Gets the body from the request.
     *
     * @param Request $request
     *
     * @return array
     */
    private function getParsedBody(Request $request)
    {
        $type = explode(';', $request->headers->get('content-type'))[0];

        // JSON object
        if ($type !== static::CONTENT_TYPE_JSON) {
            throw new BadRequestHttpException(sprintf('Only request with content type "%" is accepted.', static::CONTENT_TYPE_JSON));
        }

        $parsedBody = json_decode($request->getContent(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new BadRequestHttpException('POST body sent invalid JSON');
        }

        return $parsedBody;
    }
}
