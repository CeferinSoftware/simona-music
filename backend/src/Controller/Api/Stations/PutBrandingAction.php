<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations;

use App\Container\EntityManagerAwareTrait;
use App\Controller\SingleActionInterface;
use App\Entity\StationBrandingConfiguration;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class PutBrandingAction implements SingleActionInterface
{
    use EntityManagerAwareTrait;

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $station = $request->getStation();
        $parsedBody = (array)$request->getParsedBody();

        // Update branding configuration
        $branding = $station->branding_config;
        
        if (isset($parsedBody['primary_color'])) {
            $branding->primary_color = $parsedBody['primary_color'];
        }
        if (isset($parsedBody['secondary_color'])) {
            $branding->secondary_color = $parsedBody['secondary_color'];
        }
        if (isset($parsedBody['background_color'])) {
            $branding->background_color = $parsedBody['background_color'];
        }
        if (isset($parsedBody['text_color'])) {
            $branding->text_color = $parsedBody['text_color'];
        }
        if (isset($parsedBody['logo_url'])) {
            $branding->logo_url = $parsedBody['logo_url'];
        }
        if (isset($parsedBody['public_custom_css'])) {
            $branding->public_custom_css = $parsedBody['public_custom_css'];
        }

        $station->branding_config = $branding;
        
        $this->em->persist($station);
        $this->em->flush();

        return $response->withJson([
            'success' => true,
            'message' => __('Station branding updated successfully.'),
        ]);
    }
}
