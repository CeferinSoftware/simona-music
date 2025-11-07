<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations;

use App\Controller\SingleActionInterface;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class GetBrandingAction implements SingleActionInterface
{
    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $station = $request->getStation();
        $branding = $station->branding_config;

        return $response->withJson([
            'primary_color' => $branding->primary_color,
            'secondary_color' => $branding->secondary_color,
            'background_color' => $branding->background_color,
            'text_color' => $branding->text_color,
            'logo_url' => $branding->logo_url,
            'public_custom_css' => $branding->public_custom_css,
            'public_custom_js' => $branding->public_custom_js,
            'offline_text' => $branding->offline_text,
            'default_album_art_url' => $branding->default_album_art_url,
        ]);
    }
}
