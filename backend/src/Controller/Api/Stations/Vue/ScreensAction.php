<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations\Vue;

use App\Controller\SingleActionInterface;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class ScreensAction implements SingleActionInterface
{
    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        return $response->withJson([
            'title' => __('Gestionar Pantallas'),
        ]);
    }
}
