<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin\Vue;

use App\Controller\SingleActionInterface;
use App\Entity\Enums\AdCategories;
use App\Entity\Enums\AdMediaType;
use App\Entity\Enums\AdStatus;
use App\Entity\Station;
use App\Http\Response;
use App\Http\ServerRequest;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Proporciona los datos necesarios para el formulario de anuncios en Vue.
 */
final class AdvertisementsAction implements SingleActionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        // Obtener lista de estaciones/terrazas para selección
        $stations = $this->em->createQueryBuilder()
            ->select('s.id', 's.name', 's.short_name', 's.ad_category', 's.province', 's.city', 's.is_enabled')
            ->from(Station::class, 's')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $response->withJson([
            'categories' => AdCategories::getOptions(),
            'mediaTypes' => AdMediaType::getOptions(),
            'statuses' => AdStatus::getOptions(),
            'stations' => $stations,
            'weekDays' => [
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
                7 => 'Domingo',
            ],
            'priorityOptions' => [
                1 => 'Muy Baja',
                2 => 'Baja',
                3 => 'Normal',
                5 => 'Alta',
                7 => 'Muy Alta',
                10 => 'Máxima',
            ],
            'spanishProvinces' => [
                'Álava', 'Albacete', 'Alicante', 'Almería', 'Asturias', 'Ávila',
                'Badajoz', 'Barcelona', 'Burgos', 'Cáceres', 'Cádiz', 'Cantabria',
                'Castellón', 'Ciudad Real', 'Córdoba', 'Cuenca', 'Gerona', 'Granada',
                'Guadalajara', 'Guipúzcoa', 'Huelva', 'Huesca', 'Islas Baleares',
                'Jaén', 'La Coruña', 'La Rioja', 'Las Palmas', 'León', 'Lérida',
                'Lugo', 'Madrid', 'Málaga', 'Murcia', 'Navarra', 'Orense', 'Palencia',
                'Pontevedra', 'Salamanca', 'Santa Cruz de Tenerife', 'Segovia',
                'Sevilla', 'Soria', 'Tarragona', 'Teruel', 'Toledo', 'Valencia',
                'Valladolid', 'Vizcaya', 'Zamora', 'Zaragoza'
            ],
        ]);
    }
}
